<?php

namespace Dev3bdulrahman\Purchases\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\HasApiResponse;
use Dev3bdulrahman\Purchases\Events\SupplierPaymentMade;
use Dev3bdulrahman\Purchases\Http\Requests\Api\StoreSupplierPaymentApiRequest;
use Dev3bdulrahman\Purchases\Http\Resources\SupplierPaymentResource;
use Dev3bdulrahman\Purchases\Services\SupplierInvoiceService;
use Dev3bdulrahman\Purchases\Models\SupplierInvoice;
use Dev3bdulrahman\Purchases\Models\SupplierPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierPaymentApiController extends Controller
{
    use HasApiResponse;

    /**
     * List all supplier payments.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SupplierPayment::class);

        $query = SupplierPayment::query()->with('invoice');

        if ($request->has('supplier_invoice_id')) {
            $query->where('supplier_invoice_id', $request->supplier_invoice_id);
        }

        $perPage = (int) $request->get('per_page', 10);
        $payments = $query->paginate($perPage);

        return $this->success(
            SupplierPaymentResource::collection($payments->items()),
            __('Supplier payments retrieved successfully'),
            200,
            [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ]
        );
    }

    /**
     * Record a new supplier payment.
     */
    public function store(StoreSupplierPaymentApiRequest $request, SupplierInvoiceService $service): JsonResponse
    {
        $this->authorize('create', SupplierPayment::class);

        $validated = $request->validated();
        $invoice = SupplierInvoice::findOrFail($validated['supplier_invoice_id']);

        $payment = $service->recordPayment($invoice, $validated);

        SupplierPaymentMade::dispatch($payment, $invoice, auth()->id(), auth()->user()->company_id);

        return $this->success(
            new SupplierPaymentResource($payment),
            __('Supplier payment recorded successfully'),
            201
        );
    }

    /**
     * Delete a supplier payment.
     */
    public function destroy(SupplierPayment $supplierPayment): JsonResponse
    {
        $this->authorize('delete', $supplierPayment);

        $invoice = $supplierPayment->invoice;
        $supplierPayment->delete();

        if ($invoice) {
            $newPaidAmount = $invoice->payments()->sum('amount');
            $status = 'unpaid';
            if ($newPaidAmount >= $invoice->grand_total) {
                $status = 'paid';
            } elseif ($newPaidAmount > 0) {
                $status = 'partially_paid';
            }
            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'status' => $status,
            ]);
        }

        return $this->success(
            null,
            __('Supplier payment deleted successfully')
        );
    }
}
