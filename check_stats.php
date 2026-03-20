<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TransaksiDo;
use App\Models\TransaksiOperasional;
use Carbon\Carbon;

$month = Carbon::now()->month;
$year = Carbon::now()->year;

$pembayaranHutang = TransaksiDo::whereMonth('tanggal', $month)->whereYear('tanggal', $year)->sum('pembayaran_hutang');
$operasionalMasuk = TransaksiOperasional::where('operasional', 'pemasukan')->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->sum('nominal');

$doPengeluaran = TransaksiDo::whereMonth('tanggal', $month)->whereYear('tanggal', $year)->sum('sisa_bayar');
$operasionalKeluar = TransaksiOperasional::where('operasional', 'pengeluaran')->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->sum('nominal');

$totalTransaksi = TransaksiDo::whereMonth('tanggal', $month)->whereYear('tanggal', $year)->count();

echo "PEMASUKAN:\n";
echo "Hutang (pembayaran_hutang): " . $pembayaranHutang . "\n";
echo "Operasional Masuk: " . $operasionalMasuk . "\n";
echo "Total Pemasukan: " . ($pembayaranHutang + $operasionalMasuk) . "\n\n";

echo "PENGELUARAN:\n";
echo "DO (sisa_bayar): " . $doPengeluaran . "\n";
echo "Operasional Keluar: " . $operasionalKeluar . "\n";
echo "Total Pengeluaran: " . ($doPengeluaran + $operasionalKeluar) . "\n\n";

echo "TRANSAKSI:\n";
echo "Total DO: " . $totalTransaksi . "\n";
echo "Periode: " . Carbon::now()->startOfMonth()->format('d M Y') . " - " . Carbon::now()->endOfMonth()->format('d M Y') . "\n";
