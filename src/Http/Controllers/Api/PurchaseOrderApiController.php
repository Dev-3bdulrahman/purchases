<?php

namespace Dev3bdulrahman\Purchases\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\HasApiResponse;
use Dev3bdulrahman\Purchases\Events\PurchaseOrderApproved;
use Dev3bdulrahman\Purchases\Http\Requests\Api\StorePurchaseOrderApiRequest;
use Dev3bdulrahman\Purchases\Http\Requests\Api\UpdatePurchaseOrderApiRequest;
use Dev3bdulrahman\Purchases\Http\Resources\PurchaseOrderResource;
use Dev3bdulrahman\Purchases\Services\PurchaseOrderService;
use Dev3bdulrahman\Purchases\Models\PurchaseOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseOrderApiController extends Controller
{
    use HasApiResponse;

    /**
     * List all purchase orders.
     */
    public function index(Request $request, PurchaseOrderService $service): JsonResponse
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        $perPage = (int) $request->get('per_page', 10);
        $orders = $service->listOrders($request->all(), $perPage);

        return $this->success(
            PurchaseOrderResource::collection($orders->items()),
            __('Purchase orders retrieved successfully'),
            200,
            [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ]
        );
    }

    /**
     * Store a new purchase order.
     */
    public function store(StorePurchaseOrderApiRequest $request, PurchaseOrderService $service): JsonResponse
    {
        $this->authorize('create', PurchaseOrder::class);

        $validated = $request->validated();
        $items = $validated['items'];
        unset($validated['items']);

        $validated['company_id'] = session('active_company_id') ?: auth()->user()->company_id;
        $order = $service->createOrder($validated, $items);
        $order->load('items');

        return $this->success(
            new PurchaseOrderResource($order),
            __('Purchase order created successfully'),
            201
        );
    }

    /**
     * Show a single purchase order.
     */
    public function show(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->authorize('view', $purchaseOrder);

        $purchaseOrder->load('items');

        return $this->success(
            new PurchaseOrderResource($purchaseOrder),
            __('Purchase order retrieved successfully')
        );
    }

    /**
     * Update an existing purchase order.
     */
    public function update(UpdatePurchaseOrderApiRequest $request, PurchaseOrder $purchaseOrder, PurchaseOrderService $service): JsonResponse
    {
        $this->authorize('update', $purchaseOrder);

        $validated = $request->validated();
        $items = $validated['items'] ?? [];
        unset($validated['items']);

        $service->updateOrder($purchaseOrder, $validated, $items);
        $purchaseOrder->load('items');

        return $this->success(
            new PurchaseOrderResource($purchaseOrder),
            __('Purchase order updated successfully')
        );
    }

    /**
     * Delete a purchase order.
     */
    public function destroy(PurchaseOrder $purchaseOrder, PurchaseOrderService $service): JsonResponse
    {
        $this->authorize('delete', $purchaseOrder);

        $service->deleteOrder($purchaseOrder);

        return $this->success(
            null,
            __('Purchase order deleted successfully')
        );
    }

    /**
     * Convert Purchase Order to Invoice.
     */
    public function convertToInvoice(PurchaseOrder $purchaseOrder, Request $request, PurchaseOrderService $service): JsonResponse
    {
        $this->authorize('create', \Dev3bdulrahman\Purchases\Models\SupplierInvoice::class);

        $validated = $request->validate([
            'invoice_number' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $invoice = $service->convertToInvoice($purchaseOrder, $validated);

        return $this->success(
            [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
            ],
            __('Purchase order converted to Invoice successfully')
        );
    }
}
