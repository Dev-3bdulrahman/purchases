<?php

namespace Dev3bdulrahman\Purchases\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dev3bdulrahman\Purchases\Http\Resources\PurchaseRequestResource;
use Dev3bdulrahman\Purchases\Services\PurchaseRequestService;
use Dev3bdulrahman\Purchases\Models\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PurchaseRequestApiController extends Controller
{
    protected PurchaseRequestService $service;

    public function __construct(PurchaseRequestService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int)$request->get('per_page', 10);
        $requests = $this->service->listRequests($request->all(), $perPage);

        return response()->json([
            'success' => true,
            'message' => __('Purchase requests retrieved successfully'),
            'data' => PurchaseRequestResource::collection($requests->items()),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ],
            'errors' => []
        ]);
    }

    public function store(Request $request): JsonResponse
    {
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
        $purchaseRequest = $this->service->createRequest($validated, $items);
        $purchaseRequest->load('items');

        return response()->json([
            'success' => true,
            'message' => __('Purchase request created successfully'),
            'data' => new PurchaseRequestResource($purchaseRequest),
            'errors' => []
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $purchaseRequest = PurchaseRequest::with('items')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => __('Purchase request retrieved successfully'),
            'data' => new PurchaseRequestResource($purchaseRequest),
            'errors' => []
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $purchaseRequest = PurchaseRequest::findOrFail($id);

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

        $this->service->updateRequest($purchaseRequest, $validated, $items);
        $purchaseRequest->load('items');

        return response()->json([
            'success' => true,
            'message' => __('Purchase request updated successfully'),
            'data' => new PurchaseRequestResource($purchaseRequest),
            'errors' => []
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $purchaseRequest = PurchaseRequest::findOrFail($id);
        $this->service->deleteRequest($purchaseRequest);

        return response()->json([
            'success' => true,
            'message' => __('Purchase request deleted successfully'),
            'data' => null,
            'errors' => []
        ]);
    }

    public function convertToOrder($id, Request $request): JsonResponse
    {
        $purchaseRequest = PurchaseRequest::findOrFail($id);

        $validated = $request->validate([
            'order_number' => 'required|string|max:255',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'supplier_id' => 'required|exists:purchases_suppliers,id',
            'notes' => 'nullable|string',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $order = $this->service->convertToOrder($purchaseRequest, $validated);

        return response()->json([
            'success' => true,
            'message' => __('Purchase request converted to Purchase Order successfully'),
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
            'errors' => []
        ]);
    }
}
