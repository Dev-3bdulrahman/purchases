<?php

namespace Dev3bdulrahman\Purchases\Listeners;

use App\Services\AuditLogService;
use Dev3bdulrahman\Purchases\Events\GoodsReceiptCreated;
use Dev3bdulrahman\Purchases\Events\GoodsReceiptCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LogGoodsReceipt implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private AuditLogService $auditLogService,
    ) {}

    public function handle(GoodsReceiptCreated|GoodsReceiptCompleted $event): void
    {
        try {
            $action = $event instanceof GoodsReceiptCreated ? 'goods_receipt_created' : 'goods_receipt_completed';

            $this->auditLogService->log(
                action: $action,
                companyId: $event->companyId,
                userId: $event->userId,
                model: $event->goodsReceiptNote,
                oldValues: null,
                newValues: [
                    'grn_id' => $event->goodsReceiptNote->id,
                    'grn_number' => $event->goodsReceiptNote->grn_number,
                    'purchase_order_id' => $event->goodsReceiptNote->purchase_order_id,
                    'status' => $event->goodsReceiptNote->status,
                ],
            );
        } catch (\Throwable $e) {
            Log::error('LogGoodsReceipt: Failed to log goods receipt.', [
                'error' => $e->getMessage(),
                'grn_id' => $event->goodsReceiptNote->id ?? null,
            ]);
        }
    }
}
