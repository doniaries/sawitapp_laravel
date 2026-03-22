<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JurnalKeuangan;
use Carbon\Carbon;

$today = Carbon::today();
echo "Today: " . $today->toDateString() . "\n";

$count = JurnalKeuangan::whereDate('tanggal', $today)->count();
echo "Total Jurnal Hari Ini: " . $count . "\n";

$allToday = JurnalKeuangan::whereDate('tanggal', $today)->get(['id', 'perusahaan_id', 'jenis_transaksi', 'nominal', 'tanggal']);
foreach($allToday as $j) {
    echo "ID: {$j->id} | Tenant: {$j->perusahaan_id} | Jenis: {$j->jenis_transaksi} | Nominal: {$j->nominal} | Tanggal: {$j->tanggal}\n";
}

$last5 = JurnalKeuangan::latest()->take(5)->get(['id', 'perusahaan_id', 'tanggal']);
echo "\nLast 5 Records:\n";
foreach($last5 as $j) {
    echo "ID: {$j->id} | Tenant: {$j->perusahaan_id} | Tanggal: {$j->tanggal}\n";
}
