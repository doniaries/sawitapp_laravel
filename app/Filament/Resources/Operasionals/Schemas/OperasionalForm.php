<?php

namespace App\Filament\Resources\Operasionals\Schemas;

use App\Enums\KategoriOperasional;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;

class OperasionalForm
{
    public static function configure(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->schema([
            Section::make()
                ->schema([
                    Grid::make()
                        ->schema([
                            // Kolom Kiri
                            Group::make([
                                Forms\Components\DateTimePicker::make('tanggal')
                                    ->label('Tanggal')
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->default(now())
                                    ->required(),

                                Forms\Components\Select::make('operasional')
                                    ->label('Jenis')
                                    ->options([
                                        'pemasukan' => 'Pemasukan',
                                        'pengeluaran' => 'Pengeluaran',
                                    ])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn($state, Forms\Set $set) => $set('kategori', null)),

                                Forms\Components\Select::make('kategori')
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

                                Forms\Components\Select::make('tipe_nama')
                                    ->label('Tipe')
                                    ->options([
                                        'penjual' => 'Penjual',
                                        'supir' => 'Supir',
                                        'pekerja' => 'Pekerja',
                                        'user' => 'Karyawan'
                                    ])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn($state, Forms\Set $set) => [
                                        $set('penjual_id', null),
                                        $set('supir_id', null),
                                        $set('pekerja_id', null),
                                        $set('user_id', null)
                                    ])
                            ])->columnSpan(1),

                            // Kolom Kanan
                            Group::make([
                                Forms\Components\Select::make('penjual_id')
                                    ->label('Pilih Pihak')
                                    ->relationship('penjual', 'nama')
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->visible(fn($get) => $get('tipe_nama') === 'penjual'),

                                Forms\Components\Select::make('supir_id')
                                    ->label('Pilih Pihak')
                                    ->relationship('supir', 'nama')
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn($get) => $get('tipe_nama') === 'supir'),

                                Forms\Components\Select::make('pekerja_id')
                                    ->label('Pilih Pihak')
                                    ->relationship('pekerja', 'nama')
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn($get) => $get('tipe_nama') === 'pekerja'),

                                Forms\Components\Select::make('user_id')
                                    ->label('Pilih Pihak')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn($get) => $get('tipe_nama') === 'user'),

                                Forms\Components\TextInput::make('nominal')
                                    ->label('Nominal')
                                    ->required()
                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 0)
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->live(),

                                Forms\Components\TextInput::make('keterangan')
                                    ->label('Keterangan')
                            ])->columnSpan(1)
                        ])
                        ->columns(2)
                ])->columnSpanFull()
        ]);
    }
}
