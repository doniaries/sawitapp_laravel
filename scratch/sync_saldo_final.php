<?php
use App\Models\JurnalKeuangan;
use App\Models\Perusahaan;

$perusahaan = Perusahaan::find(1);
$initialSaldo = 0; // Mulai dari nol karena sudah ada jurnal Top Up 50jt

$journals = JurnalKeuangan::where('perusahaan_id', 1)
    ->where('mempengaruhi_kas', true)
    ->get();

$netChange = 0;
foreach ($journals as $journal) {
    if ($journal->jenis_transaksi === 'Pemasukan') {
        $netChange += (float)$journal->nominal;
    } else {
        $netChange -= (float)$journal->nominal;
    }
}

$newSaldo = $initialSaldo + $netChange;
$perusahaan->saldo = $newSaldo;
$perusahaan->save();

echo "Sinkronisasi Berhasil!\n";
echo "Saldo Akhir: " . number_format($newSaldo, 0, ',', '.') . "\n";
