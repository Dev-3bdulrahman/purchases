<?php

namespace Dev3bdulrahman\Purchases\Listeners;

use Dev3bdulrahman\Purchases\Events\GoodsReceiptCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdatePurchaseOrderOnReceipt implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function handle(GoodsReceiptCompleted $event): void
    {
        try {
            $grn = $event->goodsReceiptNote;
            $po = $grn->purchaseOrder;

            if ($po && $po->status !== 'received') {
                $po->update(['status' => 'received']);
            }
        } catch (\Throwable $e) {
            Log::error('UpdatePurchaseOrderOnReceipt: Failed to update purchase order.', [
                'error' => $e->getMessage(),
                'grn_id' => $event->goodsReceiptNote->id ?? null,
            ]);
        }
    }
}
