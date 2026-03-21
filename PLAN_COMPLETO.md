# Plan Completo: Backend Laravel 12 — Lyrium BioMarketplace

## Resumen del Proyecto

**Lyrium BioMarketplace** es un marketplace multi-vendedor para productos bio/salud en Peru.

| Componente | Tecnologia | URL Local |
|-----------|------------|-----------|
| Frontend | Next.js 16 + React 19 + TypeScript | http://localhost:3000 |
| Backend API | Laravel 12 + Sanctum + Spatie | http://127.0.0.1:8000/api |
| Base de datos | MySQL (XAMPP) | db-lyrium |
| Deploy futuro | Vercel (frontend) + Hostinger VPS (backend) | - |

**Multi-tenancy:** Single database con `store_id` (no base de datos separada por tienda).

**Autenticacion:** Laravel Sanctum con Bearer tokens. Cookie `auth_token` httpOnly en el frontend.

---

## Fases de Implementacion

---

## FASE 1 — Fundacion ✅ COMPLETADA

### Que incluye
- Proyecto Laravel 12 + configuracion base
- Auth (login/register/logout/validate) con Sanctum
- Users + Roles (administrator, seller, customer, logistics_operator)
- Stores (vendedores) + CRUD admin
- Categorias CRUD
- Productos CRUD (seller + admin approval)
- Registro diferenciado: vendedor (con RUC/tienda) y cliente (solo nombre/email/password)

### Paquetes instalados
- `laravel/sanctum` v4.3.1 — autenticacion API
- `spatie/laravel-permission` v6.24.1 — roles y permisos
- `spatie/laravel-query-builder` v6.4.3 — filtros API

### Migraciones creadas (14 total)
```
0001_01_01_000000_create_users_table          (modificada: +username, nicename, avatar, phone, document_type, document_number, is_seller, is_admin, softDeletes)
0001_01_01_000001_create_cache_table
0001_01_01_000002_create_jobs_table
2026_03_04_054735_create_permission_tables     (Spatie)
2026_03_04_072635_create_personal_access_tokens_table (Sanctum)
2026_03_04_100001_create_plans_table
2026_03_04_100002_create_stores_table
2026_03_04_100003_create_store_members_table
2026_03_04_100004_create_subscriptions_table
2026_03_04_100005_create_categories_table
2026_03_04_100006_create_products_table
2026_03_04_100007_create_product_attributes_table
2026_03_04_100008_create_category_product_table
```

### Modelos (8)
| Modelo | Relaciones clave |
|--------|-----------------|
| User | hasMany(Store, owner_id), belongsToMany(Store, store_members), HasApiTokens, HasRoles |
| Store | belongsTo(User, owner_id), hasMany(Product), hasOne(Subscription) |
| StoreMember | belongsTo(Store), belongsTo(User) |
| Category | hasMany(Category, parent_id), belongsToMany(Product) |
| Product | belongsTo(Store), belongsToMany(Category), hasMany(ProductAttribute) |
| ProductAttribute | belongsTo(Product) |
| Plan | hasMany(Subscription) |
| Subscription | belongsTo(Store), belongsTo(Plan) |

### Controllers (5)
| Controller | Metodos |
|-----------|---------|
| AuthController | login, register, registerCustomer, logout, validateToken, refreshToken |
| UserController | me, show, index, byRole, update, destroy |
| StoreController | index, show, store, update, updateStatus |
| CategoryController | index, show, store, update, destroy |
| ProductController | index, show, store, update, destroy, updateStock, updateStatus |

### Middleware (3)
| Middleware | Proposito |
|-----------|---------|
| ForceJson | Fuerza Accept: application/json en todas las API requests |
| EnsureRole | Verifica rol del usuario (admin, seller, etc.) |
| EnsureStoreApproved | Para sellers: verifica tienda approved |

### API Resources (4)
| Resource | Mapea a tipo TS del frontend |
|----------|------------------------------|
| UserResource | User { id, username, email, nicename, display_name, role, avatar } |
| ProductResource | Product { id, name, category, price, stock, image, sticker, mainAttributes, additionalAttributes, createdAt } |
| CategoryResource | ProductCategory { id, name, slug, parent, description, count, image } |
| StoreResource | Seller { id, userId, storeName, slug, logo, banner, email, status, commissionRate, ... } |

### Endpoints Fase 1 (29 rutas)

#### Publicos
| Metodo | Ruta | Descripcion |
|--------|------|-------------|
| POST | /api/auth/login | Login (email/username + password) |
| POST | /api/auth/register | Registro vendedor (storeName, email, phone, password, ruc) |
| POST | /api/auth/register-customer | Registro cliente (name, email, password) |
| GET | /api/categories | Listar categorias |
| GET | /api/categories/{id} | Detalle categoria |
| GET | /api/products | Listar productos aprobados |
| GET | /api/products/{id} | Detalle producto |

#### Autenticados (Bearer Token)
| Metodo | Ruta | Descripcion |
|--------|------|-------------|
| POST | /api/auth/logout | Cerrar sesion |
| GET | /api/auth/validate | Validar token, retorna User |
| POST | /api/auth/refresh | Rotar token |
| GET | /api/users/me | Perfil del usuario actual |
| GET | /api/users/{id} | Ver usuario |
| PUT | /api/users/{id} | Actualizar usuario |

#### Solo Admin
| Metodo | Ruta | Descripcion |
|--------|------|-------------|
| GET | /api/users | Listar usuarios (paginado, filtros) |
| GET | /api/users/role/{role} | Usuarios por rol |
| DELETE | /api/users/{id} | Eliminar usuario |
| GET | /api/stores | Listar tiendas |
| GET | /api/stores/{id} | Detalle tienda |
| PUT | /api/stores/{id}/status | Aprobar/rechazar/banear tienda |
| POST | /api/categories | Crear categoria |
| PUT | /api/categories/{id} | Editar categoria |
| DELETE | /api/categories/{id} | Eliminar categoria |
| PUT | /api/products/{id}/status | Aprobar/rechazar producto |

#### Seller + Admin
| Metodo | Ruta | Descripcion |
|--------|------|-------------|
| POST | /api/stores | Crear tienda |
| PUT | /api/stores/{id} | Editar tienda |
| POST | /api/products | Crear producto |
| PUT | /api/products/{id} | Editar producto |
| DELETE | /api/products/{id} | Eliminar producto |
| PUT | /api/products/{id}/stock | Actualizar stock |

### Seeders
| Seeder | Datos |
|--------|-------|
| RoleSeeder | administrator, seller, customer, logistics_operator |
| PlanSeeder | Emprende (5%), Crece (10%), Especial (15%) |
| AdminUserSeeder | admin@lyrium.com + vendedor@lyrium.com con tienda aprobada |
| CategorySeeder | 8 categorias: Semillas, Fertilizantes, Herramientas, Suplementos, Alimentos Organicos, Cuidado Personal, Aceites Esenciales, Productos Naturales |

### Conexion Frontend Realizada
- `.env.local` configurado con `NEXT_PUBLIC_API_BACKEND=laravel`
- URL: `http://127.0.0.1:8000/api` (IPv4 obligatorio, localhost resuelve a IPv6 en Windows)
- `LaravelAuthRepository.ts` implementado: login, register, registerCustomer, logout, validateToken, refreshToken
- Server Actions: loginAction, registerAction, registerCustomerAction, logoutAction, getSession
- Cookie `auth_token` corregida (antes leia `laravel_token`)
- CORS configurado: `allowed_origins` = FRONTEND_URL, `supports_credentials` = true

---

## FASE 2 — Comercio (PENDIENTE)

### Objetivo
Sistema de ordenes multi-vendedor con flujo de estados, gestion de inventario y calculo de comisiones.

### Nuevas migraciones

#### orders (orden principal del comprador)
```
- id
- customer_id (FK users) — quien compra
- order_number (string, unique) — ej: ORD-20260304-001
- total (decimal 10,2)
- subtotal (decimal 10,2)
- shipping_total (decimal 10,2)
- tax_total (decimal 10,2, default 0)
- discount_total (decimal 10,2, default 0)
- status (enum: pendiente, pagado, en_proceso, entregado, cancelado) default pendiente
- payment_method (string, nullable) — ej: izipay, transfer, cash
- payment_reference (string, nullable)
- shipping_address (json) — {street, city, state, zip, country, phone}
- billing_address (json, nullable)
- notes (text, nullable)
- paid_at (timestamp, nullable)
- shipped_at (timestamp, nullable)
- delivered_at (timestamp, nullable)
- cancelled_at (timestamp, nullable)
- cancellation_reason (string, nullable)
- timestamps
- soft_deletes
```

#### sub_orders (una por cada tienda involucrada en la orden)
```
- id
- order_id (FK orders)
- store_id (FK stores)
- sub_order_number (string, unique) — ej: SUB-20260304-001-01
- subtotal (decimal 10,2)
- shipping_cost (decimal 10,2, default 0)
- commission_rate (decimal 5,4) — tasa al momento de la orden
- commission_amount (decimal 10,2) — subtotal * commission_rate
- seller_amount (decimal 10,2) — subtotal - commission_amount
- status (enum: pendiente, pagado, en_proceso, entregado, cancelado) default pendiente
- tracking_number (string, nullable)
- carrier (string, nullable) — Shalom, Olva, Urbano, Chazki, Scharff
- shipped_at (timestamp, nullable)
- delivered_at (timestamp, nullable)
- timestamps
- soft_deletes
```

#### order_items (productos individuales dentro de una sub_order)
```
- id
- sub_order_id (FK sub_orders)
- product_id (FK products)
- product_name (string) — snapshot del nombre al momento de compra
- product_image (string, nullable) — snapshot
- quantity (unsigned int)
- unit_price (decimal 10,2) — precio al momento de compra
- total (decimal 10,2) — quantity * unit_price
- timestamps
```

#### commissions (registro de comisiones)
```
- id
- sub_order_id (FK sub_orders)
- store_id (FK stores)
- order_total (decimal 10,2) — subtotal de la sub_order
- commission_rate (decimal 5,4)
- commission_amount (decimal 10,2)
- status (enum: pending, settled, cancelled) default pending
- settled_at (timestamp, nullable)
- timestamps
```

### Nuevos modelos
| Modelo | Relaciones |
|--------|-----------|
| Order | belongsTo(User, customer_id), hasMany(SubOrder) |
| SubOrder | belongsTo(Order), belongsTo(Store), hasMany(OrderItem), hasOne(Commission) |
| OrderItem | belongsTo(SubOrder), belongsTo(Product) |
| Commission | belongsTo(SubOrder), belongsTo(Store) |

### Nuevos endpoints

#### Orders (comprador)
```
POST   /api/orders                    → Crear orden (items + shipping_address + payment_method)
GET    /api/orders                    → Mis ordenes (como comprador)
GET    /api/orders/{id}               → Detalle de orden
PUT    /api/orders/{id}/cancel        → Cancelar orden (si esta en pendiente)
```

#### SubOrders (vendedor)
```
GET    /api/seller/orders             → Ordenes de mi tienda (sub_orders)
GET    /api/seller/orders/{id}        → Detalle sub_order
PUT    /api/seller/orders/{id}/status → Avanzar estado (pagado→en_proceso→entregado)
PUT    /api/seller/orders/{id}/tracking → Agregar tracking number + carrier
```

#### Orders (admin)
```
GET    /api/admin/orders              → Todas las ordenes
GET    /api/admin/orders/{id}         → Detalle con sub_orders
PUT    /api/admin/orders/{id}/status  → Cambiar estado (cualquier transicion)
GET    /api/admin/commissions         → Listar comisiones
```

### Logica de negocio clave

**Crear orden:**
1. Recibe array de items [{product_id, quantity}] + shipping_address
2. Agrupa items por store_id
3. Crea 1 Order principal
4. Por cada tienda → crea 1 SubOrder + OrderItems
5. Calcula comision (subtotal * commission_rate de la tienda)
6. Descuenta stock de cada producto

**Flujo de estados:**
```
pendiente → pagado → en_proceso → entregado
    |
    └→ cancelado (restaura stock)
```

**Reglas:**
- Solo el admin puede marcar como `pagado` (confirma pago)
- El vendedor avanza de `pagado` → `en_proceso` → `entregado`
- Cancelar solo es posible si esta en `pendiente` o `pagado`
- Al cancelar se restaura el stock
- Al entregar se marca la comision como `settled`

### Frontend: LaravelOrderRepository.ts
Implementar los stubs actuales con:
- `getOrders(filters?)` → GET /api/seller/orders o /api/orders segun rol
- `getOrderById(id)` → GET /api/orders/{id}
- `createOrder(input)` → POST /api/orders
- `updateOrder(id, input)` → PUT /api/seller/orders/{id}/status
- `advanceOrderStep(id)` → PUT /api/seller/orders/{id}/status (next step)

---

## FASE 3 — Financiero (PENDIENTE)

### Objetivo
Liquidaciones semanales, gestion de pagos, integracion con pasarela de pagos y facturacion electronica.

### Componentes

#### Liquidaciones semanales
- Periodo: lunes a domingo
- Pago: lunes a miercoles siguiente
- Calculo: suma de seller_amount de sub_orders entregadas en el periodo
- Descuenta comisiones pendientes

#### Nuevas migraciones
```
settlements (liquidaciones)
- id
- store_id (FK stores)
- period_start (date)
- period_end (date)
- gross_amount (decimal 10,2) — total ventas del periodo
- commission_amount (decimal 10,2)
- net_amount (decimal 10,2) — lo que recibe el vendedor
- status (enum: pending, processing, paid, failed)
- paid_at (timestamp, nullable)
- payment_reference (string, nullable)
- timestamps

transactions (movimientos financieros)
- id
- store_id (FK stores, nullable)
- user_id (FK users, nullable)
- type (enum: cash_in, cash_out, commission, settlement, refund)
- amount (decimal 10,2)
- reference (string, nullable)
- description (text, nullable)
- timestamps
```

#### Integracion IZIPAY
- Pasarela de pagos para compradores
- Webhook para confirmar pagos → cambia orden a `pagado`
- SDK: izipay-sdk-php o API REST directa

#### Rapifac (SUNAT)
- Facturacion electronica para Peru
- Genera boletas/facturas por cada orden
- Integrar via API de Rapifac

#### Contratos digitales
- Contrato de adhesion para vendedores
- Firma digital al registrarse
- Almacenado como PDF en storage

### Endpoints
```
GET    /api/seller/settlements           → Mis liquidaciones
GET    /api/seller/settlements/{id}      → Detalle liquidacion
GET    /api/admin/settlements            → Todas las liquidaciones
POST   /api/admin/settlements/generate   → Generar liquidaciones del periodo
PUT    /api/admin/settlements/{id}/pay   → Marcar como pagada
GET    /api/transactions                 → Movimientos financieros
POST   /api/payments/izipay/create       → Iniciar pago IZIPAY
POST   /api/webhooks/izipay              → Webhook de confirmacion
```

---

## FASE 4 — Servicios (PENDIENTE)

### Objetivo
Soporte para vendedores que ofrecen servicios (no solo productos), sistema de citas y logistica.

### Componentes

#### Servicios y especialistas
```
services (nueva tabla)
- id
- store_id (FK stores)
- name
- slug
- description
- price (decimal 10,2)
- duration_minutes (int)
- image (nullable)
- status (enum: draft, approved, rejected)
- timestamps
- soft_deletes
```

#### Agenda/Citas
```
appointments (nueva tabla)
- id
- service_id (FK services)
- customer_id (FK users)
- store_id (FK stores)
- scheduled_at (datetime)
- duration_minutes (int)
- status (enum: pending, confirmed, completed, cancelled, no_show)
- notes (text, nullable)
- timestamps
```

#### Logistica (carriers)
```
shipments (nueva tabla)
- id
- sub_order_id (FK sub_orders)
- carrier (enum: shalom, olva, urbano, chazki, scharff)
- tracking_number (string)
- status (enum: created, picked_up, in_transit, delivered, returned)
- estimated_delivery (date, nullable)
- delivered_at (timestamp, nullable)
- cost (decimal 10,2)
- timestamps
```

### Endpoints
```
# Servicios
GET    /api/services                → Listar servicios aprobados
POST   /api/services               → Crear servicio (seller)
PUT    /api/services/{id}          → Editar servicio
PUT    /api/services/{id}/status   → Aprobar/rechazar (admin)

# Citas
POST   /api/appointments           → Agendar cita (customer)
GET    /api/appointments           → Mis citas
PUT    /api/appointments/{id}      → Actualizar cita
GET    /api/seller/appointments    → Citas de mi tienda

# Logistica
POST   /api/shipments              → Crear envio
GET    /api/shipments/{id}         → Tracking
PUT    /api/shipments/{id}/status  → Actualizar estado
```

---

## FASE 5 — Comunicacion (PENDIENTE)

### Objetivo
Sistema de soporte, chat en tiempo real, notificaciones y resenas.

### Componentes

#### Helpdesk/Tickets
```
tickets (nueva tabla)
- id
- user_id (FK users)
- store_id (FK stores, nullable)
- subject
- description (text)
- status (enum: open, in_progress, resolved, closed)
- priority (enum: low, medium, high)
- timestamps

ticket_messages (nueva tabla)
- id
- ticket_id (FK tickets)
- user_id (FK users)
- message (text)
- timestamps
```

#### Chat en tiempo real
- **Tecnologia recomendada:** Pusher (compatible con Vercel + Hostinger)
- Laravel Broadcasting con Pusher driver
- Canales privados por conversacion
- Alternativa: Laravel Reverb (requiere WebSocket server propio)

#### Notificaciones
```
notifications (tabla de Laravel)
- Usar sistema nativo de Laravel Notifications
- Canales: database, email, push (via web-push)
- Tipos: nueva orden, cambio de estado, aprobacion tienda, strike, etc.
```

#### Reviews/Resenas
```
reviews (nueva tabla)
- id
- user_id (FK users)
- product_id (FK products)
- order_item_id (FK order_items)
- rating (tinyint 1-5)
- comment (text, nullable)
- status (enum: pending, approved, rejected)
- timestamps
```

### Endpoints
```
# Tickets
POST   /api/tickets               → Crear ticket
GET    /api/tickets               → Mis tickets
GET    /api/tickets/{id}          → Detalle + mensajes
POST   /api/tickets/{id}/messages → Responder ticket
PUT    /api/tickets/{id}/status   → Cambiar estado

# Notificaciones
GET    /api/notifications         → Mis notificaciones
PUT    /api/notifications/{id}/read → Marcar como leida
POST   /api/notifications/read-all  → Marcar todas como leidas

# Reviews
POST   /api/reviews               → Crear resena (post-compra)
GET    /api/products/{id}/reviews → Resenas de un producto
PUT    /api/reviews/{id}/status   → Aprobar/rechazar (admin)

# Chat (via Pusher/Broadcasting)
POST   /api/conversations          → Iniciar conversacion
GET    /api/conversations          → Mis conversaciones
POST   /api/conversations/{id}/messages → Enviar mensaje
GET    /api/conversations/{id}/messages → Historial
```

---

## FASE 6 — Contenido y Analytics (PENDIENTE)

### Objetivo
Blog, foro comunitario, reportes de ventas y programa de fidelizacion.

### Componentes

#### Blog
```
posts (nueva tabla)
- id
- author_id (FK users)
- title
- slug (unique)
- content (longtext)
- excerpt (text, nullable)
- featured_image (nullable)
- status (enum: draft, published)
- published_at (timestamp, nullable)
- timestamps
- soft_deletes

post_categories (pivot)
- post_id, category_id
```

#### Foro
```
forum_topics (nueva tabla)
- id
- user_id (FK users)
- category_id
- title
- content (text)
- is_pinned (boolean, default false)
- is_locked (boolean, default false)
- timestamps

forum_replies (nueva tabla)
- id
- topic_id (FK forum_topics)
- user_id (FK users)
- content (text)
- timestamps
```

#### Analytics/Reportes
```
# Reportes calculados (no tablas nuevas, queries sobre datos existentes)
- Ventas por periodo (dia, semana, mes)
- Productos mas vendidos
- Vendedores con mas ventas
- Comisiones generadas
- Usuarios nuevos por periodo
- Ordenes por estado
```

#### Programa de fidelizacion
```
loyalty_points (nueva tabla)
- id
- user_id (FK users)
- points (int)
- type (enum: earned, redeemed, expired)
- source (string) — ej: "order:123", "review:45"
- description
- expires_at (timestamp, nullable)
- timestamps

loyalty_tiers (nueva tabla)
- id
- name (Bronce, Plata, Oro, Platino)
- min_points (int)
- discount_percentage (decimal)
- benefits (json)
```

### Endpoints
```
# Blog
GET    /api/posts                 → Listar posts publicados
GET    /api/posts/{slug}          → Detalle post
POST   /api/posts                 → Crear post (admin)
PUT    /api/posts/{id}            → Editar post
DELETE /api/posts/{id}            → Eliminar post

# Foro
GET    /api/forum/topics          → Listar topics
POST   /api/forum/topics          → Crear topic
GET    /api/forum/topics/{id}     → Topic + replies
POST   /api/forum/topics/{id}/replies → Responder

# Analytics
GET    /api/admin/analytics/sales         → Reporte de ventas
GET    /api/admin/analytics/products      → Productos top
GET    /api/admin/analytics/sellers       → Vendedores top
GET    /api/admin/analytics/commissions   → Reporte comisiones
GET    /api/seller/analytics/dashboard    → Dashboard del vendedor

# Fidelizacion
GET    /api/loyalty/points        → Mis puntos
GET    /api/loyalty/tiers         → Niveles disponibles
POST   /api/loyalty/redeem        → Canjear puntos
```

---

## Resumen de Tablas por Fase

| Fase | Tablas nuevas | Total acumulado |
|------|--------------|----------------|
| Fase 1 ✅ | users*, stores, store_members, categories, products, product_attributes, category_product, plans, subscriptions, personal_access_tokens, permission_tables | ~14 |
| Fase 2 | orders, sub_orders, order_items, commissions | ~18 |
| Fase 3 | settlements, transactions | ~20 |
| Fase 4 | services, appointments, shipments | ~23 |
| Fase 5 | tickets, ticket_messages, reviews, notifications | ~27 |
| Fase 6 | posts, post_categories, forum_topics, forum_replies, loyalty_points, loyalty_tiers | ~33 |

---

## Datos de Prueba Actuales

| Email | Password | Rol | Tienda |
|-------|----------|-----|--------|
| admin@lyrium.com | password | administrator | - |
| vendedor@lyrium.com | password | seller | BioTienda Demo (approved) |

### Como levantar
```bash
# Backend
cd C:\xampp\htdocs\backend-markplace
php artisan serve                    # → http://127.0.0.1:8000

# Frontend
cd F:\PERSONAL_JEAN\fe-001-marketplace-admin-main\frontapp
npm run dev                          # → http://localhost:3000

# Resetear DB
php artisan migrate:fresh --seed
```

---

## Reglas de Negocio (del cuestionario.md)

- **Registro vendedor:** Requiere RUC, aprobacion admin en 72h
- **3 strikes:** A la tercera infraccion, tienda baneada
- **Productos:** Requieren aprobacion admin, variantes al mismo precio
- **Ordenes:** Multi-vendedor, se divide en sub-ordenes por tienda
- **5 estados:** pendiente → pagado → en_proceso → entregado | cancelado
- **Comision:** 15% por defecto (configurable por plan)
- **Liquidaciones:** Semanales (lun-dom), pago lun-mie siguiente
- **Carriers:** Shalom, Olva Courier, Urbano, Chazki, Scharff
- **Roles duales:** Un comprador puede tambien ser vendedor
- **Categorias:** Multi-categoria por producto
