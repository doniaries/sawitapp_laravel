<?php

namespace App\Console\Commands;

use App\Models\TransaksiDo;
use App\Models\Lansir;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SinkronisasiKalkulasiTransaksi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-calculations {--dry-run : Menampilkan perubahan tanpa menyimpan ke database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi ulang kalkulasi subtotal dan total bayar untuk data transaksi lama di production';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai sinkronisasi kalkulasi transaksi...');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('MODE SIMULASI: Tidak ada perubahan yang disimpan ke database.');
        }

        DB::transaction(function () use ($dryRun) {
            // 1. Sinkronisasi Transaksi DO
            $this->syncTransaksiDo($dryRun);

            // 2. Sinkronisasi Lansir
            $this->syncLansir($dryRun);
        });

        $this->info('Sinkronisasi selesai!');
    }

    protected function syncTransaksiDo($dryRun)
    {
        $records = TransaksiDo::all();
        $this->info("Memproses " . $records->count() . " data Transaksi DO...");

        $bar = $this->output->createProgressBar($records->count());
        $updatedCount = 0;

        foreach ($records as $item) {
            $oldSubTotal = (float) $item->sub_total;
            $oldSisaBayar = (float) $item->sisa_bayar;

            // Logika kalkulasi baru
            // Jika data lama 29.000 tersimpan sebagai 29, kita asumsikan tonase * harga adalah yang benar
            $newSubTotal = (float) $item->tonase * (float) $item->harga_satuan;
            $pengurangan = (float) ($item->upah_bongkar ?? 0) + (float) ($item->biaya_lain ?? 0) + (float) ($item->pembayaran_hutang ?? 0);
            $newSisaBayar = max(0, $newSubTotal - $pengurangan);

            if ($oldSubTotal != $newSubTotal || $oldSisaBayar != $newSisaBayar) {
                if (!$dryRun) {
                    $item->sub_total = $newSubTotal;
                    $item->sisa_bayar = $newSisaBayar;
                    $item->save(); // Ini akan memicu observer untuk update jurnal
                }
                $updatedCount++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Transaksi DO diperbarui: {$updatedCount}");
    }

    protected function syncLansir($dryRun)
    {
        $records = Lansir::all();
        $this->info("Memproses " . $records->count() . " data Lansir...");

        $bar = $this->output->createProgressBar($records->count());
        $updatedCount = 0;

        foreach ($records as $item) {
            $oldTotal = (float) $item->total;
            $oldUpah = (float) $item->upah;

            // Logika kalkulasi Lansir
            $newTotal = (float) $item->tonase * (float) $item->harga_satuan;
            $newUpah = (float) $item->tonase * 100; // Rp 100/Kg atau Rp 100k/Ton

            if ($oldTotal != $newTotal || $oldUpah != $newUpah) {
                if (!$dryRun) {
                    $item->total = $newTotal;
                    $item->upah = $newUpah;
                    $item->save();
                }
                $updatedCount++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Lansir diperbarui: {$updatedCount}");
    }
}
