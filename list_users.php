<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use App\Models\User;
$users = User::withoutGlobalScopes()->get();
foreach ($users as $u) {
    printf("ID:%d | E:%s | N:%s | P:%s\n", $u->id, $u->email, $u->name, $u->perusahaan_id ?? 'NULL');
}
