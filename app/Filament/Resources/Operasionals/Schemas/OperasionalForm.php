<?php

namespace App\Filament\Resources\Operasionals\Schemas;

use App\Enums\KategoriOperasional;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;

class OperasionalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->components([
                    Grid::make()
                        ->components([
                            // Kolom Kiri
                            Group::make([
                                DateTimePicker::make('tanggal')
                                    ->label('Tanggal')
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->default(now())
                                    ->required(),

                                Select::make('operasional')
                                    ->label('Jenis')
                                    ->options([
                                        'pemasukan' => 'Pemasukan',
                                        'pengeluaran' => 'Pengeluaran',
                                    ])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn($state, Set $set) => $set('kategori', null)),

                                Select::make('kategori')
                                    ->label('Kategori')
                                    ->options(function ($get) {
                                        return match ($get('operasional')) {
                                            'pemasukan' => KategoriOperasional::forPemasukan(),
                                            'pengeluaran' => KategoriOperasional::forPengeluaran(),
                                            default => []
                                        };
                                    })
                                    ->required()
                                    ->live(),

                                Select::make('tipe_nama')
                                    ->label('Tipe')
                                    ->options([
                                        'penjual' => 'Penjual',
                                        'supir' => 'Supir',
                                        'pekerja' => 'Pekerja',
                                        'user' => 'Karyawan'
                                    ])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn($state, Set $set) => [
                                        $set('penjual_id', null),
                                        $set('supir_id', null),
                                        $set('pekerja_id', null),
                                        $set('user_id', null)
                                    ])
                            ])->columnSpan(1),

                            // Kolom Kanan
                            Group::make([
                                Select::make('penjual_id')
                                    ->label('Pilih Pihak')
                                    ->relationship('penjual', 'nama')
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->visible(fn($get) => $get('tipe_nama') === 'penjual'),

                                Select::make('supir_id')
                                    ->label('Pilih Pihak')
                                    ->relationship('supir', 'nama')
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn($get) => $get('tipe_nama') === 'supir'),

                                Select::make('pekerja_id')
                                    ->label('Pilih Pihak')
                                    ->relationship('pekerja', 'nama')
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn($get) => $get('tipe_nama') === 'pekerja'),

                                Select::make('user_id')
                                    ->label('Pilih Pihak')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn($get) => $get('tipe_nama') === 'user'),

                                TextInput::make('nominal')
                                    ->label('Nominal')
                                    ->required()
                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->live(),

                                TextInput::make('keterangan')
                                    ->label('Keterangan')
                            ])->columnSpan(1)
                        ])
                        ->columns(2)
                ])->columnSpanFull()
        ]);
    }
}
