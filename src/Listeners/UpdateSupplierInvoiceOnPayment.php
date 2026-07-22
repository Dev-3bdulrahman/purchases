<?php

namespace Dev3bdulrahman\Purchases\Listeners;

use Dev3bdulrahman\Purchases\Events\SupplierPaymentMade;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateSupplierInvoiceOnPayment implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Handle the SupplierPaymentMade event.
     *
     * Recalculates supplier invoice paid_amount from sum of all payments
     * and updates status accordingly.
     */
    public function handle(SupplierPaymentMade $event): void
    {
        try {
            $invoice = $event->supplierInvoice;

            $paidAmount = $invoice->payments()->sum('amount');

            $status = $invoice->status;
            if ($paidAmount >= $invoice->grand_total) {
                $status = 'paid';
            } elseif ($paidAmount > 0) {
                $status = 'partial';
            }

            $invoice->update([
                'paid_amount' => $paidAmount,
                'status' => $status,
            ]);
        } catch (\Throwable $e) {
            Log::error('UpdateSupplierInvoiceOnPayment: Failed to update supplier invoice.', [
                'error' => $e->getMessage(),
                'invoice_id' => $event->supplierInvoice->id ?? null,
                'payment_id' => $event->supplierPayment->id ?? null,
            ]);
        }
    }
}
