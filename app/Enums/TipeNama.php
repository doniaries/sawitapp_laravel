<?php

namespace App\Enums;

enum TipeNama: string
{
    case PENJUAL = 'penjual'; // lowercase sesuai database
    case PEKERJA = 'pekerja';
    case USER = 'user';
    case SUPIR = 'supir';
    case LAINNYA = 'lainnya';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENJUAL => 'Penjual',
            self::PEKERJA => 'Pekerja',
            self::USER => 'Karyawan',
            self::SUPIR => 'Supir',
            self::LAINNYA => 'Lain-lain',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
