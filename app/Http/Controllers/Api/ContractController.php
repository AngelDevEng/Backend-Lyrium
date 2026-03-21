<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContractRequest;
use App\Http\Requests\UpdateContractRequest;
use App\Http\Resources\ContractResource;
use App\Models\Contract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

final class ContractController extends Controller
{
    /**
     * GET /api/contracts
     */
    public function index(Request $request): JsonResponse
    {
        $query = Contract::with(['auditTrails', 'store']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('company', 'like', "%{$search}%")
                  ->orWhere('ruc', 'like', "%{$search}%")
                  ->orWhere('contract_number', 'like', "%{$search}%")
                  ->orWhere('representative', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($modality = $request->query('modality')) {
            $query->where('modality', $modality);
        }

        if ($storeId = $request->query('store_id')) {
            $query->where('store_id', $storeId);
        }

        $perPage = min((int) $request->query('per_page', 15), 100);
        $contracts = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // KPIs
        $allContracts = Contract::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'ACTIVE' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'EXPIRED' THEN 1 ELSE 0 END) as expired
        ")->first();

        $response = ContractResource::collection($contracts)->response()->getData(true);
        $response['kpis'] = [
            'total' => (int) $allContracts->total,
            'active' => (int) $allContracts->active,
            'pending' => (int) $allContracts->pending,
            'expired' => (int) $allContracts->expired,
        ];

        return response()->json($response);
    }

    /**
     * GET /api/contracts/{id}
     */
    public function show(string $id): JsonResponse
    {
        $contract = Contract::with(['auditTrails', 'store'])->findOrFail($id);

        return response()->json(new ContractResource($contract));
    }

    /**
     * POST /api/contracts
     */
    public function store(StoreContractRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $year = now()->year;
        $lastContract = Contract::where('contract_number', 'like', "CTR-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastContract) {
            $parts = explode('-', $lastContract->contract_number);
            $nextNumber = ((int) end($parts)) + 1;
        }

        $contractNumber = sprintf('CTR-%d-%03d', $year, $nextNumber);

        $contract = Contract::create([
            'contract_number' => $contractNumber,
            'store_id' => $data['storeId'] ?? null,
            'company' => $data['company'],
            'ruc' => $data['ruc'] ?? null,
            'representative' => $data['rep'] ?? null,
            'type' => $data['type'],
            'modality' => $data['modality'],
            'status' => 'PENDING',
            'start_date' => $data['start'],
            'end_date' => $data['end'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        $contract->addAuditEntry(
            'Contrato Borrador Creado',
            $user->name ?? 'Admin'
        );

        $contract->load(['auditTrails', 'store']);

        return response()->json(new ContractResource($contract), 201);
    }

    /**
     * PUT /api/contracts/{id}
     */
    public function update(UpdateContractRequest $request, string $id): JsonResponse
    {
        $contract = Contract::findOrFail($id);
        $data = $request->validated();
        $user = $request->user();

        $updateData = [];
        if (isset($data['storeId'])) $updateData['store_id'] = $data['storeId'];
        if (isset($data['company'])) $updateData['company'] = $data['company'];
        if (array_key_exists('ruc', $data)) $updateData['ruc'] = $data['ruc'];
        if (array_key_exists('rep', $data)) $updateData['representative'] = $data['rep'];
        if (isset($data['type'])) $updateData['type'] = $data['type'];
        if (isset($data['modality'])) $updateData['modality'] = $data['modality'];
        if (isset($data['start'])) $updateData['start_date'] = $data['start'];
        if (array_key_exists('end', $data)) $updateData['end_date'] = $data['end'];
        if (array_key_exists('notes', $data)) $updateData['notes'] = $data['notes'];

        $contract->update($updateData);

        $contract->addAuditEntry(
            'Contrato Actualizado',
            $user->name ?? 'Admin'
        );

        $contract->load(['auditTrails', 'store']);

        return response()->json(new ContractResource($contract));
    }

    /**
     * PUT /api/contracts/{id}/status
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $contract = Contract::findOrFail($id);
        $user = $request->user();

        $data = $request->validate([
            'status' => 'required|string|in:ACTIVE,PENDING,EXPIRED',
        ]);

        $oldStatus = $contract->status;
        $contract->update(['status' => $data['status']]);

        $actionMap = [
            'ACTIVE' => 'Firma Digital Validada — Contrato Activado',
            'EXPIRED' => 'Contrato Expirado/Invalidado',
            'PENDING' => 'Contrato Devuelto a Pendiente',
        ];

        $contract->addAuditEntry(
            $actionMap[$data['status']] ?? "Estado cambiado de {$oldStatus} a {$data['status']}",
            $user->name ?? 'Admin'
        );

        $contract->load(['auditTrails', 'store']);

        return response()->json(new ContractResource($contract));
    }

    /**
     * POST /api/contracts/{id}/upload
     */
    public function upload(Request $request, string $id): JsonResponse
    {
        $contract = Contract::findOrFail($id);
        $user = $request->user();

        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx|max:10240', // max 10MB
        ]);

        $file = $request->file('file');
        $companySlug = str_replace(' ', '_', $contract->company);
        $year = $contract->start_date->year;
        $path = $file->storeAs(
            "contracts/{$companySlug}/{$year}",
            $file->getClientOriginalName(),
            'private'
        );

        $contract->update(['file_path' => $path]);

        $contract->addAuditEntry(
            "Documento Cargado: {$file->getClientOriginalName()}",
            $user->name ?? 'Admin'
        );

        $contract->load(['auditTrails', 'store']);

        return response()->json(new ContractResource($contract));
    }

    /**
     * GET /api/contracts/{id}/download
     */
    public function download(string $id)
    {
        $contract = Contract::findOrFail($id);

        if (!$contract->file_path || !Storage::disk('private')->exists($contract->file_path)) {
            return response()->json(['error' => 'No hay documento cargado.'], 404);
        }

        return Storage::disk('private')->download($contract->file_path);
    }

    /**
     * DELETE /api/contracts/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $contract = Contract::findOrFail($id);
        $contract->delete();

        return response()->json(['success' => true]);
    }
}