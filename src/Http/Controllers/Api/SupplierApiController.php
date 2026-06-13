<?php

namespace Dev3bdulrahman\Purchases\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dev3bdulrahman\Purchases\Http\Resources\SupplierResource;
use Dev3bdulrahman\Purchases\Services\SupplierService;
use Dev3bdulrahman\Purchases\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SupplierApiController extends Controller
{
    protected SupplierService $service;

    public function __construct(SupplierService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int)$request->get('per_page', 10);
        $suppliers = $this->service->listSuppliers($request->all(), $perPage);

        return response()->json([
            'success' => true,
            'message' => __('Suppliers retrieved successfully'),
            'data' => SupplierResource::collection($suppliers->items()),
            'meta' => [
                'current_page' => $suppliers->currentPage(),
                'last_page' => $suppliers->lastPage(),
                'per_page' => $suppliers->perPage(),
                'total' => $suppliers->total(),
            ],
            'errors' => []
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $validated['company_id'] = session('active_company_id') ?: auth()->user()->company_id;
        $supplier = $this->service->createSupplier($validated);

        return response()->json([
            'success' => true,
            'message' => __('Supplier created successfully'),
            'data' => new SupplierResource($supplier),
            'errors' => []
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $supplier = Supplier::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => __('Supplier retrieved successfully'),
            'data' => new SupplierResource($supplier),
            'errors' => []
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $supplier = Supplier::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $this->service->updateSupplier($supplier, $validated);

        return response()->json([
            'success' => true,
            'message' => __('Supplier updated successfully'),
            'data' => new SupplierResource($supplier),
            'errors' => []
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $supplier = Supplier::findOrFail($id);
        $this->service->deleteSupplier($supplier);

        return response()->json([
            'success' => true,
            'message' => __('Supplier deleted successfully'),
            'data' => null,
            'errors' => []
        ]);
    }
}
