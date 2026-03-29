# Plan: Mega-Menú con Categorías Dinámicas desde BD (3 Niveles)

**Fecha:** 2026-03-29
**Estado:** Pendiente
**Prioridad:** Media-Alta

---

## Rutas de los Proyectos

| Proyecto | Ruta |
|----------|------|
| **Frontend (Next.js 16)** | `F:\FRONTEND\fe-001-marketplace-admin\frontapp` |
| **Backend nuevo (Laravel 12)** | `F:\TEST\Backend-Lyrium` |
| **Base de datos** | MySQL `db-lyriumv1` |
| **API URL** | `http://127.0.0.1:8000/api` |

---

## Problema Actual

El mega-menú del frontend usa datos **hardcodeados** en `src/data/menuData.ts`. El archivo ya dice en línea 5: *"Futuro: estos datos vendrán de la API de Laravel"*.

El panel admin (`/admin/categories`) existe pero está **incompleto**: solo muestra el conteo de categorías, sin UI para CRUD ni jerarquía visual.

---

## Estructura del Mega-Menú (3 Niveles)

```
NIVEL 1 — Categoría Global (lista izquierda)          → Sin imagen en menú
├── NIVEL 2 — Subcategoría (iconos circulares)         → CON imagen (.webp)
│   ├── NIVEL 3 — Sub-subcategoría (links en columnas) → Sin imagen
│   ├── NIVEL 3
│   └── NIVEL 3
├── NIVEL 2
│   ├── NIVEL 3
│   └── NIVEL 3
└── NIVEL 2
```

### Ejemplo real del frontend actual:

```
Bebés y recién nacidos          ← NIVEL 1 (parent_id = null)
├── De paseo y en el coche      ← NIVEL 2 (parent_id = 1, image = /img/.../1.webp)
│   ├── De paseo                ← NIVEL 3 (parent_id = 2)
│   └── En el coche             ← NIVEL 3
├── Alimentación                ← NIVEL 2 (parent_id = 1, image = /img/.../2.webp)
│   ├── Menaje infantil         ← NIVEL 3
│   ├── Suplementos nutricionales ← NIVEL 3
│   └── Tronas y elevadores     ← NIVEL 3
├── Juguetes                    ← NIVEL 2
├── Ropa                        ← NIVEL 2
├── Calzado                     ← NIVEL 2
└── Lactancia y chupetes        ← NIVEL 2
```

---

## Estado Actual vs Lo que Falta

### Backend

| Componente | Estado | Detalle |
|------------|--------|---------|
| Tabla `categories` con `parent_id` | ✅ | Soporta N niveles recursivamente |
| Campo `image` en categories | ✅ | `$table->string('image')->nullable()` |
| Campo `type` (product/service) | ✅ | Migración `add_type_to_categories_table` |
| Campo `sort_order` | ✅ | Para ordenar subcategorías |
| Modelo `Category` con `children()` | ✅ | `hasMany(Category::class, 'parent_id')` |
| CRUD endpoints protegidos (admin) | ✅ | POST/PUT/DELETE bajo middleware admin |
| Endpoint público GET | ✅ | `GET /api/categories` |
| **Endpoint tree con 3 niveles** | ❌ | Solo carga `with('children')`, falta `children.children` |
| **Endpoint específico para mega-menú** | ❌ | Falta endpoint optimizado |
| **CategorySeeder con 3 niveles** | ❌ | Solo tiene nivel 1 (7 product + 7 service) |
| **Upload de imagen** | ❌ | El store/update acepta `image` como string, no file upload |
| **Validación de nivel máximo (3)** | ❌ | No valida profundidad al crear |

### Frontend — Panel Admin (`/admin/categories`)

| Componente | Estado | Detalle |
|------------|--------|---------|
| Página `CategoriesPageClient.tsx` | ⚠️ Skeleton | Solo muestra conteo, sin UI real |
| Hook `useCategories.ts` | ✅ | CRUD mutations + tree builder funcionan |
| **Vista de árbol jerárquico** | ❌ | No hay componente visual de árbol |
| **Formulario crear/editar categoría** | ❌ | Botón "Nueva Categoría" sin funcionalidad |
| **Selector de padre** | ❌ | Para elegir nivel 1 o nivel 2 como padre |
| **Upload de imagen** | ❌ | Para subcategorías nivel 2 |
| **Drag & drop para reordenar** | ❌ | Para cambiar `sort_order` |
| **Indicador de nivel** | ❌ | Visual que muestre Nivel 1/2/3 |

### Frontend — Mega-Menú Público

| Componente | Estado | Detalle |
|------------|--------|---------|
| `MegaMenu.tsx` | ✅ | Renderiza correctamente los 3 niveles |
| `menuData.ts` | ⚠️ Hardcodeado | 9 categorías nivel 1 con toda su jerarquía estática |
| `DesktopNav.tsx` | ✅ | Controla hover y posición del mega-menú |
| **Hook para cargar categorías de API** | ❌ | Lee de `menuData.ts` en vez de API |
| **Transformar API response a formato MegaMenu** | ❌ | MegaMenu espera `{ icons: [...], cols: [...] }` |

---

## Tareas

### BACKEND

#### Tarea 1: Nuevo endpoint para mega-menú (3 niveles)

**Archivo:** `app/Http/Controllers/Api/CategoryController.php`

Agregar un nuevo método para el mega-menú que devuelva los 3 niveles optimizado:

```php
/**
 * GET /api/categories/mega-menu
 * Devuelve categorías en formato árbol con 3 niveles para el mega-menú público.
 */
public function megaMenu(): JsonResponse
{
    $categories = Category::whereNull('parent_id')
        ->where('type', 'product')
        ->with(['children' => function ($q) {
            $q->orderBy('sort_order')
              ->with(['children' => function ($q2) {
                  $q2->orderBy('sort_order');
              }]);
        }])
        ->orderBy('sort_order')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $categories->map(function ($cat) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'image' => $cat->image,
                'children' => $cat->children->map(function ($sub) {
                    return [
                        'id' => $sub->id,
                        'name' => $sub->name,
                        'slug' => $sub->slug,
                        'image' => $sub->image,  // imagen del nivel 2 (iconos circulares)
                        'href' => '/productos/' . $sub->slug,
                        'children' => $sub->children->map(function ($subsub) {
                            return [
                                'id' => $subsub->id,
                                'name' => $subsub->name,
                                'slug' => $subsub->slug,
                                'href' => '/productos/' . $subsub->slug,
                            ];
                        }),
                    ];
                }),
            ];
        }),
    ]);
}
```

**Ruta (agregar en `routes/api.php`):**
```php
// Rutas públicas
Route::get('/categories/mega-menu', [CategoryController::class, 'megaMenu']);
```

**IMPORTANTE:** Esta ruta debe ir ANTES de `Route::get('/categories/{id}', ...)` para que no interprete "mega-menu" como un `{id}`.

---

#### Tarea 2: Actualizar endpoint tree existente para soportar 3 niveles

**Archivo:** `app/Http/Controllers/Api/CategoryController.php` — método `index`

**Cambiar línea 33:**
```php
// ANTES:
$query->whereNull('parent_id')->with('children');

// DESPUÉS:
$query->whereNull('parent_id')->with('children.children');
```

---

#### Tarea 3: Validar profundidad máxima al crear categoría

**Archivo:** `app/Http/Controllers/Api/CategoryController.php` — método `store`

Agregar validación para que no se creen categorías de nivel 4+:

```php
public function store(Request $request): JsonResponse
{
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'parent' => 'nullable|integer|exists:categories,id',
        'image' => 'nullable|string',
        'type' => 'nullable|string|in:product,service',
        'sort_order' => 'nullable|integer|min:0',
    ]);

    // Validar profundidad máxima (3 niveles)
    if (isset($data['parent'])) {
        $parent = Category::find($data['parent']);
        if ($parent && $parent->parent_id) {
            $grandparent = Category::find($parent->parent_id);
            if ($grandparent && $grandparent->parent_id) {
                return response()->json([
                    'success' => false,
                    'error' => 'No se pueden crear categorías de más de 3 niveles de profundidad.',
                ], 422);
            }
        }
    }

    $category = Category::create([
        'name' => $data['name'],
        'slug' => Str::slug($data['name']),
        'description' => $data['description'] ?? null,
        'parent_id' => $data['parent'] ?? null,
        'image' => $data['image'] ?? null,
        'type' => $data['type'] ?? 'product',
        'sort_order' => $data['sort_order'] ?? 0,
    ]);

    return response()->json(new CategoryResource($category->loadCount('products')), 201);
}
```

---

#### Tarea 4: Agregar endpoint para upload de imagen de categoría

**Archivo:** `app/Http/Controllers/Api/CategoryController.php`

Agregar método para subir imagen:

```php
/**
 * POST /api/categories/{id}/image
 */
public function uploadImage(Request $request, int $id): JsonResponse
{
    $category = Category::findOrFail($id);

    $request->validate([
        'image' => 'required|image|mimes:webp,png,jpg,jpeg|max:2048',
    ]);

    $path = $request->file('image')->store('img/categorias', 'public');

    $category->update(['image' => '/storage/' . $path]);

    return response()->json([
        'success' => true,
        'image' => '/storage/' . $path,
    ]);
}
```

**Ruta (agregar en `routes/api.php` dentro del grupo admin):**
```php
Route::post('/categories/{id}/image', [CategoryController::class, 'uploadImage']);
```

---

#### Tarea 5: Actualizar CategorySeeder con los 3 niveles del mega-menú

**Archivo:** `database/seeders/CategorySeeder.php`

El seeder actual solo tiene nivel 1. Debe incluir los 3 niveles que están en `menuData.ts`. Ejemplo de estructura:

```php
private const PRODUCT_CATEGORIES = [
    [
        'name' => 'Bebés y recién nacidos',
        'slug' => 'bebes-recien-nacidos',
        'description' => 'Productos para bebés y recién nacidos',
        'image' => null,  // Nivel 1 no tiene imagen en mega-menú
        'sort_order' => 1,
        'type' => 'product',
        'children' => [
            [
                'name' => 'De paseo y en el coche',
                'slug' => 'bebes-paseo-coche',
                'image' => '/img/Productos/Bebes/1.webp',  // Nivel 2 SÍ tiene imagen
                'sort_order' => 1,
                'type' => 'product',
                'children' => [
                    ['name' => 'De paseo', 'slug' => 'bebes-de-paseo', 'sort_order' => 1, 'type' => 'product'],
                    ['name' => 'En el coche', 'slug' => 'bebes-en-coche', 'sort_order' => 2, 'type' => 'product'],
                ],
            ],
            [
                'name' => 'Alimentación',
                'slug' => 'bebes-alimentacion',
                'image' => '/img/Productos/Bebes/2.webp',
                'sort_order' => 2,
                'type' => 'product',
                'children' => [
                    ['name' => 'Menaje infantil', 'slug' => 'bebes-menaje-infantil', 'sort_order' => 1, 'type' => 'product'],
                    ['name' => 'Suplementos nutricionales', 'slug' => 'bebes-suplementos', 'sort_order' => 2, 'type' => 'product'],
                    ['name' => 'Tronas y elevadores', 'slug' => 'bebes-tronas', 'sort_order' => 3, 'type' => 'product'],
                ],
            ],
            // ... resto de nivel 2 con sus nivel 3
        ],
    ],
    // Repetir para las 9 categorías nivel 1 del menuData.ts:
    // - Bebés y recién nacidos
    // - Belleza
    // - Bienestar emocional y medicina natural
    // - Bienestar físico y deportes
    // - Digestión saludable
    // - Equipos y dispositivos médicos
    // - Mascotas
    // - Protección limpieza y desinfección
    // - Suplementos vitamínicos
];
```

**Referencia completa de datos:** `src/data/menuData.ts` contiene todas las categorías de los 3 niveles.

**Mapeo menuData.ts → Seeder:**
- `megaMenuData[key]` → Nivel 1 (key = nombre de categoría)
- `megaMenuData[key].icons[].title` → Nivel 2 (con `img` como image)
- `megaMenuData[key].cols[].items[]` → Nivel 3 (agrupados bajo `cols[].h` como headers)

**NOTA:** El método recursivo `createCategories()` ya existe en el seeder actual y soporta `children`, así que solo hay que actualizar el array `PRODUCT_CATEGORIES`.

---

#### Tarea 6: Fix tests de CategoryTest

**Archivo:** `tests/Feature/CategoryTest.php`

Dos tests fallan porque hacen `assertJsonCount(N)` contando keys del JSON raíz (`success`, `data`, `meta` = 3 keys) en vez de items dentro de `data`.

**Fix línea 45:**
```php
// ANTES:
->assertJsonCount(2); // 2 root categories

// DESPUÉS:
->assertJsonCount(2, 'data'); // 2 root categories
```

**Fix línea 60:**
```php
// ANTES:
->assertJsonCount(1);

// DESPUÉS:
->assertJsonCount(1, 'data');
```

---

### FRONTEND — Panel Admin

#### Tarea 7: Construir UI completa de CRUD de categorías

**Archivo:** `src/features/admin/categories/CategoriesPageClient.tsx`

La página actual solo muestra un skeleton. Debe incluir:

**Layout propuesto (2 columnas):**
```
┌─────────────────────────────┬──────────────────────────────────┐
│   ÁRBOL DE CATEGORÍAS       │   DETALLE / FORMULARIO           │
│                             │                                  │
│ ▼ Bebés y recién nacidos    │   Nombre: [Bebés y recién nac.]  │
│   ├── De paseo y coche  🖼  │   Slug:   [bebes-recien-nacidos] │
│   │   ├── De paseo          │   Tipo:   [product ▼]            │
│   │   └── En el coche       │   Padre:  [Ninguno ▼]            │
│   ├── Alimentación      🖼  │   Orden:  [1]                    │
│   │   ├── Menaje infantil   │   Imagen: [Subir imagen]         │
│   │   └── Suplementos       │   Descripción: [...]             │
│   └── Juguetes          🖼  │                                  │
│ ▼ Belleza                   │   [Guardar]  [Eliminar]          │
│   ├── Hombres           🖼  │                                  │
│   └── Mujeres           🖼  └──────────────────────────────────┘
│                             │
│ [+ Nueva Categoría]        │
└─────────────────────────────┘
```

**Componentes necesarios:**

1. **CategoryTree.tsx** — Árbol colapsable con indentación por nivel
   - Click en categoría → cargar en panel derecho
   - Icono 🖼 si tiene imagen (nivel 2)
   - Indicador de nivel (badge: N1, N2, N3)
   - Botón expandir/colapsar hijos

2. **CategoryForm.tsx** — Formulario de crear/editar
   - Input: nombre, descripción
   - Select: padre (dropdown con categorías nivel 1 y 2)
   - Select: tipo (product/service)
   - Input número: sort_order
   - Upload de imagen (solo relevante para nivel 2)
   - Botón guardar / eliminar

3. **CategoryImageUpload.tsx** — Componente de upload de imagen
   - Preview de imagen actual
   - Botón subir nueva → `POST /api/categories/{id}/image`
   - Vista previa antes de subir

**El hook `useCategories.ts` ya tiene:** `addCategory`, `editCategory`, `removeCategory` — solo necesitan ser conectados a la UI.

---

### FRONTEND — Mega-Menú Público

#### Tarea 8: Crear hook para cargar categorías de la API

**Archivo nuevo:** `src/shared/hooks/useMegaMenu.ts`

```typescript
import { useQuery } from '@tanstack/react-query';

interface MegaMenuCategory {
    id: number;
    name: string;
    slug: string;
    image: string | null;
    children: {
        id: number;
        name: string;
        slug: string;
        image: string | null;
        href: string;
        children: {
            id: number;
            name: string;
            slug: string;
            href: string;
        }[];
    }[];
}

export function useMegaMenu() {
    return useQuery<MegaMenuCategory[]>({
        queryKey: ['mega-menu'],
        queryFn: async () => {
            const LARAVEL_API = process.env.NEXT_PUBLIC_LARAVEL_API_URL || 'http://127.0.0.1:8000/api';
            const res = await fetch(`${LARAVEL_API}/categories/mega-menu`);
            const json = await res.json();
            return json.data;
        },
        staleTime: 30 * 60 * 1000, // 30 min cache — las categorías cambian poco
    });
}
```

---

#### Tarea 9: Transformar datos de API al formato que espera MegaMenu.tsx

**Archivo:** El componente que conecta el hook con MegaMenu.

El `MegaMenu.tsx` espera este formato:
```typescript
// menuData.ts actual
interface MegaCategoryData {
    icons: { title: string; img: string; href: string }[];  // ← Nivel 2
    cols: { h: string; items: string[] }[];                  // ← Nivel 3 agrupado
}
megaMenuData: Record<string, MegaCategoryData>  // key = nombre nivel 1
```

**Función de transformación:**
```typescript
function apiToMegaMenuFormat(categories: MegaMenuCategory[]): {
    menuItems: MenuItem[];
    megaMenuData: Record<string, MegaCategoryData>;
} {
    const menuItems: MenuItem[] = categories.map(cat => ({
        label: cat.name,
        href: `/productos/${cat.slug}`,
        children: cat.children.map(sub => ({
            label: sub.name,
            href: sub.href,
        })),
    }));

    const megaMenuData: Record<string, MegaCategoryData> = {};

    categories.forEach(cat => {
        megaMenuData[cat.name] = {
            // Nivel 2 → iconos circulares con imagen
            icons: cat.children.map(sub => ({
                title: sub.name,
                img: sub.image || '/img/placeholder-category.webp',
                href: sub.href,
            })),
            // Nivel 3 → columnas agrupadas por subcategoría nivel 2
            cols: cat.children
                .filter(sub => sub.children.length > 0)
                .map(sub => ({
                    h: sub.name.toUpperCase(),
                    items: sub.children.map(subsub => subsub.name),
                })),
        };
    });

    return { menuItems, megaMenuData };
}
```

---

#### Tarea 10: Integrar en DesktopNav.tsx

**Archivo:** `src/components/layout/public/DesktopNav.tsx`

Actualmente importa de `menuData.ts`. Cambiar para usar el hook:

```typescript
// ANTES:
import { menuItems, megaMenuData } from '@/data/menuData';

// DESPUÉS:
import { useMegaMenu } from '@/shared/hooks/useMegaMenu';
// + función de transformación

// Dentro del componente:
const { data: apiCategories, isLoading } = useMegaMenu();
const { menuItems, megaMenuData } = useMemo(
    () => apiToMegaMenuFormat(apiCategories || []),
    [apiCategories]
);
```

**NOTA:** Mantener `menuData.ts` como fallback mientras la API no tenga datos:
```typescript
import { menuItems as fallbackItems, megaMenuData as fallbackData } from '@/data/menuData';

const items = apiCategories?.length ? menuItems : fallbackItems;
const data = apiCategories?.length ? megaMenuData : fallbackData;
```

---

## Orden de Ejecución

```
BACKEND (hacer primero):
1. Tarea 6 — Fix tests (rápido, independiente)
2. Tarea 2 — Actualizar tree con children.children (rápido)
3. Tarea 1 — Nuevo endpoint /categories/mega-menu
4. Tarea 3 — Validar profundidad máxima
5. Tarea 4 — Upload de imagen
6. Tarea 5 — Actualizar CategorySeeder con 3 niveles

FRONTEND — Admin (puede ir en paralelo con frontend público):
7. Tarea 7 — CRUD completo en /admin/categories

FRONTEND — Público (después de que el backend esté listo):
8. Tarea 8 — Hook useMegaMenu
9. Tarea 9 — Función de transformación
10. Tarea 10 — Integrar en DesktopNav.tsx
```

**Paralelas:** [6, 2, 7] pueden ejecutarse simultáneamente.

---

## Verificación Final

```bash
# 1. Verificar endpoint mega-menu
curl http://127.0.0.1:8000/api/categories/mega-menu | jq '.data | length'
# → Debe devolver 9 categorías nivel 1

# 2. Verificar 3 niveles
curl http://127.0.0.1:8000/api/categories/mega-menu | jq '.data[0].children[0].children'
# → Debe devolver array de nivel 3

# 3. Verificar admin CRUD
# → Crear categoría nivel 1, luego nivel 2 con imagen, luego nivel 3
# → Intentar crear nivel 4 → debe dar error 422

# 4. Verificar mega-menú público
# → Abrir http://localhost:3000 → hover en nav → debe cargar categorías de la API
# → Las imágenes de nivel 2 deben aparecer en los iconos circulares

# 5. Tests
cd F:\TEST\Backend-Lyrium && php artisan test --filter=CategoryTest
# → Todos deben pasar
```

---

## Archivos Afectados (Resumen)

### Backend
| Archivo | Acción |
|---------|--------|
| `app/Http/Controllers/Api/CategoryController.php` | MODIFICAR — agregar megaMenu(), uploadImage(), validar profundidad |
| `routes/api.php` | MODIFICAR — agregar ruta /categories/mega-menu y /categories/{id}/image |
| `database/seeders/CategorySeeder.php` | MODIFICAR — agregar 3 niveles completos |
| `tests/Feature/CategoryTest.php` | MODIFICAR — fix assertJsonCount con key 'data' |

### Frontend
| Archivo | Acción |
|---------|--------|
| `src/features/admin/categories/CategoriesPageClient.tsx` | REESCRIBIR — UI completa con árbol + formulario |
| `src/features/admin/categories/components/CategoryTree.tsx` | CREAR — árbol colapsable |
| `src/features/admin/categories/components/CategoryForm.tsx` | CREAR — formulario crear/editar |
| `src/features/admin/categories/components/CategoryImageUpload.tsx` | CREAR — upload de imagen |
| `src/shared/hooks/useMegaMenu.ts` | CREAR — hook para cargar mega-menú de API |
| `src/components/layout/public/DesktopNav.tsx` | MODIFICAR — usar API en vez de menuData.ts |
| `src/data/menuData.ts` | MANTENER — como fallback temporal |
