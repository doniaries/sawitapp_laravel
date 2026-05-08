<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JurnalKeuangan;

$journals = JurnalKeuangan::where('perusahaan_id', 1)
    ->where('mempengaruhi_kas', true)
    ->where('jenis_transaksi', 'Pemasukan')
    ->get();

echo "DAFTAR PEMASUKAN AKTIF:\n";
foreach ($journals as $j) {
    echo "ID: {$j->id} | Kategori: {$j->sub_kategori} | Nominal: {$j->nominal} | Referensi ID: {$j->referensi_id}\n";
}
