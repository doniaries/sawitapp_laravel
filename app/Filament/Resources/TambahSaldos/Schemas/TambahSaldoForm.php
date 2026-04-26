<?php

namespace App\Filament\Resources\TambahSaldos\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Schema;

class TambahSaldoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('perusahaan_id')
                    ->default(fn() => Auth::user()?->perusahaan_id),
                Hidden::make('user_id')
                    ->default(fn() => Auth::id()),
                DateTimePicker::make('tanggal')
                    ->default(now())
                    ->required()
                    ->label('Tanggal Entry')
                    ->native(false)
                    ->displayFormat('d/m/Y H:i'),
                TextInput::make('nominal')
                    ->required()
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0)
                    ->debounce(500),
                Textarea::make('keterangan')
                    ->columnSpanFull()
                    ->maxLength(100)
                    ->debounce(500),
            ]);
    }
}
