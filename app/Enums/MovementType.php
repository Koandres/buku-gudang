<?php

namespace App\Enums;

enum MovementType: string
{
    case Masuk = 'masuk';
    case Keluar = 'keluar';
    case Penyesuaian = 'penyesuaian';

    public function label(): string
    {
        return match ($this) {
            self::Masuk => 'Masuk',
            self::Keluar => 'Keluar',
            self::Penyesuaian => 'Penyesuaian',
        };
    }

    public function tanda(): int
    {
        return match ($this) {
            self::Masuk, self::Penyesuaian => 1,
            self::Keluar => -1,
        };
    }
}
