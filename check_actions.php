<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ref = new ReflectionClass('Filament\Tables\Table');
foreach ($ref->getMethods() as $method) {
    if (strpos(strtolower($method->name), 'action') !== false) {
        echo $method->name . PHP_EOL;
    }
}
