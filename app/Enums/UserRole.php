<?php

namespace App\Enums;

enum UserRole: string
{
    case Pemohon = 'pemohon';
    case Penyetuju = 'penyetuju';
    case Pembelian = 'pembelian';
    case Gudang = 'gudang';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Pemohon => 'Pemohon',
            self::Penyetuju => 'Penyetuju',
            self::Pembelian => 'Pembelian',
            self::Gudang => 'Gudang',
            self::Admin => 'Admin',
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
