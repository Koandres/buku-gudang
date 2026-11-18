<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\GoodsReceipt;
use App\Models\User;

class GoodsReceiptPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->active;
    }

    public function view(User $user, GoodsReceipt $receipt): bool
    {
        return $user->active;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::Gudang, UserRole::Admin);
    }
}
