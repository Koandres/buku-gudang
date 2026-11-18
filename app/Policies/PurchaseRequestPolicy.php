<?php

namespace App\Policies;

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Models\PurchaseRequest;
use App\Models\User;

class PurchaseRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->active;
    }

    public function view(User $user, PurchaseRequest $request): bool
    {
        return $user->hasRole(UserRole::Admin)
            || $request->user_id === $user->id
            || $user->hasRole(UserRole::Penyetuju, UserRole::Pembelian, UserRole::Gudang);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::Pemohon, UserRole::Admin);
    }

    public function update(User $user, PurchaseRequest $request): bool
    {
        return ($request->user_id === $user->id || $user->hasRole(UserRole::Admin)) && $request->isDraft();
    }

    public function delete(User $user, PurchaseRequest $request): bool
    {
        return $this->update($user, $request);
    }

    public function submit(User $user, PurchaseRequest $request): bool
    {
        return ($request->user_id === $user->id || $user->hasRole(UserRole::Admin)) && $request->isDraft();
    }

    public function approve(User $user, PurchaseRequest $request): bool
    {
        return $user->hasRole(UserRole::Penyetuju, UserRole::Admin)
            && $request->status === RequestStatus::Diajukan
            && $request->user_id !== $user->id;
    }
}
