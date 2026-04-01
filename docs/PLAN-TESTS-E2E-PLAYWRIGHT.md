# PLAN-TESTS-E2E-PLAYWRIGHT

Plan completo de pruebas End-to-End para el biomarketplace Lyrium usando Playwright en modo headed (navegador visible). Cobertura total: admin, seller, customer, logistics, páginas públicas y WebSockets.

---

## Estado actual del proyecto

| Item | Valor |
|------|-------|
| Tests existentes | **0** — codebase sin tests |
| Páginas UI testables | **74** |
| Endpoints API documentados | **~203** |
| Roles | administrator, seller, customer, logistics_operator |
| Backend | `http://localhost:8000` |
| Frontend | `http://localhost:3000` |
| WebSocket (Reverb) | `ws://localhost:8080` |

### Credenciales de prueba disponibles

| Rol | Email | Password | Datos extra |
|-----|-------|----------|-------------|
| Administrator | `pierre@admin.com` | `password` | — |
| Seller (seed) | `angel.ipanaque.torre@gmail.com` | `password` | Tienda: "BioTienda Demo" (aprobada) |

---

## Estructura del proyecto de tests

```
F:\TEST\e2e\
  playwright.config.ts
  package.json
  helpers/
    auth.ts              # loginAs() helper reutilizable
    api.ts               # Setup/teardown via API directa
  fixtures/
    test-image.jpg       # Para tests de upload
  auth-state/
    admin.json           # Storage state guardado post-login admin
    seller.json          # Storage state guardado post-login seller
    customer.json        # Storage state guardado post-login customer
  tests/
    00-setup/
      health-check.spec.ts
    01-admin/
      01-auth.spec.ts
      02-categories.spec.ts
      03-sellers.spec.ts
      04-products.spec.ts
      05-helpdesk.spec.ts
      06-plans.spec.ts
      07-contracts.spec.ts
      08-dashboard-renders.spec.ts
    02-seller/
      01-auth.spec.ts
      02-store.spec.ts
      03-catalog.spec.ts
      04-services.spec.ts
      05-orders.spec.ts
      06-plans.spec.ts
      07-helpdesk.spec.ts
      08-logistics.spec.ts
    03-customer/
      01-registration.spec.ts
      02-homepage.spec.ts
      03-product-detail.spec.ts
      04-cart-checkout.spec.ts
      05-orders-bookings.spec.ts
      06-profile-support.spec.ts
    04-cross-module/
      01-category-broadcast.spec.ts
      02-notifications-websocket.spec.ts
    05-static/
      static-renders.spec.ts
```

**Total: 26 archivos de test**

---

## Configuración — `playwright.config.ts`

```typescript
import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './tests',
  timeout: 30_000,
  expect: { timeout: 10_000 },
  fullyParallel: false,   // secuencial — los datos fluyen entre fases
  workers: 1,
  retries: 1,
  reporter: [['html', { open: 'on-failure' }]],
  use: {
    baseURL: 'http://localhost:3000',
    headless: false,
    video: 'on-first-retry',
    screenshot: 'only-on-failure',
    actionTimeout: 10_000,
  },
  projects: [
    { name: 'setup', testMatch: /00-setup/ },
    {
      name: 'admin',
      testMatch: /01-admin/,
      dependencies: ['setup'],
      use: { storageState: 'auth-state/admin.json' },
    },
    {
      name: 'seller',
      testMatch: /02-seller/,
      dependencies: ['setup'],
      use: { storageState: 'auth-state/seller.json' },
    },
    {
      name: 'customer',
      testMatch: /03-customer/,
      dependencies: ['admin'],
    },
    {
      name: 'cross-module',
      testMatch: /04-cross-module/,
      dependencies: ['admin', 'seller'],
    },
    {
      name: 'static',
      testMatch: /05-static/,
      dependencies: ['setup'],
    },
  ],
});
```

---

## Helper principal — `helpers/auth.ts`

```typescript
import { Page, BrowserContext } from '@playwright/test';

export async function loginAs(
  page: Page,
  email: string,
  password: string,
  role: 'admin' | 'seller' | 'customer'
) {
  await page.goto('/login');

  // 1. Dismiss IntroCover ("Entrar" button)
  const introCover = page.locator('button', { hasText: 'Entrar' });
  if (await introCover.isVisible({ timeout: 3000 }).catch(() => false)) {
    await introCover.click();
  }

  // 2. Cambiar a tab "cliente" si es necesario
  if (role === 'customer') {
    await page.locator('[data-type="cliente"], button:has-text("Cliente")').click();
  }

  // 3. Rellenar formulario
  await page.locator('#login-email, input[name="username"]').fill(email);
  await page.locator('#login-password, input[name="password"]').fill(password);
  await page.locator('button[type="submit"]').click();

  // 4. Esperar hard navigation (window.location.href)
  const expectedPath = { admin: '/admin', seller: '/seller', customer: '/customer' }[role];
  await page.waitForURL(`**${expectedPath}**`, { waitUntil: 'networkidle', timeout: 15_000 });
}

export async function saveAuthState(context: BrowserContext, path: string) {
  await context.storageState({ path });
}
```

---

## Comandos

```bash
cd F:\TEST\e2e

# Instalar dependencias
npm init -y
npm install -D @playwright/test
npx playwright install chromium

# Ejecutar todos los tests
npx playwright test --headed

# Ejecutar solo un grupo
npx playwright test tests/01-admin --headed
npx playwright test tests/02-seller --headed
npx playwright test tests/04-cross-module --headed

# Ver reporte HTML
npx playwright show-report
```

---

## Fase 0 — Setup / Health Check

**Archivo:** `tests/00-setup/health-check.spec.ts`

| Test | Verificación |
|------|-------------|
| Backend running | `GET /api/categories` retorna HTTP 200 |
| Frontend running | `GET http://localhost:3000` retorna HTTP 200 |
| Homepage carga | Página `/` renderiza sin errores |

---

## Fase 1 — Módulo Admin

### 1.1 Auth (`01-auth.spec.ts`)

| Test | Pasos | Resultado esperado |
|------|-------|--------------------|
| Login exitoso | `pierre@admin.com` / `password` | Redirect a `/admin/sellers` |
| Sesión persiste | Refresh en `/admin/sellers` | Sigue en `/admin/sellers` |
| Guardar state | `saveAuthState(context, 'auth-state/admin.json')` | Archivo generado |

---

### 1.2 Categorías (`02-categories.spec.ts`)

| Test | Pasos | Resultado esperado |
|------|-------|--------------------|
| Página carga | Navegar a `/admin/categories` | Árbol de categorías visible |
| Crear categoría producto | Formulario: nombre + tipo=producto | Aparece en árbol |
| Crear categoría servicio | Formulario: nombre + tipo=servicio | Aparece en árbol |
| Editar categoría | Modificar nombre | Toast "actualizado" |
| Filtro padre por tipo | Tipo=servicio → select padre | Solo muestra padres tipo servicio |
| Upload imagen | Seleccionar archivo .jpg | Imagen aparece en la categoría |
| Eliminar categoría | Categoría hoja → eliminar | Desaparece del árbol |

---

### 1.3 Sellers (`03-sellers.spec.ts`)

| Test | Pasos | Resultado esperado |
|------|-------|--------------------|
| Lista carga | Navegar a `/admin/sellers` | "BioTienda Demo" visible |
| Ver detalle | Click en seller → `/admin/sellers/[id]` | Detalle carga |
| Búsqueda | Escribir en buscador | Resultados filtrados |
| Tabs | Vendedores / Aprobacion / Auditoria / Validacion | Cada tab renderiza contenido |
| Cambiar estado tienda | Tienda pendiente → aprobar | Estado cambia a "approved" |

---

### 1.4 Productos (`04-products.spec.ts`)

| Test | Pasos | Resultado esperado |
|------|-------|--------------------|
| Lista productos admin | Tab aprobacion → `/admin/sellers` | Productos con badges de estado |
| Aprobar producto | Producto pending_review → aprobar | Estado → approved |
| Rechazar producto | Producto pending_review → rechazar con motivo | Estado → rejected |

---

### 1.5 Helpdesk (`05-helpdesk.spec.ts`)

| Test | Pasos | Resultado esperado |
|------|-------|--------------------|
| Página carga | Navegar a `/admin/helpdesk` | Lista de tickets visible |
| Ver ticket | Click en ticket | Hilo de conversación visible |
| Responder ticket | Escribir + enviar mensaje | Mensaje aparece en hilo |
| Cambiar estado | Open → In progress | Estado actualiza |

---

### 1.6 Planes (`06-plans.spec.ts`)

| Test | Pasos | Resultado esperado |
|------|-------|--------------------|
| Página con 6 tabs | Navegar a `/admin/planes` | Todos los tabs visibles |
| Lista de planes | Tab "Planes" | Emprende / Crece / Especial |
| Solicitudes | Tab "Solicitudes" | Lista de solicitudes de upgrade |
| Procesar solicitud | Aprobar/rechazar solicitud | Estado actualiza |

---

### 1.7 Contratos (`07-contracts.spec.ts`)

| Test | Pasos | Resultado esperado |
|------|-------|--------------------|
| Lista carga | Navegar a `/admin/contracts` | Lista (mock data) carga |
| Crear contrato | Formulario → submit | Contrato aparece en lista |
| Ver detalle | Click en contrato | Detalle con audit trail |

---

### 1.8 Dashboards (`08-dashboard-renders.spec.ts`)

Verificar que las páginas de solo-lectura cargan sin errores:

| Página | URL |
|--------|-----|
| Finanzas | `/admin/finance` |
| Operaciones | `/admin/operations` |
| Inventario | `/admin/inventory` |
| Analytics | `/admin/analytics` |
| RapiFac | `/admin/rapifac` |
| Pagos | `/admin/payments` |

---

## Fase 2 — Módulo Seller

### 2.1 Auth (`01-auth.spec.ts`)

| Test | Resultado esperado |
|------|-------------------|
| Login seller | Redirect a `/seller/profile` |
| Guardar state | `auth-state/seller.json` generado |

---

### 2.2 Mi Tienda (`02-store.spec.ts`)

| Test | Pasos | Resultado esperado |
|------|-------|--------------------|
| Datos cargan | Navegar a `/seller/store` | "BioTienda Demo" visible |
| Editar descripción | Modificar campo → guardar | Toast éxito |
| Subir logo | Upload .jpg | Imagen aparece como logo |
| Subir banner | Upload .jpg | Imagen aparece como banner |
| Galería | Subir + verificar + eliminar imagen | Operaciones correctas |

---

### 2.3 Catálogo (`03-catalog.spec.ts`)

| Test | Pasos | Resultado esperado |
|------|-------|--------------------|
| Lista carga | Navegar a `/seller/catalog` | Productos cargan (o vacío) |
| Crear producto | `/seller/catalog/nuevo` → form completo | Aparece en catálogo |
| Editar producto | Click editar → modificar precio | Toast éxito |
| Actualizar stock | Cambiar cantidad stock | Stock actualizado |
| Búsqueda | Escribir nombre | Resultados filtrados |

---

### 2.4 Servicios (`04-services.spec.ts`)

| Test | Pasos | Resultado esperado |
|------|-------|--------------------|
| Página carga | Navegar a `/seller/services` | UI carga |
| Crear servicio | Nombre + categoría servicio + precio | Aparece en lista |
| Ver bookings | `/seller/agenda` | Agenda/calendario carga |

---

### 2.5 Ventas / Órdenes (`05-orders.spec.ts`)

| Test | Pasos | Resultado esperado |
|------|-------|--------------------|
| Página ventas | Navegar a `/seller/sales` | KPIs y lista cargan |
| Detalle orden | Click en orden → `/seller/orders/[id]` | Detalle carga con items |

---

### 2.6 Mi Plan (`06-plans.spec.ts`)

| Test | Pasos | Resultado esperado |
|------|-------|--------------------|
| Plan actual visible | Navegar a `/seller/planes` | Plan "Emprende" mostrado |
| Solicitar upgrade | Click upgrade → seleccionar "Crece" | Paso de pago renderiza |

---

### 2.7 Helpdesk Seller (`07-helpdesk.spec.ts`)

| Test | Pasos | Resultado esperado |
|------|-------|--------------------|
| Página carga | Navegar a `/seller/help` | Sidebar tickets + area principal |
| Crear ticket | Asunto + mensaje → submit | Ticket en sidebar |
| Enviar mensaje | Abrir ticket → escribir → enviar | Mensaje en chat |
| Cerrar ticket | Botón cerrar | Estado → closed |

---

### 2.8 Logística (`08-logistics.spec.ts`)

| Test | Pasos | Resultado esperado |
|------|-------|--------------------|
| Página carga | Navegar a `/seller/logistics` | Configuración de envíos |
| Activar método | Toggle método de envío → guardar | Método habilitado |

---

## Fase 3 — Módulo Customer

### 3.1 Registro (`01-registration.spec.ts`)

| Test | Pasos | Resultado esperado |
|------|-------|--------------------|
| Ir a registro | `/login` → tab "cliente" → "Crear cuenta" | Formulario `RegisterPanel` visible |
| Llenar formulario | Nombre, email único, contraseña | — |
| Submit | Enviar formulario | Redirect a `/auth/verify-otp?email=...` |
| OTP page | Ver página OTP | 6 inputs + email visible |
| **Nota:** Para tests que necesitan customer verificado → crear via API helper antes del test |

---

### 3.2 Homepage (`02-homepage.spec.ts`)

| Test | Pasos | Resultado esperado |
|------|-------|--------------------|
| Homepage carga | Navegar a `/` | Hero, buscador, grillas visibles |
| Mega-menu | Hover/click "PRODUCTOS" en navbar | Dropdown con categorías del seeder |
| Categorías en mega-menu | Verificar nombres | "Belleza", "Mascotas", etc. visibles |
| Buscar | Escribir "suplemento" → enter | Redirect a `/buscar?q=suplemento` |
| Navegar categoría | Click en categoría | `/productos/[categoria]` carga |
| Navegar servicios | Click en servicio | `/servicios/[categoria]` carga |

---

### 3.3 Detalle de Producto (`03-product-detail.spec.ts`)

| Test | Pasos | Resultado esperado |
|------|-------|--------------------|
| Lista de productos | Navegar a `/productos/[categoria]` | Grid de productos carga |
| Detalle producto | Click en producto → `/producto/[slug]` | Nombre, precio, descripción |
| Agregar al carrito | Botón "Agregar al carrito" | Badge carrito se incrementa |

---

### 3.4 Carrito / Checkout (`04-cart-checkout.spec.ts`)

| Test | Pasos | Resultado esperado |
|------|-------|--------------------|
| Carrito carga | Navegar a `/carrito` | Items del carrito visibles |
| Cambiar cantidad | Incrementar/decrementar | Precio total actualiza |
| Eliminar item | Botón eliminar | Item desaparece |
| Ir a checkout | Click "Proceder al pago" | `/checkout` carga con formulario |

---

### 3.5 Órdenes y Bookings (`05-orders-bookings.spec.ts`)

| Test | Pasos | Resultado esperado |
|------|-------|--------------------|
| Historial órdenes | Navegar a `/customer/orders` | Lista de órdenes (puede estar vacía) |
| Booking servicio | `/servicios/[categoria]` → seleccionar servicio → horario → confirmar | Booking creado |
| Ver booking | `/customer/orders` | Booking aparece en historial |

---

### 3.6 Perfil y Soporte (`06-profile-support.spec.ts`)

| Test | Pasos | Resultado esperado |
|------|-------|--------------------|
| Perfil carga | Navegar a `/customer/profile` | Formulario con datos del usuario |
| Editar perfil | Cambiar teléfono → guardar | Toast éxito |
| Crear ticket soporte | `/customer/support` → formulario → submit | Ticket creado |

---

## Fase 4 — Cross-Module (WebSockets)

### 4.1 Broadcast de Categorías (`01-category-broadcast.spec.ts`)

**Objetivo:** Verificar que al crear una categoría en el admin, el mega-menu público se actualiza en tiempo real via WebSocket (`useEchoPublic`).

```
1. Abrir contexto A (admin) en /admin/categories
2. Abrir contexto B (público) en /
3. En A: crear categoría "Test WS {timestamp}"
4. En B: reabrir mega-menu
5. Verificar que "Test WS {timestamp}" aparece en el dropdown
```

**Mecanismo:** `CategoryUpdated` evento en canal público `categories` → `useEchoPublic` invalida `queryKey: ['admin', 'categories']` → React Query re-fetcha.

---

### 4.2 Notificaciones WebSocket (`02-notifications-websocket.spec.ts`)

**Objetivo:** Verificar que una acción del seller genera una notificación en el admin en tiempo real.

```
1. Abrir contexto A (admin) con panel de notificaciones visible
2. Abrir contexto B (seller)
3. En B: crear un nuevo ticket de soporte
4. En A: verificar que el badge de notificaciones se actualiza
5. Opcional (CDP): capturar frames WebSocket y verificar evento 'NotificationCreated'
```

```typescript
// Verificación via CDP
const cdp = await page.context().newCDPSession(page);
await cdp.send('Network.enable');
const wsFrames: string[] = [];
cdp.on('Network.webSocketFrameReceived', ({ response }) => {
  wsFrames.push(response.payloadData);
});
// ... acción que dispara el evento ...
await page.waitForFunction(() => wsFrames.some(f => f.includes('NotificationCreated')));
```

---

## Fase 5 — Páginas Estáticas (`static-renders.spec.ts`)

Solo verificar que cargan sin errores (status 200, sin `console.error`):

| URL | Descripción |
|-----|-------------|
| `/bioblog` | Blog (mock posts) |
| `/bioforo` | Foro (mock topics) |
| `/nosotros` | Sobre nosotros |
| `/preguntasfrecuentes` | FAQ |
| `/contactanos` | Formulario de contacto |
| `/politicasdeprivacidad` | Política de privacidad |
| `/terminoscondiciones` | Términos y condiciones |
| `/libroreclamaciones` | Libro de reclamaciones |
| `/tiendasregistradas` | Directorio de tiendas |
| `/tienda/biotienda-demo` | Tienda pública del seller seed |
| `/seller/chat` | Chat con clientes (mock) |
| `/customer/chat` | Chat con vendedores (mock) |

---

## Orden de ejecución y dependencias

```
setup ──────────────────────────────────────────────────────────┐
  │                                                              │
  ├── admin ──────────────────────────────────────────────┐     │
  │     02-categories (crea datos para seller)            │     │
  │     03-sellers (aprueba tiendas)                      │     │
  │     04-products (aprueba productos del seller)        │     │
  │                                                        │     │
  ├── seller ──────────────────────────────────────────── │ ──┐ │
  │     03-catalog (crea productos para admin aprobar)    │   │ │
  │     07-helpdesk (crea tickets para admin helpdesk)    │   │ │
  │                                                        │   │ │
  ├── customer (usa datos de admin + seller) ─────────────┘   │ │
  │                                                            │ │
  ├── cross-module (requiere admin + seller activos) ──────────┘ │
  │                                                              │
  └── static (independiente) ────────────────────────────────────┘
```

---

## Retos y mitigaciones

| Reto | Solución |
|------|----------|
| **IntroCover** en cada visita a `/login` | `loginAs()` siempre hace click en "Entrar" primero |
| **Hard navigation** post-login (`window.location.href`) | `waitForURL(pattern, { waitUntil: 'networkidle' })` |
| **OTP** en registro de customer | Crear customer pre-verificado via API (`POST /api/auth/register-customer` + DB seed) |
| **Auth race condition** en dashboard | Usar `page.evaluate` para SPA navigation si `goto()` causa redirect |
| **WebSocket asíncrono** | `page.waitForFunction()` con polling hasta que DOM se actualice |
| **Toggle vendedor/cliente** en login | `loginAs()` acepta rol y hace click en toggle correspondiente |
| **storageState** reutilizable | Cookies `laravel_token` son non-httpOnly → Playwright las captura correctamente |
| **Módulos mock** | Verificar que el mock carga (no vacío), no que llama API real |

---

## Resumen de cobertura

| Rol | Páginas cubiertas | Módulos |
|-----|------------------|---------|
| Admin | 15 páginas | Auth, Categorías, Sellers, Productos, Helpdesk, Planes, Contratos, Finance, Analytics, Operations, Inventory, Payments, RapiFac |
| Seller | 16 páginas | Auth, Tienda, Catálogo, Servicios, Ventas/Órdenes, Planes, Helpdesk, Logística, Finanzas |
| Customer | 11 páginas | Registro/OTP, Homepage, Búsqueda, Productos, Carrito, Checkout, Órdenes, Bookings, Perfil, Soporte |
| Logistics | 3 páginas | Shipment tracker, Chat, Helpdesk |
| Público/Estático | 13 páginas | Blog, Foro, About, FAQ, Contacto, Legal |
| WebSocket | 2 flujos | Category broadcast, Notifications real-time |
| **Total** | **~60 páginas** | **26 archivos de test** |