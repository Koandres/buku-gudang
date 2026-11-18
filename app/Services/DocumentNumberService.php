<?php

namespace App\Services;

use App\Models\DocumentSequence;
use Illuminate\Database\QueryException;

class DocumentNumberService
{
    /**
     * Dipanggil di dalam transaksi. lockForUpdate() mengunci baris sequence;
     * pada SQLite transaksi IMMEDIATE + indeks unik mencegah nomor ganda.
     */
    public function next(string $type): string
    {
        $prefix = config("erp.document_prefixes.$type");
        $period = now()->format('Ym');

        $sequence = DocumentSequence::query()
            ->where(['prefix' => $prefix, 'period' => $period])
            ->lockForUpdate()
            ->first();

        if (! $sequence) {
            try {
                $sequence = DocumentSequence::create([
                    'prefix' => $prefix,
                    'period' => $period,
                    'last_number' => 0,
                ]);
            } catch (QueryException) {
                // Baris bisa dibuat proses lain di antara first() dan create().
                $sequence = DocumentSequence::query()
                    ->where(['prefix' => $prefix, 'period' => $period])
                    ->lockForUpdate()
                    ->first();
            }

            if (! $sequence) {
                throw new \RuntimeException("Gagal membuat nomor dokumen untuk $prefix-$period.");
            }
        }

        $sequence->increment('last_number');

        return strtr(config('erp.document_format'), [
            '{prefix}' => $prefix,
            '{period}' => $period,
            '{sequence:04d}' => str_pad((string) $sequence->last_number, 4, '0', STR_PAD_LEFT),
        ]);
    }
}
