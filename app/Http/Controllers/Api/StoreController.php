<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StoreResource;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class StoreController extends Controller
{
    /**
     * GET /api/stores
     */
    public function index(Request $request): JsonResponse
    {
        $query = Store::with('owner');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('trade_name', 'like', "%{$search}%")
                  ->orWhere('ruc', 'like', "%{$search}%")
                  ->orWhere('corporate_email', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
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
        $store = Store::with(['owner', 'subscription.plan'])->findOrFail($id);
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
            'phone' => 'nullable|string|max:20',
        ]);

        $data['owner_id'] = $request->user()->id;
        $data['slug'] = Str::slug($data['trade_name']);

        $store = Store::create($data);

        return response()->json(new StoreResource($store), 201);
    }

    /**
     * PUT /api/stores/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $store = Store::findOrFail($id);

        $data = $request->validate([
            'trade_name' => 'sometimes|string|max:255',
            'corporate_email' => 'sometimes|email',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'logo' => 'nullable|string',
            'banner' => 'nullable|string',
        ]);

        $store->update($data);

        return response()->json(new StoreResource($store->fresh()));
    }

    /**
     * PUT /api/stores/{id}/status
     * Admin: aprobar, rechazar o banear vendedores
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $store = Store::findOrFail($id);

        $data = $request->validate([
            'status' => 'required|string|in:approved,rejected,banned',
            'reason' => 'nullable|string',
        ]);

        $updateData = ['status' => $data['status']];

        if ($data['status'] === 'approved') {
            $updateData['approved_at'] = now();
        }

        if ($data['status'] === 'banned') {
            $updateData['banned_at'] = now();
        }

        $store->update($updateData);

        return response()->json(new StoreResource($store->fresh()->load('owner')));
    }
}
