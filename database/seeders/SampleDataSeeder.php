<?php

namespace Database\Seeders;

use App\Enums\MovementType;
use App\Enums\OrderStatus;
use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\DocumentSequence;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SampleDataSeeder extends Seeder
{
    /** Nomor contoh mulai dari 0101 agar tidak bentrok dengan dokumen uji. */
    private const NOMOR_AWAL = 101;

    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $this->call(RoleAccountSeeder::class);

        DB::transaction(function (): void {
            $users = $this->users();

            $suppliers = collect([
                ['kode' => 'SUP-C01', 'nama' => 'CV Sinar Abadi', 'kontak' => 'Budi Santoso', 'telepon' => '021-555-0101', 'alamat' => 'Jl. Merdeka No. 10, Jakarta', 'aktif' => true],
                ['kode' => 'SUP-C02', 'nama' => 'PT Maju Bersama', 'kontak' => 'Rina Wijaya', 'telepon' => '021-555-0102', 'alamat' => 'Jl. Sudirman No. 22, Jakarta', 'aktif' => true],
                ['kode' => 'SUP-C03', 'nama' => 'Toko Gudang Jaya', 'kontak' => 'Agus Pratama', 'telepon' => '021-555-0103', 'alamat' => 'Jl. Pahlawan No. 5, Bandung', 'aktif' => true],
                ['kode' => 'SUP-C04', 'nama' => 'CV Prima Niaga', 'kontak' => 'Dewi Anggraini', 'telepon' => '021-555-0104', 'alamat' => 'Jl. Diponegoro No. 18, Semarang', 'aktif' => true],
            ])->mapWithKeys(fn (array $row, int $index): array => [
                $index => Supplier::updateOrCreate(['kode' => $row['kode']], $row),
            ]);

            $items = collect([
                ['kode' => 'BRG-C01', 'nama' => 'Kertas A4', 'satuan' => 'pcs', 'harga_acuan' => 25000, 'stok_minimum' => 10, 'aktif' => true],
                ['kode' => 'BRG-C02', 'nama' => 'Tinta Printer', 'satuan' => 'pcs', 'harga_acuan' => 185000, 'stok_minimum' => 4, 'aktif' => true],
                ['kode' => 'BRG-C03', 'nama' => 'Map Plastik', 'satuan' => 'box', 'harga_acuan' => 45000, 'stok_minimum' => 20, 'aktif' => true],
                ['kode' => 'BRG-C04', 'nama' => 'Steples', 'satuan' => 'box', 'harga_acuan' => 12000, 'stok_minimum' => 15, 'aktif' => true],
                ['kode' => 'BRG-C05', 'nama' => 'Buku Kas', 'satuan' => 'pcs', 'harga_acuan' => 35000, 'stok_minimum' => 8, 'aktif' => true],
                ['kode' => 'BRG-C06', 'nama' => 'Spidol', 'satuan' => 'set', 'harga_acuan' => 22000, 'stok_minimum' => 12, 'aktif' => true],
            ])->mapWithKeys(fn (array $row, int $index): array => [
                $index => Item::updateOrCreate(['kode' => $row['kode']], $row),
            ]);

            $warehouses = collect([
                ['kode' => 'GUD-C01', 'nama' => 'Gudang Pusat Contoh'],
                ['kode' => 'GUD-C02', 'nama' => 'Gudang Cabang Contoh'],
            ])->mapWithKeys(fn (array $row, int $index): array => [
                $index => Warehouse::updateOrCreate(['kode' => $row['kode']], $row),
            ]);

            $requests = $this->seedPermintaan($users, $items);

            $orders = $this->seedPesanan($users, $suppliers, $items, $requests);

            $this->seedPenerimaan($users, $warehouses, $orders);

            $this->seedMutasiLain($users, $items, $warehouses);
        });

        $this->sinkronkanNomor();
    }

    /**
     * @param  Collection<string, User>  $users
     * @param  Collection<int, Item>  $items
     * @return Collection<int, PurchaseRequest>
     */
    private function seedPermintaan(Collection $users, Collection $items): Collection
    {
        $definisi = [
            ['bulan' => 0, 'hari' => 5, 'keperluan' => 'Kertas untuk rapat mingguan', 'status' => RequestStatus::Draft, 'baris' => [[0, 20, 0]]],
            ['bulan' => 1, 'hari' => 11, 'keperluan' => 'Tinta printer ruang administrasi', 'status' => RequestStatus::Diajukan, 'baris' => [[1, 8, 0]]],
            ['bulan' => 2, 'hari' => 9, 'keperluan' => 'Map plastik arsip kuartal', 'status' => RequestStatus::Ditolak, 'alasan' => 'Anggaran tidak tersedia pada kuartal ini.', 'baris' => [[2, 40, 0]]],
            ['bulan' => 3, 'hari' => 14, 'keperluan' => 'Steples untuk bagian keuangan', 'status' => RequestStatus::Disetujui, 'baris' => [[3, 25, 0]]],
            ['bulan' => 3, 'hari' => 20, 'keperluan' => 'Buku kas cabang', 'status' => RequestStatus::Diproses, 'baris' => [[4, 30, 30]]],
            ['bulan' => 4, 'hari' => 6, 'keperluan' => 'Kertas A4 untuk produksi', 'status' => RequestStatus::Diproses, 'baris' => [[0, 50, 50]]],
            ['bulan' => 4, 'hari' => 12, 'keperluan' => 'Spidol presentasi', 'status' => RequestStatus::Diproses, 'baris' => [[5, 40, 40]]],
            ['bulan' => 4, 'hari' => 18, 'keperluan' => 'Tinta printer cadangan', 'status' => RequestStatus::Disetujui, 'baris' => [[1, 12, 0]]],
            ['bulan' => 5, 'hari' => 4, 'keperluan' => 'Map plastik untuk arsip gudang', 'status' => RequestStatus::Diproses, 'baris' => [[2, 60, 60]]],
            ['bulan' => 5, 'hari' => 12, 'keperluan' => 'Steples tambahan', 'status' => RequestStatus::Diajukan, 'baris' => [[3, 18, 0]]],
        ];

        return collect($definisi)->map(function (array $row, int $index) use ($users, $items): PurchaseRequest {
            $request = PurchaseRequest::updateOrCreate(
                ['nomor' => $this->nomor('PR', $row['bulan'], $index + 1)],
                [
                    'tanggal' => $this->tanggal($row['bulan'], $row['hari']),
                    'user_id' => $users->get('pemohon')?->id,
                    'keperluan' => $row['keperluan'],
                    'status' => $row['status'],
                    'alasan_ditolak' => $row['alasan'] ?? null,
                ],
            );

            foreach ($row['baris'] as [$itemIndex, $jumlah, $dipesan]) {
                PurchaseRequestItem::updateOrCreate(
                    [
                        'purchase_request_id' => $request->id,
                        'item_id' => $items->get($itemIndex)?->id,
                    ],
                    ['jumlah' => $jumlah, 'jumlah_dipesan' => $dipesan],
                );
            }

            $this->logPermintaan($users, $request, $row);

            return $request->load('items');
        });
    }

    /**
     * @param  Collection<string, User>  $users
     * @param  Collection<int, Supplier>  $suppliers
     * @param  Collection<int, Item>  $items
     * @param  Collection<int, PurchaseRequest>  $requests
     * @return Collection<int, PurchaseOrder>
     */
    private function seedPesanan(
        Collection $users,
        Collection $suppliers,
        Collection $items,
        Collection $requests,
    ): Collection {
        $definisi = [
            ['pr' => 4, 'bulan' => 3, 'hari' => 22, 'urutan' => 1, 'supplier' => 0, 'item' => 4, 'harga' => 35000, 'jumlah' => 30, 'diterima' => 0, 'status' => OrderStatus::Draft, 'catatan' => 'Menunggu konfirmasi harga.'],
            ['pr' => 5, 'bulan' => 4, 'hari' => 8, 'urutan' => 1, 'supplier' => 1, 'item' => 0, 'harga' => 25000, 'jumlah' => 50, 'diterima' => 0, 'status' => OrderStatus::Terbit],
            ['pr' => 6, 'bulan' => 4, 'hari' => 14, 'urutan' => 2, 'supplier' => 2, 'item' => 5, 'harga' => 22000, 'jumlah' => 40, 'diterima' => 25, 'status' => OrderStatus::SebagianDiterima, 'catatan' => 'Pengiriman bertahap.'],
            ['pr' => 7, 'bulan' => 4, 'hari' => 20, 'urutan' => 3, 'supplier' => 3, 'item' => 1, 'harga' => 185000, 'jumlah' => 12, 'diterima' => 0, 'status' => OrderStatus::Dibatalkan, 'catatan' => 'Supplier tidak dapat memenuhi.'],
            ['pr' => 8, 'bulan' => 5, 'hari' => 6, 'urutan' => 1, 'supplier' => 0, 'item' => 2, 'harga' => 45000, 'jumlah' => 60, 'diterima' => 60, 'status' => OrderStatus::Selesai],
        ];

        return collect($definisi)->map(function (array $row) use ($users, $suppliers, $items, $requests): PurchaseOrder {
            $request = $requests->get($row['pr']);
            $order = PurchaseOrder::updateOrCreate(
                ['nomor' => $this->nomor('PO', $row['bulan'], $row['urutan'])],
                [
                    'tanggal' => $this->tanggal($row['bulan'], $row['hari']),
                    'supplier_id' => $suppliers->get($row['supplier'])?->id,
                    'purchase_request_id' => $request?->id,
                    'user_id' => $users->get('pembelian')?->id,
                    'status' => $row['status'],
                    'catatan' => $row['catatan'] ?? null,
                ],
            );

            $requestItem = $request?->items->first();
            PurchaseOrderItem::updateOrCreate(
                [
                    'purchase_order_id' => $order->id,
                    'purchase_request_item_id' => $requestItem?->id,
                ],
                [
                    'item_id' => $items->get($row['item'])?->id,
                    'nama' => $items->get($row['item'])?->nama,
                    'harga' => $row['harga'],
                    'jumlah' => $row['jumlah'],
                    'jumlah_diterima' => $row['diterima'],
                ],
            );

            $pembelian = $users->get('pembelian');
            if ($pembelian) {
                $this->log($pembelian, 'Buat PO', "PO: {$order->nomor}", "Dari PR: {$request?->nomor}", $row['bulan'], $row['hari'], 10);

                if ($row['status'] === OrderStatus::Dibatalkan) {
                    $this->log($pembelian, 'Batalkan PO', "PO: {$order->nomor}", 'Sisa PR dikembalikan', $row['bulan'], $row['hari'], 11);
                } elseif ($row['status'] !== OrderStatus::Draft) {
                    $this->log($pembelian, 'Terbitkan PO', "PO: {$order->nomor}", 'Status menjadi terbit', $row['bulan'], $row['hari'], 11);
                }
            }

            return $order->load('items');
        });
    }

    /**
     * @param  Collection<string, User>  $users
     * @param  Collection<int, Warehouse>  $warehouses
     * @param  Collection<int, PurchaseOrder>  $orders
     */
    private function seedPenerimaan(Collection $users, Collection $warehouses, Collection $orders): void
    {
        $definisi = [
            ['po' => 2, 'bulan' => 4, 'hari' => 17, 'urutan' => 1, 'gudang' => 0, 'jumlah' => 15, 'catatan' => 'Penerimaan pertama.'],
            ['po' => 2, 'bulan' => 4, 'hari' => 25, 'urutan' => 2, 'gudang' => 0, 'jumlah' => 10, 'catatan' => 'Penerimaan kedua.'],
            ['po' => 4, 'bulan' => 5, 'hari' => 9, 'urutan' => 1, 'gudang' => 0, 'jumlah' => 60, 'catatan' => 'Penerimaan lengkap.'],
        ];

        $gudang = $users->get('gudang');
        foreach ($definisi as $row) {
            $order = $orders->get($row['po']);
            $receipt = GoodsReceipt::updateOrCreate(
                ['nomor' => $this->nomor('GR', $row['bulan'], $row['urutan'])],
                [
                    'tanggal' => $this->tanggal($row['bulan'], $row['hari']),
                    'purchase_order_id' => $order?->id,
                    'warehouse_id' => $warehouses->get($row['gudang'])?->id,
                    'user_id' => $gudang?->id,
                    'catatan' => $row['catatan'],
                ],
            );

            $orderItem = $order?->items->first();
            GoodsReceiptItem::updateOrCreate(
                [
                    'goods_receipt_id' => $receipt->id,
                    'purchase_order_item_id' => $orderItem?->id,
                ],
                ['jumlah' => $row['jumlah']],
            );

            $this->mutasi(
                $orderItem?->item_id,
                $warehouses->get($row['gudang'])?->id,
                MovementType::Masuk,
                $row['jumlah'],
                $receipt->nomor,
                $row['catatan'],
                $gudang,
                $row['bulan'],
                $row['hari'],
                13,
            );

            if ($gudang) {
                $this->log($gudang, 'Terima barang', "GR: {$receipt->nomor}", "Dari PO: {$order?->nomor}", $row['bulan'], $row['hari'], 14);
            }
        }
    }

    /**
     * @param  Collection<string, User>  $users
     * @param  Collection<int, Item>  $items
     * @param  Collection<int, Warehouse>  $warehouses
     */
    private function seedMutasiLain(Collection $users, Collection $items, Collection $warehouses): void
    {
        $gudang = $users->get('gudang');
        $definisi = [
            ['bulan' => 4, 'hari' => 2, 'reference' => 'SALDO-AWAL-C02', 'item' => 0, 'gudang' => 1, 'jenis' => MovementType::Masuk, 'quantity' => 12, 'catatan' => 'Saldo awal gudang cabang.'],
            ['bulan' => 4, 'hari' => 28, 'reference' => 'KELUAR-C02', 'item' => 0, 'gudang' => 1, 'jenis' => MovementType::Keluar, 'quantity' => 5, 'catatan' => 'Pemakaian cabang.'],
            ['bulan' => 5, 'hari' => 14, 'reference' => $this->nomor('ADJ', 5, 1), 'item' => 2, 'gudang' => 0, 'jenis' => MovementType::Penyesuaian, 'quantity' => -4, 'catatan' => 'Hitung fisik: selisih kurang 4.'],
        ];

        foreach ($definisi as $row) {
            $this->mutasi(
                $items->get($row['item'])?->id,
                $warehouses->get($row['gudang'])?->id,
                $row['jenis'],
                $row['quantity'],
                $row['reference'],
                $row['catatan'],
                $gudang,
                $row['bulan'],
                $row['hari'],
                15,
            );

            if ($row['jenis'] === MovementType::Penyesuaian && $gudang) {
                $this->log($gudang, 'Penyesuaian stok', "ADJ: {$row['reference']}", $row['catatan'], $row['bulan'], $row['hari'], 16);
            }
        }
    }

    /** @param Collection<string, User> $users */
    private function logPermintaan(Collection $users, PurchaseRequest $request, array $row): void
    {
        $pemohon = $users->get('pemohon');
        $penyetuju = $users->get('penyetuju');
        $pembelian = $users->get('pembelian');

        $riwayat = [
            ['Buat PR', $pemohon, $request->keperluan, 8],
        ];

        if ($row['status'] !== RequestStatus::Draft) {
            $riwayat[] = ['Ajukan PR', $pemohon, 'Status menjadi diajukan', 9];
        }

        if (in_array($row['status'], [RequestStatus::Disetujui, RequestStatus::Diproses], true)) {
            $riwayat[] = ['Setujui PR', $penyetuju, 'Status menjadi disetujui', 10];
        }

        if ($row['status'] === RequestStatus::Diproses) {
            $riwayat[] = ['PR diproses', $pembelian, 'Semua baris telah masuk PO', 11];
        }

        if ($row['status'] === RequestStatus::Ditolak) {
            $riwayat[] = ['Tolak PR', $penyetuju, $row['alasan'] ?? null, 10];
        }

        foreach ($riwayat as [$aksi, $user, $ringkasan, $jam]) {
            if ($user) {
                $this->log($user, $aksi, "PR: {$request->nomor}", $ringkasan, $row['bulan'], $row['hari'], $jam);
            }
        }
    }

    private function mutasi(
        ?int $itemId,
        ?int $warehouseId,
        MovementType $jenis,
        int $quantity,
        string $reference,
        ?string $catatan,
        ?User $user,
        int $bulan,
        int $hari,
        int $jam,
    ): void {
        if ($itemId === null || $warehouseId === null || $user === null) {
            return;
        }

        StockMovement::firstOrCreate(
            [
                'reference' => $reference,
                'item_id' => $itemId,
                'warehouse_id' => $warehouseId,
                'jenis' => $jenis,
                'quantity' => $quantity,
            ],
            [
                'catatan' => $catatan,
                'user_id' => $user->id,
                'waktu' => $this->waktu($bulan, $hari, $jam),
            ],
        );
    }

    private function log(
        User $user,
        string $aksi,
        string $objek,
        ?string $ringkasan,
        int $bulan,
        int $hari,
        int $jam,
    ): void {
        ActivityLog::firstOrCreate(
            ['aksi' => $aksi, 'objek' => $objek, 'ringkasan' => $ringkasan],
            ['user_id' => $user->id, 'waktu' => $this->waktu($bulan, $hari, $jam)],
        );
    }

    private function users(): Collection
    {
        $emails = array_map(
            fn (UserRole $role): string => $role->value.'@bukugudang.test',
            UserRole::cases(),
        );

        return User::query()
            ->whereIn('email', $emails)
            ->get()
            ->keyBy(fn (User $user): string => $user->role->value);
    }

    private function waktu(int $bulan, int $hari, int $jam = 9): Carbon
    {
        return now()->startOfMonth()->subMonths(5)
            ->addMonths($bulan)
            ->startOfMonth()
            ->addDays($hari - 1)
            ->setTime($jam, 0);
    }

    private function tanggal(int $bulan, int $hari): string
    {
        return $this->waktu($bulan, $hari)->toDateString();
    }

    private function nomor(string $jenis, int $bulan, int $urutan): string
    {
        return sprintf(
            '%s-%s-%04d',
            config("erp.document_prefixes.$jenis"),
            $this->waktu($bulan, 1)->format('Ym'),
            self::NOMOR_AWAL + $urutan - 1,
        );
    }

    /** Menyesuaikan last_number agar dokumen baru tidak memakai nomor yang sudah dipakai. */
    private function sinkronkanNomor(): void
    {
        $peta = [
            'PR' => [PurchaseRequest::class, 'nomor'],
            'PO' => [PurchaseOrder::class, 'nomor'],
            'GR' => [GoodsReceipt::class, 'nomor'],
            'ADJ' => [StockMovement::class, 'reference'],
        ];

        foreach ($peta as $jenis => [$model, $kolom]) {
            $prefix = (string) config("erp.document_prefixes.$jenis");
            $pola = '/^'.preg_quote($prefix, '/').'-(\d{6})-(\d+)$/';

            $model::query()
                ->where($kolom, 'like', $prefix.'-%')
                ->get([$kolom])
                ->each(function (object $baris) use ($pola, $kolom, $prefix): void {
                    if (! preg_match($pola, (string) $baris->{$kolom}, $cocok)) {
                        return;
                    }

                    $sequence = DocumentSequence::firstOrNew([
                        'prefix' => $prefix,
                        'period' => $cocok[1],
                    ]);
                    $sequence->last_number = max((int) $sequence->last_number, (int) $cocok[2]);
                    $sequence->save();
                });
        }
    }
}
