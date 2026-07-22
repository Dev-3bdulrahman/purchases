<?php

namespace Dev3bdulrahman\Purchases\Events;

use Dev3bdulrahman\Purchases\Models\GoodsReceiptNote;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GoodsReceiptCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public GoodsReceiptNote $goodsReceiptNote,
        public int $userId,
        public int $companyId,
    ) {}
}
