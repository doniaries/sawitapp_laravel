<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Perusahaan;
use App\Models\JurnalKeuangan;

$perusahaans = Perusahaan::all();

foreach ($perusahaans as $p) {
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
    
    echo "Perusahaan: {$p->name} | Saldo: {$saldoBaru}\n";
}
