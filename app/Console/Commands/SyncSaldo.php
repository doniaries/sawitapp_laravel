<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Perusahaan;
use App\Models\JurnalKeuangan;
use Illuminate\Support\Facades\DB;

class SyncSaldo extends Command
{
    protected $signature = 'sync:saldo {perusahaan_id?}';
    protected $description = 'Sinkronisasi ulang saldo kas perusahaan berdasarkan riwayat jurnal';

    public function handle()
    {
        $perusahaanId = $this->argument('perusahaan_id');
        
        $perusahaans = $perusahaanId 
            ? Perusahaan::where('id', $perusahaanId)->get()
            : Perusahaan::all();

        if ($perusahaans->isEmpty()) {
            $this->error('Perusahaan tidak ditemukan.');
            return;
        }

        foreach ($perusahaans as $perusahaan) {
            $this->info("Memproses: {$perusahaan->nama}");
            
            $totalPemasukan = JurnalKeuangan::where('perusahaan_id', $perusahaan->id)
                ->where('jenis_transaksi', 'Pemasukan')
                ->where('mempengaruhi_kas', true)
                ->sum('nominal');

            $totalPengeluaran = JurnalKeuangan::where('perusahaan_id', $perusahaan->id)
                ->where('jenis_transaksi', 'Pengeluaran')
                ->where('mempengaruhi_kas', true)
                ->sum('nominal');

            $saldoBaru = $totalPemasukan - $totalPengeluaran;
            
            $oldSaldo = $perusahaan->saldo;
            $perusahaan->saldo = $saldoBaru;
            $perusahaan->save();

            $this->table(
                ['Keterangan', 'Nilai'],
                [
                    ['Saldo Lama', number_format($oldSaldo)],
                    ['Total Pemasukan (Kas)', number_format($totalPemasukan)],
                    ['Total Pengeluaran (Kas)', number_format($totalPengeluaran)],
                    ['Saldo Baru', number_format($saldoBaru)],
                ]
            );
        }

        $this->info('Sinkronisasi selesai!');
    }
}
