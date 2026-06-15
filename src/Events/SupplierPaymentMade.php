<?php

namespace Dev3bdulrahman\Purchases\Events;

use Dev3bdulrahman\Purchases\Models\SupplierInvoice;
use Dev3bdulrahman\Purchases\Models\SupplierPayment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupplierPaymentMade
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public SupplierPayment $supplierPayment,
        public SupplierInvoice $supplierInvoice,
        public int $userId,
        public int $companyId,
    ) {}
}
