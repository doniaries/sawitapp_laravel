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
                    ->description('Kalkulasi otomatis sistem berdasarkan transaksi yang tercatat.')
                    ->schema([
                        Placeholder::make('full_summary')
                            ->label('')
                            ->content(fn (Get $get) => self::getSummaryTableHtml($get('tanggal'))),
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
            <div class='overflow-hidden border rounded-lg bg-gray-50 dark:bg-gray-900/50'>
                <table class='w-full text-sm text-left border-collapse'>
                    <thead>
                        <tr class='bg-gray-100 dark:bg-gray-800 border-b dark:border-gray-700'>
                            <th class='px-4 py-2 font-bold text-gray-700 dark:text-gray-200'>Keterangan Ringkasan</th>
                            <th class='px-4 py-2 font-bold text-right text-gray-700 dark:text-gray-200'>Jumlah / Nilai</th>
                        </tr>
                    </thead>
                    <tbody class='divide-y dark:divide-gray-700'>
                        <!-- Transaksi DO -->
                        <tr>
                            <td class='px-4 py-2 text-gray-600 dark:text-gray-400 italic font-medium bg-white dark:bg-gray-800' colspan='2'>Transaksi Penjualan (DO)</td>
                        </tr>
                        <tr class='bg-white dark:bg-gray-800'>
                            <td class='px-8 py-1 text-gray-600 dark:text-gray-400'>- Banyak Transaksi</td>
                            <td class='px-4 py-1 text-right font-semibold'>{$doCount} TRX</td>
                        </tr>
                        <tr class='bg-white dark:bg-gray-800'>
                            <td class='px-8 py-1 text-gray-600 dark:text-gray-400'>- Total Rupiah DO</td>
                            <td class='px-4 py-1 text-right font-semibold text-primary-600 font-mono'>Rp " . number_format($doTotal, 0, ',', '.') . "</td>
                        </tr>

                        <!-- Operasional -->
                        <tr>
                            <td class='px-4 py-2 text-gray-600 dark:text-gray-400 italic font-medium bg-white dark:bg-gray-800 border-t dark:border-gray-700' colspan='2'>Biaya Operasional</td>
                        </tr>
                        <tr class='bg-white dark:bg-gray-800'>
                            <td class='px-8 py-1 text-gray-600 dark:text-gray-400'>- Jumlah Item Biaya</td>
                            <td class='px-4 py-1 text-right font-semibold'>{$opCount} Item</td>
                        </tr>
                        <tr class='bg-white dark:bg-gray-800'>
                            <td class='px-8 py-1 text-gray-600 dark:text-gray-400'>- Total Biaya</td>
                            <td class='px-4 py-1 text-right font-semibold text-warning-600 font-mono'>Rp " . number_format($opTotal, 0, ',', '.') . "</td>
                        </tr>

                        <!-- Arus Kas -->
                        <tr>
                            <td class='px-4 py-2 text-gray-600 dark:text-gray-400 italic font-medium bg-white dark:bg-gray-800 border-t dark:border-gray-700' colspan='2'>Rekonsiliasi Kas (Sistem)</td>
                        </tr>
                        <tr class='bg-white dark:bg-gray-800'>
                            <td class='px-8 py-1 text-gray-500 dark:text-gray-500'>[+] Saldo Awal</td>
                            <td class='px-4 py-1 text-right font-medium font-mono'>Rp " . number_format($saldoAwal, 0, ',', '.') . "</td>
                        </tr>
                        <tr class='bg-white dark:bg-gray-800'>
                            <td class='px-8 py-1 text-success-600'>[+] Total Pemasukan</td>
                            <td class='px-4 py-1 text-right font-medium text-success-600 font-mono'>Rp " . number_format($masuk, 0, ',', '.') . "</td>
                        </tr>
                        <tr class='bg-white dark:bg-gray-800'>
                            <td class='px-8 py-1 text-danger-600'>[-] Total Pengeluaran</td>
                            <td class='px-4 py-1 text-right font-medium text-danger-600 font-mono'>Rp " . number_format($keluar, 0, ',', '.') . "</td>
                        </tr>
                        <tr class='bg-blue-50/50 dark:bg-blue-900/10'>
                            <td class='px-4 py-2 font-black text-blue-700 dark:text-blue-400 uppercase'>Saldo Akhir Sistem</td>
                            <td class='px-4 py-2 text-right font-black text-blue-700 dark:text-blue-400 text-base font-mono underline decoration-double decoration-blue-200 underline-offset-4'>Rp " . number_format($saldoSistem, 0, ',', '.') . "</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        ");
    }
}
