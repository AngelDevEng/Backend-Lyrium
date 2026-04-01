# PLAN: Badge de mensajes no leídos (estilo WhatsApp) — Mesa de Ayuda

**Objetivo**: Cuando llega un mensaje en un ticket que NO está abierto, mostrar en el ítem de la lista un badge con el contador de mensajes no leídos (igual que WhatsApp).

---

## Estado actual

| Aspecto | Seller | Admin |
|---------|--------|-------|
| `unreadCount` en adapter | **Hardcodeado a `0`** | Mapea correctamente desde `mensajes_sin_leer` |
| Visual en `TicketItem` | Nunca muestra nada | Punto azul pulsante pequeño si > 0 |
| Listener WebSocket | Solo en ticket activo | Solo en ticket seleccionado |
| Refresca la lista al recibir mensaje | Sí (invalidateQueries) | Sí (invalidateQueries) |

**Problema raíz**: El listener de WebSocket solo escucha el canal del ticket *actualmente abierto*. Si el seller está en ticket A y llega un mensaje en ticket B, no hay ningún evento que dispare el refresh de la lista para ticket B.

---

## Archivos clave

### Backend
- `app/Events/TicketMessageReceived.php` — evento existente, canal `private-ticket.{id}`
- `app/Http/Controllers/Api/TicketController.php` — método `sendMessage()` (seller)
- `app/Http/Controllers/Api/AdminTicketController.php` — método `sendMessage()` (admin)
- `routes/channels.php`

### Frontend
- `src/modules/helpdesk/adapters/sellerAdapter.ts` — hardcodea `unreadCount: 0`
- `src/modules/helpdesk/adapters/adminAdapter.ts` — ya mapea correctamente
- `src/modules/helpdesk/components/TicketItem.tsx` — solo muestra puntito, sin número
- `src/features/seller/help/hooks/useSellerHelp.ts` — listeners WebSocket
- `src/features/admin/helpdesk/hooks/useMesaAyuda.ts` — listeners WebSocket

---

## Cambios a implementar

### PASO 1 — Backend: Nuevo evento `TicketInboxUpdated`

Crear `app/Events/TicketInboxUpdated.php`:

```php
<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TicketInboxUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $notifyUserId,  // usuario que debe recibir la notificación
        public readonly int $ticketId,
    ) {}

    public function broadcastOn(): array|Channel
    {
        return new PrivateChannel("user.{$this->notifyUserId}");
    }

    public function broadcastAs(): string
    {
        return 'TicketInboxUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'ticket_id' => $this->ticketId,
        ];
    }
}
```

---

### PASO 2 — Backend: Disparar `TicketInboxUpdated` en ambos controllers

#### `TicketController::sendMessage()` (seller envía → notificar al admin asignado o cualquier admin)

Después del broadcast existente de `TicketMessageReceived`, agregar:

```php
use App\Events\TicketInboxUpdated;

// Dentro de sendMessage(), después de broadcast(new TicketMessageReceived($message)):
$ticket->loadMissing('assignedAdmin');

if ($ticket->assignedAdmin) {
    broadcast(new TicketInboxUpdated($ticket->assignedAdmin->id, $ticket->id));
} else {
    // Sin admin asignado: notificar a todos los administradores
    \App\Models\User::role('administrator')->each(function ($admin) use ($ticket) {
        broadcast(new TicketInboxUpdated($admin->id, $ticket->id));
    });
}
```

> **Nota**: Si notificar a todos los admins es costoso, se puede limitar a los últimos N admins activos o al admin con menos tickets asignados. Para MVP, notificar a todos los admins está bien.

#### `AdminTicketController::sendMessage()` (admin responde → notificar al seller/dueño del ticket)

```php
use App\Events\TicketInboxUpdated;

// Dentro de sendMessage(), después de broadcast(new TicketMessageReceived($message)):
broadcast(new TicketInboxUpdated($ticket->user_id, $ticket->id));
```

---

### PASO 3 — Frontend: Escuchar `TicketInboxUpdated` en el canal del usuario

#### `useSellerHelp.ts` — agregar listener en el canal `user.{myUserId}`

```ts
// Importar useAuth o equivalente para obtener el user.id
// Ya se usa 'user' en NotificationContext, verificar si hay hook de auth disponible

useEcho(
    `user.${user?.id ?? 0}`,
    'TicketInboxUpdated',
    () => {
        void queryClient.invalidateQueries({ queryKey: ['seller', 'help', 'tickets'] });
    },
    [user?.id]
);
```

> **Nota**: Verificar cómo se obtiene el `user` actual en este hook. Si hay un `useAuth()` o similar disponible, usarlo. Si no, extraer el user del context.

#### `useMesaAyuda.ts` — ídem

```ts
useEcho(
    `user.${user?.id ?? 0}`,
    'TicketInboxUpdated',
    () => {
        void queryClient.invalidateQueries({ queryKey: ['admin', 'helpdesk'] });
    },
    [user?.id]
);
```

---

### PASO 4 — Frontend: Corregir `sellerAdapter.ts` para mapear `mensajes_sin_leer`

**Archivo**: `src/modules/helpdesk/adapters/sellerAdapter.ts`

**Verificar primero** que el backend retorna `mensajes_sin_leer` en el listado de tickets del seller. Buscar en `app/Http/Resources/TicketResource.php` o similar si incluye ese campo.

Si lo retorna, cambiar:

```ts
// ANTES
unreadCount: 0,

// DESPUÉS
unreadCount: ticket.mensajes_sin_leer ?? 0,
```

Si el tipo `SellerTicket` en el adapter no tiene ese campo, agregarlo:

```ts
interface SellerTicket {
  // ...campos existentes...
  mensajes_sin_leer?: number;
}
```

---

### PASO 5 — Frontend: Rediseñar el badge en `TicketItem.tsx`

**Archivo**: `src/modules/helpdesk/components/TicketItem.tsx`

Reemplazar el punto pulsante actual por un badge estilo WhatsApp con el número:

```tsx
// ANTES (puntito sin número):
{ticket.unreadCount > 0 && (
    <div className="w-1.5 h-1.5 bg-sky-500 rounded-full animate-pulse"></div>
)}

// DESPUÉS (badge con contador, estilo WhatsApp):
{ticket.unreadCount > 0 && !isActive && (
    <span className="min-w-[18px] h-[18px] px-1 bg-emerald-500 text-white text-[10px] font-black rounded-full flex items-center justify-center leading-none">
        {ticket.unreadCount > 99 ? '99+' : ticket.unreadCount}
    </span>
)}
```

**Comportamiento**:
- Solo se muestra cuando `unreadCount > 0` **y el ticket NO está activo** (`!isActive`)
- Al abrir el ticket, el backend marca como leído y el badge desaparece en el próximo refetch
- Verde (`emerald-500`) para consistencia con los checkmarks de mensajes leídos
- Cap en 99+ si hay muchos mensajes sin leer

**Adicionalmente**: Cuando `unreadCount > 0`, poner el título del ticket en **bold** para más énfasis:

```tsx
// En el título del ticket:
<h4 className={`text-sm truncate ${ticket.unreadCount > 0 && !isActive ? 'font-black text-[var(--text-primary)]' : 'font-semibold text-[var(--text-secondary)]'}`}>
    {ticket.title}
</h4>
```

---

## Flujo completo después de implementar

```
CASO 1: Seller abre ticket A. Admin responde en ticket A.
→ TicketMessageReceived en private-ticket.A → seller ve mensaje en tiempo real ✓
→ TicketInboxUpdated en private-user.{sellerId} → invalida lista → unreadCount actualizado ✓
→ Pero seller ya tiene el ticket abierto → badge no aparece (isActive = true) ✓

CASO 2: Seller está en ticket A. Admin responde en ticket B (diferente).
→ TicketMessageReceived en private-ticket.B → seller NO está suscrito a ese canal (ticket B no es el activo)
→ TicketInboxUpdated en private-user.{sellerId} → invalida lista → refetch → unreadCount de ticket B aumenta
→ Badge verde aparece en ticket B de la sidebar ✓

CASO 3: Admin no tiene ningún ticket seleccionado. Seller envía mensaje en ticket X.
→ TicketInboxUpdated en private-user.{adminId} → invalida ['admin', 'helpdesk'] → refetch
→ Badge verde aparece en ticket X de la lista admin ✓

CASO 4: Admin abre ticket X.
→ Backend show() marca mensajes como leídos → broadcast TicketMessagesRead
→ refetch → unreadCount vuelve a 0 → badge desaparece ✓
→ Seller recibe TicketMessagesRead → checkmarks se ponen azules ✓
```

---

## Resumen de archivos a crear/modificar

| Archivo | Tipo | Cambio |
|---------|------|--------|
| `app/Events/TicketInboxUpdated.php` | **Nuevo** | Evento que notifica al usuario receptor |
| `app/Http/Controllers/Api/TicketController.php` | Modificar | Broadcast `TicketInboxUpdated` al admin en `sendMessage()` |
| `app/Http/Controllers/Api/AdminTicketController.php` | Modificar | Broadcast `TicketInboxUpdated` al seller en `sendMessage()` |
| `src/modules/helpdesk/adapters/sellerAdapter.ts` | Modificar | Mapear `mensajes_sin_leer` → `unreadCount` |
| `src/features/seller/help/hooks/useSellerHelp.ts` | Modificar | Añadir listener `TicketInboxUpdated` en canal `user.{id}` |
| `src/features/admin/helpdesk/hooks/useMesaAyuda.ts` | Modificar | Añadir listener `TicketInboxUpdated` en canal `user.{id}` |
| `src/modules/helpdesk/components/TicketItem.tsx` | Modificar | Badge verde con número + título bold |

---

## Prerequisitos verificar antes de implementar

1. **¿El backend retorna `mensajes_sin_leer` en el endpoint de listado de tickets del seller?**
   - Revisar `app/Http/Resources/TicketResource.php` o el controller que devuelve la lista
   - Si no, agregar ese campo al resource

2. **¿Cómo se obtiene el `user.id` actual en `useSellerHelp.ts` y `useMesaAyuda.ts`?**
   - Buscar si hay un `useAuth()` hook o similar en el proyecto
   - Puede venir del cookie `user_id` o de un context de auth

3. **¿`Ticket` tiene la relación `assignedAdmin`?**
   - Verificar en `app/Models/Ticket.php` que exista `assignedAdmin()` BelongsTo

---

## Notas de diseño

- El badge solo aparece en tickets **no activos** para no crear ruido visual cuando el ticket está abierto
- El color verde (`emerald-500`) es consistente con los checkmarks de "visto" ya implementados
- Al abrir el ticket, el badge desaparece automáticamente porque el backend marca como leído y el WebSocket actualiza la UI
- No se necesita estado local optimista: el refetch es rápido y el flujo es correcto