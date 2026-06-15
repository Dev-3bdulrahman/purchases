<?php

namespace Dev3bdulrahman\Purchases\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\HasApiResponse;
use Dev3bdulrahman\Purchases\Http\Requests\Api\StoreSupplierApiRequest;
use Dev3bdulrahman\Purchases\Http\Requests\Api\UpdateSupplierApiRequest;
use Dev3bdulrahman\Purchases\Http\Resources\SupplierResource;
use Dev3bdulrahman\Purchases\Services\SupplierService;
use Dev3bdulrahman\Purchases\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierApiController extends Controller
{
    use HasApiResponse;

    /**
     * List all suppliers.
     */
    public function index(Request $request, SupplierService $service): JsonResponse
    {
        $this->authorize('viewAny', Supplier::class);

        $perPage = (int) $request->get('per_page', 10);
        $suppliers = $service->listSuppliers($request->all(), $perPage);

        return $this->success(
            SupplierResource::collection($suppliers->items()),
            __('Suppliers retrieved successfully'),
            200,
            [
                'current_page' => $suppliers->currentPage(),
                'last_page' => $suppliers->lastPage(),
                'per_page' => $suppliers->perPage(),
                'total' => $suppliers->total(),
            ]
        );
    }

    /**
     * Store a new supplier.
     */
    public function store(StoreSupplierApiRequest $request, SupplierService $service): JsonResponse
    {
        $this->authorize('create', Supplier::class);

        $validated = $request->validated();
        $validated['company_id'] = session('active_company_id') ?: auth()->user()->company_id;

        $supplier = $service->createSupplier($validated);

        return $this->success(
            new SupplierResource($supplier),
            __('Supplier created successfully'),
            201
        );
    }

    /**
     * Show a single supplier.
     */
    public function show(Supplier $supplier): JsonResponse
    {
        $this->authorize('view', $supplier);

        return $this->success(
            new SupplierResource($supplier),
            __('Supplier retrieved successfully')
        );
    }

    /**
     * Update an existing supplier.
     */
    public function update(UpdateSupplierApiRequest $request, Supplier $supplier, SupplierService $service): JsonResponse
    {
        $this->authorize('update', $supplier);

        $validated = $request->validated();
        $service->updateSupplier($supplier, $validated);

        return $this->success(
            new SupplierResource($supplier->fresh()),
            __('Supplier updated successfully')
        );
    }

    /**
     * Delete a supplier.
     */
    public function destroy(Supplier $supplier, SupplierService $service): JsonResponse
    {
        $this->authorize('delete', $supplier);

        $service->deleteSupplier($supplier);

        return $this->success(
            null,
            __('Supplier deleted successfully')
        );
    }
}
