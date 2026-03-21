<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Penjual;
use App\Actions\Finance\ProcessDebtPayment;
use App\Models\MutasiHutang;
use App\Models\JurnalKeuangan;

DB::beginTransaction();

try {
    $penjual = Penjual::where('hutang', '>', 0)->first();
    if (!$penjual) {
        echo "No penjual with debt found.\n";
        exit;
    }

    $initialHutang = (float) $penjual->hutang;
    $payAmount = 10000;

    echo "Testing payment for {$penjual->nama}. Initial Debt: {$initialHutang}\n";

    app(ProcessDebtPayment::class)->execute(
        $penjual,
        $payAmount,
        now()->toDateTimeString(),
        'tunai',
        'Test Payment'
    );

    $penjual->refresh();
    $finalHutang = (float) $penjual->hutang;

    echo "Final Debt: {$finalHutang}\n";

    if ($finalHutang === $initialHutang - $payAmount) {
        echo "SUCCESS: Debt balance updated correctly.\n";
    } else {
        echo "FAILURE: Debt balance mismatch! Expected " . ($initialHutang - $payAmount) . " but got {$finalHutang}\n";
    }

    $mutasi = MutasiHutang::where('pihak_id', $penjual->id)->where('pihak_type', Penjual::class)->latest()->first();
    if ($mutasi && (float)$mutasi->nominal === (float)$payAmount && $mutasi->tipe === 'HUTANG_KELUAR') {
        echo "SUCCESS: Mutasi record created correctly.\n";
    } else {
        echo "FAILURE: Mutasi record missing or incorrect.\n";
    }

    $jurnal = JurnalKeuangan::where('pihak_terkait', $penjual->nama)->latest()->first();
    if ($jurnal && (float)$jurnal->nominal === (float)$payAmount) {
        echo "SUCCESS: Jurnal record created correctly.\n";
    } else {
        echo "FAILURE: Jurnal record missing or incorrect.\n";
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
} finally {
    DB::rollBack();
    echo "Rollback test data.\n";
}
