<?php

namespace App\Filament\Resources\TutupHaris\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use App\Models\TransaksiDo;
use App\Models\JurnalKeuangan;
use App\Models\TransaksiOperasional;
use Illuminate\Support\HtmlString;

class TutupHariForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Tanggal')
                    ->schema([
                        DatePicker::make('tanggal')
                            ->label('Tanggal Tutup Buku')
                            ->required()
                            ->default(now())
                            ->live()
                            ->unique(ignorable: fn($record) => $record, modifyRuleUsing: function ($rule) {
                                return $rule->where('perusahaan_id', \Filament\Facades\Filament::getTenant()->id);
                            })
                            ->displayFormat('d/m/Y')
                            ->native(false),
                    ]),

                Section::make('Ringkasan Transaksi (Sistem)')
                    ->description('Data di bawah ini adalah kalkulasi otomatis dari sistem untuk tanggal yang dipilih.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Placeholder::make('summary_do')
                                    ->label('Transaksi DO')
                                    ->content(fn (Get $get) => self::getSummaryHtml($get('tanggal'), 'do')),
                                Placeholder::make('summary_operasional')
                                    ->label('Operasional')
                                    ->content(fn (Get $get) => self::getSummaryHtml($get('tanggal'), 'operasional')),
                                Placeholder::make('summary_keuangan')
                                    ->label('Arus Kas')
                                    ->content(fn (Get $get) => self::getSummaryHtml($get('tanggal'), 'keuangan')),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Input Saldo Fisik')
                    ->description('Masukkan jumlah uang tunai yang ada di laci kasir saat ini.')
                    ->schema([
                        TextInput::make('saldo_akhir_fisik')
                            ->label('Total Uang Fisik di Kasir')
                            ->helperText('Uang ini akan menjadi saldo awal perusahaan untuk hari berikutnya.')
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                            ->prefix('Rp')
                            ->required()
                            ->default(0)
                            ->extraAttributes(['class' => 'text-2xl font-bold text-success-600']),
                        Textarea::make('catatan')
                            ->label('Catatan/Keterangan')
                            ->placeholder('Contoh: Ada selisih karena pembulatan...')
                            ->maxLength(255),
                    ]),
            ]);
    }

    protected static function getSummaryHtml($tanggal, $type): HtmlString
    {
        if (!$tanggal) return new HtmlString('<span class="text-gray-400 italic">Pilih tanggal...</span>');

        $perusahaanId = \Filament\Facades\Filament::getTenant()->id;

        switch ($type) {
            case 'do':
                $count = TransaksiDo::where('perusahaan_id', $perusahaanId)->whereDate('tanggal', $tanggal)->count();
                $total = TransaksiDo::where('perusahaan_id', $perusahaanId)->whereDate('tanggal', $tanggal)->sum('sub_total');
                return new HtmlString("
                    <div class='text-sm'>
                        <p class='font-bold text-lg'>{$count} <span class='text-xs font-normal text-gray-500'>Transaksi</span></p>
                        <p class='text-primary-600 font-medium'>Rp " . number_format($total, 0, ',', '.') . "</p>
                    </div>
                ");

            case 'operasional':
                $count = TransaksiOperasional::where('perusahaan_id', $perusahaanId)->whereDate('tanggal', $tanggal)->count();
                $total = TransaksiOperasional::where('perusahaan_id', $perusahaanId)->whereDate('tanggal', $tanggal)->sum('nominal');
                return new HtmlString("
                    <div class='text-sm'>
                        <p class='font-bold text-lg'>{$count} <span class='text-xs font-normal text-gray-500'>Item</span></p>
                        <p class='text-warning-600 font-medium'>Rp " . number_format($total, 0, ',', '.') . "</p>
                    </div>
                ");

            case 'keuangan':
                $masuk = JurnalKeuangan::where('perusahaan_id', $perusahaanId)->whereDate('tanggal', $tanggal)->where('jenis_transaksi', 'Pemasukan')->sum('nominal');
                $keluar = JurnalKeuangan::where('perusahaan_id', $perusahaanId)->whereDate('tanggal', $tanggal)->where('jenis_transaksi', 'Pengeluaran')->sum('nominal');
                $saldoSistem = $masuk - $keluar;
                return new HtmlString("
                    <div class='text-xs space-y-1'>
                        <p class='text-success-600'>Masuk: Rp " . number_format($masuk, 0, ',', '.') . "</p>
                        <p class='text-danger-600'>Keluar: Rp " . number_format($keluar, 0, ',', '.') . "</p>
                        <hr class='my-1'>
                        <p class='font-bold text-blue-600'>Sistem: Rp " . number_format($saldoSistem, 0, ',', '.') . "</p>
                    </div>
                ");
        }

        return new HtmlString('');
    }
}
