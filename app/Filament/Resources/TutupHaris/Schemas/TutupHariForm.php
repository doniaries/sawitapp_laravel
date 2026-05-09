<?php

namespace App\Filament\Resources\TutupHaris\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use App\Models\TransaksiDo;
use App\Models\JurnalKeuangan;
use App\Models\TransaksiOperasional;
use App\Models\TutupHari;
use Carbon\Carbon;

class TutupHariForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        // Kolom Kiri: Ringkasan (2/3 Luas Modal)
                        Section::make('Ringkasan Transaksi (Sistem)')
                            ->schema([
                                \Filament\Schemas\Components\Livewire::make(\App\Livewire\SummaryKasInfolist::class, [
                                    'tanggal' => fn(\Filament\Schemas\Components\Utilities\Get $get = null) => $get ? $get('tanggal') : today()->toDateString(),
                                ])
                                    ->columnSpan(2),
                            ])
                            ->columnSpan(2),

                        // Kolom Kanan: Input Data (1/3 Luas Modal)
                        Grid::make(1)
                            ->schema([
                                Section::make('Informasi Tanggal')
                                    ->schema([
                                        DatePicker::make('tanggal')
                                            ->label('Tanggal Tutup Buku')
                                            ->required()
                                            ->default(fn() => self::getNextClosingDate())
                                            ->readOnly() // Mencegah kasir mengubah tanggal secara manual
                                            ->live()
                                            ->unique(ignorable: fn($record) => $record, modifyRuleUsing: function ($rule) {
                                                return $rule->where('perusahaan_id', \Filament\Facades\Filament::getTenant()->id);
                                            })
                                            ->displayFormat('d/m/Y')
                                            ->native(false),
                                    ]),

                                Section::make('Input Saldo Fisik')
                                    ->schema([
                                        TextInput::make('saldo_akhir_fisik')
                                            ->label('Uang Fisik Kasir')
                                            ->helperText('Saldo awal esok hari.')
                                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                            ->prefix('Rp')
                                            ->required()
                                            ->default(0)
                                            ->extraAttributes(['class' => 'text-2xl font-bold text-success-600']),
                                        Textarea::make('catatan')
                                            ->label('Catatan')
                                            ->placeholder('Keterangan...')
                                            ->maxLength(255),
                                    ]),
                            ])
                            ->columnSpan(1),
                    ])
                    ->columnSpanFull(),
            ]);
    }


    public static function getNextClosingDate(): string
    {
        $perusahaanId = \Filament\Facades\Filament::getTenant()->id;

        $lastClosing = TutupHari::query()
            ->where('perusahaan_id', $perusahaanId)
            ->latest('tanggal')
            ->first();

        if ($lastClosing) {
            return Carbon::parse($lastClosing->tanggal)->addDay()->format('Y-m-d');
        }

        $oldestDo = TransaksiDo::where('perusahaan_id', $perusahaanId)->oldest('tanggal')->first()?->tanggal;
        $oldestOp = TransaksiOperasional::where('perusahaan_id', $perusahaanId)->oldest('tanggal')->first()?->tanggal;
        $oldestJurnal = JurnalKeuangan::where('perusahaan_id', $perusahaanId)->oldest('tanggal')->first()?->tanggal;

        $dates = array_filter([$oldestDo, $oldestOp, $oldestJurnal]);

        return !empty($dates) ? Carbon::parse(min($dates))->format('Y-m-d') : now()->format('Y-m-d');
    }
}
