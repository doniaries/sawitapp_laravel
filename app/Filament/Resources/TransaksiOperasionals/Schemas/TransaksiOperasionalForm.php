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

                                Select::make('pihak_type')
                                    ->label('Tipe Profil')
                                    ->options([
                                        'App\Models\Penjual' => 'Penjual',
                                        'App\Models\Supir' => 'Supir',
                                        'App\Models\Pekerja' => 'Pekerja',
                                        'App\Models\User' => 'Karyawan'
                                    ])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn(Set $set) => $set('pihak_id', null)),

                                Select::make('pihak_id')
                                    ->label('Pihak Terkait')
                                    ->options(function (Get $get) {
                                        $type = $get('pihak_type');
                                        if (!$type) return [];
                                        
                                        // Gunakan query builder untuk efisiensi dan auto-scoping via Trait
                                        return $type::query()
                                            ->when($type === 'App\Models\User', 
                                                fn($q) => $q->pluck('name', 'id'),
                                                fn($q) => $q->pluck('nama', 'id')
                                            );
                                    })
                                    ->searchable()
                                    ->required()
                                    ->visible(fn(Get $get) => !!$get('pihak_type')),
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
