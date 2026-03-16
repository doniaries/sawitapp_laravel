<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting advanced database repair for global roles...\n";

$database = config('database.connections.mysql.database');
$table = 'model_has_roles';

try {
    // 1. Drop all Foreign Keys on model_has_roles
    echo "Finding foreign keys on $table...\n";
    $fks = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = '$table' AND TABLE_SCHEMA = '$database' AND CONSTRAINT_NAME != 'PRIMARY' AND CONSTRAINT_NAME IS NOT NULL");
    
    foreach ($fks as $fk) {
        $name = $fk->CONSTRAINT_NAME;
        if (str_contains($name, '_foreign')) {
            echo "Dropping FK: $name...\n";
            try {
                DB::statement("ALTER TABLE $table DROP FOREIGN KEY $name");
            } catch (\Exception $e) {
                echo "Warning dropping $name: " . $e->getMessage() . "\n";
            }
        }
    }

    // 2. Drop Primary Key
    echo "Dropping primary key...\n";
    try {
        DB::statement("ALTER TABLE $table DROP PRIMARY KEY");
    } catch (\Exception $e) {
        echo "PK drop info: " . $e->getMessage() . "\n";
    }

    // 3. Make perusahaan_id nullable
    echo "Making perusahaan_id nullable...\n";
    DB::statement("ALTER TABLE $table MODIFY perusahaan_id BIGINT UNSIGNED NULL");

    // 4. Add UNIQUE index instead of PRIMARY to support NULL
    echo "Adding unique index...\n";
    try {
        DB::statement("ALTER TABLE $table ADD UNIQUE KEY model_has_roles_role_model_type_unique (role_id, model_id, model_type, perusahaan_id)");
    } catch (\Exception $e) {
        echo "Unique key info: " . $e->getMessage() . "\n";
    }

    // 5. Re-add Foreign Key for role_id (standard for Spatie)
    echo "Re-adding role_id foreign key...\n";
    try {
        DB::statement("ALTER TABLE $table ADD CONSTRAINT model_has_roles_role_id_foreign FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE");
    } catch (\Exception $e) {
        echo "FK role_id info: " . $e->getMessage() . "\n";
    }

    // 6. Fix roles for Admin and SuperAdmin
    $emails = [
        'superadmin@gmail.com' => 'super_admin',
        'yondra@gmail.com' => 'admin'
    ];

    foreach ($emails as $email => $roleName) {
        $user = User::where('email', $email)->first();
        if ($user) {
            echo "Assigning $roleName to $email...\n";
            DB::table($table)->where('model_id', $user->id)->delete();
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                DB::table($table)->insert([
                    'role_id' => $role->id,
                    'model_id' => $user->id,
                    'model_type' => User::class,
                    'perusahaan_id' => null
                ]);
                echo "Success: $email is now global $roleName.\n";
            }
        }
    }

    echo "Repair completed successfully.\n";

} catch (\Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}
