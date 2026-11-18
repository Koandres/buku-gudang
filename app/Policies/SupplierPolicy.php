<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Supplier;
use App\Models\User;

class SupplierPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->active;
    }

    public function manage(User $user): bool
    {
        return $user->hasRole(UserRole::Pembelian, UserRole::Admin);
    }

    public function create(User $user): bool
    {
        return $this->manage($user);
    }

    public function update(User $user, Supplier $supplier): bool
    {
        return $this->manage($user);
    }

    public function delete(User $user, Supplier $supplier): bool
    {
        return $this->manage($user);
    }
}
