<?php

namespace Dev3bdulrahman\Purchases\Policies;

use App\Models\User;
use Dev3bdulrahman\Purchases\Models\SupplierPayment;

class SupplierPaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('purchases.payments.view');
    }

    public function view(User $user, SupplierPayment $supplierPayment): bool
    {
        return $user->can('purchases.payments.view') && $supplierPayment->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->can('purchases.payments.create');
    }

    public function delete(User $user, SupplierPayment $supplierPayment): bool
    {
        return $user->can('purchases.payments.delete') && $supplierPayment->company_id === $user->company_id;
    }
}
