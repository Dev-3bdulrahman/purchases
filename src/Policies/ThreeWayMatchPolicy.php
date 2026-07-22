<?php

namespace Dev3bdulrahman\Purchases\Policies;

use App\Models\User;

class ThreeWayMatchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('purchases.matching.view');
    }

    public function view(User $user): bool
    {
        return $user->can('purchases.matching.view');
    }

    public function resolve(User $user): bool
    {
        return $user->can('purchases.matching.resolve');
    }
}
