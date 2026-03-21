# API de Autenticacion - Lyrium Marketplace

**Base URL:** `http://localhost:8000/api`

**Headers requeridos en todas las peticiones:**
```
Content-Type: application/json
Accept: application/json
```

**Headers adicionales para rutas protegidas:**
```
Authorization: Bearer {token}
```

---

## Resumen de Endpoints

| Metodo | Endpoint | Auth | Descripcion |
|--------|----------|------|-------------|
| POST | `/auth/login` | No | Iniciar sesion (email o username) |
| POST | `/auth/register` | No | Registro de vendedor + tienda |
| POST | `/auth/register-customer` | No | Registro de cliente |
| POST | `/auth/verify-otp` | No | Verificar codigo OTP (email) |
| POST | `/auth/resend-otp` | No | Reenviar codigo OTP |
| POST | `/auth/google` | No | Login/registro con Google |
| POST | `/auth/logout` | Si | Cerrar sesion |
| GET | `/auth/validate` | Si | Validar token y obtener usuario |
| POST | `/auth/refresh` | Si | Renovar token |

---

## Flujo de Autenticacion

```
Vendedor:
  register ──> verify-otp ──> login ──> (usar token)
                                           │
Cliente:                                   ├── validate (verificar sesion)
  register-customer ──> verify-otp ──> login  ├── refresh (renovar token)
                                           └── logout (cerrar sesion)
Google (solo clientes):
  google ──> (usuario creado/vinculado, token emitido)
```

### Notas importantes:
- Despues de `register` o `register-customer`, el usuario **debe verificar su email** con OTP antes de poder hacer login.
- Si un usuario no verificado intenta login, recibe un 403 con `requires_verification: true` y se le envia un nuevo codigo OTP automaticamente.
- Google Auth **no requiere verificacion OTP** (el email ya esta verificado por Google).
- Google Auth solo crea usuarios con rol `customer`.

---

## Roles de Usuario

| Rol | Descripcion | Como se crea |
|-----|-------------|--------------|
| `administrator` | Acceso total al sistema | Seeder (manual) |
| `seller` | Gestiona tienda y productos | `POST /auth/register` |
| `customer` | Compra productos | `POST /auth/register-customer` o `POST /auth/google` |
| `logistics_operator` | Gestiona envios | Panel admin (manual) |

---

## Endpoints Detallados

### 1. Login

```
POST /api/auth/login
```

**Request:**
```json
{
  "email": "usuario@email.com",
  "password": "mipassword123"
}
```

| Campo | Tipo | Requerido | Reglas |
|-------|------|-----------|--------|
| `email` | string | Si | Email valido o username |
| `password` | string | Si | - |

**Response exitoso (200):**
```json
{
  "success": true,
  "token": "1|abc123def456...",
  "user": {
    "id": 1,
    "username": "mi_tienda",
    "email": "usuario@email.com",
    "nicename": "mi-tienda",
    "display_name": "Mi Tienda",
    "role": "seller",
    "avatar": null
  }
}
```

**Response - credenciales invalidas (401):**
```json
{
  "success": false,
  "error": "Credenciales invalidas."
}
```

**Response - email no verificado (403):**
```json
{
  "success": false,
  "error": "Debes verificar tu correo electronico. Te enviamos un nuevo codigo.",
  "requires_verification": true,
  "email": "usuario@email.com"
}
```

> Cuando recibes `requires_verification: true`, redirige al usuario a la pantalla de verificacion OTP.

---

### 2. Registro de Vendedor

```
POST /api/auth/register
```

**Request:**
```json
{
  "storeName": "Mi Tienda Bio",
  "email": "tienda@email.com",
  "phone": "999888777",
  "password": "mipassword123",
  "ruc": "20123456789"
}
```

| Campo | Tipo | Requerido | Reglas |
|-------|------|-----------|--------|
| `storeName` | string | Si | Max 255 caracteres |
| `email` | string | Si | Email valido, unico en BD |
| `phone` | string | Si | Max 20 caracteres |
| `password` | string | Si | Min 8 caracteres |
| `ruc` | string | Si | Exactamente 11 digitos, unico en BD |

**Response exitoso (201):**
```json
{
  "success": true,
  "message": "Registro exitoso. Revisa tu correo para el codigo de verificacion.",
  "requires_verification": true,
  "email": "tienda@email.com"
}
```

**Response - errores de validacion (422):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "ruc": ["El RUC debe tener exactamente 11 digitos."],
    "email": ["Este correo ya esta registrado."]
  }
}
```

**Que se crea en el backend:**
- Usuario con rol `seller`, `is_seller: true`, `document_type: RUC`
- Tienda con `status: pending` (requiere aprobacion del admin)
- Se envia codigo OTP de 6 digitos al email

---

### 3. Registro de Cliente

```
POST /api/auth/register-customer
```

**Request:**
```json
{
  "name": "Juan Perez",
  "email": "juan@email.com",
  "password": "mipassword123"
}
```

| Campo | Tipo | Requerido | Reglas |
|-------|------|-----------|--------|
| `name` | string | Si | Max 255 caracteres |
| `email` | string | Si | Email valido, unico en BD |
| `password` | string | Si | Min 8 caracteres |

**Response exitoso (201):**
```json
{
  "success": true,
  "message": "Cuenta creada. Revisa tu correo para el codigo de verificacion.",
  "requires_verification": true,
  "email": "juan@email.com"
}
```

**Response - errores de validacion (422):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["Este correo ya esta registrado."]
  }
}
```

---

### 4. Verificar OTP

```
POST /api/auth/verify-otp
```

**Request:**
```json
{
  "email": "usuario@email.com",
  "code": "482937"
}
```

| Campo | Tipo | Requerido | Reglas |
|-------|------|-----------|--------|
| `email` | string | Si | Email valido |
| `code` | string | Si | Exactamente 6 caracteres |

**Response exitoso (200):**
```json
{
  "success": true
}
```

**Response - ya verificado (200):**
```json
{
  "success": true,
  "message": "El email ya esta verificado."
}
```

**Response - codigo incorrecto (422):**
```json
{
  "success": false,
  "error": "Codigo incorrecto. Te quedan 4 intentos."
}
```

**Response - codigo expirado (422):**
```json
{
  "success": false,
  "error": "El codigo ha expirado. Solicita uno nuevo."
}
```

**Response - demasiados intentos (422):**
```json
{
  "success": false,
  "error": "Demasiados intentos. Solicita un nuevo codigo."
}
```

**Response - usuario no encontrado (404):**
```json
{
  "success": false,
  "error": "Usuario no encontrado."
}
```

**Reglas del OTP:**
- Codigo de 6 digitos enviado por email
- Expira en **10 minutos**
- Maximo **5 intentos** por codigo
- Despues de verificar, el usuario puede hacer login normalmente

---

### 5. Reenviar OTP

```
POST /api/auth/resend-otp
```

**Rate limit:** 3 peticiones por minuto.

**Request:**
```json
{
  "email": "usuario@email.com"
}
```

| Campo | Tipo | Requerido | Reglas |
|-------|------|-----------|--------|
| `email` | string | Si | Email valido |

**Response exitoso (200):**
```json
{
  "success": true,
  "message": "Codigo reenviado a tu correo."
}
```

**Response - cooldown activo (429):**
```json
{
  "success": false,
  "error": "Espera 60 segundos antes de solicitar otro codigo."
}
```

**Response - ya verificado (200):**
```json
{
  "success": true,
  "message": "El email ya esta verificado."
}
```

**Cooldown:** 60 segundos entre reenvios.

---

### 6. Google Auth

```
POST /api/auth/google
```

**Request:**
```json
{
  "credential": "eyJhbGciOiJSUzI1NiIs..."
}
```

| Campo | Tipo | Requerido | Descripcion |
|-------|------|-----------|-------------|
| `credential` | string | Si | ID Token JWT de Google Sign-In |

**Response exitoso (200):**
```json
{
  "success": true,
  "token": "1|xyz789...",
  "user": {
    "id": 5,
    "username": "juan_perez",
    "email": "juan@gmail.com",
    "nicename": "juan-perez",
    "display_name": "Juan Perez",
    "role": "customer",
    "avatar": "https://lh3.googleusercontent.com/a/photo..."
  },
  "is_new_user": true
}
```

**Response - token invalido (401):**
```json
{
  "success": false,
  "error": "Token de Google invalido."
}
```

**Logica del backend:**

| Escenario | Accion | `is_new_user` |
|-----------|--------|---------------|
| Usuario con ese `google_id` ya existe | Login directo | `false` |
| Email ya existe pero sin `google_id` | Vincula cuenta a Google, actualiza avatar | `false` |
| Email no existe | Crea usuario `customer`, email auto-verificado | `true` |

**Integracion en frontend (React):**

```bash
npm install @react-oauth/google
```

```tsx
// Wrapper (layout o providers)
import { GoogleOAuthProvider } from '@react-oauth/google';

<GoogleOAuthProvider clientId={process.env.NEXT_PUBLIC_GOOGLE_CLIENT_ID}>
  {children}
</GoogleOAuthProvider>

// Boton de login
import { GoogleLogin } from '@react-oauth/google';

<GoogleLogin
  onSuccess={(response) => {
    // response.credential contiene el ID Token
    // Enviar a POST /api/auth/google
    fetch('/api/auth/google', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ credential: response.credential }),
    });
  }}
  onError={() => console.error('Google login failed')}
/>
```

**Variables de entorno necesarias:**
```env
# Backend (.env)
GOOGLE_CLIENT_ID=tu-client-id.apps.googleusercontent.com

# Frontend (.env.local)
NEXT_PUBLIC_GOOGLE_CLIENT_ID=tu-client-id.apps.googleusercontent.com
```

---

### 7. Logout

```
POST /api/auth/logout
```

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "success": true
}
```

Elimina el token actual del usuario.

---

### 8. Validar Token

```
GET /api/auth/validate
```

**Headers:** `Authorization: Bearer {token}`

**Response exitoso (200):**
```json
{
  "id": 1,
  "username": "admin",
  "email": "admin@lyrium.com",
  "nicename": "admin",
  "display_name": "Admin Marketplace",
  "role": "administrator",
  "avatar": null
}
```

**Response - token invalido (401):**
```json
{
  "message": "Unauthenticated."
}
```

Usa este endpoint para:
- Verificar si un token sigue siendo valido
- Obtener la data actualizada del usuario en sesion

---

### 9. Refresh Token

```
POST /api/auth/refresh
```

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "token": "2|newtoken789..."
}
```

Elimina el token anterior y genera uno nuevo. Util para renovar tokens antes de que expiren.

---

## Objeto User (referencia)

Todos los endpoints que devuelven un usuario usan este formato:

```typescript
interface User {
  id: number;
  username: string;       // Identificador unico (slug)
  email: string;
  nicename: string;       // Slug del nombre
  display_name: string;   // Nombre para mostrar
  role: 'administrator' | 'seller' | 'customer' | 'logistics_operator';
  avatar: string | null;  // URL de imagen (Google photo o custom)
}
```

---

## Codigos de Estado HTTP

| Codigo | Significado |
|--------|-------------|
| 200 | Exito |
| 201 | Recurso creado (registro) |
| 401 | No autenticado / credenciales invalidas |
| 403 | Email no verificado (`requires_verification: true`) |
| 404 | Usuario no encontrado |
| 422 | Error de validacion / OTP incorrecto |
| 429 | Rate limit excedido (resend-otp) |

---

## Ejemplo: Flujo Completo de Registro + Login

```bash
# 1. Registrar cliente
curl -X POST http://localhost:8000/api/auth/register-customer \
  -H "Content-Type: application/json" \
  -d '{"name":"Juan","email":"juan@mail.com","password":"secret1234"}'

# Response: { requires_verification: true, email: "juan@mail.com" }
# >>> Se envia OTP al email

# 2. Verificar OTP (codigo recibido por email)
curl -X POST http://localhost:8000/api/auth/verify-otp \
  -H "Content-Type: application/json" \
  -d '{"email":"juan@mail.com","code":"482937"}'

# Response: { success: true }

# 3. Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"juan@mail.com","password":"secret1234"}'

# Response: { success: true, token: "1|abc...", user: {...} }

# 4. Usar token en peticiones protegidas
curl http://localhost:8000/api/auth/validate \
  -H "Authorization: Bearer 1|abc..."

# 5. Cerrar sesion
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Authorization: Bearer 1|abc..."
```

---

## Ejemplo: Flujo Google Auth

```bash
# 1. Obtener credential desde Google Sign-In (frontend)
# El boton de Google devuelve un JWT (credential)

# 2. Enviar al backend
curl -X POST http://localhost:8000/api/auth/google \
  -H "Content-Type: application/json" \
  -d '{"credential":"eyJhbGciOiJSUzI1NiIs..."}'

# Response: { success: true, token: "1|xyz...", user: {...}, is_new_user: true }

# 3. Usar el token normalmente
curl http://localhost:8000/api/auth/validate \
  -H "Authorization: Bearer 1|xyz..."
```

---

## Manejo de Errores en Frontend

```typescript
const result = await fetch('/api/auth/login', { ... });
const data = await result.json();

if (data.requires_verification) {
  // Redirigir a pantalla de verificacion OTP
  // Usar data.email para pre-llenar el campo
  redirectTo('/verify-otp', { email: data.email });
  return;
}

if (!data.success) {
  // Mostrar error al usuario
  showError(data.error);
  return;
}

// Login exitoso
saveToken(data.token);
redirectByRole(data.user.role);
```

```typescript
// Redireccion por rol
function redirectByRole(role: string) {
  switch (role) {
    case 'administrator': return router.push('/admin');
    case 'seller':        return router.push('/seller');
    case 'customer':      return router.push('/customer');
    case 'logistics_operator': return router.push('/logistics');
  }
}
```
