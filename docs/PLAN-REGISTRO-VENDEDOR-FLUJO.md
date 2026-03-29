 # Plan: Flujo Completo de Registro de Vendedor + OTP + Aprobación

**Fecha:** 2026-03-29
**Estado:** ✅ Implementado
**Prioridad:** Alta

---

## Problema Actual

El registro de vendedor en el frontend (`useAuthForm.ts`) **NO llama al backend**. Solo valida localmente y muestra un mensaje hardcodeado. Los datos nunca se guardan en la base de datos.

Además faltan:
- Pantalla de verificación OTP
- Email de notificación cuando el admin aprueba/rechaza la tienda
- Flujo de redirección post-registro

---

## Flujo Objetivo

```
[1] Vendedor llena formulario de registro
         ↓
[2] Frontend POST /api/auth/register → Backend crea user + store (pending) + envía OTP
         ↓
[3] Frontend redirige a pantalla OTP: "Ingresa el código de 6 dígitos"
         ↓
[4] Vendedor ingresa código → POST /api/auth/verify-otp
         ↓
[5] Mensaje: "Tu correo fue verificado. Te notificaremos cuando tu tienda sea aprobada."
         ↓
[6] Admin aprueba tienda desde /admin/sellers → PUT /stores/{id}/status
         ↓
[7] Backend envía email al vendedor: "Tu tienda fue aprobada, ya puedes ingresar"
         ↓
[8] Vendedor hace login → accede a su panel
```

---

## Tareas

### FRONTEND (F:\FRONTEND\fe-001-marketplace-admin\frontapp)

#### Tarea 1: Conectar registro al backend

**Archivo:** `src/features/auth/hooks/useAuthForm.ts`

**Cambio:** La función `register` (línea 52-75) actualmente NO llama a la API. Debe:

1. Importar o crear un servicio que llame al backend
2. Para vendedor: `POST http://127.0.0.1:8000/api/auth/register` con body:
```json
{
  "storeName": "valor del formulario",
  "email": "valor del formulario",
  "phone": "valor del formulario",
  "password": "valor del formulario",
  "ruc": "valor del formulario"
}
```
3. Para cliente: `POST http://127.0.0.1:8000/api/auth/register-customer` con body:
```json
{
  "name": "valor del formulario",
  "email": "valor del formulario",
  "password": "valor del formulario"
}
```
4. Manejar respuesta exitosa (`requires_verification: true`) → redirigir a pantalla OTP
5. Manejar errores de validación (422) → mostrar mensajes del backend

**Código actual (REEMPLAZAR):**
```typescript
// ACTUAL — líneas 52-75 en useAuthForm.ts
const register = useCallback(async (data: RegisterFormData) => {
    setFormError(null);
    setFormSuccess(null);

    if (userType === 'vendedor') {
        if (data.ruc.length !== 11) {
            setFormError('El RUC debe tener exactamente 11 dígitos');
            return { success: false, message: 'El RUC debe...' };
        }
        // ⚠️ NUNCA LLAMA AL BACKEND — solo muestra mensaje fake
        setFormSuccess('Registro exitoso. Recibirás un email cuando sea aprobado.');
        return { success: true };
    }
    // ...
}, [userType]);
```

**Código nuevo (IMPLEMENTAR):**
```typescript
const register = useCallback(async (data: RegisterFormData) => {
    setFormError(null);
    setFormSuccess(null);

    try {
        const LARAVEL_API = process.env.NEXT_PUBLIC_LARAVEL_API_URL || 'http://127.0.0.1:8000/api';

        let url: string;
        let body: Record<string, string>;

        if (userType === 'vendedor') {
            url = `${LARAVEL_API}/auth/register`;
            body = {
                storeName: data.storeName,
                email: data.email,
                phone: data.phone,
                password: data.password,
                ruc: data.ruc,
            };
        } else {
            url = `${LARAVEL_API}/auth/register-customer`;
            body = {
                name: data.storeName || data.email.split('@')[0],
                email: data.email,
                password: data.password,
            };
        }

        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(body),
        });

        const result = await response.json();

        if (!response.ok) {
            // Error de validación (422) u otro error
            const errorMsg = result.errors
                ? Object.values(result.errors).flat().join('. ')
                : result.error || 'Error al registrar';
            setFormError(errorMsg);
            return { success: false, message: errorMsg };
        }

        if (result.requires_verification) {
            // Redirigir a pantalla OTP con el email
            setFormSuccess('Registro exitoso. Revisa tu correo para el código de verificación.');
            // TODO: redirigir a /auth/verify-otp?email=result.email
            return { success: true, message: result.message, requiresVerification: true, email: result.email };
        }

        setFormSuccess(result.message);
        return { success: true, message: result.message };
    } catch (error) {
        setFormError('Error de conexión con el servidor');
        return { success: false, message: 'Error de conexión' };
    }
}, [userType]);
```

---

#### Tarea 2: Crear pantalla de verificación OTP

**Archivo nuevo:** `src/app/(public)/auth/verify-otp/page.tsx`

Crear una página con:
- Input para código de 6 dígitos
- Recibe el `email` como query param (`/auth/verify-otp?email=user@mail.com`)
- Botón "Verificar" → `POST /api/auth/verify-otp` con `{ email, code }`
- Botón "Reenviar código" → `POST /api/auth/resend-otp` con `{ email }` (cooldown 60s)
- Manejar respuestas:
  - Éxito → mostrar mensaje según tipo de usuario:
    - **Vendedor:** "Tu correo fue verificado. Te notificaremos por email cuando tu tienda sea aprobada." + botón "Volver al inicio"
    - **Cliente:** "Tu correo fue verificado. Ya puedes iniciar sesión." + redirigir a login
  - Error código incorrecto (422): "Código incorrecto. Te quedan X intentos."
  - Error expirado (422): "El código ha expirado. Solicita uno nuevo."
  - Ya verificado (200): "El email ya está verificado."

**Referencia de diseño:** Usar el mismo estilo visual del `RegisterPanel.tsx` (bordes redondeados, iconos, gradientes sky-500).

**Endpoints del backend (ya implementados):**
```
POST /api/auth/verify-otp   → { email: string, code: string }
POST /api/auth/resend-otp   → { email: string }
```

---

#### Tarea 3: Redirigir a OTP después del registro

**Archivo:** `src/features/auth/hooks/useAuthForm.ts` (o el componente que llama a `register`)

Después de registro exitoso con `requires_verification: true`:
```typescript
import { useRouter } from 'next/navigation';
const router = useRouter();

// Después de registro exitoso:
if (result.requiresVerification) {
    router.push(`/auth/verify-otp?email=${encodeURIComponent(result.email)}`);
}
```

---

#### Tarea 4: Manejar login de vendedor con tienda pendiente

**Archivo:** Componente de login o `useLogin.ts`

Cuando un vendedor verificado hace login pero su tienda está en `pending`:
- El login funciona (recibe token + user)
- Pero al intentar acceder al panel de vendedor, el middleware `EnsureStoreApproved` del backend devuelve 403
- El frontend debe mostrar: "Tu tienda está pendiente de aprobación. Te notificaremos por email."
- Puede tener una página `/seller/pending` que muestre este estado

---

### BACKEND (F:\TEST\Backend-Lyrium)

#### Tarea 5: Crear notificación de aprobación/rechazo de tienda

**Archivo nuevo:** `app/Notifications/StoreStatusNotification.php`

```php
<?php

namespace App\Notifications;

use App\Models\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StoreStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Store $store,
        private string $newStatus,
        private ?string $reason = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        if ($this->newStatus === 'approved') {
            return (new MailMessage)
                ->subject('¡Tu tienda ha sido aprobada! - Lyrium BioMarketplace')
                ->greeting('¡Felicidades, ' . $notifiable->name . '!')
                ->line('Tu tienda "' . $this->store->trade_name . '" ha sido aprobada.')
                ->line('Ya puedes iniciar sesión y comenzar a gestionar tus productos.')
                ->action('Iniciar Sesión', config('app.frontend_url') . '/auth')
                ->line('¡Bienvenido a Lyrium BioMarketplace!');
        }

        if ($this->newStatus === 'rejected') {
            $mail = (new MailMessage)
                ->subject('Actualización sobre tu tienda - Lyrium BioMarketplace')
                ->greeting('Hola, ' . $notifiable->name)
                ->line('Lamentablemente, tu tienda "' . $this->store->trade_name . '" no fue aprobada.');

            if ($this->reason) {
                $mail->line('Motivo: ' . $this->reason);
            }

            return $mail->line('Puedes contactarnos si tienes alguna consulta.');
        }

        return (new MailMessage)
            ->subject('Actualización de tu tienda - Lyrium BioMarketplace')
            ->line('El estado de tu tienda "' . $this->store->trade_name . '" ha cambiado a: ' . $this->newStatus);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'store_id' => $this->store->id,
            'store_name' => $this->store->trade_name,
            'status' => $this->newStatus,
            'reason' => $this->reason,
        ];
    }
}
```

---

#### Tarea 6: Enviar notificación al cambiar status de tienda

**Archivo:** `app/Http/Controllers/Api/StoreController.php` → método `updateStatus`

Agregar al final del método `updateStatus`, después de cambiar el status:

```php
use App\Notifications\StoreStatusNotification;

// Después de $store->update(['status' => $newStatus]):
$store->owner->notify(new StoreStatusNotification(
    $store,
    $newStatus,
    $request->input('reason'), // motivo opcional del admin
));
```

**También:** Agregar campo opcional `reason` al `UpdateStoreStatusRequest`:
```php
'reason' => ['nullable', 'string', 'max:500'],
```

---

#### Tarea 7: Agregar FRONTEND_URL al .env

**Archivo:** `F:\TEST\Backend-Lyrium\.env`

Verificar que exista:
```
FRONTEND_URL=http://localhost:3000
```

**Archivo:** `F:\TEST\Backend-Lyrium\config\app.php`

Verificar que exista:
```php
'frontend_url' => env('FRONTEND_URL', 'http://localhost:3000'),
```

---

## Orden de Ejecución Recomendado

1. **Tarea 5** (Backend: crear StoreStatusNotification) — independiente
2. **Tarea 6** (Backend: integrar notificación en StoreController) — depende de 5
3. **Tarea 7** (Backend: verificar FRONTEND_URL) — independiente
4. **Tarea 1** (Frontend: conectar registro al backend) — independiente
5. **Tarea 2** (Frontend: crear pantalla OTP) — independiente
6. **Tarea 3** (Frontend: redirigir a OTP) — depende de 1 y 2
7. **Tarea 4** (Frontend: manejar tienda pendiente) — independiente

Tareas paralelas posibles: [5, 7, 1, 2, 4] pueden ejecutarse simultáneamente.

---

## Verificación Final

Después de implementar todo, verificar este flujo completo:

```bash
# 1. Registro vendedor desde frontend
# → Debe crear user + store en BD
# → Debe enviar OTP al email

# 2. Verificar en BD
SELECT * FROM users WHERE email = 'test@mail.com';
SELECT * FROM stores WHERE corporate_email = 'test@mail.com';
# → user debe existir con rol 'seller'
# → store debe existir con status 'pending'

# 3. Verificar OTP desde pantalla OTP del frontend
# → Debe marcar email_verified_at en users
# → Debe mostrar mensaje "te notificaremos cuando sea aprobado"

# 4. Admin aprueba tienda desde /admin/sellers
# → Debe cambiar store.status a 'approved'
# → Debe enviar email al vendedor

# 5. Vendedor hace login
# → Debe poder acceder al panel /seller
```

---

## Archivos Afectados (Resumen)

### Frontend
| Archivo | Acción |
|---------|--------|
| `src/features/auth/hooks/useAuthForm.ts` | MODIFICAR — conectar al backend |
| `src/app/(public)/auth/verify-otp/page.tsx` | CREAR — pantalla OTP |
| Login/panel vendedor | MODIFICAR — manejar tienda pendiente |

### Backend
| Archivo | Acción |
|---------|--------|
| `app/Notifications/StoreStatusNotification.php` | CREAR |
| `app/Http/Controllers/Api/StoreController.php` | MODIFICAR — enviar notificación |
| `app/Http/Requests/UpdateStoreStatusRequest.php` | MODIFICAR — agregar campo reason |
| `.env` | VERIFICAR — FRONTEND_URL |
