<?php

namespace Dev3bdulrahman\Purchases\Events;

use Dev3bdulrahman\Purchases\Models\SupplierInvoice;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupplierInvoiceReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public SupplierInvoice $supplierInvoice,
        public int $userId,
        public int $companyId,
    ) {}
}
