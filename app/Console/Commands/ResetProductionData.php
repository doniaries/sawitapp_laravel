<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetProductionData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-production-data';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Me-nol-kan data transaksi dan finansial untuk persiapan Go-Live (Keep Master Data)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->laravel->environment('production')) {
            if (!$this->confirm('PERHATIAN: Anda sedang di PRODUKSI. Perintah ini akan MENGHAPUS SEMUA DATA TRANSAKSI. Lanjutkan?')) {
                return;
            }
        }

        $this->info('Memulai pembersihan data...');

        \Illuminate\Support\Facades\DB::transaction(function () {
            // Disable foreign key checks
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Tabel-tabel yang akan di-truncate (Dihapus isinya)
            $tables = [
                'transaksi_do',
                'transaksi_operasional',
                'lansirs',
                'jurnal_keuangan',
                'pembayaran_hutang',
                'tambah_saldo',
                'mutasi_hutang',
                'notifications',
                'failed_jobs',
            ];

            foreach ($tables as $table) {
                if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                    \Illuminate\Support\Facades\DB::table($table)->truncate();
                    $this->line("Tabel {$table} berhasil dikosongkan.");
                }
            }

            // Reset Saldo Perusahaan di Master Data
            if (\Illuminate\Support\Facades\Schema::hasTable('perusahaan')) {
                \Illuminate\Support\Facades\DB::table('perusahaan')->update([
                    'saldo' => 0,
                    'sisa_saldo_kemarin' => 0,
                    'sudah_diproses' => false,
                ]);
                $this->line("Saldo semua perusahaan di-reset ke 0.");
            }

            // Reset Hutang Penjual di Master Data
            if (\Illuminate\Support\Facades\Schema::hasTable('penjual')) {
                \Illuminate\Support\Facades\DB::table('penjual')->update([
                    'hutang' => 0,
                ]);
                $this->line("Hutang semua penjual di-reset ke 0.");
            }

            // Re-enable foreign key checks
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1');
        });

        $this->info('Pembersihan data selesai. Sistem siap untuk Go-Live!');
    }

}
