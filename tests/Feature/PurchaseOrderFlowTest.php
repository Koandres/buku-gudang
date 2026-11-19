<?php

use App\Enums\OrderStatus;
use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function supplierUntukTesPO(): Supplier
{
    return Supplier::create([
        'kode' => 'SUP-PO-'.fake()->unique()->numerify('###'),
        'nama' => 'Supplier Tes PO',
        'kontak' => 'Kontak',
        'telepon' => '0210000000',
        'alamat' => 'Alamat tes',
        'aktif' => true,
    ]);
}

function itemUntukTesPO(): Item
{
    return Item::create([
        'kode' => 'BRG-PO-'.fake()->unique()->numerify('###'),
        'nama' => 'Barang Tes PO',
        'satuan' => 'pcs',
        'harga_acuan' => 25000,
        'stok_minimum' => 1,
        'aktif' => true,
    ]);
}

function prDisetujuiUntukPO(User $pemohon, Item $item, int $jumlah = 10): PurchaseRequest
{
    $pr = PurchaseRequest::create([
        'nomor' => 'PR-PO-'.fake()->unique()->numerify('####'),
        'tanggal' => now()->toDateString(),
        'user_id' => $pemohon->id,
        'keperluan' => 'Keperluan tes PO',
        'status' => RequestStatus::Disetujui,
    ]);
    $pr->items()->create(['item_id' => $item->id, 'jumlah' => $jumlah]);

    return $pr;
}

/** @return array<string, mixed> */
function dataPesananUntukPO(PurchaseRequest $pr, Supplier $supplier, int $jumlah): array
{
    return [
        'tanggal' => now()->toDateString(),
        'supplier_id' => $supplier->id,
        'purchase_request_id' => $pr->id,
        'catatan' => 'Catatan tes',
        'items' => [[
            'purchase_request_item_id' => $pr->items->first()->id,
            'jumlah' => $jumlah,
            'harga' => 25000,
        ]],
    ];
}

it('membuat PO bertahap dari PR lalu menerbitkannya', function () {
    $pemohon = User::factory()->create(['role' => UserRole::Pemohon]);
    $pembelian = User::factory()->create(['role' => UserRole::Pembelian]);
    $supplier = supplierUntukTesPO();
    $item = itemUntukTesPO();
    $pr = prDisetujuiUntukPO($pemohon, $item, 10);

    $this->actingAs($pembelian);
    $this->post(route('purchase-orders.store'), dataPesananUntukPO($pr, $supplier, 6))->assertRedirect();

    $order = PurchaseOrder::sole();
    expect($order->status)->toBe(OrderStatus::Draft)
        ->and($order->items->first()->harga)->toBe(25000)
        ->and($pr->items()->first()->jumlah_dipesan)->toBe(6)
        ->and($pr->fresh()->status)->toBe(RequestStatus::Disetujui);

    $this->post(route('purchase-orders.issue', $order))->assertRedirect();
    expect($order->fresh()->status)->toBe(OrderStatus::Terbit);

    $this->post(route('purchase-orders.store'), dataPesananUntukPO($pr, $supplier, 4))->assertRedirect();
    expect($pr->fresh()->status)->toBe(RequestStatus::Diproses)
        ->and(PurchaseOrder::count())->toBe(2);
});

it('mengembalikan sisa PR saat PO dibatalkan', function () {
    $pemohon = User::factory()->create(['role' => UserRole::Pemohon]);
    $pembelian = User::factory()->create(['role' => UserRole::Pembelian]);
    $supplier = supplierUntukTesPO();
    $pr = prDisetujuiUntukPO($pemohon, itemUntukTesPO(), 5);

    $this->actingAs($pembelian);
    $this->post(route('purchase-orders.store'), dataPesananUntukPO($pr, $supplier, 5))->assertRedirect();
    expect($pr->fresh()->status)->toBe(RequestStatus::Diproses);

    $order = PurchaseOrder::sole();
    $this->post(route('purchase-orders.cancel', $order))->assertRedirect();

    expect($order->fresh()->status)->toBe(OrderStatus::Dibatalkan)
        ->and($pr->items()->first()->jumlah_dipesan)->toBe(0)
        ->and($pr->fresh()->status)->toBe(RequestStatus::Disetujui);
});

it('menolak pembatalan PO yang sudah diterima sebagian', function () {
    $pemohon = User::factory()->create(['role' => UserRole::Pemohon]);
    $pembelian = User::factory()->create(['role' => UserRole::Pembelian]);
    $gudang = User::factory()->create(['role' => UserRole::Gudang]);
    $pr = prDisetujuiUntukPO($pemohon, itemUntukTesPO(), 10);

    $this->actingAs($pembelian);
    $this->post(route('purchase-orders.store'), dataPesananUntukPO($pr, supplierUntukTesPO(), 10))->assertRedirect();
    $order = PurchaseOrder::sole();
    $this->post(route('purchase-orders.issue', $order))->assertRedirect();

    $warehouse = Warehouse::create(['kode' => 'GUD-PO-1', 'nama' => 'Gudang Tes PO']);
    $this->actingAs($gudang)
        ->post(route('goods-receipts.store'), [
            'tanggal' => now()->toDateString(),
            'purchase_order_id' => $order->id,
            'warehouse_id' => $warehouse->id,
            'catatan' => 'Penerimaan sebagian',
            'items' => [[
                'purchase_order_item_id' => $order->items->first()->id,
                'jumlah' => 4,
            ]],
        ])
        ->assertRedirect();

    expect($order->fresh()->status)->toBe(OrderStatus::SebagianDiterima);

    $this->actingAs($pembelian)
        ->post(route('purchase-orders.cancel', $order))
        ->assertSessionHasErrors('status');

    expect($order->fresh()->status)->toBe(OrderStatus::SebagianDiterima)
        ->and($pr->fresh()->status)->toBe(RequestStatus::Diproses);
});

it('menolak jumlah pesanan melebihi sisa PR', function () {
    $pemohon = User::factory()->create(['role' => UserRole::Pemohon]);
    $pembelian = User::factory()->create(['role' => UserRole::Pembelian]);
    $pr = prDisetujuiUntukPO($pemohon, itemUntukTesPO(), 5);

    $this->actingAs($pembelian);
    $this->post(route('purchase-orders.store'), dataPesananUntukPO($pr, supplierUntukTesPO(), 6))
        ->assertSessionHasErrors('items');

    expect(PurchaseOrder::count())->toBe(0)
        ->and($pr->items()->first()->jumlah_dipesan)->toBe(0);
});

it('melarang pemohon membuat PO', function () {
    $pemohon = User::factory()->create(['role' => UserRole::Pemohon]);
    $pr = prDisetujuiUntukPO($pemohon, itemUntukTesPO(), 5);

    $this->actingAs($pemohon)
        ->post(route('purchase-orders.store'), [
            'tanggal' => now()->toDateString(),
            'supplier_id' => supplierUntukTesPO()->id,
            'purchase_request_id' => $pr->id,
            'items' => [[
                'purchase_request_item_id' => $pr->items->first()->id,
                'jumlah' => 5,
                'harga' => 25000,
            ]],
        ])
        ->assertForbidden();
});
