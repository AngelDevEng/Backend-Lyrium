Plan: Mesa de Ayuda Completa — Imágenes + Limpieza Legacy + Paginación

 Contexto

 El módulo de mesa de ayuda (seller + admin) tiene el CRUD funcional y WebSocket en tiempo real, pero le 
  faltan 3 cosas para estar completo:
 1. Subida de imágenes — modelo y read-path existen, pero el write-path (upload) es todo placeholder     
 2. Componentes legacy muertos — 2 ChatInputs, 1 ChatMessage, 1 TicketItem, 1 SurveyArea que nadie usa   
 3. Paginación de mensajes — show() carga TODOS los mensajes de golpe, problema para tickets largos      

 Decisión tomada: Refactorizar ChatView para extraer su textarea inline a un ChatInput mejorado con      
 soporte de archivos. Eliminar los componentes legacy.

 ---
 Ya implementado (NO tocar)

 - TicketMessageResource ya incluye attachments via whenLoaded('attachments')
 - TicketMessageReceived event ya carga attachments via loadMissing
 - ChatMessage (shared) ya renderiza imágenes desde message.attachments
 - sellerTicketAdapter ya mapea attachments a MessageAttachment[]
 - Tabla ticket_attachments existe con: ticket_message_id, name, file_type(image|file), path
 - Canal broadcast private-ticket.{id} autoriza owner + admin

 ---
 Fase 1: Backend — Subida de imágenes

 1.1 Crear TicketAttachmentService

 Archivo nuevo: app/Services/TicketAttachmentService.php

 - Método storeAttachments(TicketMessage $message, array $files): void
   - Guarda cada archivo en tickets/attachments/{ticket_id}/{message_id}/ dentro del disco public        
   - Crea registro en ticket_attachments con name, file_type: 'image', path
 - Reutilizable por TicketController y AdminTicketController

 1.2 Modificar SendTicketMessageRequest

 Archivo: app/Http/Requests/SendTicketMessageRequest.php

 // Cambiar:
 'content' => 'required|string|min:1|max:5000'
 // A:
 'content' => 'required_without:attachments|nullable|string|min:1|max:5000',
 'attachments' => 'required_without:content|array|max:3',
 'attachments.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120', // 5MB

 1.3 Modificar StoreTicketRequest

 Archivo: app/Http/Requests/StoreTicketRequest.php

 // Cambiar:
 'mensaje' => 'required|string|min:10|max:5000'
 // A:
 'mensaje' => 'required_without:adjuntos|nullable|string|min:10|max:5000',
 'adjuntos' => 'nullable|array|max:3',
 'adjuntos.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',

 1.4 Modificar TicketController::store()

 Archivo: app/Http/Controllers/Api/TicketController.php

 - Después de crear el mensaje inicial, llamar TicketAttachmentService::storeAttachments() si hay        
 adjuntos
 - Cargar $message->load('attachments') antes de responder/broadcast
 - En TicketInboxUpdated: si no hay texto y hay adjuntos, preview_text = '[Imagen]' o '[N imágenes]'     

 1.5 Modificar TicketController::sendMessage()

 Archivo: app/Http/Controllers/Api/TicketController.php

 - Llamar TicketAttachmentService::storeAttachments() si hay attachments
 - $message->load('attachments') antes de broadcast
 - Fallback preview igual que store()

 1.6 Modificar AdminTicketController::sendMessage()

 Archivo: app/Http/Controllers/Api/AdminTicketController.php

 - Misma lógica: servicio + load attachments + fallback preview

 1.7 Verificar storage:link

 - Confirmar que php artisan storage:link está en el setup del proyecto
 - Agregar a composer run setup si no está

 ---
 Fase 2: Frontend — Refactorizar ChatInput + Subida de imágenes

 2.1 Reescribir modules/chat/components/ChatInput.tsx

 Archivo: src/modules/chat/components/ChatInput.tsx

 Convertirlo en el input definitivo que ChatView usará:

 Props:
 interface ChatInputProps {
   onSendMessage: (payload: { text: string; attachments?: File[] }) => void;
   disabled?: boolean;
   placeholder?: string;
   maxAttachments?: number; // default 3
   acceptedTypes?: string; // default 'image/jpeg,image/png,image/webp'
 }

 Funcionalidad:
 - Textarea con auto-resize (traer lógica actual de ChatView)
 - Botón paperclip → abre file picker (solo imágenes)
 - Estado local pendingFiles: File[] con previews (URL.createObjectURL)
 - Mostrar thumbnails con opción de quitar (X) antes de enviar
 - Validar max 3 archivos, solo tipos aceptados
 - Enviar con Enter (solo texto) o botón Send (texto + archivos)
 - Permitir enviar solo imágenes sin texto
 - Bloquear envío si no hay texto ni archivos

 2.2 Refactorizar ChatView para usar ChatInput

 Archivo: src/modules/chat/components/ChatView.tsx

 - Eliminar la textarea inline y el estado replyText del reply area
 - Reemplazar con <ChatInput onSendMessage={handleSend} disabled={...} />
 - handleSend recibe { text, attachments } y llama onSendMessage del ChatViewProps
 - Actualizar ChatViewProps.onSendMessage signature:
 onSendMessage: (payload: { text: string; attachments?: File[] }) => void;

 2.3 Actualizar tipos de payload

 Archivo: src/modules/helpdesk/types.ts

 interface SendMessagePayload {
   content?: string;
   attachments?: File[];
 }

 interface CreateTicketPayload {
   asunto: string;
   mensaje?: string;
   tipo_ticket: TicketType;
   criticidad: TicketPriority;
   adjuntos?: File[];
 }

 2.4 Actualizar ticketRepository

 Archivo: src/lib/api/ticketRepository.ts

 - sendMessage(): si hay attachments, usar FormData en vez de JSON
 - create(): si hay adjuntos, usar FormData en vez de JSON
 - No enviar Content-Type header con FormData (el browser lo pone con boundary)

 // Ejemplo sendMessage:
 async sendMessage(ticketId: number, payload: SendMessagePayload) {
   if (payload.attachments?.length) {
     const form = new FormData();
     if (payload.content) form.append('content', payload.content);
     payload.attachments.forEach(f => form.append('attachments[]', f));
     return api.post(`/tickets/${ticketId}/messages`, form);
   }
   return api.post(`/tickets/${ticketId}/messages`, { content: payload.content });
 }

 2.5 Propagar attachments por hooks

 useSellerHelp.ts:
 - handleSendMessage(text, attachments?) → pasa a ticketApi.seller.sendMessage
 - createTicket(data) → pasa adjuntos a ticketApi.seller.create

 useMesaAyuda.ts:
 - actions.sendReply(text, isQuick, attachments?) → pasa a ticketApi.admin.sendMessage

 2.6 Propagar en bridge components

 TicketChatView.tsx (seller):
 - onSendMessage recibe { text, attachments } → llama hook con ambos

 HelpDeskModule.tsx o equivalente admin:
 - Mismo cambio para propagar attachments

 2.7 Wiring en NewTicketForm

 Archivo: src/features/seller/help/components/NewTicketForm.tsx

 La UI de drop zone ya existe. Solo wiring:
 - onChange handler en el input file → guardar en estado files: File[]
 - Preview de thumbnails con nombre y botón X
 - Validar max 3 imágenes
 - Pasar adjuntos en el payload al submit
 - Actualizar TicketFormData para incluir adjuntos

 2.8 Arreglar adminTicketAdapter

 Archivo: src/modules/chat/adapters/adminTicketAdapter.ts

 - Mapear attachments del backend a MessageAttachment[] en UnifiedMessage
 - Hoy ignora attachments completamente — el backend los envía pero el adapter los descarta

 ---
 Fase 3: Limpieza de componentes legacy

 3.1 Eliminar archivos muertos

 ┌─────────────────────────────────────────────────┬─────────────────────────────────────────────────┐   
 │                     Archivo                     │              Razón de eliminación               │   
 ├─────────────────────────────────────────────────┼─────────────────────────────────────────────────┤   
 │ features/seller/help/components/ChatInput.tsx   │ Legacy — nadie lo importa, reemplazado por      │   
 │                                                 │ ChatView shared                                 │   
 ├─────────────────────────────────────────────────┼─────────────────────────────────────────────────┤   
 │ features/seller/help/components/ChatMessage.tsx │ Legacy — seller usa ChatMessage de modules/chat │   
 ├─────────────────────────────────────────────────┼─────────────────────────────────────────────────┤   
 │ features/seller/help/components/TicketItem.tsx  │ Legacy — sidebar usa TicketItem de              │   
 │                                                 │ modules/helpdesk                                │   
 ├─────────────────────────────────────────────────┼─────────────────────────────────────────────────┤   
 │ features/seller/help/components/SurveyArea.tsx  │ Legacy — ChatView tiene su propio SurveyArea    │   
 │                                                 │ inline                                          │   
 └─────────────────────────────────────────────────┴─────────────────────────────────────────────────┘   

 3.2 Limpiar exports

 - Verificar features/seller/help/index.ts y cualquier barrel export
 - Remover re-exports de los componentes eliminados
 - Verificar que no haya imports rotos en ningún archivo

 3.3 Limpiar tipos legacy

 - features/admin/helpdesk/types.ts tiene Message con archivo?: string | null — verificar si algo lo     
 usa, si no, eliminar

 ---
 Fase 4: Paginación de mensajes

 4.1 Backend — Paginar mensajes en show()

 TicketController::show() y AdminTicketController::show():

 - En vez de $ticket->load('messages.user', 'messages.attachments'), paginar:
 $messages = $ticket->messages()
     ->with(['user', 'attachments'])
     ->orderBy('created_at', 'desc')
     ->cursorPaginate(30);
 - Devolver mensajes paginados con cursor para "cargar más" (scroll up = mensajes anteriores)
 - El cursor-based pagination es mejor para chat porque no se desincroniza con nuevos mensajes

 4.2 Backend — Ajustar resources

 - TicketResource: cuando mensajes es paginado, devolver { data: [...], next_cursor, has_more }
 - Mantener compatibilidad: si messages se carga como relación (no paginado), devolver array plano       

 4.3 Frontend — Scroll infinito hacia arriba

 ChatView.tsx:
 - Detectar scroll al top del contenedor de mensajes
 - Llamar endpoint con cursor para cargar página anterior
 - Prepend mensajes antiguos manteniendo scroll position
 - Mostrar spinner "Cargando mensajes anteriores..." mientras carga

 ticketRepository.ts:
 - get(id, cursor?) → pasar cursor como query param

 Hooks (useSellerHelp, useMesaAyuda):
 - Estado para messagesCursor y hasMoreMessages
 - Función loadMoreMessages() que llama API con cursor y concatena

 ---
 Archivos a modificar (resumen)

 Backend

 ┌────────────────────────────────────────────────────┬──────────────────────────────────────────────┐   
 │                      Archivo                       │                    Cambio                    │   
 ├────────────────────────────────────────────────────┼──────────────────────────────────────────────┤   
 │ app/Services/TicketAttachmentService.php           │ NUEVO — lógica de upload                     │   
 ├────────────────────────────────────────────────────┼──────────────────────────────────────────────┤   
 │ app/Http/Requests/SendTicketMessageRequest.php     │ Validación condicional + attachments         │   
 ├────────────────────────────────────────────────────┼──────────────────────────────────────────────┤   
 │ app/Http/Requests/StoreTicketRequest.php           │ Validación condicional + adjuntos            │   
 ├────────────────────────────────────────────────────┼──────────────────────────────────────────────┤   
 │ app/Http/Controllers/Api/TicketController.php      │ store() y sendMessage() con attachments +    │   
 │                                                    │ load + preview                               │   
 ├────────────────────────────────────────────────────┼──────────────────────────────────────────────┤   
 │ app/Http/Controllers/Api/AdminTicketController.php │ sendMessage() con attachments + load +       │   
 │                                                    │ preview                                      │   
 ├────────────────────────────────────────────────────┼──────────────────────────────────────────────┤   
 │ app/Http/Resources/TicketResource.php              │ Soporte mensajes paginados                   │   
 └────────────────────────────────────────────────────┴──────────────────────────────────────────────┘   

 Frontend

 ┌────────────────────────────────────────────────────┬──────────────────────────────────────────────┐   
 │                      Archivo                       │                    Cambio                    │   
 ├────────────────────────────────────────────────────┼──────────────────────────────────────────────┤   
 │ modules/chat/components/ChatInput.tsx              │ REESCRIBIR — input definitivo con file       │   
 │                                                    │ support                                      │   
 ├────────────────────────────────────────────────────┼──────────────────────────────────────────────┤   
 │ modules/chat/components/ChatView.tsx               │ Extraer textarea → usar ChatInput + scroll   │   
 │                                                    │ infinito                                     │   
 ├────────────────────────────────────────────────────┼──────────────────────────────────────────────┤   
 │ modules/chat/adapters/adminTicketAdapter.ts        │ Mapear attachments                           │   
 ├────────────────────────────────────────────────────┼──────────────────────────────────────────────┤   
 │ modules/helpdesk/types.ts                          │ Ampliar payloads                             │   
 ├────────────────────────────────────────────────────┼──────────────────────────────────────────────┤   
 │ lib/api/ticketRepository.ts                        │ FormData + cursor pagination                 │   
 ├────────────────────────────────────────────────────┼──────────────────────────────────────────────┤   
 │ features/seller/help/hooks/useSellerHelp.ts        │ Attachments + loadMore                       │   
 ├────────────────────────────────────────────────────┼──────────────────────────────────────────────┤   
 │ features/admin/helpdesk/hooks/useMesaAyuda.ts      │ Attachments + loadMore                       │   
 ├────────────────────────────────────────────────────┼──────────────────────────────────────────────┤   
 │ features/seller/help/components/TicketChatView.tsx │ Propagar attachments                         │   
 ├────────────────────────────────────────────────────┼──────────────────────────────────────────────┤   
 │ features/seller/help/components/NewTicketForm.tsx  │ Wiring upload existente                      │   
 └────────────────────────────────────────────────────┴──────────────────────────────────────────────┘   

 Eliminar

 ┌─────────────────────────────────────────────────┐
 │                     Archivo                     │
 ├─────────────────────────────────────────────────┤
 │ features/seller/help/components/ChatInput.tsx   │
 ├─────────────────────────────────────────────────┤
 │ features/seller/help/components/ChatMessage.tsx │
 ├─────────────────────────────────────────────────┤
 │ features/seller/help/components/TicketItem.tsx  │
 ├─────────────────────────────────────────────────┤
 │ features/seller/help/components/SurveyArea.tsx  │
 └─────────────────────────────────────────────────┘

 ---
 Orden de implementación recomendado

 1. Fase 3 primero (limpieza legacy) — eliminar código muerto antes de modificar, reduce confusión       
 2. Fase 1 (backend imágenes) — endpoints listos para recibir archivos
 3. Fase 2 (frontend imágenes) — refactor ChatInput + wiring completo
 4. Fase 4 (paginación) — mejora de performance, independiente de imágenes

 ---
 Verificación

 Imágenes

 1. Seller crea ticket con solo texto — funciona como antes
 2. Seller crea ticket con texto + 1-3 imágenes — se guardan y renderizan
 3. Seller crea ticket con solo imágenes — preview muestra "[Imagen]"
 4. Rechazar >3 imágenes, mime inválido, >5MB
 5. Seller responde con imágenes en chat existente
 6. Admin ve imágenes del seller en su vista
 7. Admin responde con imágenes
 8. Seller recibe imágenes del admin via WebSocket en tiempo real
 9. Mobile: previews de imágenes no desbordan layout

 Paginación

 10. Ticket con 50+ mensajes carga solo los últimos 30
 11. Scroll arriba carga los anteriores sin perder posición
 12. Mensajes nuevos (WebSocket) se agregan al final sin afectar cursor

 Limpieza

 13. No hay imports rotos después de eliminar componentes legacy
 14. Build limpio sin warnings de archivos faltantes