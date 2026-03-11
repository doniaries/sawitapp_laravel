<?php
// File: app/Enums/KategoriOperasional.php

namespace App\Enums;

enum KategoriOperasional: string
{
    case PINJAMAN = 'pinjaman';
    case BAYAR_HUTANG = 'bayar_hutang';
    case PIJAK_GAS = 'pijakan_gas';
    case UANG_JALAN = 'uang_jalan';
    case BAHAN_BAKAR = 'bahan_bakar';
    case PERAWATAN = 'perawatan';
    case LAIN_LAIN = 'lain_lain';
    case TAMBAH_SALDO = 'tambah_saldo';

    public function label(): string
    {
        return match ($this) {
            self::PINJAMAN => 'Pinjaman',
            self::BAYAR_HUTANG => 'Bayar Hutang',
            self::PIJAK_GAS => 'Pijak Gas',
            self::UANG_JALAN => 'Uang Jalan',
            self::BAHAN_BAKAR => 'Bahan Bakar',
            self::PERAWATAN => 'Perawatan',
            self::LAIN_LAIN => 'Lain-lain',
            self::TAMBAH_SALDO => 'Tambah Saldo',
            // self::SISA_SALDO => 'Sisa Saldo',
        };
    }

    // Tambahkan method helper untuk cek jenis operasional
    public function getJenisOperasional(): string
    {
        return match ($this) {
            self::BAYAR_HUTANG => 'pemasukan',
            self::TAMBAH_SALDO => 'pemasukan',

            self::PINJAMAN => 'pengeluaran',
            default => 'pengeluaran'
        };
    }

    // Helper untuk cek apakah kategori terkait hutang
    public function isHutangRelated(): bool
    {
        return in_array($this, [self::PINJAMAN, self::BAYAR_HUTANG]);
    }

    // Helper untuk mendapatkan semua kategori pemasukan
    public static function forPemasukan(): array
    {
        return [
            self::BAYAR_HUTANG->value => self::BAYAR_HUTANG->label(),
            self::TAMBAH_SALDO->value => self::TAMBAH_SALDO->label(),
        ];
    }

    // Helper untuk mendapatkan semua kategori pengeluaran
    public static function forPengeluaran(): array
    {
        return [
            self::PINJAMAN->value => self::PINJAMAN->label(),
            self::UANG_JALAN->value => self::UANG_JALAN->label(),
            self::PIJAK_GAS->value => self::PIJAK_GAS->label(),
            self::BAHAN_BAKAR->value => self::BAHAN_BAKAR->label(),
            self::PERAWATAN->value => self::PERAWATAN->label(),
            self::LAIN_LAIN->value => self::LAIN_LAIN->label(),
        ];
    }
}