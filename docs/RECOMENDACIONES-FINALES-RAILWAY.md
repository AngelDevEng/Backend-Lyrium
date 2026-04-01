# Recomendaciones Finales Railway

Estado actual asumido:

- `app` ya esta desplegado
- `worker` ya esta desplegado
- `reverb` ya esta desplegado

## Lo que todavia recomiendo hacer

### 1. Agregar `cron`

Todavia falta el servicio `cron` si quieres que produccion se parezca mas a local.

Al crear el servicio:

- elige `GitHub Repository`
- usa el mismo repo del backend
- `Root Directory`: `Backend-Lyrium`
- `Start Command`: `chmod +x ./railway/run-cron.sh && sh ./railway/run-cron.sh`
- sin dominio publico

Variables recomendadas:

```env
APP_ENV=production
APP_KEY=TU_APP_KEY
DB_CONNECTION=mysql
DB_URL=${{MySQL.MYSQL_URL}}
QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=public
RAILPACK_PHP_EXTENSIONS=exif,pcntl
```

## 2. Confirmar variables finales en `app`

En `app` debes tener Reverb activo:

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=TU_REVERB_APP_ID
REVERB_APP_KEY=TU_REVERB_APP_KEY
REVERB_APP_SECRET=TU_REVERB_APP_SECRET
REVERB_HOST=servicio-reverb-production-60eb.up.railway.app
REVERB_PORT=443
REVERB_SCHEME=https
```

Y para extensiones PHP:

```env
RAILPACK_PHP_EXTENSIONS=exif
```

Si quieres unificarlo todo sin pensar demasiado, tambien puedes usar:

```env
RAILPACK_PHP_EXTENSIONS=exif,pcntl
```

## 3. Confirmar variables finales en `worker`

`worker` no necesita dominio publico.
Se conecta a la misma base de datos y cola que `app`.

Variables recomendadas:

```env
APP_ENV=production
APP_KEY=TU_APP_KEY
DB_CONNECTION=mysql
DB_URL=${{MySQL.MYSQL_URL}}
QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=public
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=TU_REVERB_APP_ID
REVERB_APP_KEY=TU_REVERB_APP_KEY
REVERB_APP_SECRET=TU_REVERB_APP_SECRET
REVERB_HOST=servicio-reverb-production-60eb.up.railway.app
REVERB_PORT=443
REVERB_SCHEME=https
RAILPACK_PHP_EXTENSIONS=exif,pcntl
```

## 4. Confirmar variables finales en `reverb`

En `reverb`:

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=TU_REVERB_APP_ID
REVERB_APP_KEY=TU_REVERB_APP_KEY
REVERB_APP_SECRET=TU_REVERB_APP_SECRET
REVERB_HOST=servicio-reverb-production-60eb.up.railway.app
REVERB_PORT=443
REVERB_SCHEME=https
RAILPACK_PHP_EXTENSIONS=exif,pcntl
```

No uses `:8080` en el dominio publico.
`8080` queda como puerto interno del proceso.

## 5. Actualizar el frontend local

Archivo:

- `F:\FRONTEND\fe-001-marketplace-admin\frontapp\.env.local`

Deja al menos esto:

```env
NEXT_PUBLIC_API_MODE=laravel
NEXT_PUBLIC_API_BACKEND=laravel
NEXT_PUBLIC_LARAVEL_API_URL=https://TU-DOMINIO-DEL-APP.up.railway.app/api
NEXT_PUBLIC_GOOGLE_CLIENT_ID=TU_GOOGLE_CLIENT_ID

NEXT_PUBLIC_REVERB_APP_KEY=TU_REVERB_APP_KEY
NEXT_PUBLIC_REVERB_HOST=servicio-reverb-production-60eb.up.railway.app
NEXT_PUBLIC_REVERB_PORT=443
NEXT_PUBLIC_REVERB_SCHEME=https
```

Luego reinicia el frontend local.

## 6. Montar un `Volume` en `app`

Para no perder imagenes y adjuntos en redeploys, monta un `Volume` en el servicio `app` para persistir:

```text
storage/app/public
```

## 7. Seed de datos iniciales

Si en local tienes roles, planes, categorias, shipping y usuarios demo, entonces para parecerse a local te conviene correr una vez:

```bash
php artisan db:seed --force
```

Hazlo solo si quieres datos iniciales de prueba en produccion.

## 8. Orden de verificacion

Prueba en este orden:

1. `https://TU-DOMINIO-DEL-APP.up.railway.app/up`
2. login desde el frontend local
3. una accion que dispare cola para validar `worker`
4. una accion en tiempo real para validar `reverb`
5. valida que `cron` siga corriendo sin errores

## 9. Resumen practico

Si quieres cerrar bien el despliegue:

1. agrega `cron`
2. confirma variables de `app`, `worker` y `reverb`
3. actualiza `.env.local` del frontend
4. monta `Volume` en `app`
5. corre seed una vez si necesitas data demo
6. prueba login, colas y tiempo real
