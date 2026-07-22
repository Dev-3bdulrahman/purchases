<?php

namespace Dev3bdulrahman\Purchases\Events;

use Dev3bdulrahman\Purchases\Models\PurchaseOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PurchaseOrderApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public PurchaseOrder $purchaseOrder,
        public int $userId,
        public int $companyId,
    ) {}
}
