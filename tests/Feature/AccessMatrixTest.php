<?php

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/** @return array<string, User> */
function penggunaPeran(): array
{
    return collect(UserRole::cases())
        ->mapWithKeys(fn (UserRole $role): array => [$role->value => User::factory()->create(['role' => $role])])
        ->all();
}

function buatPRUntukAkses(User $pemohon, string $nomor, ?string $tanggal = null): PurchaseRequest
{
    return PurchaseRequest::create([
        'nomor' => $nomor,
        'tanggal' => $tanggal ?? now()->toDateString(),
        'user_id' => $pemohon->id,
        'keperluan' => 'Keperluan tes',
        'status' => RequestStatus::Draft,
    ]);
}

it('membatasi dashboard dan laporan bernilai uang hanya untuk pembelian dan admin', function (string $rute) {
    $pengguna = penggunaPeran();

    foreach ([UserRole::Pemohon, UserRole::Penyetuju, UserRole::Gudang] as $role) {
        $this->actingAs($pengguna[$role->value])->get(route($rute))->assertForbidden();
    }

    foreach ([UserRole::Pembelian, UserRole::Admin] as $role) {
        $this->actingAs($pengguna[$role->value])->get(route($rute))->assertOk();
    }
})->with([
    'dashboard',
    'reports.index',
    'reports.outstanding',
    'reports.suppliers',
    'reports.reorder',
]);

it('membatasi ekspor laporan bernilai uang hanya untuk pembelian dan admin', function (string $jenis) {
    $pengguna = penggunaPeran();

    foreach ([UserRole::Pemohon, UserRole::Penyetuju, UserRole::Gudang] as $role) {
        $this->actingAs($pengguna[$role->value])
            ->get(route('reports.export', ['jenis' => $jenis]))
            ->assertForbidden();
    }

    $this->actingAs($pengguna['pembelian'])
        ->get(route('reports.export', ['jenis' => $jenis]))
        ->assertOk();
})->with(['ringkasan', 'outstanding', 'suppliers', 'reorder']);

it('mengizinkan laporan dan ekspor stok bagi semua peran aktif', function () {
    $pengguna = penggunaPeran();

    foreach ($pengguna as $user) {
        $this->actingAs($user)->get(route('reports.stock'))->assertOk();
        $this->actingAs($user)->get(route('reports.export', ['jenis' => 'stock']))->assertOk();
    }
});

it('membatasi kartu stok untuk gudang, pembelian, dan admin', function () {
    $pengguna = penggunaPeran();

    foreach ([UserRole::Pemohon, UserRole::Penyetuju] as $role) {
        $this->actingAs($pengguna[$role->value])->get(route('stock.index'))->assertForbidden();
    }

    foreach ([UserRole::Gudang, UserRole::Pembelian, UserRole::Admin] as $role) {
        $this->actingAs($pengguna[$role->value])->get(route('stock.index'))->assertOk();
    }
});

it('hanya menampilkan PR milik pemohon', function () {
    $pemohon = penggunaPeran()['pemohon'];
    $pemohonLain = User::factory()->create(['role' => UserRole::Pemohon]);
    $punya = buatPRUntukAkses($pemohon, 'PR-AKS-0001');
    $asing = buatPRUntukAkses($pemohonLain, 'PR-AKS-0002');

    $this->actingAs($pemohon)
        ->get(route('purchase-requests.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('requests.data.0.id', $punya->id)
            ->missing('requests.data.1'));

    $this->actingAs($pemohon)
        ->get(route('purchase-requests.show', $asing))
        ->assertForbidden();
});

it('tetap menampilkan semua PR untuk peran selain pemohon', function (UserRole $role) {
    $pengguna = penggunaPeran();
    $pemohon = $pengguna['pemohon'];
    $punya = buatPRUntukAkses($pemohon, 'PR-AKS-0003', now()->subDay()->toDateString());
    $asing = buatPRUntukAkses(User::factory()->create(['role' => UserRole::Pemohon]), 'PR-AKS-0004', now()->toDateString());

    $this->actingAs($pengguna[$role->value])
        ->get(route('purchase-requests.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('requests.total', 2)
            ->where('requests.data.0.id', $asing->id)
            ->where('requests.data.1.id', $punya->id));

    $this->actingAs($pengguna[$role->value])
        ->get(route('purchase-requests.show', $asing))
        ->assertOk();
})->with([
    'penyetuju' => UserRole::Penyetuju,
    'pembelian' => UserRole::Pembelian,
    'gudang' => UserRole::Gudang,
    'admin' => UserRole::Admin,
]);

it('tetap mengizinkan daftar PO dan GR untuk semua peran aktif', function (string $rute) {
    foreach (penggunaPeran() as $user) {
        $this->actingAs($user)->get(route($rute))->assertOk();
    }
})->with(['purchase-orders.index', 'goods-receipts.index']);
