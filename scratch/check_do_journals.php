<?php
use App\Models\JurnalKeuangan;

$journals = JurnalKeuangan::where('nomor_referensi', 'DO-20260508-0002')->get();
echo "JURNAL DO-0002:\n";
foreach ($journals as $j) {
    echo "{$j->sub_kategori}: " . number_format($j->nominal, 0) . " ({$j->jenis_transaksi})\n";
}
