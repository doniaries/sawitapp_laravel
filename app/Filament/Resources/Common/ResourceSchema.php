<?php

namespace App\Filament\Resources\Common;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use Filament\Forms\Get;

class ResourceSchema
{
    public static function getContactSection(string $label = 'Informasi'): Section
    {
        return Section::make($label)
            ->description("Detail data diri " . strtolower($label))
            ->compact()
            ->components([
                TextInput::make('nama')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->extraInputAttributes(['style' => 'text-transform: uppercase;'])
                    ->afterStateHydrated(fn($state, $set) => $set('nama', strtoupper($state)))
                    ->dehydrateStateUsing(fn($state) => strtoupper($state))
                    ->debounce(500),

                TextInput::make('telepon')
                    ->tel()
                    ->label('Nomor Telepon')
                    ->debounce(500),

                TextInput::make('alamat')
                    ->label('Alamat')
                    ->debounce(500),
            ]);
    }

    public static function getHutangSection(): Section
    {
        return Section::make('Statistik Keuangan')
            ->description('Informasi hutang dan pembayaran')
            ->compact()
            ->components([
                TextInput::make('hutang')
                    ->label(fn($context) => $context === 'create' ? 'Hutang Awal' : 'Total Akumulasi Hutang')
                    ->helperText(fn($context) => $context === 'create' ? 'Masukkan hutang awal jika ada.' : 'Total hutang awal + penambahan dari transaksi.')
                    ->prefix('Rp')
                    ->numeric()
                    ->default(0)
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                    ->debounce(500),

                TextInput::make('total_pembayaran')
                    ->label('Total Sudah Dibayar')
                    ->disabled()
                    ->dehydrated(false)
                    ->prefix('Rp')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0),

                TextInput::make('sisa_hutang')
                    ->label('Sisa Hutang Saat Ini')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder(function ($record) {
                        if (!$record) return 'Rp 0';
                        return 'Rp ' . number_format($record->sisa_hutang, 0, ',', '.');
                    })
                    ->prefix('Rp')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                    ->extraInputAttributes(['class' => 'font-bold text-danger-600']),
            ]);
    }
}
