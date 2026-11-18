<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\RequestStatus;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ReportService
{
    /** @return array<string, int> */
    public function statistik(): array
    {
        return [
            'menunggu' => PurchaseRequest::where('status', RequestStatus::Diajukan)->count(),
            'poTerbuka' => PurchaseOrder::whereIn('status', [
                OrderStatus::Draft,
                OrderStatus::Terbit,
                OrderStatus::SebagianDiterima,
            ])->count(),
            'stokMinimum' => $this->perluDipesan()->count(),
            'totalNilaiPo' => $this->totalNilaiPo(),
            'totalBarang' => Item::count(),
            'totalSupplier' => Supplier::count(),
            'totalGudang' => Warehouse::count(),
        ];
    }

    /** @return array<int, array{label: string, total: int}> */
    public function chartPembelian(int $bulan = 6): array
    {
        $awal = now()->startOfMonth()->subMonths($bulan - 1);
        $data = collect(range(0, $bulan - 1))->mapWithKeys(function (int $offset) use ($awal): array {
            $periode = $awal->copy()->addMonths($offset);

            return [$periode->format('Y-m') => ['label' => $periode->format('M'), 'total' => 0]];
        });

        PurchaseOrder::query()
            ->with('items')
            ->where('status', '!=', OrderStatus::Dibatalkan)
            ->where('tanggal', '>=', $awal->toDateString())
            ->get()
            ->each(function (PurchaseOrder $order) use ($data): void {
                $kunci = $order->tanggal->format('Y-m');
                $entri = $data->get($kunci);

                if ($entri === null) {
                    return;
                }

                $entri['total'] += $order->items->sum(fn ($item): int => $item->harga * $item->jumlah);
                $data->put($kunci, $entri);
            });

        return $data->values()->all();
    }

    /** @return array<string, mixed> */
    public function ringkasan(): array
    {
        return [
            'pr' => collect(RequestStatus::cases())->map(fn (RequestStatus $status): array => [
                'label' => $status->label(),
                'jumlah' => PurchaseRequest::where('status', $status)->count(),
            ])->all(),
            'po' => collect(OrderStatus::cases())->map(fn (OrderStatus $status): array => [
                'label' => $status->label(),
                'jumlah' => PurchaseOrder::where('status', $status)->count(),
            ])->all(),
            'angka' => [
                ['label' => 'Total nilai PO (di luar yang dibatalkan)', 'nilai' => $this->totalNilaiPo()],
                ['label' => 'Jumlah barang', 'nilai' => Item::count()],
                ['label' => 'Jumlah supplier', 'nilai' => Supplier::count()],
                ['label' => 'Jumlah gudang', 'nilai' => Warehouse::count()],
            ],
        ];
    }

    /** @return Collection<int, array<string, mixed>> */
    public function outstanding(): Collection
    {
        return PurchaseOrder::query()
            ->with(['supplier:id,nama', 'items'])
            ->whereIn('status', [OrderStatus::Draft, OrderStatus::Terbit, OrderStatus::SebagianDiterima])
            ->orderBy('tanggal')
            ->get()
            ->map(fn (PurchaseOrder $order): array => [
                'nomor' => $order->nomor,
                'tanggal' => $order->tanggal->toDateString(),
                'supplier' => $order->supplier?->nama,
                'sisa' => $order->items->sum(fn ($item): int => $item->sisa()),
                'nilai_sisa' => $order->items->sum(fn ($item): int => $item->sisa() * $item->harga),
                'status' => $order->status->label(),
            ])->values();
    }

    /** @return Collection<int, array<string, mixed>> */
    public function perSupplier(): Collection
    {
        return PurchaseOrder::query()
            ->with(['supplier:id,kode,nama', 'items'])
            ->where('status', '!=', OrderStatus::Dibatalkan)
            ->get()
            ->groupBy('supplier_id')
            ->map(function ($orders, int $supplierId): array {
                $supplier = $orders->first()?->supplier;

                return [
                    'kode' => $supplier?->kode,
                    'nama' => $supplier?->nama,
                    'jumlah_po' => $orders->count(),
                    'total_nilai' => $orders->sum(fn (PurchaseOrder $order): int => $order->items->sum(
                        fn ($item): int => $item->harga * $item->jumlah,
                    )),
                    'total_diterima' => $orders->sum(fn (PurchaseOrder $order): int => $order->items->sum(
                        fn ($item): int => $item->harga * $item->jumlah_diterima,
                    )),
                ];
            })
            ->values()
            ->sortBy('nama');
    }

    /** @return Collection<int, array<string, mixed>> */
    public function stokPerGudang(): Collection
    {
        $saldo = $this->saldoPerItemGudang();
        $itemIds = $saldo->keys()->map(fn (string $kunci): int => (int) explode(':', $kunci)[0])->unique()->all();
        $gudangIds = $saldo->keys()->map(fn (string $kunci): int => (int) explode(':', $kunci)[1])->unique()->all();

        if ($itemIds === [] || $gudangIds === []) {
            return collect();
        }

        $items = Item::whereIn('id', $itemIds)->get()->keyBy('id');
        $gudangs = Warehouse::whereIn('id', $gudangIds)->get()->keyBy('id');

        return $saldo
            ->filter(fn (int $jumlah): bool => $jumlah !== 0)
            ->map(function (int $jumlah, string $kunci) use ($items, $gudangs): array {
                [$itemId, $gudangId] = explode(':', $kunci);
                $item = $items->get((int) $itemId);
                $gudang = $gudangs->get((int) $gudangId);

                return [
                    'gudang' => $gudang?->nama,
                    'kode' => $item?->kode,
                    'nama' => $item?->nama,
                    'satuan' => $item?->satuan,
                    'saldo' => $jumlah,
                ];
            })
            ->values()
            ->sortBy(fn (array $baris): string => (string) $baris['gudang'].'|'.(string) $baris['kode']);
    }

    /** @return Collection<int, array<string, mixed>> */
    public function perluDipesan(): Collection
    {
        $total = [];
        foreach ($this->saldoPerItemGudang() as $kunci => $jumlah) {
            $itemId = (int) explode(':', $kunci)[0];
            $total[$itemId] = ($total[$itemId] ?? 0) + $jumlah;
        }

        return Item::query()
            ->active()
            ->orderBy('kode')
            ->get()
            ->filter(fn (Item $item): bool => ($total[$item->id] ?? 0) < $item->stok_minimum)
            ->map(fn (Item $item): array => [
                'kode' => $item->kode,
                'nama' => $item->nama,
                'satuan' => $item->satuan,
                'saldo' => $total[$item->id] ?? 0,
                'minimum' => $item->stok_minimum,
                'kurang' => max(0, $item->stok_minimum - ($total[$item->id] ?? 0)),
                'harga_acuan' => $item->harga_acuan,
                'estimasi' => max(0, $item->stok_minimum - ($total[$item->id] ?? 0)) * $item->harga_acuan,
            ])->values();
    }

    /** @return Collection<string, int> */
    private function saldoPerItemGudang(): Collection
    {
        $saldo = collect();

        StockMovement::query()
            ->get(['item_id', 'warehouse_id', 'jenis', 'quantity'])
            ->each(function (StockMovement $movement) use ($saldo): void {
                $kunci = $movement->item_id.':'.$movement->warehouse_id;
                $saldo->put(
                    $kunci,
                    ($saldo->get($kunci, 0) + $movement->quantity * $movement->jenis->tanda()),
                );
            });

        return $saldo;
    }

    private function totalNilaiPo(): int
    {
        return PurchaseOrder::query()
            ->with('items')
            ->where('status', '!=', OrderStatus::Dibatalkan)
            ->get()
            ->sum(fn (PurchaseOrder $order): int => $order->items->sum(
                fn ($item): int => $item->harga * $item->jumlah,
            ));
    }

    /**
     * @return array{headers: array<int, string>, rows: array<int, array<int, mixed>>}
     */
    public function dataEkspor(string $jenis): array
    {
        return match ($jenis) {
            'ringkasan' => [
                'headers' => ['Kelompok', 'Keterangan', 'Nilai'],
                'rows' => $this->barisRingkasan(),
            ],
            'outstanding' => [
                'headers' => ['Nomor PO', 'Tanggal', 'Supplier', 'Sisa jumlah', 'Nilai sisa', 'Status'],
                'rows' => $this->outstanding()->map(fn (array $baris): array => [
                    $baris['nomor'], $baris['tanggal'], $baris['supplier'],
                    $baris['sisa'], $baris['nilai_sisa'], $baris['status'],
                ])->all(),
            ],
            'suppliers' => [
                'headers' => ['Kode', 'Supplier', 'Jumlah PO', 'Total nilai', 'Total diterima'],
                'rows' => $this->perSupplier()->map(fn (array $baris): array => [
                    $baris['kode'], $baris['nama'], $baris['jumlah_po'],
                    $baris['total_nilai'], $baris['total_diterima'],
                ])->all(),
            ],
            'stock' => [
                'headers' => ['Gudang', 'Kode', 'Barang', 'Satuan', 'Saldo'],
                'rows' => $this->stokPerGudang()->map(fn (array $baris): array => [
                    $baris['gudang'], $baris['kode'], $baris['nama'], $baris['satuan'], $baris['saldo'],
                ])->all(),
            ],
            'reorder' => [
                'headers' => ['Kode', 'Barang', 'Satuan', 'Saldo', 'Minimum', 'Kurang', 'Harga acuan', 'Estimasi'],
                'rows' => $this->perluDipesan()->map(fn (array $baris): array => [
                    $baris['kode'], $baris['nama'], $baris['satuan'], $baris['saldo'],
                    $baris['minimum'], $baris['kurang'], $baris['harga_acuan'], $baris['estimasi'],
                ])->all(),
            ],
            default => throw ValidationException::withMessages(['jenis' => 'Jenis laporan tidak dikenal.']),
        };
    }

    /** @return array<int, array<int, mixed>> */
    private function barisRingkasan(): array
    {
        $ringkasan = $this->ringkasan();
        $baris = [];

        foreach ($ringkasan['pr'] as $item) {
            $baris[] = ['PR', $item['label'], $item['jumlah']];
        }
        foreach ($ringkasan['po'] as $item) {
            $baris[] = ['PO', $item['label'], $item['jumlah']];
        }
        foreach ($ringkasan['angka'] as $item) {
            $baris[] = ['Umum', $item['label'], $item['nilai']];
        }

        return $baris;
    }
}
