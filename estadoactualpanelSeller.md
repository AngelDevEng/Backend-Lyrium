 Estado actual del panel Seller

  ┌─────────────────────────┬──────────────────────────────┬──────────────┐   
  │         Página          │       Fuente de datos        │  Conexión    │   
  │                         │                              │     real     │   
  ├─────────────────────────┼──────────────────────────────┼──────────────┤   
  │ Dashboard               │ Mock hardcoded               │ Ninguna      │   
  ├─────────────────────────┼──────────────────────────────┼──────────────┤   
  │ Catálogo (lista)        │ Mock                         │ Ninguna      │   
  ├─────────────────────────┼──────────────────────────────┼──────────────┤   
  │ Catálogo (crear         │ WooCommerce REST API         │ ✅ WP real   │    
  │ producto)               │                              │              │   
  ├─────────────────────────┼──────────────────────────────┼──────────────┤   
  │ Detalle orden           │ WooCommerce REST API         │ ✅ WP real   │    
  ├─────────────────────────┼──────────────────────────────┼──────────────┤   
  │ Ventas                  │ Mock                         │ Ninguna      │   
  ├─────────────────────────┼──────────────────────────────┼──────────────┤   
  │ Finanzas                │ Mock                         │ Ninguna      │   
  ├─────────────────────────┼──────────────────────────────┼──────────────┤   
  │ Perfil                  │ Dokan API                    │ ✅ WP real   │    
  ├─────────────────────────┼──────────────────────────────┼──────────────┤   
  │ Mi Tienda               │ Dokan API                    │ ✅ WP real   │    
  ├─────────────────────────┼──────────────────────────────┼──────────────┤   
  │ Facturas                │ Mock (lista) / Rapifac       │ Parcial      │   
  │                         │ (emitir)                     │              │   
  ├─────────────────────────┼──────────────────────────────┼──────────────┤   
  │ Logística               │ Mock                         │ Ninguna      │   
  ├─────────────────────────┼──────────────────────────────┼──────────────┤   
  │ Chat                    │ Mock                         │ Ninguna      │   
  ├─────────────────────────┼──────────────────────────────┼──────────────┤   
  │ Tickets/Ayuda           │ Mock                         │ Ninguna      │   
  ├─────────────────────────┼──────────────────────────────┼──────────────┤   
  │ Planes                  │ Mock                         │ Ninguna      │   
  ├─────────────────────────┼──────────────────────────────┼──────────────┤   
  │ Servicios               │ Mock inline                  │ Ninguna      │   
  ├─────────────────────────┼──────────────────────────────┼──────────────┤   
  │ Agenda                  │ Mock                         │ Ninguna      │   
  └─────────────────────────┴──────────────────────────────┴──────────────┘   

  Resumen:

  - 11 páginas usan solo mocks (datos ficticios ya existen)
  - 4 páginas conectan a WordPress/Dokan real (crear producto, detalle orden, 
  perfil, tienda)

  La mayoría ya tiene mocks. Para desconectar WordPress completamente,        
  necesitamos:

  1. Activar NEXT_PUBLIC_USE_MOCKS=true en tu .env.local — esto hace que las  
  11 páginas con mocks funcionen sin WP
  2. Implementar los LaravelProductRepository y LaravelUserRepository para    
  reemplazar las 4 conexiones WP restantes