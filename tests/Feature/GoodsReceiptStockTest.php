<?php

use App\Enums\MovementType;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

function supplierUntukTesGR(): Supplier
{
    return Supplier::create([
        'kode' => 'SUP-GR-'.fake()->unique()->numerify('###'),
        'nama' => 'Supplier Tes GR',
        'kontak' => 'Kontak',
        'telepon' => '0210000000',
        'alamat' => 'Alamat tes',
        'aktif' => true,
    ]);
}

function itemUntukTesGR(): Item
{
    return Item::create([
        'kode' => 'BRG-GR-'.fake()->unique()->numerify('###'),
        'nama' => 'Barang Tes GR',
        'satuan' => 'pcs',
        'harga_acuan' => 10000,
        'stok_minimum' => 1,
        'aktif' => true,
    ]);
}

function poUntukTesGR(Item $item, OrderStatus $status = OrderStatus::Terbit, int $jumlah = 10): PurchaseOrder
{
    $user = User::factory()->create(['role' => UserRole::Pembelian]);
    $order = PurchaseOrder::create([
        'nomor' => 'PO-GR-'.fake()->unique()->numerify('####'),
        'tanggal' => now()->toDateString(),
        'supplier_id' => supplierUntukTesGR()->id,
        'user_id' => $user->id,
        'status' => $status,
    ]);
    $order->items()->create([
        'item_id' => $item->id,
        'nama' => $item->nama,
        'harga' => 10000,
        'jumlah' => $jumlah,
    ]);

    return $order;
}

/** @return array<string, mixed> */
function dataTerimaUntukGR(PurchaseOrder $order, Warehouse $warehouse, int $jumlah): array
{
    return [
        'tanggal' => now()->toDateString(),
        'purchase_order_id' => $order->id,
        'warehouse_id' => $warehouse->id,
        'catatan' => 'Catatan terima',
        'items' => [[
            'purchase_order_item_id' => $order->items->first()->id,
            'jumlah' => $jumlah,
        ]],
    ];
}

it('menerima barang sebagian lalu melunasi PO dan menambah stok', function () {
    $gudang = User::factory()->create(['role' => UserRole::Gudang]);
    $item = itemUntukTesGR();
    $warehouse = Warehouse::create(['kode' => 'GUD-GR-1', 'nama' => 'Gudang Tes']);
    $order = poUntukTesGR($item);
    $stock = app(StockService::class);

    $this->actingAs($gudang)
        ->post(route('goods-receipts.store'), dataTerimaUntukGR($order, $warehouse, 6))
        ->assertRedirect();

    expect($order->fresh()->status)->toBe(OrderStatus::SebagianDiterima)
        ->and($order->items()->first()->jumlah_diterima)->toBe(6)
        ->and($stock->saldo($item->id, $warehouse->id))->toBe(6);

    $this->post(route('goods-receipts.store'), dataTerimaUntukGR($order, $warehouse, 4))
        ->assertRedirect();

    expect($order->fresh()->status)->toBe(OrderStatus::Selesai)
        ->and($stock->saldo($item->id, $warehouse->id))->toBe(10)
        ->and(StockMovement::count())->toBe(2);
});

it('menolak penerimaan melebihi sisa PO', function () {
    $gudang = User::factory()->create(['role' => UserRole::Gudang]);
    $warehouse = Warehouse::create(['kode' => 'GUD-GR-2', 'nama' => 'Gudang Tes 2']);
    $order = poUntukTesGR(itemUntukTesGR(), OrderStatus::Terbit, 5);

    $this->actingAs($gudang)
        ->post(route('goods-receipts.store'), dataTerimaUntukGR($order, $warehouse, 6))
        ->assertSessionHasErrors('items');

    expect($order->items()->first()->jumlah_diterima)->toBe(0)
        ->and(StockMovement::count())->toBe(0);
});

it('menolak penerimaan dari PO draft', function () {
    $gudang = User::factory()->create(['role' => UserRole::Gudang]);
    $warehouse = Warehouse::create(['kode' => 'GUD-GR-3', 'nama' => 'Gudang Tes 3']);
    $order = poUntukTesGR(itemUntukTesGR(), OrderStatus::Draft, 5);

    $this->actingAs($gudang)
        ->post(route('goods-receipts.store'), dataTerimaUntukGR($order, $warehouse, 5))
        ->assertSessionHasErrors('purchase_order_id');
});

it('menyesuaikan stok dan menolak penyesuaian yang membuat saldo negatif', function () {
    $gudang = User::factory()->create(['role' => UserRole::Gudang]);
    $item = itemUntukTesGR();
    $warehouse = Warehouse::create(['kode' => 'GUD-GR-4', 'nama' => 'Gudang Tes 4']);
    $stock = app(StockService::class);

    StockMovement::create([
        'item_id' => $item->id,
        'warehouse_id' => $warehouse->id,
        'jenis' => MovementType::Masuk,
        'quantity' => 5,
        'reference' => 'GR-TEST-0001',
        'user_id' => $gudang->id,
        'waktu' => now(),
    ]);

    $this->actingAs($gudang)
        ->post(route('stock.adjust.store'), [
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => -3,
            'catatan' => 'Hitung fisik',
        ])
        ->assertRedirect();

    expect($stock->saldo($item->id, $warehouse->id))->toBe(2);

    $this->post(route('stock.adjust.store'), [
        'item_id' => $item->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => -5,
        'catatan' => 'Salah hitung',
    ])->assertSessionHasErrors('quantity');

    expect($stock->saldo($item->id, $warehouse->id))->toBe(2);
});

it('menampilkan saldo berjalan kartu stok per barang dan gudang', function () {
    $gudang = User::factory()->create(['role' => UserRole::Gudang]);
    $itemPertama = itemUntukTesGR();
    $itemKedua = itemUntukTesGR();
    $warehouse = Warehouse::create(['kode' => 'GUD-GR-5', 'nama' => 'Gudang Tes 5']);

    foreach ([[$itemPertama, 10, now()->subHour()], [$itemKedua, 3, now()]] as [$item, $jumlah, $waktu]) {
        StockMovement::create([
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'jenis' => MovementType::Masuk,
            'quantity' => $jumlah,
            'reference' => 'GR-KARTU-'.fake()->unique()->numerify('####'),
            'user_id' => $gudang->id,
            'waktu' => $waktu,
        ]);
    }

    $this->actingAs($gudang)
        ->get(route('stock.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Stock/Index')
            ->where('rows.0.saldo', 3)
            ->where('rows.1.saldo', 10)
            ->where('saldoAkhir', 13));
});
