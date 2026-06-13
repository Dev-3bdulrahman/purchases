<?php

namespace Dev3bdulrahman\Purchases\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Dev3bdulrahman\Purchases\Http\Resources\SupplierPaymentResource;
use Dev3bdulrahman\Purchases\Services\SupplierInvoiceService;
use Dev3bdulrahman\Purchases\Models\SupplierInvoice;
use Dev3bdulrahman\Purchases\Models\SupplierPayment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SupplierPaymentApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = SupplierPayment::query()->with('invoice');

        if ($request->has('supplier_invoice_id')) {
            $query->where('supplier_invoice_id', $request->supplier_invoice_id);
        }

        $perPage = (int)$request->get('per_page', 10);
        $payments = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => __('Supplier payments retrieved successfully'),
            'data' => SupplierPaymentResource::collection($payments->items()),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
            'errors' => []
        ]);
    }

    public function store(Request $request, SupplierInvoiceService $service): JsonResponse
    {
        $validated = $request->validate([
            'supplier_invoice_id' => 'required|exists:purchases_invoices,id',
            'payment_number' => 'required|string|max:255',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|in:cash,bank_transfer,card,check,online',
            'amount' => 'required|numeric|min:0.0001',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $invoice = SupplierInvoice::findOrFail($validated['supplier_invoice_id']);

        $payment = $service->recordPayment($invoice, $validated);

        return response()->json([
            'success' => true,
            'message' => __('Supplier payment recorded successfully'),
            'data' => new SupplierPaymentResource($payment),
            'errors' => []
        ], 201);
    }

    public function destroy($id): JsonResponse
    {
        $payment = SupplierPayment::findOrFail($id);

        $invoice = $payment->invoice;
        if ($invoice) {
            $newPaidAmount = max(0, $invoice->paid_amount - $payment->amount);
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

        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => __('Supplier payment deleted successfully'),
            'data' => null,
            'errors' => []
        ]);
    }
}
