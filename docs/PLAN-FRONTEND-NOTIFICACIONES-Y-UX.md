# PLAN — Frontend: Notificaciones y UX Feedback

**Fecha:** 2026-03-29
**Sesión:** Continuar en F:\TEST\Backend-Lyrium
**Frontend:** F:\FRONTEND\fe-001-marketplace-admin\frontapp

---

## Contexto

El sistema de toasts ya existe y está completo en:
```
src/shared/lib/context/ToastContext.tsx
```

Exporta `useToast()` con `showToast(message, type)` donde `type` puede ser:
- `'success'` — verde (CheckCircle2)
- `'error'` — rojo (XCircle)
- `'info'` — azul (Info)
- `'warning'` — amarillo (AlertTriangle)

Los toasts aparecen bottom-right, duran 4s, máximo 3 apilados, con animación `slideInRight`.

**El problema:** Los hooks de CRUD tienen `useMutation` pero ninguno llama a `showToast` en `onSuccess` ni `onError`. El usuario no recibe ningún feedback visual al crear, editar o eliminar.

---

## T1 — Toasts en CRUD de Categorías (PRIORITARIO)

**Archivo:** `src/features/admin/categories/hooks/useCategories.ts`

Las mutaciones `createMutation`, `updateMutation`, `deleteMutation`, `uploadImageMutation` no tienen callbacks de feedback.

**Cambios a hacer:**

```ts
// Importar el hook
import { useToast } from '@/shared/lib/context/ToastContext';

// Dentro de useCategories():
const { showToast } = useToast();

// En createMutation:
onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['admin', 'categories'] });
    showToast('Categoría creada correctamente', 'success');
},
onError: (err: Error) => {
    showToast(err.message || 'Error al crear la categoría', 'error');
},

// En updateMutation:
onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['admin', 'categories'] });
    showToast('Categoría actualizada', 'success');
},
onError: (err: Error) => {
    showToast(err.message || 'Error al actualizar', 'error');
},

// En deleteMutation:
onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['admin', 'categories'] });
    setSelectedCategoryId(null);
    showToast('Categoría eliminada', 'success');
},
onError: (err: Error) => {
    showToast(err.message || 'Error al eliminar', 'error');
},

// En uploadImageMutation:
onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['admin', 'categories'] });
    showToast('Imagen subida correctamente', 'success');
},
onError: (err: Error) => {
    showToast(err.message || 'Error al subir imagen', 'error');
},
```

---

## T2 — Auditar otros hooks CRUD sin feedback

Verificar estos archivos y agregar toasts donde falten:

| Hook | Ruta | Estado |
|------|------|--------|
| `useSellerServices.ts` | `src/features/seller/services/hooks/useSellerServices.ts` | Ya importa `useToast` — verificar si lo usa |
| `useControlVendedores.ts` | `src/features/admin/sellers/hooks/useControlVendedores.ts` | Verificar |
| Hooks de productos | `src/features/seller/products/` | Verificar |
| Hooks de pedidos | `src/features/seller/orders/` | Verificar |

**Patrón estándar a aplicar en todos:**
```ts
onSuccess: () => {
    queryClient.invalidateQueries(...);
    showToast('Acción completada', 'success');
},
onError: (err: Error) => {
    showToast(err.message || 'Algo salió mal', 'error');
},
```

---

## T3 — Verificar conexión mega-menu con nuevo backend

**Archivos:**
- `src/shared/hooks/useMegaMenu.ts`
- `src/components/layout/public/PublicHeader.tsx`

El endpoint `GET /api/categories/mega-menu` en `F:\TEST\Backend-Lyrium` ya responde correctamente con 3 niveles.

Verificar que:
1. `NEXT_PUBLIC_LARAVEL_API_URL` apunte al nuevo backend (puerto 8000)
2. El hook transforma correctamente la respuesta en formato `MenuItem[]`
3. El fallback a `menuData.ts` estático funciona si la API falla

---

## T4 — Verificar upload de imagen en admin categorías

**Archivos:**
- `src/features/admin/categories/components/CategoryForm.tsx`
- `src/features/admin/categories/hooks/useCategories.ts` → `apiUploadImage()`

El endpoint en el nuevo backend: `POST /api/categories/{id}/image`
Requiere: `multipart/form-data` con campo `image` (webp, png, jpg, jpeg, max 2MB)

Verificar que el form envíe correctamente el archivo y que la URL devuelta se muestre en el preview.

---

## T5 — Conectar `/seller/services` al backend real

**Archivos:**
- `src/features/seller/services/hooks/useSellerServices.ts`
- `src/shared/lib/api/serviceRepository.ts` (verificar si existe)

El hook usa `USE_MOCKS` flag. Cuando está en false, llama a `serviceApi.list()` que devuelve array vacío.

El nuevo backend tiene:
- `GET /api/seller/services` — listar servicios de la tienda
- `POST /api/seller/services` — crear servicio
- `PUT /api/seller/services/{id}` — editar
- `DELETE /api/seller/services/{id}` — eliminar
- `GET /api/seller/services/{id}/schedules` — horarios
- `GET /api/seller/services/{id}/bookings` — citas

Conectar `serviceApi` a estos endpoints reales.

---

## Orden de ejecución recomendado

1. **T1** — Toasts categorías (15 min, impacto inmediato)
2. **T2** — Auditar otros hooks (30 min)
3. **T3** — Verificar mega-menu (10 min, solo prueba visual)
4. **T4** — Upload imagen categorías (20 min)
5. **T5** — Servicios reales (mayor, requiere revisar endpoints del nuevo backend)

---

## Notas importantes para la sesión

- **Cookie de auth:** Se llama `laravel_token` (NO `auth_token`). Usar siempre la función de `src/lib/api/apiClient.ts` para leerla.
- **Backend activo:** `F:\TEST\Backend-Lyrium` corriendo en `http://127.0.0.1:8000`
- **`ToastProvider`** debe estar en el layout raíz — verificar que esté en `app/layout.tsx` o en el layout del panel admin antes de usarlo.
