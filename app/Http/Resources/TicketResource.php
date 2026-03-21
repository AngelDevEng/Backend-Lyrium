<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $store = $this->store;
        $admin = $this->assignedAdmin;

        return [
            'id' => $this->id,
            'id_display' => str_replace('TKT-', '', $this->ticket_number),
            'titulo' => $this->subject,
            'descripcion' => $this->description,
            'status' => $this->mapStatus($this->status),
            'type' => $this->category,
            'critical' => $this->is_critical,
            'tiempo' => $this->created_at->diffForHumans(),
            'mensajes_count' => $this->messages_count ?? $this->messages->count(),
            'survey_required' => $this->status === 'closed' && $this->satisfaction_rating === null,
            'satisfaction_rating' => $this->satisfaction_rating,
            'satisfaction_comment' => $this->satisfaction_comment,
            'escalated' => $this->is_escalated,
            'escalated_to' => $this->escalated_to,
            'tienda' => [
                'razon_social' => $store?->trade_name ?? '',
                'nombre_comercial' => $store?->trade_name ?? '',
            ],
            'contacto_adm' => [
                'nombre' => $admin?->name ?? 'Sin asignar',
                'apellido' => '',
                'numeros' => $admin?->phone ?? '',
                'correo' => $admin?->email ?? '',
            ],
            'mensajes' => TicketMessageResource::collection($this->whenLoaded('messages')),
        ];
    }

    private function mapStatus(string $status): string
    {
        return match ($status) {
            'open' => 'abierto',
            'in_progress' => 'proceso',
            'resolved' => 'resuelto',
            'closed' => 'cerrado',
            'reopened' => 'abierto',
            default => $status,
        };
    }
}
