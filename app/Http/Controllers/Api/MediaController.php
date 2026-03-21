<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\StoreMediaRequest;
use App\Http\Resources\MediaResource;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class MediaController extends Controller
{
    /**
     * Upload media to a product.
     * POST /api/products/{productId}/media
     */
    public function uploadProductMedia(StoreMediaRequest $request, int $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        Gate::authorize('update', $product);

        $file = $request->file('file');

        $media = $product->addMedia($file)
            ->toMediaCollection('images');

        return $this->created(new MediaResource($media));
    }

    /**
     * Get product media.
     * GET /api/products/{productId}/media
     */
    public function getProductMedia(int $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        $media = $product->getMedia('images');

        return $this->success(MediaResource::collection($media));
    }

    /**
     * Delete product media.
     * DELETE /api/products/{productId}/media/{mediaId}
     */
    public function deleteProductMedia(int $productId, int $mediaId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        Gate::authorize('update', $product);

        $media = $product->media()->find($mediaId);

        if (! $media) {
            return $this->notFound('Media no encontrado.');
        }

        $media->delete();

        return $this->success();
    }

    /**
     * Reorder product media.
     * PUT /api/products/{productId}/media/reorder
     */
    public function reorderProductMedia(Request $request, int $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        Gate::authorize('update', $product);

        $order = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['required', 'integer'],
        ])['order'];

        foreach ($order as $index => $mediaId) {
            $product->media()->where('id', $mediaId)->update(['order_column' => $index]);
        }

        return $this->success();
    }

    /**
     * Upload store logo.
     * POST /api/stores/{storeId}/media/logo
     */
    public function uploadStoreLogo(StoreMediaRequest $request, int $storeId): JsonResponse
    {
        $store = Store::findOrFail($storeId);

        Gate::authorize('update', $store);

        $file = $request->file('file');

        $store->addMedia($file)
            ->preservingOriginal()
            ->toMediaCollection('logo');

        return $this->created(['logo' => $store->getFirstMediaUrl('logo')]);
    }

    /**
     * Upload store banner.
     * POST /api/stores/{storeId}/media/banner
     */
    public function uploadStoreBanner(StoreMediaRequest $request, int $storeId): JsonResponse
    {
        $store = Store::findOrFail($storeId);

        Gate::authorize('update', $store);

        $file = $request->file('file');

        $store->addMedia($file)
            ->preservingOriginal()
            ->toMediaCollection('banner');

        return $this->created(['banner' => $store->getFirstMediaUrl('banner')]);
    }

    /**
     * Delete store media.
     * DELETE /api/stores/{storeId}/media/{mediaId}
     */
    public function deleteStoreMedia(int $storeId, int $mediaId): JsonResponse
    {
        $store = Store::findOrFail($storeId);

        Gate::authorize('update', $store);

        $media = $store->media()->find($mediaId);

        if (! $media) {
            return $this->notFound('Media no encontrado.');
        }

        $media->delete();

        return $this->success();
    }
}
