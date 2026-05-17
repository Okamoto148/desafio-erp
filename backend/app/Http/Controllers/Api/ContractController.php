<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Services\ContractService;
use App\Http\Requests\StoreContractRequest;
use App\Http\Requests\UpdateContractRequest;
use App\Http\Requests\AddContractItemRequest;
use Illuminate\Http\JsonResponse;

class ContractController extends Controller
{
    protected $contractService;

    public function __construct(ContractService $contractService)
    {
        $this->contractService = $contractService;
    }

    public function index(): JsonResponse
    {
        return response()->json($this->contractService->getAllContracts());
    }

    public function store(StoreContractRequest $request): JsonResponse
    {
        try {
            $contract = $this->contractService->createContract($request->validated());
            return response()->json(['message' => 'Contrato criado com sucesso!', 'data' => $contract], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(Contract $contract): JsonResponse
    {
        $contract->load(['customer', 'items.service']);
        
        return response()->json([
            'contract' => $contract
        ]);
    }

    public function update(UpdateContractRequest $request, Contract $contract): JsonResponse
    {
        try {
            $updatedContract = $this->contractService->updateContract($contract, $request->validated());
            return response()->json($updatedContract);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function addItem(AddContractItemRequest $request, Contract $contract): JsonResponse
    {
        try {
            $item = $this->contractService->addItem($contract, $request->validated());
            return response()->json(['message' => 'Serviço vinculado com sucesso!', 'item' => $item]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }


    public function removeItem(Contract $contract, $serviceId): JsonResponse
    {
        try {
            $this->contractService->removeItem($contract, $serviceId);
            return response()->json(['message' => 'Serviço removido do contrato com sucesso.']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    
    public function destroy(Contract $contract): JsonResponse
    {
        try {
            $contract->items()->delete();
            $contract->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}