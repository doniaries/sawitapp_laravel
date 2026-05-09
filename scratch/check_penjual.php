<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Penjual Names:\n";
foreach(DB::table('penjual')->get() as $p) {
    echo "- " . $p->nama . "\n";
}
