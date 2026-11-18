<?php

use App\Enums\MovementType;
use App\Enums\OrderStatus;
use App\Enums\RequestStatus;
use App\Models\ActivityLog;
use App\Models\DocumentSequence;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\StockMovement;
use App\Services\DocumentNumberService;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/** @return array<string, int> */
function hitungDataContoh(): array
{
    return [
        'pr' => PurchaseRequest::count(),
        'po' => PurchaseOrder::count(),
        'gr' => GoodsReceipt::count(),
        'mutasi' => StockMovement::count(),
        'log' => ActivityLog::count(),
        'urutan' => DocumentSequence::count(),
    ];
}

/** @return array<int, string> */
function daftarUrutan(): array
{
    return DocumentSequence::query()
        ->orderBy('prefix')
        ->orderBy('period')
        ->get()
        ->map(fn (DocumentSequence $sequence): string => "{$sequence->prefix}-{$sequence->period}-{$sequence->last_number}")
        ->all();
}

it('mengisi data contoh enam bulan dengan seluruh status dokumen', function () {
    $this->seed();

    expect(PurchaseRequest::count())->toBeGreaterThanOrEqual(10);

    foreach (RequestStatus::cases() as $status) {
        expect(PurchaseRequest::where('status', $status)->exists())->toBeTrue("Status PR {$status->value} tidak ada.");
    }

    foreach (OrderStatus::cases() as $status) {
        expect(PurchaseOrder::where('status', $status)->exists())->toBeTrue("Status PO {$status->value} tidak ada.");
    }

    $bulan = PurchaseRequest::query()
        ->get()
        ->map(fn (PurchaseRequest $request): string => $request->tanggal->format('Y-m'))
        ->unique();

    expect($bulan->count())->toBeGreaterThanOrEqual(6)
        ->and(GoodsReceipt::count())->toBeGreaterThanOrEqual(3)
        ->and(StockMovement::where('jenis', MovementType::Masuk)->exists())->toBeTrue()
        ->and(StockMovement::where('jenis', MovementType::Keluar)->exists())->toBeTrue()
        ->and(StockMovement::where('jenis', MovementType::Penyesuaian)->exists())->toBeTrue()
        ->and(ActivityLog::count())->toBeGreaterThan(10);
});

it('dapat dijalankan ulang tanpa menambah data ganda', function () {
    $this->seed();
    $sebelum = hitungDataContoh();
    $urutanSebelum = daftarUrutan();

    $this->seed();

    expect(hitungDataContoh())->toBe($sebelum)
        ->and(daftarUrutan())->toBe($urutanSebelum);
});

it('menyelaraskan nomor urut dengan nomor dokumen terbesar', function () {
    $this->seed();

    $peta = [
        'PR' => [PurchaseRequest::class, 'nomor'],
        'PO' => [PurchaseOrder::class, 'nomor'],
        'GR' => [GoodsReceipt::class, 'nomor'],
        'ADJ' => [StockMovement::class, 'reference'],
    ];

    foreach ($peta as $jenis => [$model, $kolom]) {
        $prefix = (string) config("erp.document_prefixes.$jenis");
        $maksimum = [];

        $model::query()
            ->where($kolom, 'like', $prefix.'-%')
            ->get([$kolom])
            ->each(function (object $baris) use ($prefix, &$maksimum, $kolom): void {
                if (! preg_match('/^'.preg_quote($prefix, '/').'-(\d{6})-(\d+)$/', (string) $baris->{$kolom}, $cocok)) {
                    return;
                }

                $maksimum[$cocok[1]] = max($maksimum[$cocok[1]] ?? 0, (int) $cocok[2]);
            });

        expect($maksimum)->not->toBeEmpty();

        foreach ($maksimum as $periode => $terbesar) {
            $sequence = DocumentSequence::where(['prefix' => $prefix, 'period' => $periode])->first();

            expect($sequence)->not->toBeNull()
                ->and($sequence->last_number)->toBeGreaterThanOrEqual($terbesar);
        }

        $berikutnya = app(DocumentNumberService::class)->next($jenis);
        expect($model::query()->where($kolom, $berikutnya)->exists())->toBeFalse();
    }
});

it('menghasilkan laporan dan saldo yang wajar dari data contoh', function () {
    $this->seed();

    $laporan = app(ReportService::class);
    $ringkasan = $laporan->ringkasan();

    expect(collect($ringkasan['pr'])->every(fn (array $baris): bool => $baris['jumlah'] > 0))->toBeTrue()
        ->and(collect($ringkasan['po'])->every(fn (array $baris): bool => $baris['jumlah'] > 0))->toBeTrue()
        ->and($laporan->outstanding())->not->toBeEmpty()
        ->and($laporan->perSupplier())->not->toBeEmpty()
        ->and($laporan->stokPerGudang())->not->toBeEmpty()
        ->and($laporan->perluDipesan())->not->toBeEmpty();

    $saldo = [];
    foreach (StockMovement::query()->get() as $mutasi) {
        $kunci = $mutasi->item_id.':'.$mutasi->warehouse_id;
        $saldo[$kunci] = ($saldo[$kunci] ?? 0) + $mutasi->quantity * $mutasi->jenis->tanda();
    }

    expect($saldo)->not->toBeEmpty()
        ->and(collect($saldo)->every(fn (int $jumlah): bool => $jumlah >= 0))->toBeTrue();
});
