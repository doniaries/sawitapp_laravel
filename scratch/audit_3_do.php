<?php

use App\Models\TransaksiDo;
use App\Jobs\ProsesJurnalDo;
use App\Actions\Finance\RecordFinanceTransactionAction;
use App\Models\JurnalKeuangan;

// 1. Bersihkan Jurnal DO hari ini yang mungkin duplikat
JurnalKeuangan::where('kategori', 'DO')->whereDate('tanggal', '2026-05-08')->delete();

// 2. Proses Jurnal untuk ketiga DO tersebut
$numbers = ['DO-20260508-0001', 'DO-20260508-0002', 'DO-20260508-0003'];
$action = new RecordFinanceTransactionAction();

foreach ($numbers as $no) {
    $do = TransaksiDo::where('nomor', $no)->first();
    if ($do) {
        echo "Memproses Jurnal $no...\n";
        (new ProsesJurnalDo($do))->handle($action);
    }
}

// 3. Sinkronisasi Saldo
echo "Menyinkronkan Saldo Akhir...\n";
$totalNet = JurnalKeuangan::where('perusahaan_id', 1)
    ->where('mempengaruhi_kas', true)
    ->selectRaw('SUM(CASE WHEN jenis_transaksi = "Pemasukan" THEN nominal ELSE -nominal END) as balance')
    ->value('balance') ?? 0;

App\Models\Perusahaan::find(1)->update(['saldo' => $totalNet]);

echo "SELESAI! Saldo Akhir di Database: " . number_format($totalNet, 0, ',', '.') . "\n";
