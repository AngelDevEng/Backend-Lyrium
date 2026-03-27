<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class CategoryController extends Controller
{
    /**
     * GET /api/categories
     * GET /api/categories?type=product|service
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::withCount('products');

        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->boolean('hide_empty')) {
            $query->has('products');
        }

        if ($request->boolean('tree')) {
            $query->whereNull('parent_id')->with('children');
        }

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        if (! $request->query('type') && ! $request->query('tree') && ! $request->query('search')) {
            $query->where(function ($q) {
                $q->where('type', 'product')->orWhereNull('type');
            });
        }

        $perPage = min((int) $request->query('per_page', 15), 100);
        $categories = $query->orderBy('sort_order')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => CategoryResource::collection($categories),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
                'total_pages' => $categories->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/categories/{id}
     */
    public function show(int $id): JsonResponse
    {
        $category = Category::withCount('products')
            ->with('children')
            ->findOrFail($id);

        return response()->json(new CategoryResource($category));
    }

    /**
     * POST /api/categories
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent' => 'nullable|integer|exists:categories,id',
            'image' => 'nullable|string',
        ]);

        $category = Category::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'parent_id' => $data['parent'] ?? null,
            'image' => isset($data['image']) ? $data['image'] : null,
        ]);

        return response()->json(new CategoryResource($category->loadCount('products')), 201);
    }

    /**
     * PUT /api/categories/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'parent' => 'nullable|integer|exists:categories,id',
            'image' => 'nullable|string',
        ]);

        $updateData = [];
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
            $updateData['slug'] = Str::slug($data['name']);
        }
        if (array_key_exists('description', $data)) {
            $updateData['description'] = $data['description'];
        }
        if (array_key_exists('parent', $data)) {
            $updateData['parent_id'] = $data['parent'];
        }
        if (array_key_exists('image', $data)) {
            $updateData['image'] = $data['image'];
        }

        $category->update($updateData);

        return response()->json(new CategoryResource($category->fresh()->loadCount('products')));
    }

    /**
     * DELETE /api/categories/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $category->products()->detach();
        $category->delete();

        return response()->json(new CategoryResource($category));
    }
}
