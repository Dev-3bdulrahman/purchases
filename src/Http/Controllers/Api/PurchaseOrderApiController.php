<?php

namespace Dev3bdulrahman\Purchases\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dev3bdulrahman\Purchases\Http\Resources\PurchaseOrderResource;
use Dev3bdulrahman\Purchases\Services\PurchaseOrderService;
use Dev3bdulrahman\Purchases\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PurchaseOrderApiController extends Controller
{
    protected PurchaseOrderService $service;

    public function __construct(PurchaseOrderService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int)$request->get('per_page', 10);
        $orders = $this->service->listOrders($request->all(), $perPage);

        return response()->json([
            'success' => true,
            'message' => __('Purchase orders retrieved successfully'),
            'data' => PurchaseOrderResource::collection($orders->items()),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
            'errors' => []
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:purchases_suppliers,id',
            'purchase_request_id' => 'nullable|exists:purchases_requests,id',
            'order_number' => 'required|string|max:255',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'status' => 'nullable|string|in:draft,pending,confirmed,received,cancelled',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
        ]);

        $items = $validated['items'];
        unset($validated['items']);

        $validated['company_id'] = session('active_company_id') ?: auth()->user()->company_id;
        $order = $this->service->createOrder($validated, $items);
        $order->load('items');

        return response()->json([
            'success' => true,
            'message' => __('Purchase order created successfully'),
            'data' => new PurchaseOrderResource($order),
            'errors' => []
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $order = PurchaseOrder::with('items')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => __('Purchase order retrieved successfully'),
            'data' => new PurchaseOrderResource($order),
            'errors' => []
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $order = PurchaseOrder::findOrFail($id);

        $validated = $request->validate([
            'supplier_id' => 'sometimes|required|exists:purchases_suppliers,id',
            'purchase_request_id' => 'nullable|exists:purchases_requests,id',
            'order_number' => 'sometimes|required|string|max:255',
            'order_date' => 'sometimes|required|date',
            'delivery_date' => 'nullable|date',
            'status' => 'nullable|string|in:draft,pending,confirmed,received,cancelled',
            'notes' => 'nullable|string',
            'items' => 'sometimes|required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
        ]);

        $items = $validated['items'] ?? [];
        unset($validated['items']);

        $this->service->updateOrder($order, $validated, $items);
        $order->load('items');

        return response()->json([
            'success' => true,
            'message' => __('Purchase order updated successfully'),
            'data' => new PurchaseOrderResource($order),
            'errors' => []
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $order = PurchaseOrder::findOrFail($id);
        $this->service->deleteOrder($order);

        return response()->json([
            'success' => true,
            'message' => __('Purchase order deleted successfully'),
            'data' => null,
            'errors' => []
        ]);
    }

    public function convertToInvoice($id, Request $request): JsonResponse
    {
        $order = PurchaseOrder::findOrFail($id);

        $validated = $request->validate([
            'invoice_number' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $invoice = $this->service->convertToInvoice($order, $validated);

        return response()->json([
            'success' => true,
            'message' => __('Purchase order converted to Invoice successfully'),
            'data' => [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
            ],
            'errors' => []
        ]);
    }
}
