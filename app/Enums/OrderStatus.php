<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Draft = 'draft';
    case Terbit = 'terbit';
    case SebagianDiterima = 'sebagian_diterima';
    case Selesai = 'selesai';
    case Dibatalkan = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Terbit => 'Terbit',
            self::SebagianDiterima => 'Sebagian diterima',
            self::Selesai => 'Selesai',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Draft, self::Dibatalkan => 'netral',
            self::Terbit, self::Selesai => 'aksen',
            self::SebagianDiterima => 'peringatan',
        };
    }

    public function bisaDiterima(): bool
    {
        return in_array($this, [self::Terbit, self::SebagianDiterima], true);
    }
}
