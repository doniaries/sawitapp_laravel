<?php

namespace App\Filament\Resources\TransaksiOperasionals\Schemas;

use App\Enums\KategoriOperasional;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;
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
                Hidden::make('perusahaan_id')
                    ->default(fn() => Auth::user()?->perusahaan_id),
                Hidden::make('user_id')
                    ->default(fn() => Auth::id()),

                Group::make()
                    ->columnSpan(['default' => 3, 'md' => 2])
                    ->components([
                        Section::make('Informasi Operasional')
                            ->description('Detail transaksi pemasukan atau pengeluaran')
                            ->components([
                                Select::make('kategori')
                                    ->label('Kategori')
                                    ->options(array_merge(
                                        KategoriOperasional::forPemasukan(),
                                        KategoriOperasional::forPengeluaran()
                                    ))
                                    ->required()
                                    ->searchable()
                                    ->searchDebounce(500)
                                    ->live()
                                    ->noOptionsMessage('Kategori tidak ditemukan')
                                    ->searchingMessage('Mencari kategori...')
                                    ->placeholder('Pilih Kategori')
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if ($state) {
                                            $kat = KategoriOperasional::tryFrom($state);
                                            if ($kat) {
                                                $set('operasional', $kat->getJenisOperasional());
                                            }
                                        }
                                    }),
                                Select::make('pihak_type')
                                    ->label('Jenis Pihak')
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
                                    ->label('Nama')
                                    ->options(function (Get $get) {
                                        $type = $get('pihak_type');
                                        if (!$type) return [];

                                        // Gunakan query builder untuk efisiensi dan auto-scoping via Trait
                                        return $type::query()
                                            ->when(
                                                $type === 'App\Models\User',
                                                fn($q) => $q->pluck('name', 'id'),
                                                fn($q) => $q->pluck('nama', 'id')
                                            );
                                    })
                                    ->searchable()
                                    ->searchDebounce(500)
                                    ->required()
                                    ->visible(fn(Get $get) => !!$get('pihak_type'))
                                    ->noOptionsMessage('Data tidak ditemukan')
                                    ->searchingMessage('Mencari nama...')
                                    ->loadingMessage('Memuat data...')
                                    ->placeholder('Pilih Nama')
,
                                TextInput::make('nominal')
                                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                    ->prefix('Rp')
                                    ->debounce(500)
                                    ->label('Nominal')
                                    ->required()
                                    ->rules([
                                        function (Get $get) {
                                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                                $user = Auth::user();
                                                // Admin bypass validasi saldo
                                                if ($user && method_exists($user, 'isAdminOrSuperAdmin') && $user->isAdminOrSuperAdmin()) {
                                                    return;
                                                }

                                                if ($get('operasional') === 'pengeluaran') {
                                                    $perusahaan = \Filament\Facades\Filament::getTenant();
                                                    // Plugin returns plain number, so we cast to float
                                                    $nominalVal = (float) $value;
                                                    
                                                    if ($perusahaan && $nominalVal > $perusahaan->saldo) {
                                                        $fail("Saldo perusahaan tidak mencukupi (Saldo: " . money($perusahaan->saldo, 'IDR') . "). Hanya Admin yang dapat melanjutkan transaksi ini.");
                                                    }
                                                }
                                            };
                                        },
                                    ]),
                                TextInput::make('keterangan')
                                    ->label('Keterangan')
                                    ->debounce(500)
                                    ->columnSpanFull(),
                            ])->columns(2),
                    ]),

                Group::make()
                    ->columnSpan(['default' => 3, 'md' => 1])
                    ->components([
                        Section::make('Waktu & Catatan')
                            ->description('Konteks operasional')
                            ->compact()
                            ->components([
                                Group::make()
                                    ->components([
                                        Select::make('operasional')
                                            ->label('Jenis')
                                            ->options([
                                                'pemasukan' => 'Pemasukan',
                                                'pengeluaran' => 'Pengeluaran',
                                            ])
                                            ->required()
                                            ->disabled()
                                            ->dehydrated(),

                                        DateTimePicker::make('tanggal')
                                            ->label('Waktu')
                                            ->native(false)
                                            ->displayFormat('d/m/Y H:i')
                                            ->default(now())
                                            ->required()
                                            ->readOnly(fn($record) => $record && !Auth::user()->isSuperAdmin())
                                            ->rules([
                                                fn() => function (string $attribute, $value, \Closure $fail) {
                                                    if (auth()->user()->isSuperAdmin()) return;
                                                    
                                                    $perusahaanId = \Filament\Facades\Filament::getTenant()->id;
                                                    if (\App\Models\TutupHari::isClosed(\Carbon\Carbon::parse($value)->toDateString(), $perusahaanId)) {
                                                        $fail('Tanggal ini sudah ditutup. Hanya Superadmin yang dapat menambah atau mengubah data pada tanggal ini.');
                                                    }
                                                },
                                            ]),
                                    ])->columns(2),
                            ]),
                    ]),
            ]);
    }
}
