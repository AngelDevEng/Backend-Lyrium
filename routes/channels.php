<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

// Registrar rutas de broadcasting con guard Sanctum (tokens de API)
Broadcast::routes(['middleware' => ['auth:sanctum']]);

// Canal por defecto de Laravel (notificaciones del modelo User)
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal privado por usuario (notificaciones, tickets)
Broadcast::channel('user.{userId}', function (User $user, int $userId) {
    return $user->id === $userId;
});

// Canal privado por ticket (mensajes en tiempo real — seller + admin)
Broadcast::channel('ticket.{ticketId}', function (User $user, int $ticketId) {
    $ticket = \App\Models\Ticket::find($ticketId);
    if (! $ticket) {
        return false;
    }
    return $user->id === $ticket->user_id
        || $user->hasRole('administrator');
});

// Canal privado por tienda (órdenes, bookings, estado de plan, productos)
Broadcast::channel('store.{storeId}', function (User $user, int $storeId) {
    return $user->stores()->where('stores.id', $storeId)->exists()
        || $user->hasRole('administrator');
});
