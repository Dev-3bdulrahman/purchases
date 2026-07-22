<?php

namespace Dev3bdulrahman\Purchases\Policies;

use App\Models\User;
use Dev3bdulrahman\Purchases\Models\Supplier;

class SupplierPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('purchases.suppliers.view');
    }

    public function view(User $user, Supplier $supplier): bool
    {
        return $user->can('purchases.suppliers.view') && $supplier->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->can('purchases.suppliers.create');
    }

    public function update(User $user, Supplier $supplier): bool
    {
        return $user->can('purchases.suppliers.update') && $supplier->company_id === $user->company_id;
    }

    public function delete(User $user, Supplier $supplier): bool
    {
        return $user->can('purchases.suppliers.delete') && $supplier->company_id === $user->company_id;
    }
}
