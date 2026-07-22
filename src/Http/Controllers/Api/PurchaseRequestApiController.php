<?php

namespace Dev3bdulrahman\Purchases\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\HasApiResponse;
use Dev3bdulrahman\Purchases\Http\Resources\PurchaseRequestResource;
use Dev3bdulrahman\Purchases\Models\PurchaseOrder;
use Dev3bdulrahman\Purchases\Services\PurchaseRequestService;
use Dev3bdulrahman\Purchases\Models\PurchaseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseRequestApiController extends Controller
{
    use HasApiResponse;

    /**
     * List all purchase requests.
     */
    public function index(Request $request, PurchaseRequestService $service): JsonResponse
    {
        $this->authorize('viewAny', PurchaseRequest::class);

        $perPage = (int) $request->get('per_page', 10);
        $requests = $service->listRequests($request->all(), $perPage);

        return $this->success(
            PurchaseRequestResource::collection($requests->items()),
            __('Purchase requests retrieved successfully'),
            200,
            [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ]
        );
    }

    /**
     * Store a new purchase request.
     */
    public function store(Request $request, PurchaseRequestService $service): JsonResponse
    {
        $this->authorize('create', PurchaseRequest::class);

        $validated = $request->validate([
            'request_number' => 'required|string|max:255',
            'request_date' => 'required|date',
            'status' => 'nullable|string|in:draft,pending,approved,rejected',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'nullable|numeric|min:0',
        ]);

        $items = $validated['items'];
        unset($validated['items']);

        $validated['company_id'] = session('active_company_id') ?: auth()->user()->company_id;
        $purchaseRequest = $service->createRequest($validated, $items);
        $purchaseRequest->load('items');

        return $this->success(
            new PurchaseRequestResource($purchaseRequest),
            __('Purchase request created successfully'),
            201
        );
    }

    /**
     * Show a single purchase request.
     */
    public function show(PurchaseRequest $purchaseRequest): JsonResponse
    {
        $this->authorize('view', $purchaseRequest);

        $purchaseRequest->load('items');

        return $this->success(
            new PurchaseRequestResource($purchaseRequest),
            __('Purchase request retrieved successfully')
        );
    }

    /**
     * Update an existing purchase request.
     */
    public function update(Request $request, PurchaseRequest $purchaseRequest, PurchaseRequestService $service): JsonResponse
    {
        $this->authorize('update', $purchaseRequest);

        $validated = $request->validate([
            'request_number' => 'sometimes|required|string|max:255',
            'request_date' => 'sometimes|required|date',
            'status' => 'nullable|string|in:draft,pending,approved,rejected',
            'notes' => 'nullable|string',
            'items' => 'sometimes|required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'nullable|numeric|min:0',
        ]);

        $items = $validated['items'] ?? [];
        unset($validated['items']);

        $service->updateRequest($purchaseRequest, $validated, $items);
        $purchaseRequest->load('items');

        return $this->success(
            new PurchaseRequestResource($purchaseRequest),
            __('Purchase request updated successfully')
        );
    }

    /**
     * Delete a purchase request.
     */
    public function destroy(PurchaseRequest $purchaseRequest, PurchaseRequestService $service): JsonResponse
    {
        $this->authorize('delete', $purchaseRequest);

        $service->deleteRequest($purchaseRequest);

        return $this->success(
            null,
            __('Purchase request deleted successfully')
        );
    }

    /**
     * Convert Purchase Request to Order.
     */
    public function convertToOrder(PurchaseRequest $purchaseRequest, Request $request, PurchaseRequestService $service): JsonResponse
    {
        $this->authorize('create', PurchaseOrder::class);

        $validated = $request->validate([
            'order_number' => 'required|string|max:255',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'supplier_id' => 'required|exists:purchases_suppliers,id',
            'notes' => 'nullable|string',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $order = $service->convertToOrder($purchaseRequest, $validated);

        return $this->success(
            [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
            __('Purchase request converted to Purchase Order successfully')
        );
    }
}
