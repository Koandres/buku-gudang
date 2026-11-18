<?php

namespace App\Enums;

enum RequestStatus: string
{
    case Draft = 'draft';
    case Diajukan = 'diajukan';
    case Disetujui = 'disetujui';
    case Ditolak = 'ditolak';
    case Diproses = 'diproses';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Diajukan => 'Diajukan',
            self::Disetujui => 'Disetujui',
            self::Ditolak => 'Ditolak',
            self::Diproses => 'Diproses',
        };
    }

    /** Hanya tiga nuansa: netral, aksen, peringatan. */
    public function tone(): string
    {
        return match ($this) {
            self::Draft, self::Ditolak => 'netral',
            self::Diajukan, self::Disetujui, self::Diproses => 'aksen',
        };
    }
}
