<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('mengarahkan pengguna ke halaman awal yang diizinkan setelah masuk', function (UserRole $role, string $rute) {
    $user = User::factory()->create(['role' => $role]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route($rute));

    $this->get(route($rute))->assertOk();
})->with([
    'pemohon' => [UserRole::Pemohon, 'purchase-requests.index'],
    'penyetuju' => [UserRole::Penyetuju, 'purchase-requests.index'],
    'pembelian' => [UserRole::Pembelian, 'dashboard'],
    'gudang' => [UserRole::Gudang, 'stock.index'],
    'admin' => [UserRole::Admin, 'dashboard'],
]);
