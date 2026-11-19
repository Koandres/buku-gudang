<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menolak seluruh akses akun nonaktif', function (string $rute) {
    $user = User::factory()->create([
        'role' => UserRole::Admin,
        'active' => false,
    ]);

    $this->actingAs($user)
        ->get(route($rute))
        ->assertForbidden();
})->with([
    'dashboard',
    'purchase-requests.index',
    'purchase-orders.index',
    'goods-receipts.index',
    'stock.index',
    'suppliers.index',
    'items.index',
    'warehouses.index',
    'users.index',
    'reports.index',
    'activities.index',
]);

it('tetap mengizinkan akun aktif mengakses daftar dokumen dan laporan stok', function (string $rute) {
    $user = User::factory()->create(['role' => UserRole::Pemohon]);

    $this->actingAs($user)
        ->get(route($rute))
        ->assertOk();
})->with([
    'purchase-requests.index',
    'purchase-orders.index',
    'goods-receipts.index',
    'reports.stock',
]);
