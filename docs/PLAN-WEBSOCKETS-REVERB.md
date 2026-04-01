# PLAN — WebSockets con Laravel Reverb + Laravel Echo

**Fecha:** 2026-03-29
**Backend:** F:\TEST\Backend-Lyrium
**Frontend:** F:\FRONTEND\fe-001-marketplace-admin\frontapp

---

## Stack elegido

| Componente | Tecnología | Por qué |
|---|---|---|
| Servidor WS | **Laravel Reverb** | First-party de Laravel 11+, sin dependencias externas |
| Cliente JS | **Laravel Echo** | Oficial de Laravel, integra nativamente con Reverb |
| Driver | **pusher-js** | Echo lo requiere como peer dependency |

---

## Estado actual

- `BROADCAST_CONNECTION=log` — broadcasting desactivado
- No hay eventos de broadcast
- Reverb no instalado
- Laravel Echo no instalado en frontend

---

## FASE 1 — Infraestructura base (hacer UNA VEZ)

### T1 — Instalar Laravel Reverb (Backend)

```bash
cd F:\TEST\Backend-Lyrium
composer require laravel/reverb
php artisan reverb:install
```

Genera automáticamente en `.env`:
```
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=my-app-id
REVERB_APP_KEY=my-app-key
REVERB_APP_SECRET=my-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

### T2 — Instalar Laravel Echo + pusher-js (Frontend)

```bash
cd F:\FRONTEND\fe-001-marketplace-admin\frontapp
npm install laravel-echo pusher-js
```

### T3 — Configurar Echo (Frontend)

**Archivo a crear:** `src/lib/echo.ts`

```ts
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

declare global { interface Window { Pusher: typeof Pusher } }
window.Pusher = Pusher;

const echo = new Echo({
    broadcaster: 'reverb',
    key: process.env.NEXT_PUBLIC_REVERB_APP_KEY,
    wsHost: process.env.NEXT_PUBLIC_REVERB_HOST || 'localhost',
    wsPort: Number(process.env.NEXT_PUBLIC_REVERB_PORT) || 8080,
    wssPort: Number(process.env.NEXT_PUBLIC_REVERB_PORT) || 8080,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
});

export default echo;
```

**Variables en `.env.local` del frontend:**
```
NEXT_PUBLIC_REVERB_APP_KEY=my-app-key
NEXT_PUBLIC_REVERB_HOST=localhost
NEXT_PUBLIC_REVERB_PORT=8080
```

### T4 — Correr Reverb junto al servidor

```bash
php artisan reverb:start   # puerto 8080
php artisan serve          # puerto 8000
```

---

## FASE 2 — Eventos por prioridad de negocio

### PRIORIDAD 1 — Nueva orden recibida (seller)
> **Impacto:** El seller recibe alerta inmediata cuando un cliente compra.

**Canal:** `private-store.{store_id}` (privado, solo el seller de esa tienda)
**Evento:** `app/Events/NewOrderReceived.php`
**Se dispara en:** `OrderController::store()`
**Frontend escucha en:** `/seller/orders` — badge de notificación + toast

---

### PRIORIDAD 2 — Nueva cita agendada (seller de servicios)
> **Impacto:** El seller ve al instante cuando un cliente agenda una cita.

**Canal:** `private-store.{store_id}`
**Evento:** `app/Events/NewBookingReceived.php`
**Se dispara en:** `ServiceBookingController::store()`
**Frontend escucha en:** `/seller/services` — calendario se actualiza sin recargar

---

### PRIORIDAD 3 — Estado de tienda cambiado (seller)
> **Impacto:** El seller recibe notificación cuando el admin aprueba o rechaza su tienda.

**Canal:** `private-store.{store_id}`
**Evento:** `app/Events/StoreStatusChanged.php`
**Se dispara en:** `StoreController::updateStatus()`
**Frontend escucha en:** Dashboard del seller

---

### PRIORIDAD 4 — Estado de producto cambiado (seller)
> **Impacto:** El seller sabe al instante si su producto fue aprobado o rechazado.

**Canal:** `private-store.{store_id}`
**Evento:** `app/Events/ProductStatusChanged.php`
**Se dispara en:** `ProductController::updateStatus()`
**Frontend escucha en:** `/seller/products`

---

### PRIORIDAD 5 — Ticket respondido (seller)
> **Impacto:** El seller recibe notificación cuando soporte responde su ticket.

**Canal:** `private-user.{user_id}`
**Evento:** `app/Events/TicketMessageReceived.php`
**Se dispara en:** `AdminTicketController::sendMessage()`
**Frontend escucha en:** `/seller/support`

---

### PRIORIDAD 6 — Categoría actualizada (web pública)
> **Impacto:** Mega-menu y panel admin reflejan cambios de imágenes/nombres al instante.

**Canal:** `categories` (público)
**Evento:** `app/Events/CategoryUpdated.php`
**Se dispara en:** `CategoryController::update()`, `uploadImage()`, `destroy()`
**Frontend escucha en:** `PublicHeader` + admin `/admin/categories`

---

## FASE 3 — Patrón de implementación (igual para todos)

### Backend — Estructura de un evento

```php
// app/Events/NombreEvento.php
class NombreEvento implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(public Model $model) {}

    public function broadcastOn(): Channel
    {
        // Canal público:
        return new Channel('nombre-canal');

        // Canal privado (solo usuarios autenticados con acceso):
        return new PrivateChannel('store.' . $this->model->store_id);
    }

    public function broadcastAs(): string
    {
        return 'evento.nombre'; // nombre del evento en el frontend
    }

    public function broadcastWith(): array
    {
        return [ /* datos a enviar */ ];
    }
}
```

### Frontend — Hook reutilizable por canal

```ts
// src/shared/hooks/useChannel.ts
export function useChannel(channel: string, event: string, callback: (data: any) => void) {
    useEffect(() => {
        if (typeof window === 'undefined') return;
        import('@/lib/echo').then(({ default: echo }) => {
            echo.channel(channel).listen(event, callback);
        });
        return () => {
            import('@/lib/echo').then(({ default: echo }) => {
                echo.leaveChannel(channel);
            });
        };
    }, [channel, event]);
}
```

**Uso en cualquier módulo:**
```ts
// En seller/orders — escuchar nuevas órdenes
useChannel(`private-store.${storeId}`, '.order.received', () => {
    queryClient.invalidateQueries({ queryKey: ['seller', 'orders'] });
    showToast('¡Nueva orden recibida!', 'success');
});

// En public header — escuchar cambios de categorías
useChannel('categories', '.category.updated', () => {
    queryClient.invalidateQueries({ queryKey: ['mega-menu'] });
});
```

---

## Canales necesarios

| Canal | Tipo | Quién escucha |
|---|---|---|
| `categories` | Público | Todos los visitantes |
| `private-store.{id}` | Privado | Solo el seller de esa tienda |
| `private-user.{id}` | Privado | Solo ese usuario |

---

## Flujo completo — ejemplo NewOrder

```
Cliente hace checkout → OrderController::store()
        ↓
NewOrderReceived::dispatch($order)
        ↓
Reverb emite al canal private-store.{store_id}
        ↓
Echo en el browser del seller recibe '.order.received'
        ↓
queryClient.invalidateQueries(['seller', 'orders'])
showToast('¡Nueva orden recibida!', 'success')
        ↓
La lista de órdenes se actualiza al instante + badge en sidebar
```

---

## Orden de implementación recomendado

### Sprint 1 — Base + Primera funcionalidad
1. **T1** Backend: `composer require laravel/reverb` + `php artisan reverb:install`
2. **T2** Frontend: `npm install laravel-echo pusher-js`
3. **T3** Frontend: crear `src/lib/echo.ts` + variables `.env.local`
4. **T4** Frontend: crear hook genérico `src/shared/hooks/useChannel.ts`
5. **Prioridad 1**: Evento `NewOrderReceived` → seller ve nuevas órdenes en tiempo real

### Sprint 2 — Servicios y aprobaciones
6. **Prioridad 2**: Evento `NewBookingReceived` → calendario de servicios en tiempo real
7. **Prioridad 3**: Evento `StoreStatusChanged` → seller notificado al ser aprobado
8. **Prioridad 4**: Evento `ProductStatusChanged` → seller notificado al aprobar producto

### Sprint 3 — Soporte y categorías
9. **Prioridad 5**: Evento `TicketMessageReceived` → chat de soporte en tiempo real
10. **Prioridad 6**: Evento `CategoryUpdated` → mega-menu actualizado al instante

---

## Notas

- Reverb corre en puerto **8080** (separado del API en 8000)
- `ShouldBroadcastNow` = sincrónico (sin queue). Cambiar a `ShouldBroadcast` si tienes queue worker
- Canales privados requieren autenticación — configurar `routes/channels.php`
- En producción: `REVERB_HOST=tudominio.com`, `REVERB_SCHEME=https`, puerto 443