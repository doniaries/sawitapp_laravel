<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TransaksiDo;
use App\Models\Penjual;
use App\Models\Supir;
use App\Models\Operasional;
use App\Models\LaporanKeuangan;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SimulationSeeder extends Seeder
{
    public function run(): void
    {
        $lastMonth = now()->subMonth();
        $thisMonth = now();

        // Ensure we have some penjual and supir
        $penjualIds = Penjual::pluck('id')->toArray();
        $supirIds = Supir::pluck('id')->toArray();

        // Direct Perusahaan Balance Adjustment to avoid Observer block during seeder
        $perusahaan = \App\Models\Perusahaan::first();
        if ($perusahaan) {
            $perusahaan->update(['saldo' => 1000000000]); // Set 1 Billion to be safe
        }

        // Disable Observers for simulation
        TransaksiDo::unsetEventDispatcher();

        if (empty($penjualIds)) {
            $penjual = Penjual::create(['nama' => 'SIMULASI PENJUAL 1', 'hutang' => 0]);
            $penjualIds = [$penjual->id];
        }

        if (empty($supirIds)) {
            $supir = Supir::create(['nama' => 'SIMULASI SUPIR 1']);
            $supirIds = [$supir->id];
        }

        // 1. Create 30 Transactions for Last Month
        $this->createTransactions($lastMonth, 30, $penjualIds, $supirIds);

        // 2. Create 10 Transactions for This Month (Randomized dates)
        $this->createTransactions($thisMonth, 10, $penjualIds, $supirIds);

        // 3. Create 5 TRANSACTIONS FOR TODAY (Flexible)
        $this->createTransactions(now(), 5, $penjualIds, $supirIds, true);

        // 4. Create Operasional data for simulation
        $this->createOperasional($lastMonth, 'Pemasukan Saldo Awal', 500000000, 'pemasukan');
        $this->createOperasional($lastMonth, 'Biaya Kantor Desember', 5000000, 'pengeluaran');

        $this->createOperasional($thisMonth, 'Tambah Modal Januari', 10000000, 'pemasukan');
        $this->createOperasional($thisMonth, 'Biaya Listrik Januari', 1500000, 'pengeluaran');

        $this->createOperasional(now(), 'Biaya Makan Hari Ini', 200000, 'pengeluaran');
        $this->createOperasional(now(), 'Setoran Keamanan Hari Ini', 150000, 'pengeluaran');
        $this->createOperasional(now(), 'Pemasukan Tambahan Hari Ini', 500000, 'pemasukan');

        // 5. Sync any missing old data to LaporanKeuangan
        $this->syncExistingData();
    }

    private function syncExistingData()
    {
        // Sync TransaksiDo
        $missingDos = TransaksiDo::whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('laporan_keuangan')
                ->whereColumn('laporan_keuangan.referensi_id', 'transaksi_do.id')
                ->where('laporan_keuangan.sumber_transaksi', 'DO');
        })->get();

        foreach ($missingDos as $transaksi) {
            LaporanKeuangan::create([
                'tanggal' => $transaksi->tanggal,
                'jenis_transaksi' => 'Pengeluaran',
                'kategori' => 'DO',
                'sub_kategori' => 'Pembayaran DO',
                'nominal' => $transaksi->sub_total,
                'sumber_transaksi' => 'DO',
                'referensi_id' => $transaksi->id,
                'nomor_referensi' => $transaksi->nomor,
                'pihak_terkait' => $transaksi->penjual?->nama ?? 'Penjual Lama',
                'tipe_pihak' => 'penjual',
                'cara_pembayaran' => $transaksi->cara_bayar,
                'keterangan' => "Sync: Transaksi Lama {$transaksi->nomor}",
                'mempengaruhi_kas' => $transaksi->cara_bayar === 'tunai',
            ]);
        }

        // Sync Operasional
        $missingOps = Operasional::whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('laporan_keuangan')
                ->whereColumn('laporan_keuangan.referensi_id', 'operasional.id')
                ->where('laporan_keuangan.sumber_transaksi', 'Operasional');
        })->get();

        foreach ($missingOps as $op) {
            LaporanKeuangan::create([
                'tanggal' => $op->tanggal,
                'jenis_transaksi' => ucfirst($op->operasional),
                'kategori' => 'Operasional',
                'sub_kategori' => $op->kategori,
                'nominal' => $op->nominal,
                'sumber_transaksi' => 'Operasional',
                'referensi_id' => $op->id,
                'pihak_terkait' => 'Legacy Op Sync',
                'tipe_pihak' => $op->tipe_nama,
                'cara_pembayaran' => 'tunai',
                'keterangan' => $op->keterangan,
                'mempengaruhi_kas' => true,
            ]);
        }
    }

    private function createTransactions($date, $count, $penjualIds, $supirIds, $isToday = false)
    {
        for ($i = 1; $i <= $count; $i++) {
            $tonase = rand(1000, 10000);
            $harga = rand(3000, 3500);
            $subTotal = $tonase * $harga;
            $caraBayar = ['tunai', 'transfer', 'cair di luar', 'belum dibayar'][rand(0, 3)];

            if ($isToday) {
                $tanggal = Carbon::parse($date)->setHour(rand(8, 17))->setMinute(rand(0, 59));
            } else {
                $tanggal = Carbon::parse($date)->setDay(rand(1, 28))->setHour(rand(8, 17));
            }

            $nomor = 'DO-' . $tanggal->format('Ymd') . '-' . Str::padLeft($i, 4, '0') . '-' . rand(100, 999);

            $transaksi = TransaksiDo::create([
                'nomor' => $nomor,
                'tanggal' => $tanggal,
                'penjual_id' => $penjualIds[array_rand($penjualIds)],
                'supir_id' => $supirIds[array_rand($supirIds)],
                'tonase' => $tonase,
                'harga_satuan' => $harga,
                'sub_total' => $subTotal,
                'upah_bongkar' => 0,
                'biaya_lain' => 0,
                'sisa_bayar' => $subTotal,
                'pembayaran_hutang' => 0,
                'cara_bayar' => $caraBayar,
            ]);

            // MANUALLY CREATE LAPORAN KEUANGAN since observer is disabled
            LaporanKeuangan::create([
                'tanggal' => $tanggal,
                'jenis_transaksi' => 'Pengeluaran',
                'kategori' => 'DO',
                'sub_kategori' => 'Pembayaran DO',
                'nominal' => $subTotal,
                'sumber_transaksi' => 'DO',
                'referensi_id' => $transaksi->id,
                'nomor_referensi' => $transaksi->nomor,
                'pihak_terkait' => $transaksi->penjual?->nama ?? 'Penjual Simulasi',
                'tipe_pihak' => 'penjual',
                'cara_pembayaran' => $caraBayar,
                'keterangan' => "Simulasi Transaksi {$nomor}",
                'mempengaruhi_kas' => $caraBayar === 'tunai',
            ]);
        }
    }

    private function createOperasional($date, $keterangan, $nominal, $jenis)
    {
        $tanggal = Carbon::parse($date)->setDay(rand(1, 10));

        $op = Operasional::create([
            'tanggal' => $tanggal,
            'operasional' => $jenis,
            'kategori' => $jenis === 'pemasukan' ? 'tambah_saldo' : 'lain_lain',
            'tipe_nama' => 'user',
            'user_id' => 1,
            'nominal' => $nominal,
            'keterangan' => $keterangan,
        ]);

        LaporanKeuangan::create([
            'tanggal' => $tanggal,
            'jenis_transaksi' => ucfirst($jenis),
            'kategori' => 'Operasional',
            'sub_kategori' => $op->kategori,
            'nominal' => $nominal,
            'sumber_transaksi' => 'Operasional',
            'referensi_id' => $op->id,
            'pihak_terkait' => 'System Simulation',
            'tipe_pihak' => 'user',
            'cara_pembayaran' => 'tunai',
            'keterangan' => $keterangan,
            'mempengaruhi_kas' => true,
        ]);
    }
}
