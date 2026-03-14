<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$perusahaans = \App\Models\Perusahaan::all();
echo "Total Perusahaan (all): " . $perusahaans->count() . "\n";
foreach ($perusahaans as $p) {
    echo "ID: {$p->id} | Name: {$p->name} | Slug: {$p->slug}\n";
}

$sa = \App\Models\User::where('email', 'superadmin@gmail.com')->first();
if ($sa) {
    $roles = \DB::table('model_has_roles')
        ->join('roles', 'roles.id', 'model_has_roles.role_id')
        ->where('model_has_roles.model_id', $sa->id)
        ->get(['roles.name', 'model_has_roles.perusahaan_id']);
    echo "\nRoles Superadmin:\n";
    foreach ($roles as $r) {
        echo "Role: {$r->name} | Perusahaan ID: {$r->perusahaan_id}\n";
    }
}
