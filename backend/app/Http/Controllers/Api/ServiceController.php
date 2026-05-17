<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\ServiceService;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    protected $serviceService;

    public function __construct(ServiceService $serviceService)
    {
        $this->serviceService = $serviceService;
    }

    public function index(): JsonResponse
    {
        return response()->json(Service::paginate(10));
    }

    public function store(StoreServiceRequest $request): JsonResponse
    {
        try {
            $service = $this->serviceService->createService($request->validated());
            return response()->json($service, 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(Service $service): JsonResponse
    {
        return response()->json($service);
    }

    public function update(UpdateServiceRequest $request, Service $service): JsonResponse
    {
        try {
            $updatedService = $this->serviceService->updateService($service, $request->validated());
            return response()->json($updatedService);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(Service $service): JsonResponse
    {
        $isLinked = \DB::table('contract_items')->where('service_id', $service->id)->exists();

        if ($isLinked) {
            return response()->json([
                'message' => 'Não é possível excluir este serviço pois ele já está vinculado a contratos ativos.'
            ], 400);
        }

        $service->delete();
        return response()->json(null, 204);
    }
}