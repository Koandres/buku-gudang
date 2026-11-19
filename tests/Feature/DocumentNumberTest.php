<?php

use App\Models\DocumentSequence;
use App\Services\DocumentNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('menghasilkan nomor dokumen berturut-turut tanpa duplikat', function (string $type, string $prefix) {
    $periode = now()->format('Ym');

    $nomor = DB::transaction(function () use ($type): array {
        $service = app(DocumentNumberService::class);

        return collect(range(1, 5))
            ->map(fn (): string => $service->next($type))
            ->all();
    });

    $diharapkan = array_map(
        fn (int $urutan): string => sprintf('%s-%s-%04d', $prefix, $periode, $urutan),
        range(1, 5),
    );

    expect($nomor)->toBe($diharapkan)
        ->and(count(array_unique($nomor)))->toBe(5)
        ->and(DocumentSequence::where(['prefix' => $prefix, 'period' => $periode])->sole()->last_number)->toBe(5);
})->with([
    'PR' => ['PR', 'PR'],
    'PO' => ['PO', 'PO'],
    'GR' => ['GR', 'GR'],
    'ADJ' => ['ADJ', 'ADJ'],
]);
