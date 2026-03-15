<?php

namespace App\Filament\Resources\TransaksiOperasionals\Schemas;

use App\Enums\KategoriOperasional;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;

class TransaksiOperasionalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(['default' => 3, 'md' => 2])
                    ->components([
                        Section::make('Informasi Operasional')
                            ->description('Detail transaksi pemasukan atau pengeluaran')
                            ->components([
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
                                    ->options(function (Get $get) {
                                        return match ($get('operasional')) {
                                            'pemasukan' => KategoriOperasional::forPemasukan(),
                                            'pengeluaran' => KategoriOperasional::forPengeluaran(),
                                            default => []
                                        };
                                    })
                                    ->required(),

                                TextInput::make('nominal')
                                    ->label('Nominal')
                                    ->required()
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('Rp')
                                    ->numeric(),

                                Select::make('tipe_nama')
                                    ->label('Tipe Profil')
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
                                    ]),

                                Select::make('penjual_id')
                                    ->label('Penjual')
                                    ->relationship('penjual', 'nama')
                                    ->searchable()
                                    ->required()
                                    ->visible(fn(Get $get) => $get('tipe_nama') === 'penjual'),

                                Select::make('supir_id')
                                    ->label('Supir')
                                    ->relationship('supir', 'nama')
                                    ->searchable()
                                    ->required()
                                    ->visible(fn(Get $get) => $get('tipe_nama') === 'supir'),

                                Select::make('pekerja_id')
                                    ->label('Pekerja')
                                    ->relationship('pekerja', 'nama')
                                    ->searchable()
                                    ->required()
                                    ->visible(fn(Get $get) => $get('tipe_nama') === 'pekerja'),

                                Select::make('user_id')
                                    ->label('Karyawan')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->required()
                                    ->visible(fn(Get $get) => $get('tipe_nama') === 'user'),
                            ])->columns(2),
                    ]),

                Group::make()
                    ->columnSpan(['default' => 3, 'md' => 1])
                    ->components([
                        Section::make('Waktu & Catatan')
                            ->description('Konteks operasional')
                            ->components([
                                DateTimePicker::make('tanggal')
                                    ->label('Tanggal')
                                    ->readOnly()
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->default(now())
                                    ->required(),

                                TextInput::make('keterangan')
                                    ->label('Keterangan')
                            ]),
                    ]),
            ]);
    }
}
