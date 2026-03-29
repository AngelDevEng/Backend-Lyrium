# Backend Lyrium — Documentacion Completa de Funcionalidades

## Resumen General

Backend API REST multi-vendor para el marketplace Lyrium Bio.
- **Framework:** Laravel 12, PHP 8.2+
- **Auth:** Sanctum (bearer tokens) + Google OAuth
- **Roles:** Spatie Permission (administrator, seller, customer, logistics_operator)
- **Base de datos:** MySQL (db-lyriumv1)
- **Media:** Spatie MediaLibrary
- **Busqueda:** Laravel Scout + Meilisearch (fallback: query DB)
- **Notificaciones:** Laravel Notifications (database + email)
- **Real-time:** Server-Sent Events (SSE)

---

## Modulos por Estado

### Ya existian en el backend original (C:\xampp\htdocs\backend-markplace)

| Modulo | Descripcion |
|--------|-------------|
| Auth (login, register seller/customer, OTP, Google) | Autenticacion completa |
| Users (CRUD, roles, perfil) | Gestion de usuarios |
| Stores (CRUD, status, miembros) | Gestion de tiendas |
| Products (CRUD, stock, aprobacion) | Catalogo de productos |
| Categories (CRUD, jerarquia parent/child) | Categorias con subcategorias |
| Plans / Subscriptions | Planes de suscripcion |
| Suppliers (CRUD) | Directorio de proveedores |
| Contracts (CRUD, upload, audit trail) | Gestion de contratos |
| Tickets / Mesa de Ayuda (seller + admin) | Sistema de soporte |

### NUEVOS en el backend F:\TEST\Backend-Lyrium

Los siguientes modulos son funcionalidades nuevas que no existian en el backend original:

---

## 1. Carrito de Compras (Cart)

**Controller:** `CartController`
**Modelo:** `Cart`, `CartItem`

| Endpoint | Metodo | Descripcion |
|----------|--------|-------------|
| `/api/cart` | GET | Ver carrito del usuario |
| `/api/cart/items` | POST | Agregar producto al carrito |
| `/api/cart/items/{id}` | PUT | Actualizar cantidad de item |
| `/api/cart/items/{id}` | DELETE | Eliminar item del carrito |
| `/api/cart/clear` | DELETE | Vaciar carrito completo |

**Requiere:** Login (auth:sanctum). Sin carrito para invitados.
**Almacenamiento:** MySQL (no Redis).

---

## 2. Ordenes (Orders)

**Controller:** `OrderController`
**Modelos:** `Order`, `OrderItem`

| Endpoint | Metodo | Auth | Descripcion |
|----------|--------|------|-------------|
| `/api/orders` | GET | auth | Listar ordenes (admin: todas, seller: sus tiendas, customer: las suyas) |
| `/api/orders/{id}` | GET | auth | Ver detalle de orden |
| `/api/orders` | POST | auth | Crear orden desde carrito |
| `/api/orders/{id}/confirm` | POST | seller/admin | Confirmar orden |
| `/api/orders/{id}/status` | PUT | auth | Cambiar estado de orden |
| `/api/orders/{id}/items/{itemId}/confirm` | POST | seller/admin | Confirmar item individual |
| `/api/orders/{id}/items/{itemId}/status` | PUT | auth | Cambiar estado de item individual |

**Flujo de estados del OrderItem:**
```
pending_seller → confirmed → processing → shipped → delivered
                                                   → cancelled (en cualquier punto antes de delivered)
```

**Caracteristicas:**
- Ordenes multi-vendedor (items de distintas tiendas en una sola orden)
- Estado por item (cada vendedor confirma sus items)
- Restauracion automatica de stock al cancelar
- Soporte de cupones de descuento
- Calculo de impuestos (16%)
- Monto minimo de orden

---

## 3. Pagos (Payments)

**Controller:** `PaymentController`
**Modelos:** `SellerPayment`, `PaymentSchedule`

| Endpoint | Metodo | Auth | Descripcion |
|----------|--------|------|-------------|
| `/api/admin/payments` | GET | admin | Listar todos los pagos |
| `/api/admin/payments/{id}` | GET | admin | Ver detalle de pago |
| `/api/admin/payments/{id}/process` | POST | admin | Procesar pago a vendedor |
| `/api/admin/payments/{id}/cancel` | POST | admin | Cancelar pago |
| `/api/admin/payments/{id}/reschedule` | POST | admin | Reprogramar pago |
| `/api/admin/payments/schedules` | GET | admin | Ver calendario de pagos |
| `/api/admin/payments/schedules` | PUT | admin | Actualizar calendario |
| `/api/seller/payments` | GET | seller | Mis pagos |
| `/api/seller/payments/pending` | GET | seller | Pagos pendientes |
| `/api/seller/payments/completed` | GET | seller | Pagos completados |
| `/api/seller/payments/pending-total` | GET | seller | Total pendiente |

**Caracteristicas:**
- Programacion de pagos por dia de la semana
- Estados: pending, processing, completed, cancelled
- Verificacion de si hoy es dia de pago
- Calculo de proxima fecha de pago

---

## 4. Envios / Shipping

**Controller:** `ShippingController`
**Modelos:** `Shipment`, `ShippingMethod`, `ShippingRate`, `ShippingZone`, `StoreShippingMethod`

| Endpoint | Metodo | Auth | Descripcion |
|----------|--------|------|-------------|
| `/api/shipping/methods` | GET | publico | Metodos de envio disponibles |
| `/api/shipping/zones` | GET | publico | Zonas de envio |
| `/api/shipping/calculate` | POST | publico | Calcular costo de envio |
| `/api/seller/shipping/configure` | POST | seller | Configurar envio de tienda |
| `/api/seller/shipping/methods` | GET | seller | Metodos de la tienda |
| `/api/seller/shipments` | GET | seller | Envios de la tienda |
| `/api/seller/shipments` | POST | seller | Crear envio |
| `/api/seller/shipments/{id}/tracking` | PUT | seller | Actualizar tracking |
| `/api/seller/shipments/{id}/ship` | POST | seller | Marcar como enviado |
| `/api/seller/shipments/{id}/deliver` | POST | seller | Marcar como entregado |

**Estados del envio:**
```
pending → picked_up → in_transit → out_for_delivery → delivered
                                                     → failed
```

---

## 5. Devoluciones (Returns)

**Controller:** `ReturnController`
**Modelos:** `ProductReturn`, `ReturnItem`

| Endpoint | Metodo | Auth | Descripcion |
|----------|--------|------|-------------|
| `/api/returns` | GET | auth | Mis devoluciones |
| `/api/returns` | POST | auth | Solicitar devolucion |
| `/api/returns/{id}` | GET | auth | Ver detalle |
| `/api/returns/{id}/cancel` | POST | auth | Cancelar solicitud |
| `/api/seller/returns` | GET | seller | Devoluciones de mi tienda |
| `/api/seller/returns/{id}/approve` | POST | seller | Aprobar devolucion |
| `/api/seller/returns/{id}/reject` | POST | seller | Rechazar devolucion |
| `/api/seller/returns/{id}/received` | POST | seller | Marcar como recibido |
| `/api/seller/returns/{id}/refund` | POST | seller | Procesar reembolso |
| `/api/seller/returns/{id}/tracking` | PUT | seller | Actualizar tracking |

**Flujo:**
```
pending → approved → shipped_back → received → refunded
        → rejected
        → cancelled
```

---

## 6. Disputas (Disputes)

**Controller:** `DisputeController`
**Modelos:** `Dispute`, `DisputeMessage`, `DisputeAttachment`
**Service:** `DisputeService`

| Endpoint | Metodo | Auth | Descripcion |
|----------|--------|------|-------------|
| `/api/disputes` | POST | auth | Crear disputa |
| `/api/disputes/mine` | GET | auth | Mis disputas |
| `/api/disputes/{id}` | GET | auth | Ver detalle |
| `/api/disputes/{id}/cancel` | POST | auth | Cancelar disputa |
| `/api/disputes/{id}/messages` | POST | auth | Agregar mensaje |
| `/api/seller/disputes` | GET | seller | Disputas de mi tienda |
| `/api/seller/disputes/{id}` | GET | seller | Ver detalle |
| `/api/admin/disputes` | GET | admin | Todas las disputas |
| `/api/admin/disputes/{id}/assign` | POST | admin | Asignar a admin |
| `/api/admin/disputes/{id}/status` | PUT | admin | Cambiar estado |
| `/api/admin/disputes/{id}/resolve` | POST | admin | Resolver disputa |
| `/api/admin/disputes/{id}/close` | POST | admin | Cerrar disputa |

**Estados:** `open → in_review → resolved → closed / escalated`

---

## 7. Servicios y Reservas (Services & Bookings)

**Controller:** `ServiceController`
**Modelos:** `Service`, `ServiceBooking`, `ServiceSchedule`
**Service:** `ServiceService`

| Endpoint | Metodo | Auth | Descripcion |
|----------|--------|------|-------------|
| `/api/services` | GET | publico | Listar servicios |
| `/api/services/{id}` | GET | publico | Ver servicio |
| `/api/services/{id}/available-slots` | GET | publico | Horarios disponibles |
| `/api/services/{id}/book` | POST | auth | Reservar turno |
| `/api/services/bookings` | GET | auth | Mis reservas |
| `/api/services/bookings/{id}/cancel` | POST | auth | Cancelar reserva |
| `/api/services/bookings/{id}/reschedule` | POST | auth | Reprogramar |
| `/api/services/bookings/{id}/no-show` | POST | auth | Marcar no-show |
| `/api/seller/services` | GET | seller | Mis servicios |
| `/api/seller/services` | POST | seller | Crear servicio |
| `/api/seller/services/{id}` | PUT | seller | Actualizar servicio |
| `/api/seller/services/{id}` | DELETE | seller | Eliminar servicio |
| `/api/seller/services/bookings` | GET | seller | Reservas de mis servicios |
| `/api/seller/services/bookings/{id}/confirm` | POST | seller | Confirmar reserva |

**Tipos de servicio:** presencial, virtual, domicilio

---

## 8. Reviews / Resenas

**Controller:** `ReviewController`
**Modelo:** `Review`

| Endpoint | Metodo | Auth | Descripcion |
|----------|--------|------|-------------|
| `/api/reviews?product_id=X` | GET | publico | Resenas de un producto |
| `/api/reviews/{id}` | GET | publico | Ver resena |
| `/api/reviews` | POST | auth | Crear resena |
| `/api/reviews/{id}` | PUT | auth | Editar resena |
| `/api/reviews/{id}` | DELETE | auth | Eliminar resena |

**Caracteristicas:**
- Rating 1-5 estrellas
- Compra verificada (is_verified_purchase)
- Estadisticas de rating por producto
- Solo admin puede editar/eliminar resenas de otros

---

## 9. Cupones (Coupons)

**Controller:** `CouponController`
**Modelos:** `Coupon`, `CouponUsage`

| Endpoint | Metodo | Auth | Descripcion |
|----------|--------|------|-------------|
| `/api/coupons` | GET | auth | Listar cupones |
| `/api/coupons/{id}` | GET | auth | Ver cupon |
| `/api/coupons` | POST | seller/admin | Crear cupon |
| `/api/coupons/{id}` | PUT | seller/admin | Editar cupon |
| `/api/coupons/{id}` | DELETE | seller/admin | Eliminar cupon |
| `/api/coupons/validate?code=X` | GET | auth | Validar cupon |

**Tipos:** porcentaje, monto fijo. Con limite de usos por usuario.

---

## 10. Facturas (Invoices)

**Controller:** `InvoiceController`
**Modelo:** `Invoice`

| Endpoint | Metodo | Auth | Descripcion |
|----------|--------|------|-------------|
| `/api/invoices` | GET | auth | Mis facturas |
| `/api/invoices/{id}` | GET | auth | Ver factura |
| `/api/invoices/order/{orderId}` | POST | auth | Generar factura para orden |

---

## 11. Programa de Fidelizacion (Loyalty)

**Controller:** `LoyaltyController`
**Modelos:** `LoyaltyProgram`, `LoyaltyTier`, `LoyaltyReward`, `LoyaltyTransaction`, `UserLoyaltyAccount`, `UserRedeemedReward`
**Service:** `LoyaltyService`

| Endpoint | Metodo | Auth | Descripcion |
|----------|--------|------|-------------|
| `/api/loyalty/account` | GET | auth | Mi cuenta de fidelizacion |
| `/api/loyalty/status` | GET | auth | Mi nivel/tier |
| `/api/loyalty/rewards` | GET | auth | Recompensas disponibles |
| `/api/loyalty/redeem` | POST | auth | Canjear puntos |
| `/api/loyalty/redemptions` | GET | auth | Mis canjes |
| `/api/loyalty/transactions` | GET | auth | Historial de puntos |
| `/api/loyalty/validate` | POST | auth | Validar codigo de recompensa |
| `/api/loyalty/use` | POST | auth | Usar codigo de recompensa |

---

## 12. Solicitudes de Plan (Plan Requests)

**Controller:** `PlanRequestController`
**Modelo:** `PlanRequest`

| Endpoint | Metodo | Auth | Descripcion |
|----------|--------|------|-------------|
| `/api/plans/requests` | POST | auth | Solicitar plan |
| `/api/plans/requests/me` | GET | auth | Mis solicitudes |
| `/api/admin/plan-requests` | GET | admin | Todas las solicitudes |
| `/api/admin/plan-requests/{id}` | GET | admin | Ver detalle |
| `/api/admin/plan-requests/{id}/approve` | POST | admin | Aprobar solicitud |
| `/api/admin/plan-requests/{id}/reject` | POST | admin | Rechazar solicitud |
| `/api/admin/plan-requests/stream` | GET | admin | SSE stream de solicitudes |

**Integracion:** Webhook de Izipay para confirmacion de pago.

---

## 13. Solicitudes de Perfil de Tienda (Profile Requests)

**Controller:** `ProfileRequestController`
**Modelo:** `StoreProfileRequest`

| Endpoint | Metodo | Auth | Descripcion |
|----------|--------|------|-------------|
| `/api/stores/me/profile-request` | GET | auth | Mi solicitud actual |
| `/api/stores/me/profile-request` | POST | auth | Enviar solicitud de verificacion |
| `/api/admin/profile-requests` | GET | admin | Todas las solicitudes |
| `/api/admin/profile-requests/{id}` | GET | admin | Ver detalle |
| `/api/admin/profile-requests/{id}/approve` | POST | admin | Aprobar |
| `/api/admin/profile-requests/{id}/reject` | POST | admin | Rechazar |
| `/api/admin/profile-requests/stream` | GET | admin | SSE stream |

---

## 14. Suscripciones (Subscriptions)

**Controller:** `SubscriptionController`
**Modelo:** `Subscription`

| Endpoint | Metodo | Auth | Descripcion |
|----------|--------|------|-------------|
| `/api/subscriptions` | GET | auth | Mis suscripciones |
| `/api/subscriptions` | POST | auth | Crear suscripcion |
| `/api/subscriptions/{id}` | GET | auth | Ver detalle |
| `/api/subscriptions/{id}/cancel` | POST | auth | Cancelar suscripcion |
| `/api/subscriptions/{id}/renew` | POST | auth | Renovar suscripcion |

---

## 15. Media / Archivos

**Controller:** `MediaController`

| Endpoint | Metodo | Auth | Descripcion |
|----------|--------|------|-------------|
| `/api/products/{id}/media` | POST | seller | Subir imagen de producto |
| `/api/products/{id}/media` | GET | seller | Ver imagenes de producto |
| `/api/products/{id}/media/{mediaId}` | DELETE | seller | Eliminar imagen |
| `/api/products/{id}/media/reorder` | PUT | seller | Reordenar imagenes |
| `/api/stores/{id}/media/logo` | POST | seller | Subir logo |
| `/api/stores/{id}/media/banner` | POST | seller | Subir banner |
| `/api/stores/{id}/media/banner2` | POST | seller | Subir banner secundario |
| `/api/stores/{id}/media/gallery` | POST | seller | Subir a galeria |
| `/api/stores/{id}/media/gallery/{mediaId}` | DELETE | seller | Eliminar de galeria |
| `/api/stores/{id}/media/{mediaId}` | DELETE | seller | Eliminar media |
| `/api/stores/{id}/media/policy` | POST | seller | Subir PDF de politica |
| `/api/stores/{id}/media/policy/{type}` | DELETE | seller | Eliminar politica |

---

## 16. Notificaciones

**Controller:** `NotificationController`

| Endpoint | Metodo | Auth | Descripcion |
|----------|--------|------|-------------|
| `/api/notifications` | GET | auth | Mis notificaciones |
| `/api/notifications/{id}` | GET | auth | Ver notificacion |
| `/api/notifications/{id}/read` | POST | auth | Marcar como leida |
| `/api/notifications/read-all` | POST | auth | Marcar todas como leidas |
| `/api/notifications/{id}` | DELETE | auth | Eliminar notificacion |
| `/api/notifications/delete-all` | DELETE | auth | Eliminar todas |

---

## 17. Busqueda (Search)

**Controller:** `SearchController`

| Endpoint | Metodo | Auth | Descripcion |
|----------|--------|------|-------------|
| `/api/search` | GET | publico | Busqueda global |
| `/api/search/products` | GET | publico | Buscar productos |
| `/api/search/suggestions` | GET | publico | Sugerencias de busqueda |

**Motor:** Scout + Meilisearch, con fallback a busqueda por query SQL.

---

## 18. Home / Contenido

**Controller:** `HomeController`, `BrandController`, `BenefitController`, `NewsletterController`

| Endpoint | Metodo | Descripcion |
|----------|--------|-------------|
| `/api/home/heroes` | GET | Imagenes hero del home |
| `/api/home/banners-pub` | GET | Banners publicitarios |
| `/api/home/section/{slug}` | GET | Seccion del home por slug |
| `/api/brands` | GET | Listado de marcas |
| `/api/benefits` | GET | Beneficios del marketplace |
| `/api/newsletter` | POST | Suscribirse al newsletter |

---

## 19. Configuracion del Sistema

**Controller:** `SystemConfigController`

| Endpoint | Metodo | Auth | Descripcion |
|----------|--------|------|-------------|
| `/api/config/colors` | GET | publico | Colores del tema |
| `/api/config/public` | GET | publico | Configuraciones publicas |
| `/api/admin/config` | GET | admin | Todas las configuraciones |
| `/api/admin/config/{key}` | GET | admin | Ver config por clave |
| `/api/admin/config` | POST | admin | Crear config |
| `/api/admin/config/{key}` | PUT | admin | Actualizar config |
| `/api/admin/config/{key}` | DELETE | admin | Eliminar config |

---

## 20. Eventos (SSE - Server-Sent Events)

**Controller:** `EventsController`

| Endpoint | Metodo | Auth | Descripcion |
|----------|--------|------|-------------|
| `/api/events` | GET | publico | Stream SSE para actualizaciones en tiempo real |

Usado para notificaciones en vivo de solicitudes de plan y perfil.

---

## Resumen de Totales

| Concepto | Cantidad |
|----------|----------|
| Controllers | 35 |
| Models | 51 |
| Services | 10 |
| Migrations | 67 |
| Form Requests | 36 |
| API Resources | 42 |
| Middleware | 4 |
| Endpoints totales | ~150+ |
| Modulos nuevos (no en backend original) | 17 |

---

## Modulos nuevos vs Backend original

| Modulo | Backend Original | Backend Nuevo |
|--------|:---:|:---:|
| Auth + OTP + Google | Si | Si |
| Users / Roles | Si | Si (mejorado con Spatie puro) |
| Stores | Si | Si (+ branches, media, visual, profile requests) |
| Products | Si | Si (+ tipos: physical, digital, service) |
| Categories | Si | Si |
| Plans / Subscriptions | Si | Si (+ plan requests con Izipay) |
| Suppliers | Si | Si |
| Contracts | Si | Si |
| Tickets / Mesa de Ayuda | Si | Si |
| **Carrito (Cart)** | No | Si |
| **Ordenes (Orders)** | No | Si |
| **Pagos (Payments)** | No | Si |
| **Envios (Shipping)** | No | Si |
| **Devoluciones (Returns)** | No | Si |
| **Disputas (Disputes)** | No | Si |
| **Servicios / Reservas** | No | Si |
| **Reviews / Resenas** | No | Si |
| **Cupones (Coupons)** | No | Si |
| **Facturas (Invoices)** | No | Si |
| **Programa de Fidelizacion** | No | Si |
| **Solicitudes de Plan** | No | Si |
| **Solicitudes de Perfil** | No | Si |
| **Media / Archivos** | No | Si |
| **Notificaciones** | No | Si |
| **Busqueda (Search)** | No | Si |
| **Home / Contenido** | No | Si |
| **Config del Sistema** | No | Si |
| **SSE (Real-time)** | No | Si |
| **Newsletter** | No | Si |
