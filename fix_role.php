<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting database fix...\n";

try {
    // 1. Drop existing primary key if it exists
    echo "Dropping primary key...\n";
    DB::statement('ALTER TABLE model_has_roles DROP PRIMARY KEY');
} catch (\Exception $e) {
    echo "Primary key drop info: " . $e->getMessage() . "\n";
}

try {
    // 2. Make perusahaan_id nullable
    echo "Making perusahaan_id nullable...\n";
    DB::statement('ALTER TABLE model_has_roles MODIFY perusahaan_id BIGINT UNSIGNED NULL');
} catch (\Exception $e) {
    echo "Error making nullable: " . $e->getMessage() . "\n";
}

try {
    // 3. Add UNIQUE index instead of PRIMARY
    echo "Adding unique index...\n";
    DB::statement('ALTER TABLE model_has_roles ADD UNIQUE KEY model_has_roles_role_model_type_unique (role_id, model_id, model_type, perusahaan_id)');
} catch (\Exception $e) {
    echo "Unique index info: " . $e->getMessage() . "\n";
}

// Now handle the roles
$emails = ['superadmin@gmail.com', 'yondra@gmail.com'];
foreach ($emails as $email) {
    $user = User::where('email', $email)->first();
    if ($user) {
        echo "Processing user: $email\n";
        
        // Clear existing roles in model_has_roles
        DB::table('model_has_roles')->where('model_id', $user->id)->delete();
        
        $roleName = ($email === 'superadmin@gmail.com') ? 'super_admin' : 'admin';
        $role = Role::where('name', $roleName)->first();
        
        if ($role) {
            DB::table('model_has_roles')->insert([
                'role_id' => $role->id,
                'model_id' => $user->id,
                'model_type' => User::class,
                'perusahaan_id' => null
            ]);
            echo "Successfully assigned $roleName globally to $email\n";
        }
    }
}

echo "Database fix completed.\n";
