<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Perusahaan;
use App\Models\JurnalKeuangan;

$p = Perusahaan::find(1);

$pemasukan = JurnalKeuangan::where('perusahaan_id', $p->id)
    ->where('mempengaruhi_kas', true)
    ->where('jenis_transaksi', 'Pemasukan')
    ->sum('nominal');

$pengeluaran = JurnalKeuangan::where('perusahaan_id', $p->id)
    ->where('mempengaruhi_kas', true)
    ->where('jenis_transaksi', 'Pengeluaran')
    ->sum('nominal');

$saldoBaru = $pemasukan - $pengeluaran;
$p->update(['saldo' => $saldoBaru]);

echo "PEMASUKAN: " . number_format($pemasukan) . "\n";
echo "PENGELUARAN: " . number_format($pengeluaran) . "\n";
echo "SALDO AKHIR: " . number_format($saldoBaru) . "\n";
