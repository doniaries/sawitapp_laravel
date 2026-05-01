<?php

namespace App\Traits;

use Filament\Forms\Components\TextInput;

trait HasCurrencyInput
{
    /**
     * Konfigurasi standar untuk input mata uang Rupiah
     */
    public static function currencyInput(TextInput $input): TextInput
    {
        return $input
            ->prefix('Rp')
            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
            ->formatStateUsing(fn ($state) => $state ? number_format((float) $state, 0, ',', '.') : null)
            ->dehydrateStateUsing(fn ($state) => self::sanitizeNumber($state))
            ->debounce(500);
    }

    /**
     * Membersihkan string bermasker menjadi numeric (float/int)
     */
    public static function sanitizeNumber(mixed $number): float
    {
        if (empty($number)) return 0;
        
        if (is_numeric($number) && !is_string($number)) {
            return (float) $number;
        }

        $str = (string) $number;
        
        // Hapus spasi dan karakter non-numerik kecuali pemisah desimal/ribuan
        $str = str_replace(' ', '', $str);
        $str = preg_replace('/[^\d,.-]/', '', $str);

        if (empty($str)) return 0;

        // Logika pembersihan standar Indonesia (Titik ribuan, Koma desimal)
        if (str_contains($str, '.') && str_contains($str, ',')) {
            // Jika ada keduanya, hapus titik (ribuan) dan ganti koma ke titik (desimal)
            $str = str_replace('.', '', $str);
            $str = str_replace(',', '.', $str);
        } elseif (str_contains($str, ',')) {
            // Jika hanya koma, asumsikan itu desimal jika di belakangnya < 3 digit, 
            // atau ribuan jika formatnya 1.000. Tapi paling aman untuk IDR:
            // Jika input masking precision 0, maka koma/titik hanyalah pemisah ribuan.
            $str = str_replace(',', '', $str);
        } elseif (str_contains($str, '.')) {
            // Hapus titik (ribuan)
            $str = str_replace('.', '', $str);
        }
        
        return (float) $str;
    }
}
