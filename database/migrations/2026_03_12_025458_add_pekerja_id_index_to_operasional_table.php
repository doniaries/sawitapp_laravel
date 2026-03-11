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
        Schema::table('operasional', function (Blueprint $table) {
            if (!Schema::hasIndex('operasional', 'operasional_pekerja_id_index')) {
                $table->index('pekerja_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operasional', function (Blueprint $table) {
            $table->dropIndex(['pekerja_id']);
        });
    }
};
