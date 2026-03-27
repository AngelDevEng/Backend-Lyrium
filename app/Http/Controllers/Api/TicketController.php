<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendTicketMessageRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\SubmitSurveyRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketCreatedNotification;
use App\Notifications\TicketRepliedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tickets = Ticket::where('user_id', $request->user()->id)
            ->withCount('messages')
            ->with('store', 'assignedAdmin')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => TicketResource::collection($tickets),
            'meta' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'total' => $tickets->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $ticket = Ticket::where('user_id', $request->user()->id)
            ->with(['store', 'assignedAdmin', 'messages.user', 'messages.attachments'])
            ->withCount('messages')
            ->findOrFail($id);

        $ticket->messages()
            ->where('user_id', '!=', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'data' => new TicketResource($ticket),
        ]);
    }

    public function store(StoreTicketRequest $request): JsonResponse
    {
        $user = $request->user();
        $store = $user->store;

        if (! $store) {
            return response()->json([
                'success' => false,
                'message' => 'Debes tener una tienda registrada para crear tickets.',
            ], 403);
        }

        $priorityMap = [
            'baja' => 'low',
            'media' => 'medium',
            'alta' => 'high',
            'critica' => 'critical',
        ];

        $criticidad = $request->input('criticidad');

        $ticket = Ticket::create([
            'ticket_number' => Ticket::generateTicketNumber(),
            'user_id' => $user->id,
            'store_id' => $store->id,
            'subject' => $request->input('asunto'),
            'description' => $request->input('mensaje'),
            'category' => $request->input('tipo_ticket'),
            'priority' => $priorityMap[$criticidad] ?? 'medium',
            'is_critical' => in_array($criticidad, ['alta', 'critica']),
        ]);

        $ticket->messages()->create([
            'user_id' => $user->id,
            'content' => $request->input('mensaje'),
            'type' => 'normal',
        ]);

        $admins = User::where('is_admin', true)->get();
        foreach ($admins as $admin) {
            $admin->notify(new TicketCreatedNotification($ticket->load('user', 'store')));
        }

        $ticket->refresh();
        $ticket->load(['store', 'assignedAdmin', 'messages.user']);
        $ticket->loadCount('messages');

        return response()->json([
            'success' => true,
            'message' => 'Ticket creado exitosamente.',
            'data' => new TicketResource($ticket),
        ], 201);
    }

    public function sendMessage(SendTicketMessageRequest $request, int $id): JsonResponse
    {
        $ticket = Ticket::where('user_id', $request->user()->id)
            ->findOrFail($id);

        if ($ticket->status === 'closed') {
            return response()->json([
                'success' => false,
                'message' => 'No puedes enviar mensajes a un ticket cerrado.',
            ], 422);
        }

        $message = $ticket->messages()->create([
            'user_id' => $request->user()->id,
            'content' => $request->input('content'),
            'type' => 'normal',
        ]);

        if ($ticket->status === 'resolved') {
            $ticket->update(['status' => 'reopened']);
        }

        if ($ticket->assignedAdmin) {
            $ticket->assignedAdmin->notify(
                new TicketRepliedNotification($ticket, $message->load('user'))
            );
        }

        $message->load(['user', 'attachments']);

        return response()->json([
            'success' => true,
            'data' => new \App\Http\Resources\TicketMessageResource($message),
        ], 201);
    }

    public function close(Request $request, int $id): JsonResponse
    {
        $ticket = Ticket::where('user_id', $request->user()->id)
            ->findOrFail($id);

        if ($ticket->status === 'closed') {
            return response()->json([
                'success' => false,
                'message' => 'El ticket ya está cerrado.',
            ], 422);
        }

        $ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        $ticket->messages()->create([
            'user_id' => $request->user()->id,
            'content' => 'El vendedor cerró este ticket.',
            'type' => 'system',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket cerrado exitosamente.',
        ]);
    }

    public function submitSurvey(SubmitSurveyRequest $request, int $id): JsonResponse
    {
        $ticket = Ticket::where('user_id', $request->user()->id)
            ->where('status', 'closed')
            ->findOrFail($id);

        if ($ticket->satisfaction_rating !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Ya enviaste una encuesta para este ticket.',
            ], 422);
        }

        $ticket->update([
            'satisfaction_rating' => $request->input('rating'),
            'satisfaction_comment' => $request->input('comment'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gracias por tu feedback.',
        ]);
    }
}
