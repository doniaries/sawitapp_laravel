<?php

namespace App\Traits;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextInputColumn;

trait HasCurrencyInput
{
    /**
     * Konfigurasi standar untuk input mata uang Rupiah pada Form
     */
    public static function currencyInput(TextInput $input, int $precision = 0): TextInput
    {
        return $input
            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: $precision)
            ->prefix('Rp ')
            ->debounce(500);
    }

    /**
     * Konfigurasi standar untuk input angka (tonase, dsb) dengan pemisah ribuan
     */
    public static function numericInput(TextInput $input, int $precision = 0, string $suffix = ''): TextInput
    {
        $input = $input
            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: $precision)
            ->debounce(500);
            
        if ($suffix) {
            $input->suffix(' ' . $suffix);
        }
        
        return $input;
    }

    /**
     * Konfigurasi standar untuk input mata uang pada kolom tabel (TextInputColumn)
     */
    public static function currencyInputColumn(TextInputColumn $column, int $precision = 0): TextInputColumn
    {
        return $column
            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: $precision);
    }

    /**
     * Konfigurasi standar untuk tampilan mata uang Rupiah pada Tabel
     */
    public static function currencyColumn(TextColumn $column): TextColumn
    {
        return $column
            ->currency('IDR')
            ->alignEnd();
    }

    /**
     * Konfigurasi standar untuk tampilan angka (non-mata uang) pada Tabel
     */
    public static function numericColumn(TextColumn $column, int $precision = 0, string $suffix = ''): TextColumn
    {
        return $column
            ->numeric($precision, ',', '.')
            ->suffix($suffix ? ' ' . $suffix : '')
            ->alignEnd();
    }

    /**
     * Konfigurasi standar untuk ringkasan mata uang Rupiah (Summarizer/Sum/Average)
     */
    public static function currencySummarizer($summarizer): mixed
    {
        return $summarizer
            ->currency('IDR');
    }

    /**
     * Konfigurasi standar untuk ringkasan angka (Summarizer/Sum/Average)
     */
    public static function numericSummarizer($summarizer, int $precision = 0, string $suffix = ''): mixed
    {
        return $summarizer
            ->numeric($precision, ',', '.')
            ->suffix($suffix ? ' ' . $suffix : '');
    }

    /**
     * Konfigurasi standar untuk tampilan mata uang Rupiah pada InfoList
     */
    public static function currencyEntry(TextEntry $entry): TextEntry
    {
        return $entry
            ->currency('IDR');
    }

    /**
     * Membersihkan string bermasker menjadi numeric (float/int)
     * Digunakan untuk pengolahan di backend jika data tidak melalui dehidrasi Filament
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
            // Jika hanya koma, asumsikan itu desimal atau ribuan tergantung konteks
            // Untuk amannya di IDR (precision 0), koma sering jadi pemisah ribuan salah ketik
            $str = str_replace(',', '', $str);
        } elseif (str_contains($str, '.')) {
            // Hapus titik (ribuan)
            $str = str_replace('.', '', $str);
        }
        
        return (float) $str;
    }
}
