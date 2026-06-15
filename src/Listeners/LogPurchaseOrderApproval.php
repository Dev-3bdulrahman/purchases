<?php

namespace Dev3bdulrahman\Purchases\Listeners;

use App\Services\AuditLogService;
use Dev3bdulrahman\Purchases\Events\PurchaseOrderApproved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LogPurchaseOrderApproval implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private AuditLogService $auditLogService,
    ) {}

    /**
     * Handle the PurchaseOrderApproved event.
     */
    public function handle(PurchaseOrderApproved $event): void
    {
        try {
            $this->auditLogService->log(
                action: 'purchase_order_approved',
                companyId: $event->companyId,
                userId: $event->userId,
                model: $event->purchaseOrder,
                oldValues: null,
                newValues: [
                    'purchase_order_id' => $event->purchaseOrder->id,
                    'order_number' => $event->purchaseOrder->order_number,
                    'supplier_id' => $event->purchaseOrder->supplier_id,
                    'grand_total' => $event->purchaseOrder->grand_total,
                ],
            );
        } catch (\Throwable $e) {
            Log::error('LogPurchaseOrderApproval: Failed to log purchase order approval.', [
                'error' => $e->getMessage(),
                'purchase_order_id' => $event->purchaseOrder->id ?? null,
                'user_id' => $event->userId ?? null,
            ]);
        }
    }
}
