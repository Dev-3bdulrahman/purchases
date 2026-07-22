<?php

namespace Dev3bdulrahman\Purchases\Policies;

use App\Models\User;
use Dev3bdulrahman\Purchases\Models\SupplierInvoice;

class SupplierInvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('purchases.invoices.view');
    }

    public function view(User $user, SupplierInvoice $supplierInvoice): bool
    {
        return $user->can('purchases.invoices.view') && $supplierInvoice->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->can('purchases.invoices.create');
    }

    public function update(User $user, SupplierInvoice $supplierInvoice): bool
    {
        return $user->can('purchases.invoices.update') && $supplierInvoice->company_id === $user->company_id;
    }

    public function delete(User $user, SupplierInvoice $supplierInvoice): bool
    {
        return $user->can('purchases.invoices.delete') && $supplierInvoice->company_id === $user->company_id;
    }
}
