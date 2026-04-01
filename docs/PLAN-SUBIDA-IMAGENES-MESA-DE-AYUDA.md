# PLAN: Subida de Imágenes en Chat de Mesa de Ayuda

## Resumen

Integrar envío y visualización de imágenes en Mesa de Ayuda para `seller` y `admin`, incluyendo el mensaje inicial al crear un ticket y los mensajes posteriores del chat. La v1 permitirá hasta `3` imágenes por mensaje, con `texto opcional`, reutilizando el modelo `ticket_attachments` ya existente y el render actual de adjuntos en el frontend.

## Cambios de implementación

### Backend Laravel

- Extender `SendTicketMessageRequest` para aceptar `multipart/form-data` con `attachments[]` y permitir `content` vacío cuando exista al menos un adjunto.
- Extender `StoreTicketRequest` y el flujo `TicketController::store()` para aceptar imágenes en el mensaje inicial del ticket.
- Extraer la lógica de persistencia de adjuntos a un servicio o helper reutilizable para `TicketController` y `AdminTicketController`, guardando archivos en `public` bajo una ruta estable tipo `tickets/attachments`.
- Validar solo imágenes en v1 (`jpeg`, `png`, `jpg`, `webp`, y opcionalmente `gif` si quieren mantener consistencia con otros uploads), máximo `3` archivos por mensaje y tamaño máximo por archivo definido explícitamente en la request.
- Mantener la respuesta actual vía `TicketMessageResource`, pero asegurar que siempre cargue `attachments` al crear mensaje o ticket inicial para que REST y WebSocket devuelvan el mismo shape.
- Ajustar preview e inbox para mensajes sin texto:
  - si hay texto, usar el texto truncado;
  - si no hay texto y hay imágenes, usar un fallback consistente tipo `"[Imagen]"` o `"[3 imágenes]"`.

### Persistencia en base de datos y storage

- La imagen no se almacenará como binario dentro de la base de datos.
- El archivo físico se guardará en el disco `public` de Laravel, en una ruta tipo `storage/app/public/tickets/attachments/...`.
- La tabla `ticket_attachments` almacenará la referencia del archivo y su relación con el mensaje:
  - `ticket_message_id`
  - `name`
  - `file_type`
  - `path`
- La URL pública se construirá desde `path`, exponiéndola vía `/storage/...` usando `storage:link`.
- Para v1 se reutiliza la tabla existente `ticket_attachments`; como mejora opcional posterior se pueden agregar columnas como `mime_type` y `file_size` si quieren más trazabilidad.

### Frontend Next.js

- Cambiar `ticketRepository` para soportar dos modos de envío:
  - JSON cuando solo hay texto;
  - `FormData` cuando hay imágenes.
- Ampliar tipos públicos:
  - `SendMessagePayload` debe aceptar `content?: string` y `attachments?: File[]`;
  - `CreateTicketPayload` debe contemplar adjuntos para el mensaje inicial;
  - mantener `attachments` en `TicketMessage` y `UnifiedMessage` como contrato renderizable.
- Actualizar `modules/chat/components/ChatInput.tsx`:
  - usar de verdad el `fileInput`;
  - aceptar solo imágenes;
  - mostrar preview local, nombre y opción de quitar antes de enviar;
  - permitir enviar solo imágenes o imágenes con texto;
  - bloquear envío si no hay texto ni imágenes;
  - respetar el límite de `3`.
- Propagar el nuevo contrato por hooks:
  - `useMesaAyuda` para respuestas admin;
  - `useSellerHelp` para respuestas seller;
  - creación de ticket seller (`NewTicketForm` y hook asociado) para adjuntar imágenes al primer mensaje.
- Mantener el render actual de adjuntos en `ChatMessage`, pero asegurar que el preview local y el estado de “enviando” no rompan el layout móvil ni desktop.

### Adaptadores y normalización

- Alinear `modules/chat/adapters/adminTicketAdapter.ts` con el shape real del backend para mapear `attachments`; ahora el adapter admin ignora adjuntos aunque el backend los devuelve.
- Revisar `sellerAdapter` y `sellerTicketAdapter` para conservar `unreadCount` y timestamps al refrescar después de envíos con imágenes.
- No abrir alcance a `customer` ni `logistics` en esta v1; dejar el diseño del contrato reusable para esas superficies después.

## APIs e interfaces afectadas

### Backend

- `POST /api/tickets`
- `POST /api/tickets/{id}/messages`
- `POST /api/admin/tickets/{id}/messages`
- `StoreTicketRequest`
- `SendTicketMessageRequest`

### Frontend

- `ticketApi.seller.create`
- `ticketApi.seller.sendMessage`
- `ticketApi.admin.sendMessage`
- `CreateTicketPayload`
- `SendMessagePayload`
- `ChatInputProps` y callbacks de envío para aceptar texto y archivos

## Pruebas y escenarios

### Backend

- Crear ticket con solo texto.
- Crear ticket con texto más `1..3` imágenes.
- Crear ticket con solo imágenes.
- Rechazar ticket o mensaje sin texto y sin imágenes.
- Rechazar más de `3` imágenes.
- Rechazar mime inválido o tamaño excedido.
- Responder desde seller y admin verificando persistencia en `ticket_attachments` y URLs en el recurso.

### Frontend

- Seleccionar `1`, `2` y `3` imágenes.
- Quitar una imagen antes de enviar.
- Enviar solo imágenes.
- Enviar texto más imágenes.
- Ver las imágenes renderizadas tras `refetch` y por WebSocket.
- Validar que mobile en `/seller/help` siga mostrando composer y previews sin desbordes.
- Validar que admin en `/admin/helpdesk` mantenga refresh de lista, unread count y preview correcto cuando el último mensaje no tiene texto.

### E2E o manual

- Seller crea ticket con imágenes.
- Admin abre y visualiza imágenes.
- Admin responde con imágenes.
- Seller recibe el mensaje en tiempo real y puede abrir la imagen.

## Supuestos y defaults

- Alcance confirmado para esta v1: `seller + admin`.
- Regla confirmada: `texto opcional`.
- Límite confirmado: `hasta 3 imágenes por mensaje`.
- El primer mensaje del ticket también soportará imágenes.
- V1 será solo para imágenes, no para PDFs ni archivos genéricos, aunque el modelo soporte `file`.
- Se reutiliza `ticket_attachments` existente; no se requiere nueva tabla salvo que durante implementación falten columnas de tamaño o mime y decidan agregarlas explícitamente.
- Se asume que `storage:link` y el disco `public` ya forman parte del despliegue normal del backend.
