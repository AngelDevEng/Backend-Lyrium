# Despliegue en Railway

Esta base de código sí se puede subir a Railway, pero no como un único proceso si quieres que funcione de forma estable. Por cómo está implementado hoy, el backend necesita al menos estos servicios:

- `app`: API HTTP pública de Laravel.
- `worker`: procesa colas (`QUEUE_CONNECTION=database`).
- `cron`: ejecuta el scheduler de Laravel.

`reverb` es opcional. Si quieres chat y notificaciones en tiempo real, agrégalo como cuarto servicio. Si primero solo quieres publicar la API y aligerar tu PC, puedes salir a producción inicial con `BROADCAST_CONNECTION=log`.

## Archivos agregados al repo

Se dejaron estos scripts para Railway:

- `railway/init-app.sh`
- `railway/run-worker.sh`
- `railway/run-cron.sh`
- `railway/run-reverb.sh`

## Configuración recomendada en Railway

## 1. Base de datos

Crea un servicio `MySQL` en Railway y referencia su URL desde el backend:

```env
DB_CONNECTION=mysql
DB_URL=${{MySQL.MYSQL_URL}}
```

Este proyecto ya soporta `DB_URL` en [config/database.php](/F:/TEST/Backend-Lyrium/config/database.php).

Nota:

- `${{MySQL.MYSQL_URL}}` asume que el servicio en Railway se llama `MySQL`.
- Si le pones otro nombre al servicio, cambia el namespace del template, por ejemplo `${{mysql-db.MYSQL_URL}}`.

## 2. Servicio `app`

Configura el servicio principal así:

- Source repo: este repo.
- Build command: `npm run build`
- Pre-deploy command: `chmod +x ./railway/init-app.sh && sh ./railway/init-app.sh`
- Start command: dejar el autodetect de Railway para Laravel
- Healthcheck path: `/up`

Variables mínimas:

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=tu_app_key
APP_URL=https://${{RAILWAY_PUBLIC_DOMAIN}}
FRONTEND_URL=http://localhost:3000

LOG_CHANNEL=stderr
LOG_STDERR_FORMATTER=\Monolog\Formatter\JsonFormatter

DB_CONNECTION=mysql
DB_URL=${{MySQL.MYSQL_URL}}

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

FILESYSTEM_DISK=public
BROADCAST_CONNECTION=log
```

Notas:

- `FRONTEND_URL` debe ser `http://localhost:3000` mientras tu frontend siga corriendo local, o el dominio real del frontend cuando ya lo subas.
- `APP_URL` debe apuntar al dominio público del servicio `app`, porque el proyecto construye URLs de archivos con `asset(...)`.
- Laravel ya tiene health endpoint en [bootstrap/app.php](/F:/TEST/Backend-Lyrium/bootstrap/app.php).

## 3. Servicio `worker`

Duplica el servicio usando el mismo repo y configura:

- Build command: dejar el default de Railway
- Start command: `chmod +x ./railway/run-worker.sh && sh ./railway/run-worker.sh`
- Sin dominio público

Debe compartir las mismas variables del servicio `app`.

## 4. Servicio `cron`

Duplica otra vez el servicio usando el mismo repo y configura:

- Build command: dejar el default de Railway
- Start command: `chmod +x ./railway/run-cron.sh && sh ./railway/run-cron.sh`
- Sin dominio público

Debe compartir las mismas variables del servicio `app`.

Este proyecto sí usa scheduler: limpia OTP expirados cada hora en [routes/console.php](/F:/TEST/Backend-Lyrium/routes/console.php).

## 5. Servicio `reverb` opcional

Agrégalo solo si quieres WebSockets reales para helpdesk y notificaciones.

Configuración:

- Build command: dejar el default de Railway
- Start command: `chmod +x ./railway/run-reverb.sh && sh ./railway/run-reverb.sh`
- Con dominio público propio

Variables recomendadas:

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=tu_app_id
REVERB_APP_KEY=tu_app_key
REVERB_APP_SECRET=tu_app_secret

REVERB_HOST=${{reverb.RAILWAY_PUBLIC_DOMAIN}}
REVERB_PORT=443
REVERB_SCHEME=https
```

Notas:

- El script usa el `PORT` que Railway inyecta al servicio.
- El nombre `reverb` en `${{reverb.RAILWAY_PUBLIC_DOMAIN}}` debe coincidir con el nombre real de tu servicio en Railway.
- Este proyecto dispara varios eventos `ShouldBroadcastNow`, así que no conviene habilitar `BROADCAST_CONNECTION=reverb` si el servicio de Reverb aún no existe.
- Si quieres simplificar la primera subida, usa `BROADCAST_CONNECTION=log` y deja Reverb para una segunda fase.

## Storage de imágenes y adjuntos

Con la implementación actual, los archivos no se guardan en la base de datos. Se guardan en el disco `public` de Laravel y la BD solo almacena metadata o paths.

Ejemplos del proyecto:

- [TicketAttachmentService.php](/F:/TEST/Backend-Lyrium/app/Services/TicketAttachmentService.php)
- [TicketMessageResource.php](/F:/TEST/Backend-Lyrium/app/Http/Resources/TicketMessageResource.php)
- [filesystems.php](/F:/TEST/Backend-Lyrium/config/filesystems.php)

### Recomendación práctica para este repo hoy

Usa un `Volume` en Railway para persistir `storage/app/public` del servicio `app`.

Punto importante:

- Railway usa filesystem efímero en deployments normales.
- Si no montas volumen, las imágenes, banners, adjuntos y otros archivos públicos se pueden perder tras redeploys.

### Sobre Railway Buckets

Railway también tiene buckets S3 compatibles, pero hoy no son públicos por defecto. Este backend genera URLs públicas tipo `/storage/...`, así que cambiar a bucket no es drop-in con el código actual.

Para subir ya mismo sin refactor extra:

- usa `Volume` para el servicio `app`;
- mantén `FILESYSTEM_DISK=public`.

Para una segunda fase más sólida:

- migrar uploads a `s3`;
- exponer archivos con URLs firmadas o proxy desde backend;
- dejar de depender de `asset('storage/...')`.

## Variables adicionales según tu caso

Si usas correo real en Railway, completa:

```env
MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=...
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=...
MAIL_FROM_NAME="${APP_NAME}"
```

Si sigues usando frontend local mientras despliegas backend remoto:

```env
FRONTEND_URL=http://localhost:3000
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true
```

## Orden sugerido de despliegue

1. Subir `MySQL`.
2. Subir `app` con `BROADCAST_CONNECTION=log`.
3. Probar login, endpoints y uploads.
4. Subir `worker`.
5. Subir `cron`.
6. Cuando el backend estable, subir `reverb`.
7. Cambiar frontend local para apuntar al dominio público del backend.

## Checklist de verificación

- `GET /up` responde `200`.
- `php artisan migrate --force` corre sin errores en deploy.
- login funciona desde el frontend local apuntando al dominio de Railway.
- CORS responde bien con `FRONTEND_URL`.
- archivos subidos siguen existiendo después de redeploy.
- jobs encolados se procesan.
- limpieza horaria del scheduler corre.
- si activas Reverb, `broadcasting/auth` y las suscripciones privadas autentican correctamente.

## Referencias usadas

- Railway Laravel Guide: https://docs.railway.com/guides/laravel
- Railway Healthchecks: https://docs.railway.com/deployments/healthchecks
- Railway Variables: https://docs.railway.com/variables
- Railway Variables Reference: https://docs.railway.com/variables/reference
- Railway Volumes: https://docs.railway.com/volumes/reference
- Railway Storage Buckets: https://docs.railway.com/storage-buckets
- Laravel Reverb: https://laravel.com/docs/reverb
