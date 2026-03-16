<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Perusahaan;

echo "--- DELETING USER ID 4 ---\n";
$u4 = User::withoutGlobalScopes()->find(4);
if ($u4) {
    if ($u4->forceDelete()) {
        echo "User ID 4 successfully deleted.\n";
    } else {
        echo "Failed to delete User ID 4.\n";
    }
} else {
    echo "User ID 4 not found.\n";
}

echo "\n--- SYNCING USER ID 2 (Yondra) ---\n";
$u2 = User::withoutGlobalScopes()->where('email', 'yondra@gmail.com')->first();
if ($u2) {
    $p1 = Perusahaan::where('name', 'CV SUCCESS MANDIRI')->first();
    $p2 = Perusahaan::where('name', 'PT Andala Integrasi Global')->first();
    
    if ($p1 && $p2) {
        $u2->perusahaans()->sync([$p1->id, $p2->id]);
        echo "User ID 2 synced with: " . $p1->name . " and " . $p2->name . "\n";
    } else {
        echo "Could not find one or both companies (CV SUCCESS MANDIRI / PT Andala Integrasi Global).\n";
    }
} else {
    echo "User ID 2 (yondra@gmail.com) not found.\n";
}
