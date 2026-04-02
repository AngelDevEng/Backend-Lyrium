<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Events\StoreStatusChanged;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateRequest;
use App\Http\Resources\StoreResource;
use App\Models\Store;
use App\Notifications\StoreStatusNotification;
use App\Services\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class StoreController extends Controller
{
    /**
     * GET /api/stores/me
     * Retorna la tienda del vendedor autenticado
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        $store = Store::with([
            'category',
            'subscription.plan',
            'branches',
            'legalRepresentative',
            'contacts',
            'banking',
            'socialMedia',
            'locations',
            'presentation',
            'insignia',
        ])
            ->where('owner_id', $user->id)
            ->first();

        if (! $store) {
            return response()->json([
                'data' => null,
                'message' => 'No tienes una tienda registrada',
            ], 404);
        }

        return response()->json([
            'data' => new StoreResource($store),
        ]);
    }

    /**
     * GET /api/stores (listado público con filtros)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Store::with(['owner', 'category']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('trade_name', 'like', "%{$search}%")
                    ->orWhere('trade_name_deprecated', 'like', "%{$search}%")
                    ->orWhere('ruc', 'like', "%{$search}%")
                    ->orWhere('corporate_email', 'like', "%{$search}%")
                    ->orWhere('razon_social', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $stores = $query->orderBy('created_at', 'desc')
            ->paginate($request->query('per_page', 50));

        return response()->json([
            'data' => StoreResource::collection($stores),
            'pagination' => [
                'page' => $stores->currentPage(),
                'perPage' => $stores->perPage(),
                'total' => $stores->total(),
                'totalPages' => $stores->lastPage(),
                'hasMore' => $stores->hasMorePages(),
            ],
        ]);
    }

    /**
     * GET /api/stores/{id}
     */
    public function show(int $id): JsonResponse
    {
        $store = Store::with([
            'owner',
            'subscription.plan',
            'category',
            'legalRepresentative',
            'contacts',
            'banking',
            'socialMedia',
            'locations',
            'presentation',
        ])->findOrFail($id);

        return response()->json(new StoreResource($store));
    }

    /**
     * POST /api/stores
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'trade_name' => 'required|string|max:255',
            'ruc' => 'required|string|size:11|unique:stores,ruc',
            'corporate_email' => 'required|email',
            'description' => 'nullable|string',
            'razon_social' => 'nullable|string|max:255',
            'experience_years' => 'nullable|integer|min:0|max:100',
            'tax_condition' => 'nullable|string|max:100',
            'category_id' => 'nullable|integer|exists:categories,id',
            'contacts' => 'nullable|array',
            'socialMedia' => 'nullable|array',
            'legalRepresentative' => 'nullable|array',
            'banking' => 'nullable|array',
            'locations' => 'nullable|array',
        ]);

        $data['owner_id'] = $request->user()->id;
        $data['slug'] = Str::slug($data['trade_name']);

        $hasRelations = isset($data['contacts']) || isset($data['socialMedia'])
            || isset($data['legalRepresentative']) || isset($data['banking'])
            || isset($data['locations']);

        if ($hasRelations) {
            $service = app(StoreService::class);
            $store = Store::create($data);
            $service->updateWithRelations($store, $data);
            $store = $store->fresh()->load(['owner', 'category', 'contacts', 'socialMedia', 'banking', 'legalRepresentative', 'locations']);
        } else {
            $store = Store::create($data);
        }

        return response()->json(new StoreResource($store), 201);
    }

    /**
     * PUT /api/stores/{id}
     * Actualiza la tienda con soporte para relaciones anidadas
     */
    public function update(StoreUpdateRequest $request, int $id): JsonResponse
    {
        $store = Store::findOrFail($id);

        $data = $request->validated();

        // Verificar si hay datos de relaciones anidadas
        $hasRelations = isset($data['store'])
            || isset($data['legalRepresentative'])
            || isset($data['contacts'])
            || isset($data['banking'])
            || isset($data['socialMedia'])
            || isset($data['location'])
            || isset($data['presentation']);

        if ($hasRelations) {
            $service = new StoreService;
            $store = $service->updateWithRelations($store, $data);
        } else {
            if (isset($data['bank_secondary'])) {
                $data['bank_secondary'] = json_encode($data['bank_secondary']);
            }

            $store->update($data);

            $store = $store->fresh()->load(['owner', 'category']);
        }

        return response()->json(new StoreResource($store));
    }

    /**
     * PUT /api/stores/{id}/status
     * Admin: aprobar, rechazar o banear vendedores
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $store = Store::with('owner')->findOrFail($id);

        $data = $request->validate([
            'status' => 'required|string|in:approved,rejected,banned',
            'reason' => 'nullable|string|max:500',
        ]);

        $updateData = ['status' => $data['status']];

        if ($data['status'] === 'approved') {
            $updateData['approved_at'] = now();
        }

        if ($data['status'] === 'banned') {
            $updateData['banned_at'] = now();
        }

        $store->update($updateData);

        // Enviar notificación al propietario de la tienda
        $store->owner->notify(new StoreStatusNotification(
            $store,
            $data['status'],
            $data['reason'] ?? null,
        ));

        broadcast(new StoreStatusChanged($store->fresh()));

        return response()->json(new StoreResource($store->fresh()->load(['owner', 'category'])));
    }

    /**
     * GET /api/stores/{id}/branches
     * Listar sucursales de una tienda
     */
    public function branches(int $id): JsonResponse
    {
        $store = Store::findOrFail($id);
        $branches = $store->branches()->where('is_active', true)->get();

        return response()->json([
            'data' => $branches->map(fn ($branch) => [
                'id' => $branch->id,
                'name' => $branch->name,
                'address' => $branch->address,
                'city' => $branch->city,
                'phone' => $branch->phone,
                'hours' => $branch->hours,
                'is_principal' => $branch->is_principal,
                'maps_url' => $branch->maps_url,
            ]),
        ]);
    }

    /**
     * PUT /api/stores/{id}/branches
     * Actualizar todas las sucursales (sync)
     * Solo una sucursal puede ser principal
     */
    public function updateBranches(Request $request, int $id): JsonResponse
    {
        $store = Store::findOrFail($id);

        $data = $request->validate([
            'branches' => 'required|array',
            'branches.*.id' => 'nullable|integer',
            'branches.*.name' => 'required|string|max:255',
            'branches.*.address' => 'required|string|max:500',
            'branches.*.city' => 'required|string|max:255',
            'branches.*.phone' => 'required|string|max:20',
            'branches.*.hours' => 'nullable|string|max:100',
            'branches.*.is_principal' => 'boolean',
            'branches.*.maps_url' => 'nullable|string|max:500',
        ]);

        $existingIds = $store->branches()->pluck('id')->toArray();
        $incomingIds = collect($data['branches'])->pluck('id')->filter()->toArray();

        $toDelete = array_diff($existingIds, $incomingIds);
        if (! empty($toDelete)) {
            $store->branches()->whereIn('id', $toDelete)->delete();
        }

        $hasNewPrincipal = false;
        foreach ($data['branches'] as $branchData) {
            $isPrincipal = $branchData['is_principal'] ?? false;
            $branchId = $branchData['id'] ?? null;

            if ($isPrincipal) {
                $hasNewPrincipal = true;
                $store->branches()->where('is_principal', true)->update(['is_principal' => false]);
            }

            $branchData['store_id'] = $store->id;
            unset($branchData['id']);

            $store->branches()->updateOrCreate(
                ['id' => $branchId],
                $branchData
            );
        }

        return response()->json(new StoreResource($store->fresh()->load(['owner', 'category', 'branches'])));
    }

    /**
     * PUT /api/stores/me/visual
     * Actualizar layout + identidad visual (URLs)
     */
    public function updateVisual(Request $request): JsonResponse
    {
        $user = $request->user();
        $store = Store::where('owner_id', $user->id)->first();

        if (! $store) {
            return response()->json(['message' => 'No tienes una tienda registrada'], 404);
        }

        $data = $request->validate([
            'layout' => 'required|in:1,2,3',
            'logo' => 'nullable|file|image|max:2048',
            'banner' => 'nullable|file|image|max:4096',
            'banner_secondary' => 'nullable|file|image|max:4096',
            'gallery' => 'nullable|array',
            'gallery.*' => 'file|image|max:2048',
        ]);

        $store->update(['layout' => $data['layout']]);

        if ($request->hasFile('logo')) {
            $store->clearMediaCollection('logo');
            $store->addMediaFromRequest('file')->toMediaCollection('logo');
        }

        if ($request->hasFile('banner')) {
            $store->clearMediaCollection('banner');
            $store->addMediaFromRequest('banner')->toMediaCollection('banner');
        }

        if ($request->hasFile('banner_secondary')) {
            $store->clearMediaCollection('banner2');
            $store->addMediaFromRequest('banner_secondary')->toMediaCollection('banner2');
        }

        if ($request->hasFile('gallery')) {
            $galleryUrls = [];
            foreach ($request->file('gallery') as $file) {
                $media = $store->addMedia($file)->toMediaCollection('gallery');
                $galleryUrls[] = $media->getUrl();
            }

            $currentGallery = $store->media()->where('collection_name', 'gallery')->get();
            foreach ($currentGallery as $item) {
                $galleryUrls[] = $item->getUrl();
            }
        }

        return response()->json(new StoreResource($store->fresh()));
    }

    /**
     * POST /api/stores/me/media/logo
     * Upload de logo
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $user = $request->user();
        $store = Store::where('owner_id', $user->id)->first();

        if (! $store) {
            return response()->json(['message' => 'No tienes una tienda registrada'], 404);
        }

        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $store->clearMediaCollection('logo');
        $media = $store->addMediaFromRequest('file')->toMediaCollection('logo');

        return response()->json([
            'url' => $media->getUrl(),
            'message' => 'Logo actualizado correctamente',
        ]);
    }

    /**
     * POST /api/stores/me/media/banner
     * Upload de banner(s)
     */
    public function uploadBanner(Request $request): JsonResponse
    {
        $user = $request->user();
        $store = Store::where('owner_id', $user->id)->first();

        if (! $store) {
            return response()->json(['message' => 'No tienes una tienda registrada'], 404);
        }

        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'type' => 'nullable|in:banner,banner2',
        ]);

        $type = $request->input('type', 'banner');
        $collection = $type === 'banner2' ? 'banner2' : 'banner';

        $store->clearMediaCollection($collection);
        $media = $store->addMediaFromRequest('file')->toMediaCollection($collection);

        return response()->json([
            'url' => $media->getUrl(),
            'type' => $type,
            'message' => 'Banner actualizado correctamente',
        ]);
    }

    /**
     * POST /api/stores/me/media/gallery
     * Upload de imágenes a galería
     */
    public function uploadGallery(Request $request): JsonResponse
    {
        $user = $request->user();
        $store = Store::where('owner_id', $user->id)->first();

        if (! $store) {
            return response()->json(['message' => 'No tienes una tienda registrada'], 404);
        }

        $request->validate([
            'files.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $urls = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $media = $store->addMedia($file)->toMediaCollection('gallery');
                $urls[] = $media->getUrl();
            }
        }

        return response()->json([
            'urls' => $urls,
            'message' => count($urls).' imágenes agregadas a la galería',
        ]);
    }

    /**
     * DELETE /api/stores/me/media/gallery/{index}
     * Eliminar imagen de galería por índice
     */
    public function deleteGalleryImage(Request $request, int $index): JsonResponse
    {
        $user = $request->user();
        $store = Store::where('owner_id', $user->id)->first();

        if (! $store) {
            return response()->json(['message' => 'No tienes una tienda registrada'], 404);
        }

        $gallery = $store->media()->where('collection_name', 'gallery')->get();

        if (! isset($gallery[$index])) {
            return response()->json(['message' => 'Imagen no encontrada'], 404);
        }

        $gallery[$index]->delete();

        return response()->json([
            'message' => 'Imagen eliminada correctamente',
        ]);
    }

    /**
     * GET /api/stores/{id}/badges
     * Retorna los badges activos del vendedor
     */
    public function getStoreBadges(int $id): JsonResponse
    {
        $store = Store::findOrFail($id);

        $store->calculateAndSyncBadges();

        $badges = $store->activeBadges()
            ->with('badge')
            ->get()
            ->map(fn ($sb) => [
                'type' => $sb->badge->type,
                'name' => $sb->badge->name,
                'description' => $sb->badge->description,
                'icon' => $sb->badge->icon,
                'earned_at' => $sb->earned_at?->toIso8601String(),
            ]);

        return response()->json([
            'data' => $badges,
        ]);
    }

    /**
     * POST /api/stores/{id}/insignia/request
     * El vendedor solicita la insignia premium
     */
    public function requestInsignia(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $store = Store::where('owner_id', $user->id)->with('insignia')->findOrFail($id);

        if (! $store->canRequestInsignia()) {
            return response()->json([
                'message' => 'No puedes solicitar la insignia en este momento.',
            ], 400);
        }

        $store->requestInsignia();

        return response()->json([
            'message' => 'Solicitud de insignia enviada correctamente.',
            'insignia' => $store->fresh()->insignia,
        ]);
    }

    /**
     * PUT /api/stores/{id}/insignia/approve
     * El admin aprueba o rechaza la insignia premium
     */
    public function manageInsignia(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'action' => ['required', 'in:approve,reject'],
        ]);

        $store = Store::with('insignia')->findOrFail($id);
        $action = $request->input('action');
        $adminId = $request->user()->id;

        if ($action === 'approve') {
            $store->grantPremiumInsignia($adminId);

            return response()->json([
                'message' => 'Insignia premium otorgada correctamente.',
                'insignia' => $store->fresh()->insignia,
            ]);
        } else {
            $store->rejectInsigniaRequest();

            return response()->json([
                'message' => 'Solicitud de insignia rechazada.',
            ]);
        }
    }
}
