<?php

namespace App\Filament\Resources\PengajuanDanas\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PengajuanDanaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('perusahaan_id')
                    ->relationship('perusahaan', 'name')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                DateTimePicker::make('tanggal_pengajuan')
                    ->default(now())
                    ->required(),
                TextInput::make('nominal')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0),
                Textarea::make('keperluan')
                    ->required()
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak'
                    ])
                    ->default('pending')
                    ->disabled() // Tidak bisa diubah manual
                    ->dehydrated()
                    ->required(),
                TextInput::make('bukti_transfer')
                    ->placeholder('Akan diisi setelah disetujui')
                    ->disabled(),
            ]);
    }
}
