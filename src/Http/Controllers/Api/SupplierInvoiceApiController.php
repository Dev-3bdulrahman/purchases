<?php

namespace Dev3bdulrahman\Purchases\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dev3bdulrahman\Purchases\Http\Resources\SupplierInvoiceResource;
use Dev3bdulrahman\Purchases\Services\SupplierInvoiceService;
use Dev3bdulrahman\Purchases\Models\SupplierInvoice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SupplierInvoiceApiController extends Controller
{
    protected SupplierInvoiceService $service;

    public function __construct(SupplierInvoiceService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int)$request->get('per_page', 10);
        $invoices = $this->service->listInvoices($request->all(), $perPage);

        return response()->json([
            'success' => true,
            'message' => __('Supplier invoices retrieved successfully'),
            'data' => SupplierInvoiceResource::collection($invoices->items()),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
            ],
            'errors' => []
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:purchases_suppliers,id',
            'purchase_order_id' => 'nullable|exists:purchases_orders,id',
            'invoice_number' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date',
            'status' => 'nullable|string|in:draft,unpaid,partially_paid,paid,overdue,cancelled',
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
        $invoice = $this->service->createInvoice($validated, $items);
        $invoice->load('items');

        return response()->json([
            'success' => true,
            'message' => __('Supplier invoice created successfully'),
            'data' => new SupplierInvoiceResource($invoice),
            'errors' => []
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $invoice = SupplierInvoice::with('items')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => __('Supplier invoice retrieved successfully'),
            'data' => new SupplierInvoiceResource($invoice),
            'errors' => []
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $invoice = SupplierInvoice::findOrFail($id);

        $validated = $request->validate([
            'supplier_id' => 'sometimes|required|exists:purchases_suppliers,id',
            'purchase_order_id' => 'nullable|exists:purchases_orders,id',
            'invoice_number' => 'sometimes|required|string|max:255',
            'invoice_date' => 'sometimes|required|date',
            'due_date' => 'sometimes|required|date',
            'status' => 'nullable|string|in:draft,unpaid,partially_paid,paid,overdue,cancelled',
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

        $this->service->updateInvoice($invoice, $validated, $items);
        $invoice->load('items');

        return response()->json([
            'success' => true,
            'message' => __('Supplier invoice updated successfully'),
            'data' => new SupplierInvoiceResource($invoice),
            'errors' => []
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $invoice = SupplierInvoice::findOrFail($id);
        $this->service->deleteInvoice($invoice);

        return response()->json([
            'success' => true,
            'message' => __('Supplier invoice deleted successfully'),
            'data' => null,
            'errors' => []
        ]);
    }
}
