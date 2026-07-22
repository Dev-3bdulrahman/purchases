<?php

namespace Dev3bdulrahman\Purchases\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\HasApiResponse;
use Dev3bdulrahman\Purchases\Events\SupplierInvoiceReceived;
use Dev3bdulrahman\Purchases\Http\Requests\Api\StoreSupplierInvoiceApiRequest;
use Dev3bdulrahman\Purchases\Http\Requests\Api\UpdateSupplierInvoiceApiRequest;
use Dev3bdulrahman\Purchases\Http\Resources\SupplierInvoiceResource;
use Dev3bdulrahman\Purchases\Services\SupplierInvoiceService;
use Dev3bdulrahman\Purchases\Models\SupplierInvoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierInvoiceApiController extends Controller
{
    use HasApiResponse;

    /**
     * List all supplier invoices.
     */
    public function index(Request $request, SupplierInvoiceService $service): JsonResponse
    {
        $this->authorize('viewAny', SupplierInvoice::class);

        $perPage = (int) $request->get('per_page', 10);
        $invoices = $service->listInvoices($request->all(), $perPage);

        return $this->success(
            SupplierInvoiceResource::collection($invoices->items()),
            __('Supplier invoices retrieved successfully'),
            200,
            [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
            ]
        );
    }

    /**
     * Store a new supplier invoice.
     */
    public function store(StoreSupplierInvoiceApiRequest $request, SupplierInvoiceService $service): JsonResponse
    {
        $this->authorize('create', SupplierInvoice::class);

        $validated = $request->validated();
        $items = $validated['items'];
        unset($validated['items']);

        $validated['company_id'] = session('active_company_id') ?: auth()->user()->company_id;
        $invoice = $service->createInvoice($validated, $items);
        $invoice->load('items');

        SupplierInvoiceReceived::dispatch($invoice, auth()->id(), auth()->user()->company_id);

        return $this->success(
            new SupplierInvoiceResource($invoice),
            __('Supplier invoice created successfully'),
            201
        );
    }

    /**
     * Show a single supplier invoice.
     */
    public function show(SupplierInvoice $supplierInvoice): JsonResponse
    {
        $this->authorize('view', $supplierInvoice);

        $supplierInvoice->load('items');

        return $this->success(
            new SupplierInvoiceResource($supplierInvoice),
            __('Supplier invoice retrieved successfully')
        );
    }

    /**
     * Update an existing supplier invoice.
     */
    public function update(UpdateSupplierInvoiceApiRequest $request, SupplierInvoice $supplierInvoice, SupplierInvoiceService $service): JsonResponse
    {
        $this->authorize('update', $supplierInvoice);

        $validated = $request->validated();
        $items = $validated['items'] ?? [];
        unset($validated['items']);

        $service->updateInvoice($supplierInvoice, $validated, $items);
        $supplierInvoice->load('items');

        return $this->success(
            new SupplierInvoiceResource($supplierInvoice),
            __('Supplier invoice updated successfully')
        );
    }

    /**
     * Delete a supplier invoice.
     */
    public function destroy(SupplierInvoice $supplierInvoice, SupplierInvoiceService $service): JsonResponse
    {
        $this->authorize('delete', $supplierInvoice);

        $service->deleteInvoice($supplierInvoice);

        return $this->success(
            null,
            __('Supplier invoice deleted successfully')
        );
    }
}
