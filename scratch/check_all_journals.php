<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JurnalKeuangan;

$journals = JurnalKeuangan::where('perusahaan_id', 1)
    ->where('mempengaruhi_kas', true)
    ->get();

echo "DAFTAR SEMUA JURNAL KAS AKTIF:\n";
foreach ($journals as $j) {
    echo "ID: {$j->id} | Tanggal: {$j->tanggal->format('Y-m-d')} | Jenis: {$j->jenis_transaksi} | Sub: {$j->sub_kategori} | Nominal: {$j->nominal}\n";
}
