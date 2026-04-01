# Plan Manual de Pruebas por Modulo

## Resumen

Este documento separa los modulos del frontend segun su fuente real de datos para que puedas probarlos manualmente sin mezclar:

- modulos conectados a `Backend-Lyrium` por Laravel
- modulos que siguen usando WordPress, WooCommerce o Dokan
- modulos que todavia dependen de `mock`, `fallback` o estado local

La idea no es ejecutar pruebas automaticas, sino darte una guia clara para decidir que pantallas realmente validan el backend Laravel y cuales no.

## Regla de lectura

- `Laravel`: el modulo puede usarse para validar comportamiento real del backend Laravel.
- `WP/WooCommerce/Dokan`: el modulo sigue conectado al backend legado o a servicios WordPress.
- `Mock/Local`: la pantalla puede abrir, pero no sirve como prueba de backend real.
- `Parcial`: parte del flujo pega a Laravel, pero otras acciones siguen mockeadas o simplificadas.

## Matriz Principal

| Modulo | Pantalla / Hook principal | Fuente real | Usar para validar Laravel | Estado | Notas |
|---|---|---|---|---|---|
| Auth | `LaravelAuthRepository` | Laravel | Si | Activo | Login, register seller/customer, validate, refresh, logout |
| Users / Perfil | `LaravelUserRepository` | Laravel | Si | Activo | Usuario actual, perfil y listados por rol |
| Home publico | `LaravelHomeRepository` | Laravel | Si | Activo | Heroes, banners, brands, benefits, newsletter, categorias y productos |
| Admin Categories | `useCategories` | Laravel | Si | Activo | CRUD, upload de imagen y Echo para categorias |
| Seller Store | `useSellerStore` + `sellerApi` | Laravel | Si | Activo | Tienda, sucursales, visual, policies, media |
| Seller Catalog | `useSellerCatalog` + `LaravelProductRepository` | Laravel | Si | Parcial | Tiene fallback a mock si falla la API |
| Seller Sales / Orders | `useSellerSales` + `LaravelOrderRepository` | Laravel | Si | Parcial | Lee ordenes reales; KPIs y parte de UI siguen simplificados |
| Seller Services | `useSellerServices` + `serviceApi`/`bookingApi` | Laravel | Si | Parcial | Lectura real de servicios y bookings; mutaciones del hook siguen mockeadas |
| Productos publicos | `LaravelProductRepository` / `LaravelHomeRepository` | Laravel | Si | Activo | Listado y detalle dependen del shape real del backend |
| Ordenes public/customer | `LaravelOrderRepository` | Laravel | Si | Parcial | Revisar si la UI concreta usa este repo o una pantalla mock |
| API factory | `shared/lib/api/factory.ts` | Mixto | No directo | Sensible a config | Si `NEXT_PUBLIC_API_MODE=wp`, las pantallas factory-based no validan Laravel |
| Home WP | `WPHomeRepository` | WP/WooCommerce/Dokan | No | Legado | No usar para validar Laravel |
| Products WP | `WPProductRepository` | WP/WooCommerce/Dokan | No | Legado/Incompleto | Tiene varios `TODO`, no sirve para Laravel |
| Orders WP | `WPOrderRepository` | WP/WooCommerce/Dokan | No | Legado/Incompleto | Tiene varios `TODO` |
| Users WP | `WPUserRepository` | WP/WooCommerce/Dokan | No | Legado/Incompleto | No usar para validar Laravel |
| Auth WP | `WPAuthRepository` | WP/WooCommerce/Dokan | No | Legado/Parcial | Flujo distinto al backend Laravel |
| Seller Plans | `useSellerPlans` / `usePlanes` | Mock/Local | No | Mock | Usa datos mock y estado local |
| Admin Planes | `usePlanesAdmin` | Mock/Local | No | Mock | Mucho estado en localStorage y datos simulados |
| Seller Finance | `useSellerFinance` | Mock/Local | No | Mock | No valida pagos reales de Laravel |
| Seller Chat | `useSellerChat` | Mock/Local | No | Mock | No usar para backend |
| Seller Agenda | `useAgenda` | Mock/Local | No | Mock | Depende de mocks de citas y ordenes |
| Seller Logistics | `useSellerLogistics` | Mock/Local | No | Mock | No valida shipping real |
| Seller Invoices | `useSellerInvoices` | Mock/Local | No | Parcial | Tiene fallback a mock |
| Admin Inventory | `useInventory` | Mock/Local | No | Mock | No validar backend con esta UI |
| Admin Analytics | `useAnalytics` | Mock/Local | No | Mock | Hay `TODO` para endpoint real |
| Admin Treasury | `useTreasury` | Mock/Local | No | Mock | No conectado a backend real |
| Admin Helpdesk | `useMesaAyuda` | Mock/Local | No | Mock | Pantalla no confiable para backend |
| Admin Sellers | `useControlVendedores` | Mock/Local | No | Parcial | Mezcla datos simulados con supuestos |
| Admin Contracts | `useContratos` | Mock/Local | No | Mock | No valida contratos Laravel |
| Customer Chat | `useCustomerChat` | Mock/Local | No | Mock | Conversaciones simuladas |
| Customer Support | `useCustomerSupport` | Mock/Local | No | Mock | Tickets simulados |
| Logistics Helpdesk | `useLogisticsHelpdesk` | Mock/Local | No | Mock | No validar backend con esta UI |
| Logistics Chat | `useLogisticsChat` | Mock/Local | No | Mock | Conversaciones simuladas |
| Logistics Shipments | `useLogisticsShipments` | Mock/Local | No | Mock | Envios simulados |
| Blog / Foro / Rapifac / Woo routes | `app/api/*` e integraciones varias | WP/Servicios externos | No | Externo | No son prueba del backend Laravel del marketplace |

## Mapa del Frontend por Tipo de Usuario

Esta seccion te sirve para saber que paneles existen en el frontend y que modulos vale la pena recorrer cuando hagas la prueba manual.

### 1. Panel Publico

**Rutas principales**
- `/`
- `/login`
- `/auth/verify-otp`
- `/buscar`
- `/carrito`
- `/checkout`
- `/producto/[slug]`
- `/productos/[categoria]`
- `/servicios/[categoria]`
- `/tienda/[slug]`
- `/tiendasregistradas`
- `/nosotros`
- `/preguntasfrecuentes`
- `/politicasdeprivacidad`
- `/terminoscondiciones`
- `/contactanos`
- `/support`
- `/help`
- `/bioforo`
- `/bioblog`

**Modulos visibles**
- home
- login y verificacion OTP
- catalogo publico de productos
- categorias publicas
- servicios publicos
- tienda publica
- carrito y checkout
- buscador
- contenido institucional
- blog y foro

**Lectura para testing**
- probar Laravel: home, categorias, productos, tienda publica, newsletter, parte de auth
- no usar para validar Laravel: blog, foro, rutas WooCommerce y servicios externos

### 2. Panel Seller

**Navegacion detectada**
- `Mi Plan` -> `/seller/planes`
- `Mis Datos` -> `/seller/profile`
- `Mi Tienda` -> `/seller/store`
- `Mi Catalogo` -> `/seller/catalog`
- `Mis Servicios` -> `/seller/services`
- `Mis Ventas` -> `/seller/sales`
- `Mi Agenda` -> `/seller/agenda`
- `Mi Logistica` -> `/seller/logistics`
- `Centro de Finanzas` -> `/seller/finance`
- `Chat con Clientes` -> `/seller/chat`
- `Mesa de Ayuda` -> `/seller/help`
- `Mis Comprobantes` -> `/seller/invoices`

**Que modulos puedes testear**
- `Mi Tienda`: si, valida Laravel
- `Mi Catalogo`: si, valida Laravel con fallback a mock si falla
- `Mis Servicios`: parcial, lectura real y mutaciones no completas
- `Mis Ventas`: parcial, ordenes reales y KPIs simplificados
- `Mis Datos`: depende de `LaravelUserRepository`, util para validar perfil
- `Mi Plan`: no usar para validar Laravel
- `Mi Agenda`: no usar para validar Laravel
- `Mi Logistica`: no usar para validar Laravel
- `Centro de Finanzas`: no usar para validar Laravel
- `Chat con Clientes`: no usar para validar Laravel
- `Mesa de Ayuda`: no usar como verdad de backend hasta revisar integracion real
- `Mis Comprobantes`: parcial, revisar con cautela por fallback

### 3. Panel Admin

**Navegacion detectada**
- `Control de Vendedores` -> `/admin/sellers`
- `Mesa de Ayuda` -> `/admin/helpdesk`
- `Centro de Finanzas y Estadisticas` -> `/admin/finance`
- `Gestion de Pagos` -> `/admin/payments`
- `Facturacion Rapida` -> `/admin/rapifac`
- `Analitica` -> `/admin/analytics`
- `Gestion Operativa` -> `/admin/operations`
- `Contratos` -> `/admin/contracts`
- `Gestion de Categorias` -> `/admin/categories`
- `Gestion de Inventario` -> `/admin/inventory`
- `Gestion de Puntuacion` -> `/admin/reviews`
- `Planes y Suscripciones` -> `/admin/planes`

**Que modulos puedes testear**
- `Gestion de Categorias`: si, valida Laravel y realtime
- `Control de Vendedores`: parcial, revisar pero no tomarlo como verdad absoluta
- `Gestion de Pagos`: revisar solo como UI hasta confirmar integracion real
- `Contratos`: no usar como modulo confiable de Laravel desde esta UI
- `Gestion de Inventario`: no usar para validar Laravel
- `Gestion de Puntuacion`: revisar con cautela; no asumir integracion completa
- `Mesa de Ayuda`: no usar como verdad de backend aun
- `Centro de Finanzas y Estadisticas`: no usar
- `Analitica`: no usar
- `Gestion Operativa`: no usar
- `Planes y Suscripciones`: no usar
- `Facturacion Rapida`: externo, no es prueba del backend Laravel del marketplace

### 4. Panel Customer

**Navegacion detectada**
- `Mi Perfil` -> `/customer/profile`
- `Mis Pedidos` -> `/customer/orders`
- `Lista de Deseos` -> `/customer/wishlist`
- `Metodos de Pago` -> `/customer/payment-methods`
- `Direcciones de Envio` -> `/customer/addresses`
- `Seguridad` -> `/customer/security`
- `Chat con Vendedores` -> `/customer/chat`
- `Mesa de Ayuda` -> `/customer/support`
- `Configuracion` -> `/customer/settings`
- `Ayuda` -> `/customer/help`

**Que modulos puedes testear**
- `Mi Perfil`: revisar si consume Laravel segun el flujo actual
- `Mis Pedidos`: revisar si la pantalla usa ordenes reales o datos simulados
- `Lista de Deseos`: no tomar como validacion de Laravel
- `Metodos de Pago`: no tomar como validacion de Laravel
- `Direcciones de Envio`: no tomar como validacion de Laravel
- `Seguridad`: revisar solo como UI
- `Chat con Vendedores`: no usar para validar Laravel
- `Mesa de Ayuda`: no usar para validar Laravel
- `Configuracion`: revisar solo como UI
- `Ayuda`: contenido, no valida backend

### 5. Panel Logistics

**Navegacion detectada**
- `Rastreador de Envios` -> `/logistics`
- `Chat con Vendedores` -> `/logistics/chat-vendors`
- `Mesa de Ayuda` -> `/logistics/helpdesk`

**Que modulos puedes testear**
- `Rastreador de Envios`: no usar para validar Laravel
- `Chat con Vendedores`: no usar para validar Laravel
- `Mesa de Ayuda`: no usar para validar Laravel

## Resumen rapido de que paneles si valen la pena

Si tu objetivo es validar el backend Laravel desde el frontend, prioriza estos paneles:

- publico: home, login, OTP, categorias, productos, tienda publica
- seller: perfil, tienda, catalogo, ventas, servicios
- admin: categorias

El resto hoy sirve mas para:

- revisar UX
- clasificar integracion pendiente
- detectar modulos mock o legacy
- confirmar migracion incompleta

## Grupo A: Modulos que si debes probar para validar Laravel

### 1. Auth Laravel

**Objetivo**
Validar autenticacion y registro contra `Backend-Lyrium`.

**Origen**
- `frontapp/src/shared/lib/api/laravel/LaravelAuthRepository.ts`

**Pruebas manuales**
- login correcto
- login con password incorrecto
- registro seller
- registro customer
- validacion de token
- refresh token
- logout

**Resultado esperado**
- respuestas coherentes del backend
- cookie o token disponible para el resto de modulos protegidos
- errores de auth visibles cuando corresponde

### 2. Home publico Laravel

**Objetivo**
Validar que el contenido publico mostrado por frontend provenga del backend Laravel.

**Origen**
- `frontapp/src/shared/lib/api/laravel/LaravelHomeRepository.ts`

**Pruebas manuales**
- cargar home
- revisar heroes
- revisar banners publicitarios
- revisar marcas
- revisar beneficios
- probar suscripcion a newsletter
- navegar categorias y secciones home

**Resultado esperado**
- contenido cargado sin fallback raro
- imagenes y URLs resueltas desde el backend Laravel

### 3. Admin Categories

**Objetivo**
Validar CRUD real de categorias y sincronizacion visual.

**Origen**
- `frontapp/src/features/admin/categories/hooks/useCategories.ts`

**Pruebas manuales**
- listar categorias
- crear categoria raiz
- crear subcategoria
- editar nombre, descripcion, tipo y orden
- subir imagen
- eliminar categoria
- verificar refresco de lista

**Resultado esperado**
- cambios persistidos en Laravel
- errores de validacion visibles
- si Echo esta operativo, refresco de categorias tras cambios

### 4. Seller Store

**Objetivo**
Validar administracion de tienda del vendedor.

**Origen**
- `frontapp/src/features/seller/store/hooks/useSellerStore.ts`
- `frontapp/src/shared/lib/api/sellerRepository.ts`

**Pruebas manuales**
- abrir datos de la tienda
- editar informacion basica
- guardar cambios
- agregar/editar sucursales
- subir logo
- subir banner 1 y banner 2
- subir imagenes de galeria
- eliminar imagen de galeria
- subir policies PDF
- eliminar policies PDF
- cambiar layout visual

**Resultado esperado**
- datos persistidos en backend
- media asociada correctamente
- recarga de pagina mostrando datos reales

### 5. Seller Catalog

**Objetivo**
Validar catalogo de productos del vendedor.

**Origen**
- `frontapp/src/features/seller/catalog/hooks/useSellerCatalog.ts`
- `frontapp/src/shared/lib/api/laravel/LaravelProductRepository.ts`

**Pruebas manuales**
- listar productos
- crear producto
- editar producto
- eliminar producto
- cambiar stock
- subir imagen de producto si la UI lo expone

**Resultado esperado**
- operaciones persistidas en Laravel
- si la API falla, anotar si la pantalla cayo a mock para no confundir el resultado

### 6. Seller Sales / Orders

**Objetivo**
Validar lectura de ordenes y transiciones basicas de estado.

**Origen**
- `frontapp/src/features/seller/sales/hooks/useSellerSales.ts`
- `frontapp/src/shared/lib/api/laravel/LaravelOrderRepository.ts`

**Pruebas manuales**
- listar ordenes
- filtrar por fecha
- abrir detalle de orden
- confirmar orden o item si la UI lo permite
- avanzar estado

**Resultado esperado**
- ordenes leidas desde Laravel
- cambios reflejados tras refresco

**Advertencia**
- el hook deja KPIs vacios cuando va por Laravel
- parte de la experiencia puede verse "bien" aunque no este completamente integrada

### 7. Seller Services

**Objetivo**
Validar modulos de servicios y reservas que ya leen datos reales.

**Origen**
- `frontapp/src/features/seller/services/hooks/useSellerServices.ts`
- `frontapp/src/shared/lib/api/serviceRepository.ts`

**Pruebas manuales**
- listar servicios
- listar bookings del seller
- confirmar que lectura de datos funciona
- intentar crear/editar/eliminar y anotar si la accion fue real o solo simulada

**Resultado esperado**
- lectura de servicios y bookings desde Laravel
- documentar claramente que mutaciones del hook actual siguen mockeadas

## Grupo B: Modulos que no debes usar para validar Laravel

### 1. WP / WooCommerce / Dokan

Estos modulos dependen de la capa heredada:

- `shared/lib/api/base-client.ts`
- `shared/lib/config/api.ts`
- `shared/lib/api/wp/*`
- integraciones WooCommerce y Dokan

**Regla**
Si una pantalla depende de estos repositorios, no la uses como prueba del backend Laravel.

### 2. Modulos con mock o fallback local

Estos modulos no son una fuente confiable para validar backend:

- planes seller/admin
- finance
- logistics
- analytics
- inventory
- helpdesk varias vistas
- chat customer/seller/logistics
- agenda
- invoices con fallback

**Regla**
Si el modulo usa arrays mock, `localStorage`, o retorna datos simulados cuando falla la API, marcarlo como `Mock/Local` y no sacar conclusiones del backend con esa pantalla.

## Checklist sugerido para tu prueba manual

Para cada modulo que revises, registra:

- pantalla probada
- clasificacion: `Laravel`, `WP`, `Mock/Local`, `Parcial`
- usuario usado
- endpoint esperado
- accion realizada
- resultado observado
- evidencia: captura, log o respuesta visible
- conclusion: `valida Laravel` o `no valida Laravel`

## Orden recomendado de prueba manual

1. Auth Laravel
2. Home publico Laravel
3. Admin Categories
4. Seller Store
5. Seller Catalog
6. Seller Sales / Orders
7. Seller Services
8. Resto de pantallas solo para clasificar si son `WP` o `Mock/Local`

## Criterio final

Al terminar, cada modulo debe quedar en una de estas categorias:

- `Probado contra Laravel`
- `No aplica: usa WP`
- `No aplica: usa Mock/Local`
- `Parcial: mezcla Laravel con fallback o simulacion`

Si quieres validar estrictamente el backend, centrate en el Grupo A y usa el resto solo como referencia de migracion pendiente.
