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
use App\Models\Perusahaan;
use Illuminate\Support\HtmlString;

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
                                Placeholder::make('full_summary')
                                    ->label('')
                                    ->content(fn(Get $get) => self::getSummaryTableHtml($get('tanggal'))),
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
                                            ->default(now())
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
                                            ->placeholder('Keterangan selisih...')
                                            ->maxLength(255),
                                    ]),
                            ])
                            ->columnSpan(1),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected static function getSummaryTableHtml(string|null $tanggal): HtmlString
    {
        if (!$tanggal) return new HtmlString('<div class="p-4 border border-dashed rounded-lg text-gray-400 text-center italic">Pilih tanggal untuk melihat ringkasan</div>');

        $perusahaanId = \Filament\Facades\Filament::getTenant()->id;

        // Data DO
        $doCount = TransaksiDo::where('perusahaan_id', $perusahaanId)->whereDate('tanggal', $tanggal)->count();
        $doTotal = TransaksiDo::where('perusahaan_id', $perusahaanId)->whereDate('tanggal', $tanggal)->sum('sub_total');

        // Data Operasional
        $opCount = TransaksiOperasional::where('perusahaan_id', $perusahaanId)->whereDate('tanggal', $tanggal)->count();
        $opTotal = TransaksiOperasional::where('perusahaan_id', $perusahaanId)->whereDate('tanggal', $tanggal)->sum('nominal');

        // Data Keuangan
        $perusahaan = Perusahaan::query()->find($perusahaanId);
        $saldoAwal = $perusahaan?->saldo ?? 0;
        $masuk = JurnalKeuangan::where('perusahaan_id', $perusahaanId)->whereDate('tanggal', $tanggal)->where('jenis_transaksi', 'Pemasukan')->sum('nominal');
        $keluar = JurnalKeuangan::where('perusahaan_id', $perusahaanId)->whereDate('tanggal', $tanggal)->where('jenis_transaksi', 'Pengeluaran')->sum('nominal');
        $saldoSistem = $saldoAwal + $masuk - $keluar;

        return new HtmlString("
            <div class='overflow-hidden border rounded-lg bg-gray-50 dark:bg-gray-900/50 shadow-sm'>
                <table class='w-full text-base text-left border-collapse table-fixed'>
                    <thead>
                        <tr class='bg-gray-100 dark:bg-gray-800 border-b dark:border-gray-700'>
                            <th class='w-3/4 px-6 py-3 font-bold text-gray-700 dark:text-gray-200'>Keterangan Ringkasan</th>
                            <th class='w-1/4 px-6 py-3 font-bold text-right text-gray-700 dark:text-gray-200'>Jumlah / Nilai</th>
                        </tr>
                    </thead>
                    <tbody class='divide-y dark:divide-gray-700'>
                        <!-- Transaksi DO -->
                        <tr>
                            <td class='px-4 py-3 text-gray-800 dark:text-gray-200 font-bold bg-gray-50/50 dark:bg-gray-800/50' colspan='2'>TRANSAKSI PENJUALAN (DO)</td>
                        </tr>
                        <tr class='bg-white dark:bg-gray-800'>
                            <td class='px-6 py-2 text-gray-600 dark:text-gray-400'>Banyak Transaksi</td>
                            <td class='px-6 py-2 text-right font-semibold text-gray-900 dark:text-white'>{$doCount} Transaksi</td>
                        </tr>
                        <tr class='bg-white dark:bg-gray-800'>
                            <td class='px-6 py-2 text-gray-600 dark:text-gray-400'>Total Rupiah DO</td>
                            <td class='px-6 py-2 text-right font-bold text-primary-600 font-mono text-base'>Rp " . number_format($doTotal, 0, ',', '.') . "</td>
                        </tr>

                        <!-- Operasional -->
                        <tr>
                            <td class='px-4 py-3 text-gray-800 dark:text-gray-200 font-bold bg-gray-50/50 dark:bg-gray-800/50 border-t dark:border-gray-700' colspan='2'>BIAYA OPERASIONAL</td>
                        </tr>
                        <tr class='bg-white dark:bg-gray-800'>
                            <td class='px-6 py-2 text-gray-600 dark:text-gray-400'>Jumlah Item Biaya</td>
                            <td class='px-6 py-2 text-right font-semibold text-gray-900 dark:text-white'>{$opCount} Item</td>
                        </tr>
                        <tr class='bg-white dark:bg-gray-800'>
                            <td class='px-6 py-2 text-gray-600 dark:text-gray-400'>Total Biaya</td>
                            <td class='px-6 py-2 text-right font-bold text-warning-600 font-mono text-base'>Rp " . number_format($opTotal, 0, ',', '.') . "</td>
                        </tr>

                        <!-- Arus Kas -->
                        <tr>
                            <td class='px-4 py-3 text-gray-800 dark:text-gray-200 font-bold bg-gray-50/50 dark:bg-gray-800/50 border-t dark:border-gray-700' colspan='2'>REKONSILIASI KAS (SISTEM)</td>
                        </tr>
                        <tr class='bg-white dark:bg-gray-800'>
                            <td class='px-6 py-2 text-gray-500 dark:text-gray-500'>[+] Saldo Awal</td>
                            <td class='px-6 py-2 text-right font-medium font-mono text-gray-900 dark:text-white'>Rp " . number_format($saldoAwal, 0, ',', '.') . "</td>
                        </tr>
                        <tr class='bg-white dark:bg-gray-800'>
                            <td class='px-6 py-2 text-success-600 font-medium'>[+] Total Pemasukan</td>
                            <td class='px-6 py-2 text-right font-bold text-success-600 font-mono'>Rp " . number_format($masuk, 0, ',', '.') . "</td>
                        </tr>
                        <tr class='bg-white dark:bg-gray-800'>
                            <td class='px-6 py-2 text-danger-600 font-medium'>[-] Total Pengeluaran</td>
                            <td class='px-6 py-2 text-right font-bold text-danger-600 font-mono'>Rp " . number_format($keluar, 0, ',', '.') . "</td>
                        </tr>
                        <tr class='bg-blue-50 dark:bg-blue-900/20 border-t-2 border-blue-200 dark:border-blue-800'>
                            <td class='px-4 py-4 font-black text-blue-800 dark:text-blue-400 uppercase text-lg italic'>SALDO AKHIR SISTEM</td>
                            <td class='px-6 py-4 text-right font-black text-blue-800 dark:text-blue-400 text-2xl font-mono underline decoration-double decoration-blue-300 underline-offset-4 tracking-tighter'>Rp " . number_format($saldoSistem, 0, ',', '.') . "</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        ");
    }
}
