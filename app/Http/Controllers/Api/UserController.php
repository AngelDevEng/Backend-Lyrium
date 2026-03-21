<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserController extends Controller
{
    /**
     * GET /api/users/me
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json(new UserResource($request->user()));
    }

    /**
     * GET /api/users/{id}
     */
    public function show(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        return response()->json(new UserResource($user));
    }

    /**
     * GET /api/users
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($role = $request->query('role')) {
            match ($role) {
                'administrator' => $query->where('is_admin', true),
                'seller' => $query->where('is_seller', true),
                'customer' => $query->where('is_admin', false)->where('is_seller', false),
                default => null,
            };
        }

        $users = $query->paginate($request->query('per_page', 50));

        return response()->json([
            'data' => UserResource::collection($users),
            'pagination' => [
                'page' => $users->currentPage(),
                'perPage' => $users->perPage(),
                'total' => $users->total(),
                'totalPages' => $users->lastPage(),
                'hasMore' => $users->hasMorePages(),
            ],
        ]);
    }

    /**
     * GET /api/users/role/{role}
     */
    public function byRole(Request $request, string $role): JsonResponse
    {
        $query = User::query();

        match ($role) {
            'administrator' => $query->where('is_admin', true),
            'seller' => $query->where('is_seller', true),
            'customer' => $query->where('is_admin', false)->where('is_seller', false),
            'logistics_operator' => $query->role('logistics_operator'),
            default => null,
        };

        $perPage = min((int) $request->query('per_page', 50), 100);
        $users = $query->paginate($perPage);

        return response()->json([
            'data' => UserResource::collection($users),
            'pagination' => [
                'page' => $users->currentPage(),
                'perPage' => $users->perPage(),
                'total' => $users->total(),
                'totalPages' => $users->lastPage(),
                'hasMore' => $users->hasMorePages(),
            ],
        ]);
    }

    /**
     * PUT /api/users/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'display_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'avatar' => 'sometimes|nullable|string',
        ]);

        if (isset($data['display_name'])) {
            $data['name'] = $data['display_name'];
            unset($data['display_name']);
        }

        $user->update($data);

        return response()->json(new UserResource($user->fresh()));
    }

    /**
     * DELETE /api/users/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['success' => true]);
    }
}
