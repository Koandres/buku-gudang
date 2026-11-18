<?php

use App\Enums\MovementType;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

/** @return array<string, mixed> */
function siapDataLaporan(): array
{
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $gudang = User::factory()->create(['role' => UserRole::Gudang]);
    $supplier = Supplier::create([
        'kode' => 'SUP-LAP-1', 'nama' => 'Supplier Laporan', 'kontak' => 'Kontak',
        'telepon' => '0211111111', 'alamat' => 'Alamat', 'aktif' => true,
    ]);
    $item = Item::create([
        'kode' => 'BRG-LAP-1', 'nama' => 'Barang Laporan', 'satuan' => 'pcs',
        'harga_acuan' => 10000, 'stok_minimum' => 10, 'aktif' => true,
    ]);
    $warehouse = Warehouse::create(['kode' => 'GUD-LAP-1', 'nama' => 'Gudang Laporan']);
    $order = PurchaseOrder::create([
        'nomor' => 'PO-LAP-0001', 'tanggal' => now()->toDateString(),
        'supplier_id' => $supplier->id, 'user_id' => $admin->id, 'status' => OrderStatus::Terbit,
    ]);
    $order->items()->create([
        'item_id' => $item->id, 'nama' => $item->nama, 'harga' => 10000,
        'jumlah' => 8, 'jumlah_diterima' => 8,
    ]);
    StockMovement::create([
        'item_id' => $item->id, 'warehouse_id' => $warehouse->id,
        'jenis' => MovementType::Masuk, 'quantity' => 8,
        'reference' => 'GR-LAP-0001', 'user_id' => $gudang->id, 'waktu' => now(),
    ]);
    ActivityLog::create([
        'user_id' => $admin->id, 'aksi' => 'Buat PO', 'objek' => 'PO: PO-LAP-0001',
        'ringkasan' => 'Dari PR: PR-LAP-0001', 'waktu' => now(),
    ]);

    return compact('admin', 'gudang', 'supplier', 'item', 'warehouse', 'order');
}

it('menghitung statistik dan grafik dari data nyata', function () {
    $data = siapDataLaporan();
    $laporan = app(ReportService::class);

    expect($laporan->statistik()['totalNilaiPo'])->toBe(80000)
        ->and($laporan->statistik()['poTerbuka'])->toBe(1)
        ->and($laporan->statistik()['stokMinimum'])->toBe(1)
        ->and($laporan->chartPembelian()[5]['total'])->toBe(80000);

    $this->actingAs($data['admin'])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Dashboard')
            ->where('stats.totalNilaiPo', 80000)
            ->where('stats.poTerbuka', 1)
            ->where('stats.stokMinimum', 1)
            ->where('chart.5.total', 80000));
});

it('menampilkan kelima halaman laporan', function () {
    $data = siapDataLaporan();
    $this->actingAs($data['admin']);

    $this->get(route('reports.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Reports/Index')
            ->where('ringkasan.po.1.jumlah', 1));
    $this->get(route('reports.outstanding'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Reports/Outstanding')
            ->where('rows.0.nomor', $data['order']->nomor));
    $this->get(route('reports.suppliers'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Reports/Suppliers')
            ->where('rows.0.nama', $data['supplier']->nama));
    $this->get(route('reports.stock'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Reports/Stock')
            ->where('rows.0.kode', $data['item']->kode));
    $this->get(route('reports.reorder'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Reports/Reorder')
            ->where('rows.0.kode', $data['item']->kode));
});

it('mengekspor setiap laporan sebagai CSV', function () {
    $data = siapDataLaporan();
    $this->actingAs($data['admin']);

    $judul = [
        'ringkasan' => 'Kelompok',
        'outstanding' => 'Nomor PO',
        'suppliers' => 'Kode',
        'stock' => 'Kode',
        'reorder' => 'Kode',
    ];

    foreach ($judul as $jenis => $kop) {
        $response = $this->get(route('reports.export', ['jenis' => $jenis]))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $isi = $response->streamedContent();
        expect($isi)->toContain($kop)
            ->and($isi)->toContain("\xEF\xBB\xBF");
    }
});

it('hanya admin yang dapat melihat log aktivitas', function () {
    $data = siapDataLaporan();

    $this->actingAs($data['gudang'])->get(route('activities.index'))->assertForbidden();

    $this->actingAs($data['admin'])
        ->get(route('activities.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Activities/Index')
            ->where('logs.data.0.aksi', 'Buat PO')
            ->where('logs.data.0.objek', 'PO: PO-LAP-0001'));
});
