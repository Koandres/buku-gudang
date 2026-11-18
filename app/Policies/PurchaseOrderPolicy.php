<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->active;
    }

    public function view(User $user, PurchaseOrder $order): bool
    {
        return $user->active;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::Pembelian, UserRole::Admin);
    }

    public function issue(User $user, PurchaseOrder $order): bool
    {
        return $this->create($user);
    }

    public function cancel(User $user, PurchaseOrder $order): bool
    {
        return $this->create($user);
    }
}
