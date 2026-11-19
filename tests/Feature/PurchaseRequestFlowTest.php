<?php

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Models\Item;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function itemUntukTesPR(): Item
{
    return Item::create([
        'kode' => 'BRG-TEST',
        'nama' => 'Barang Tes',
        'satuan' => 'pcs',
        'harga_acuan' => 10000,
        'stok_minimum' => 1,
        'aktif' => true,
    ]);
}

/** @param array<string, mixed> $overrides */
function buatPRTes(User $pemohon, Item $item, array $overrides = []): PurchaseRequest
{
    $request = PurchaseRequest::create(array_merge([
        'nomor' => 'PR-TEST-'.fake()->unique()->numerify('####'),
        'tanggal' => now()->toDateString(),
        'user_id' => $pemohon->id,
        'keperluan' => 'Keperluan tes',
        'status' => RequestStatus::Draft,
    ], $overrides));

    $request->items()->create(['item_id' => $item->id, 'jumlah' => 5]);

    return $request;
}

it('membuat PR dengan nomor otomatis lalu mengajukannya', function () {
    $pemohon = User::factory()->create(['role' => UserRole::Pemohon]);
    $item = itemUntukTesPR();

    $this->actingAs($pemohon)
        ->post(route('purchase-requests.store'), [
            'tanggal' => now()->toDateString(),
            'keperluan' => 'Keperluan operasional',
            'items' => [['item_id' => $item->id, 'jumlah' => 5]],
        ])
        ->assertRedirect();

    $pr = PurchaseRequest::sole();
    expect($pr->nomor)->toStartWith('PR-'.now()->format('Ym').'-')
        ->and($pr->status)->toBe(RequestStatus::Draft);

    $this->post(route('purchase-requests.submit', $pr))->assertRedirect();

    expect($pr->fresh()->status)->toBe(RequestStatus::Diajukan);
});

it('menolak persetujuan PR oleh pembuatnya sendiri', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $pr = buatPRTes($admin, itemUntukTesPR(), ['status' => RequestStatus::Diajukan]);

    $this->actingAs($admin)
        ->post(route('purchase-requests.approve', $pr))
        ->assertForbidden();

    expect($pr->fresh()->status)->toBe(RequestStatus::Diajukan);
});

it('memungkinkan penyetuju lain menyetujui PR', function () {
    $pemohon = User::factory()->create(['role' => UserRole::Pemohon]);
    $penyetuju = User::factory()->create(['role' => UserRole::Penyetuju]);
    $pr = buatPRTes($pemohon, itemUntukTesPR(), ['status' => RequestStatus::Diajukan]);

    $this->actingAs($penyetuju)
        ->post(route('purchase-requests.approve', $pr))
        ->assertRedirect();

    expect($pr->fresh()->status)->toBe(RequestStatus::Disetujui);
});

it('menolak PR disertai alasan', function () {
    $pemohon = User::factory()->create(['role' => UserRole::Pemohon]);
    $penyetuju = User::factory()->create(['role' => UserRole::Penyetuju]);
    $pr = buatPRTes($pemohon, itemUntukTesPR(), ['status' => RequestStatus::Diajukan]);

    $this->actingAs($penyetuju)
        ->post(route('purchase-requests.reject', $pr), ['alasan' => 'Anggaran tidak tersedia'])
        ->assertRedirect();

    expect($pr->fresh()->status)->toBe(RequestStatus::Ditolak)
        ->and($pr->fresh()->alasan_ditolak)->toBe('Anggaran tidak tersedia');
});

it('menolak barang nonaktif dan baris duplikat pada PR', function () {
    $pemohon = User::factory()->create(['role' => UserRole::Pemohon]);
    $itemAktif = itemUntukTesPR();
    $itemNonaktif = Item::create([
        'kode' => 'BRG-MATI-'.fake()->unique()->numerify('###'),
        'nama' => 'Barang Nonaktif',
        'satuan' => 'pcs',
        'harga_acuan' => 10000,
        'stok_minimum' => 1,
        'aktif' => false,
    ]);

    $this->actingAs($pemohon)
        ->post(route('purchase-requests.store'), [
            'tanggal' => now()->toDateString(),
            'keperluan' => 'Keperluan tes',
            'items' => [['item_id' => $itemNonaktif->id, 'jumlah' => 2]],
        ])
        ->assertSessionHasErrors('items.0.item_id');

    $this->post(route('purchase-requests.store'), [
        'tanggal' => now()->toDateString(),
        'keperluan' => 'Keperluan tes',
        'items' => [
            ['item_id' => $itemAktif->id, 'jumlah' => 2],
            ['item_id' => $itemAktif->id, 'jumlah' => 3],
        ],
    ])->assertSessionHasErrors('items.1.item_id');

    expect(PurchaseRequest::count())->toBe(0);
});

it('menolak perubahan PR yang sudah diajukan', function () {
    $pemohon = User::factory()->create(['role' => UserRole::Pemohon]);
    $item = itemUntukTesPR();
    $pr = buatPRTes($pemohon, $item, ['status' => RequestStatus::Diajukan]);

    $this->actingAs($pemohon)
        ->put(route('purchase-requests.update', $pr), [
            'tanggal' => now()->toDateString(),
            'keperluan' => 'Keperluan diubah',
            'items' => [['item_id' => $item->id, 'jumlah' => 7]],
        ])
        ->assertForbidden();

    expect($pr->fresh()->keperluan)->toBe('Keperluan tes')
        ->and($pr->items()->first()->jumlah)->toBe(5);
});
