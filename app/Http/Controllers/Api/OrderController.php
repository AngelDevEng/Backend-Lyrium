<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->is_admin) {
            $orders = Order::with(['items.product.store', 'user'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        } elseif ($user->is_seller) {
            $storeIds = $user->stores()->pluck('stores.id');
            $orders = Order::whereHas('items', fn ($q) => $q->whereIn('store_id', $storeIds))
                ->with(['items.product.store', 'user'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        } else {
            $orders = Order::where('user_id', $user->id)
                ->with(['items.product.store', 'user'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        }

        return $this->success([
            'data' => OrderResource::collection($orders),
            'pagination' => [
                'page' => $orders->currentPage(),
                'perPage' => $orders->perPage(),
                'total' => $orders->total(),
                'totalPages' => $orders->lastPage(),
                'hasMore' => $orders->hasMorePages(),
            ],
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $order = Order::with(['items.product.store', 'user'])->findOrFail($id);

        if (! $user->is_admin && ! $user->is_seller && $order->user_id !== $user->id) {
            return $this->forbidden('No tienes acceso a esta orden.');
        }

        if ($user->is_seller) {
            $storeIds = $user->stores()->pluck('stores.id');
            $hasAccess = $order->items->every(fn ($item) => $storeIds->contains($item->store_id));
            if (! $hasAccess) {
                return $this->forbidden('No tienes acceso a esta orden.');
            }
        }

        return $this->success(new OrderResource($order));
    }

    public function store(CreateOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $cart = Cart::where('user_id', $user->id)->with('items.product')->first();

        if (! $cart || $cart->items->isEmpty()) {
            return $this->error('El carrito está vacío.', 400);
        }

        $order = DB::transaction(function () use ($data, $user, $cart) {
            $subtotal = 0;
            $orderItems = [];

            foreach ($cart->items as $item) {
                $product = $item->product;

                if ($product->status !== 'approved') {
                    throw new \Exception("El producto '{$product->name}' no está disponible.");
                }

                if ($product->stock < $item->quantity) {
                    throw new \Exception("Stock insuficiente para '{$product->name}'.");
                }

                $lineTotal = $item->quantity * $item->unit_price;
                $subtotal += $lineTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'store_id' => $product->store_id,
                    'product_name' => $product->name,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'line_total' => $lineTotal,
                    'status' => 'pending',
                ];

                $product->decrement('stock', $item->quantity);
            }

            $shippingCost = $data['shipping_cost'] ?? 0;
            $taxRate = 0.16;
            $taxAmount = round($subtotal * $taxRate, 2);
            $total = $subtotal + $shippingCost + $taxAmount;

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user->id,
                'status' => 'pending',
                'payment_method' => $data['payment_method'] ?? null,
                'payment_status' => 'pending',
                'shipping_name' => $data['shipping_name'] ?? null,
                'shipping_email' => $data['shipping_email'] ?? $user->email,
                'shipping_phone' => $data['shipping_phone'] ?? null,
                'shipping_address' => $data['shipping_address'] ?? null,
                'shipping_city' => $data['shipping_city'] ?? null,
                'shipping_postal_code' => $data['shipping_postal_code'] ?? null,
                'shipping_notes' => $data['shipping_notes'] ?? null,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($orderItems as $item) {
                $order->items()->create($item);
            }

            $cart->items()->delete();

            return $order;
        });

        $order->load(['items.product.store', 'user']);

        return $this->created(new OrderResource($order));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();
        $order = Order::findOrFail($id);

        if ($user->is_seller) {
            $storeIds = $user->stores()->pluck('stores.id');
            $hasAccess = $order->items()->whereIn('store_id', $storeIds)->exists();
            if (! $hasAccess) {
                return $this->forbidden('No tienes acceso a esta orden.');
            }

            $sellerStatuses = ['processing', 'shipped', 'delivered', 'cancelled'];
            if (! in_array($data['status'], $sellerStatuses)) {
                return $this->forbidden('No tienes permiso para cambiar a este estado.');
            }

            $order->items()->whereIn('store_id', $storeIds)->update(['status' => $data['status']]);

            $allItems = $order->items;
            $allStatuses = $allItems->pluck('status')->unique();

            if ($allStatuses->count() === 1) {
                $order->update(['status' => $data['status']]);
            }
        } else {
            $validStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
            if (! in_array($data['status'], $validStatuses)) {
                return $this->error('Estado no válido.', 400);
            }

            $order->update(['status' => $data['status']]);
        }

        if (isset($data['payment_status'])) {
            $order->update(['payment_status' => $data['payment_status']]);
        }

        $order->load(['items.product.store', 'user']);

        return $this->success(new OrderResource($order));
    }
}
