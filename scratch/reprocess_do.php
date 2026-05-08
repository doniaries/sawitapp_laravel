<?php
use App\Models\JurnalKeuangan;
use App\Models\TransaksiDo;
use App\Jobs\ProsesJurnalDo;
use App\Actions\Finance\RecordFinanceTransactionAction;

// 1. Hapus Jurnal Lama
JurnalKeuangan::where('nomor_referensi', 'DO-20260508-0002')->forceDelete();

// 2. Ambil Data Terbaru (yang sudah ada Biaya Lain 200rb)
$do = TransaksiDo::where('nomor', 'DO-20260508-0002')->first();

// 3. Jalankan Logika Penjurnalan Baru secara Langsung
$job = new ProsesJurnalDo($do);
$job->handle(new RecordFinanceTransactionAction());

echo "Proses Jurnal Ulang DO-0002 Selesai!\n";
