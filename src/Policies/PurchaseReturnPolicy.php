<?php

namespace Dev3bdulrahman\Purchases\Policies;

use App\Models\User;
use Dev3bdulrahman\Purchases\Models\PurchaseReturn;

class PurchaseReturnPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('purchases.returns.view');
    }

    public function view(User $user, PurchaseReturn $purchaseReturn): bool
    {
        return $user->can('purchases.returns.view') && $purchaseReturn->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->can('purchases.returns.create');
    }

    public function update(User $user, PurchaseReturn $purchaseReturn): bool
    {
        return $user->can('purchases.returns.update') && $purchaseReturn->company_id === $user->company_id;
    }

    public function delete(User $user, PurchaseReturn $purchaseReturn): bool
    {
        return $user->can('purchases.returns.delete') && $purchaseReturn->company_id === $user->company_id;
    }
}
