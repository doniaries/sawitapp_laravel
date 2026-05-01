<?php

namespace App\Filament\Resources\TutupHaris\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TutupHariForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('tanggal')
                    ->label('Tanggal Tutup Buku')
                    ->required()
                    ->default(now())
                    ->unique(ignorable: fn($record) => $record, modifyRuleUsing: function($rule) {
                        return $rule->where('perusahaan_id', \Filament\Facades\Filament::getTenant()->id);
                    })
                    ->displayFormat('d/m/Y')
                    ->native(false),
                \Filament\Forms\Components\TextInput::make('saldo_akhir_fisik')
                    ->label('Saldo Kas Fisik')
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                    ->prefix('Rp')
                    ->required()
                    ->default(0),
                Textarea::make('catatan')
                    ->label('Catatan/Keterangan')
                    ->placeholder('Opsional')
                    ->maxLength(255),
            ]);
    }
}
