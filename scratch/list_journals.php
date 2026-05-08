<?php
use App\Models\JurnalKeuangan;

$journals = JurnalKeuangan::where('mempengaruhi_kas', true)->get();
echo "DAFTAR JURNAL KAS:\n";
echo "--------------------------------------------------\n";
foreach ($journals as $j) {
    echo "ID: {$j->id} | {$j->jenis_transaksi} | " . number_format($j->nominal, 0) . " | {$j->keterangan}\n";
}
echo "--------------------------------------------------\n";
