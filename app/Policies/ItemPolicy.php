<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Item;
use App\Models\User;

class ItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->active;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::Admin);
    }

    public function update(User $user, Item $item): bool
    {
        return $user->hasRole(UserRole::Admin);
    }

    public function delete(User $user, Item $item): bool
    {
        return $user->hasRole(UserRole::Admin);
    }
}
