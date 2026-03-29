# Reporte de Implementación: Flujo de Registro de Vendedor y Aprobación

Este documento detalla todas las modificaciones y funcionalidades implementadas a nivel de Frontend y Backend para el flujo completo de "Registro → OTP → Aprobación" de vendedores y clientes en el marketplace.

## 1. Modificaciones en el Frontend (`frontend/fe-001-marketplace-admin`)

### 1.1 Registro Compartido y Formularios Dinámicos
- **`RegisterPanel.tsx`**: 
  - Se modificó la interfaz para que sea dinámica según el tipo de usuario (Cliente vs. Vendedor).
  - Los campos **RUC**, **Razón Social** o **Teléfono** adicionales se ocultan automáticamente si el usuario selecciona crear una cuenta de Cliente.
  - Se conectaron correctamente al estado y envío del componente.
- **`hooks/useAuthForm.ts`**:
  - Se agregó la conectividad a los endpoints separados: `POST /api/auth/register` para vendedores y `POST /api/auth/register-customer` para clientes.
  - Se configuró la redirección a `/auth/verify-otp?email=x` de manera adecuada al recibir un 201 Created.

### 1.2 Verificación de Identidad (OTP)
- **`app/(public)/auth/verify-otp/page.tsx`**:
  - Se desarrolló la vista completa de Verificación OTP con funcionalidad avanzada (entradas auto-avanzables, validación por teclado numérico y pegado rápido).
  - Incluye temporizador visual (cooldown) de 60 segundos para evitar abusos en el sistema de reenvío (Rate Limiting).
  - Control de redirección post-verificación: Si es cliente redirige al `/login`, y si es vendedor le advierte que la tienda está en evaluación.

### 1.3 Bloqueo de Acceso (Tienda Pendiente)
- **`app/seller/pending/page.tsx`**:
  - Se implementó la vista disuasoria para vendedores recién registrados o aún no aprobados.
- **`app/seller/SellerLayoutClient.tsx`**:
  - Se incorporó una guarda de hidratación que consulta en montaje local el endpoint `GET /api/stores/me`.
  - El acceso a todas las rutas dentro de `/seller/*` queda totalmente restringido si el servidor devuelve un status diferente a `approved` (ej. `pending`), forzando una redirección de Next.js (`router.replace`) a la pantalla Pendiente.

### 1.4 Panel de Administrador: Control de Estado de Tiendas
- **`SellerList.tsx` / `SellersPageClient.tsx`**:
  - Se refactorizó la tabla de gestión de vendedores incorporando un moderno **menú dropdown de 3 puntos (⋮)**.
  - **Lógica Dinámica**: Se adaptaron las acciones visibles dependiendo del estado real del vendedor:
    - *Pendientes*: Opciones "Aprobar Tienda" o "Rechazar Tienda".
    - *Activos*: Opciones "Suspender Cuenta" o "Dar de Baja".
    - *Suspendidos/Rechazados*: "Reactivar Tienda".
  - Todos los estados se muestran con insignias visuales (ej. Reloj amber `Pendiente`, Punto verde `Aprobada`).
- **`hooks/useControlVendedores.ts` & `sellerRepository.ts`**:
  - Se estabilizó el mapeo de estados provenientes del backend (`approved`, `pending`, `banned`, `rejected`) a la arquitectura de tipos del Frontend (`ACTIVE`, `PENDING`, `SUSPENDED`, `REJECTED`).
  - Se configuró el envío del parámetro opcional `reason` para que el administrador justifique bloqueos o rechazos.

## 2. Modificaciones en el Backend (`Backend-Lyrium`)

### 2.1 Controladores de Autenticación
- **`AuthController@verifyOtp`**:
  - Adaptado para devolver la propiedad `user_type` (`customer` o `seller`) junto al `message` correcto.
  - Esto solucionó los problemas de enrutamiento fallidos en el frontend post-verificación.
- **`StoreController@updateStatus`**:
  - Endpoint `PUT /api/stores/{id}/status` modificado para aceptar y procesar exitosamente los estados `approved`, `rejected` y `banned`.
  - Inyección de fechas (`approved_at`, `banned_at`) para garantizar trazabilidad de auditoría.
  - Enlace al modelo `StoreStatusNotification`.

### 2.2 Sistema de Notificaciones (Mails y BD)
- **`StoreStatusNotification.php`**:
  - Creación del sistema que notifica al dueño del negocio los cambios de estado impuestos por el administrador.
  - Correo para **aprobación**: Celebra la apertura de tienda y provee instrucciones vitales.
  - Correo para **rechazo/bloqueo**: Informa las razones o justificaciones ingresadas por el administrador en base al parámetro `reason`.
  - Ejecución encolada (`ShouldQueue`) para no penalizar el tiempo de respuesta del panel administrativo.

### 2.3 Seguridad de Sesión
- **`EnsureStoreApproved.php`**:
  - Middleware encargado de interceptar todas las peticiones a la API del vendedor, retornando HTTP `403 Forbidden` si la tienda intentase eludir las protecciones frontend y no está validada.

---

**Estado Final:** 
La funcionalidad principal ha sido terminada en su integridad. Todo vendedor debe de validar su correo y esperar validación administrativa para acceder a las herramientas funcionales. Los correos saldrán de manera transparente e inyectarán el reporte al portal de admin sin bloquear requests.
