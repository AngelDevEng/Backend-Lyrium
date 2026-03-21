<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'nicename' => $this->nicename ?? $this->username,
            'display_name' => $this->name,
            'role' => $this->frontend_role,
            'avatar' => $this->avatar,
        ];
    }
}
