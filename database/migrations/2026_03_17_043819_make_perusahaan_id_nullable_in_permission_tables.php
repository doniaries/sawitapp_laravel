<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. model_has_roles
        Schema::table('model_has_roles', function (Blueprint $table) {
            // Drop current primary key
            $table->dropPrimary('model_has_roles_role_model_type_primary');
        });

        Schema::table('model_has_roles', function (Blueprint $table) {
            // Make perusahaan_id nullable
            $table->unsignedBigInteger('perusahaan_id')->nullable()->change();
            
            // Add unique index that allows NULL
            $table->unique(['role_id', 'model_id', 'model_type', 'perusahaan_id'], 'model_has_roles_role_model_type_unique');
        });

        // 2. roles
        Schema::table('roles', function (Blueprint $table) {
            // Handle roles table if it has unique constraint with perusahaan_id
            try {
                $table->dropUnique('roles_perusahaan_id_name_guard_name_unique');
            } catch (\Exception $e) {}

            $table->unsignedBigInteger('perusahaan_id')->nullable()->change();
            $table->unique(['perusahaan_id', 'name', 'guard_name'], 'roles_perusahaan_id_name_guard_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique('roles_perusahaan_id_name_guard_name_unique');
            $table->unsignedBigInteger('perusahaan_id')->nullable(false)->change();
            $table->unique(['perusahaan_id', 'name', 'guard_name'], 'roles_perusahaan_id_name_guard_name_unique');
        });

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropUnique('model_has_roles_role_model_type_unique');
            $table->unsignedBigInteger('perusahaan_id')->nullable(false)->change();
            $table->primary(['perusahaan_id', 'role_id', 'model_id', 'model_type'], 'model_has_roles_role_model_type_primary');
        });
    }
};
