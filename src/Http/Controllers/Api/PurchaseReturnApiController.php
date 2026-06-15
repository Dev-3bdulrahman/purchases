<?php

namespace Dev3bdulrahman\Purchases\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\HasApiResponse;
use Dev3bdulrahman\Purchases\Http\Requests\Api\StorePurchaseReturnApiRequest;
use Dev3bdulrahman\Purchases\Http\Requests\Api\UpdatePurchaseReturnApiRequest;
use Dev3bdulrahman\Purchases\Http\Resources\PurchaseReturnResource;
use Dev3bdulrahman\Purchases\Services\PurchaseReturnService;
use Dev3bdulrahman\Purchases\Models\PurchaseReturn;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseReturnApiController extends Controller
{
    use HasApiResponse;

    /**
     * List all purchase returns.
     */
    public function index(Request $request, PurchaseReturnService $service): JsonResponse
    {
        $this->authorize('viewAny', PurchaseReturn::class);

        $perPage = (int) $request->get('per_page', 10);
        $returns = $service->listReturns($request->all(), $perPage);

        return $this->success(
            PurchaseReturnResource::collection($returns->items()),
            __('Purchase returns retrieved successfully'),
            200,
            [
                'current_page' => $returns->currentPage(),
                'last_page' => $returns->lastPage(),
                'per_page' => $returns->perPage(),
                'total' => $returns->total(),
            ]
        );
    }

    /**
     * Store a new purchase return.
     */
    public function store(StorePurchaseReturnApiRequest $request, PurchaseReturnService $service): JsonResponse
    {
        $this->authorize('create', PurchaseReturn::class);

        $validated = $request->validated();
        $items = $validated['items'];
        unset($validated['items']);

        $validated['company_id'] = session('active_company_id') ?: auth()->user()->company_id;
        $return = $service->createReturn($validated, $items);
        $return->load('items');

        return $this->success(
            new PurchaseReturnResource($return),
            __('Purchase return created successfully'),
            201
        );
    }

    /**
     * Show a single purchase return.
     */
    public function show(PurchaseReturn $purchaseReturn): JsonResponse
    {
        $this->authorize('view', $purchaseReturn);

        $purchaseReturn->load('items');

        return $this->success(
            new PurchaseReturnResource($purchaseReturn),
            __('Purchase return retrieved successfully')
        );
    }

    /**
     * Update an existing purchase return.
     */
    public function update(UpdatePurchaseReturnApiRequest $request, PurchaseReturn $purchaseReturn, PurchaseReturnService $service): JsonResponse
    {
        $this->authorize('update', $purchaseReturn);

        $validated = $request->validated();
        $items = $validated['items'] ?? [];
        unset($validated['items']);

        $service->updateReturn($purchaseReturn, $validated, $items);
        $purchaseReturn->load('items');

        return $this->success(
            new PurchaseReturnResource($purchaseReturn),
            __('Purchase return updated successfully')
        );
    }

    /**
     * Delete a purchase return.
     */
    public function destroy(PurchaseReturn $purchaseReturn, PurchaseReturnService $service): JsonResponse
    {
        $this->authorize('delete', $purchaseReturn);

        $service->deleteReturn($purchaseReturn);

        return $this->success(
            null,
            __('Purchase return deleted successfully')
        );
    }
}
