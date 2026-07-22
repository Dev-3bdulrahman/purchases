<?php

namespace Dev3bdulrahman\Purchases\Policies;

use App\Models\User;
use Dev3bdulrahman\Purchases\Models\PurchaseRequest;

class PurchaseRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('purchases.requests.view');
    }

    public function view(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->can('purchases.requests.view') && $purchaseRequest->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->can('purchases.requests.create');
    }

    public function update(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->can('purchases.requests.update') && $purchaseRequest->company_id === $user->company_id;
    }

    public function delete(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->can('purchases.requests.delete') && $purchaseRequest->company_id === $user->company_id;
    }

    public function approve(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $user->can('purchases.requests.approve') && $purchaseRequest->company_id === $user->company_id;
    }
}
