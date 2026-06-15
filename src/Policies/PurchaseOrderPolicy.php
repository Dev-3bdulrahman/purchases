<?php

namespace Dev3bdulrahman\Purchases\Policies;

use App\Models\User;
use Dev3bdulrahman\Purchases\Models\PurchaseOrder;

class PurchaseOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('purchases.orders.view');
    }

    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->can('purchases.orders.view') && $purchaseOrder->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->can('purchases.orders.create');
    }

    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->can('purchases.orders.update') && $purchaseOrder->company_id === $user->company_id;
    }

    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->can('purchases.orders.delete') && $purchaseOrder->company_id === $user->company_id;
    }

    public function approve(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->can('purchases.orders.approve') && $purchaseOrder->company_id === $user->company_id;
    }
}
