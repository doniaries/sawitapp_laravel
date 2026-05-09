<?php

namespace App\Livewire;

use App\Models\Perusahaan;
use App\Models\TransaksiDo;
use App\Models\TutupHari;
use App\Models\TransaksiOperasional;
use App\Models\JurnalKeuangan;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Enums\TextSize;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SummaryKasInfolist extends Component implements HasForms, HasSchemas
{
    use InteractsWithForms, InteractsWithSchemas {
        InteractsWithSchemas::getCachedSchemas insteadof InteractsWithForms;
    }

    public $tanggal;

    public function mount($tanggal = null)
    {
        if (is_callable($tanggal)) {
            try {
                $this->tanggal = $tanggal();
            } catch (\TypeError $e) {
                $this->tanggal = today()->toDateString();
            }
        } else {
            $this->tanggal = $tanggal ?: today()->toDateString();
        }
    }

    public function getData(): array
    {
        $perusahaanId = Filament::getTenant()->id;
        $tanggal = $this->tanggal;

        // 1. Data DO
        $doQuery = TransaksiDo::query()
            ->where('perusahaan_id', $perusahaanId)
            ->whereDate('tanggal', $tanggal);
        $doCount = $doQuery->count();
        $doTotal = $doQuery->sum('sub_total');

        // 2. Data Operasional
        $opQuery = TransaksiOperasional::query()
            ->where('perusahaan_id', $perusahaanId)
            ->whereDate('tanggal', $tanggal);
        $opCount = $opQuery->count();
        $opTotal = $opQuery->sum('nominal');

        // 3. Rekonsiliasi Kas (Sesuai logika getSummaryTableHtml)
        $masuk = JurnalKeuangan::where('perusahaan_id', $perusahaanId)
            ->whereDate('tanggal', $tanggal)
            ->where('jenis_transaksi', 'Pemasukan')
            ->where('mempengaruhi_kas', true)
            ->sum('nominal');

        $keluar = JurnalKeuangan::where('perusahaan_id', $perusahaanId)
            ->whereDate('tanggal', $tanggal)
            ->where('jenis_transaksi', 'Pengeluaran')
            ->where('mempengaruhi_kas', true)
            ->sum('nominal');

        // Saldo Awal: Ambil dari saldo_akhir_fisik TutupHari sebelumnya
        $lastClosing = TutupHari::where('perusahaan_id', $perusahaanId)
            ->where('tanggal', '<', $tanggal)
            ->latest('tanggal')
            ->first();

        if ($lastClosing) {
            $saldoAwal = $lastClosing->saldo_akhir_fisik;
        } else {
            // Jika belum pernah tutup hari, ambil saldo perusahaan saat ini - net hari ini
            $perusahaan = Perusahaan::find($perusahaanId);
            $saldoAwal = ($perusahaan?->saldo ?? 0) - ($masuk - $keluar);
        }

        $saldoSistem = $saldoAwal + $masuk - $keluar;

        return [
            'do_count' => $doCount,
            'do_total' => $doTotal,
            'op_count' => $opCount,
            'op_total' => $opTotal,
            'saldo_awal' => $saldoAwal,
            'total_masuk' => $masuk,
            'total_keluar' => $keluar,
            'saldo_sistem' => $saldoSistem,
        ];
    }

    public function schema(Schema $schema): Schema
    {
        return $schema
            ->state($this->getData())
            ->schema([
                Section::make('Ringkasan Transaksi Pembelian (DO)')
                    ->description('Ringkasan volume dan nilai pembelian buah sawit.')
                    ->icon('heroicon-o-shopping-cart')
                    ->collapsible()
                    ->schema([
                        TextEntry::make('do_count')
                            ->label('Banyak DO')
                            ->badge()
                            ->color('primary')
                            ->suffix(' DO'),
                        TextEntry::make('do_total')
                            ->label('Total Pembelian DO')
                            ->money('IDR')
                            ->size(TextSize::Large)
                            ->weight('bold'),
                    ])->columns(2),

                Section::make('Biaya Operasional')
                    ->description('Total pengeluaran biaya operasional kantor dan lapangan.')
                    ->icon('heroicon-o-banknotes')
                    ->collapsible()
                    ->schema([
                        TextEntry::make('op_count')
                            ->label('Jumlah Item')
                            ->badge()
                            ->color('warning')
                            ->suffix(' Item'),
                        TextEntry::make('op_total')
                            ->label('Total Biaya')
                            ->money('IDR')
                            ->size(TextSize::Large)
                            ->weight('bold'),
                    ])->columns(2),

                Section::make('Rekonsiliasi Kas Tunai')
                    ->description('Perhitungan arus kas masuk dan keluar sistem.')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        TextEntry::make('saldo_awal')
                            ->label('Saldo Awal')
                            ->money('IDR'),
                        TextEntry::make('total_masuk')
                            ->label('Total Pemasukan')
                            ->color('success')
                            ->money('IDR'),
                        TextEntry::make('total_keluar')
                            ->label('Total Pengeluaran')
                            ->color('danger')
                            ->money('IDR'),
                        TextEntry::make('saldo_sistem')
                            ->label('SALDO AKHIR SISTEM')
                            ->money('IDR')
                            ->size(TextSize::Large)
                            ->weight('black')
                            ->color('primary'),
                    ])->columns(2),
            ]);
    }

    public function render()
    {
        return view('livewire.summary-kas-infolist');
    }
}
