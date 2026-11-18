<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class StockPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->active;
    }

    public function adjust(User $user): bool
    {
        return $user->hasRole(UserRole::Gudang, UserRole::Admin);
    }
}
