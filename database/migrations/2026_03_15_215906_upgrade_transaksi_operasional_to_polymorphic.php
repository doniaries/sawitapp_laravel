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
        Schema::table('transaksi_operasional', function (Blueprint $table) {
            $table->nullableMorphs('pihak');
            $table->index(['perusahaan_id', 'pihak_type', 'pihak_id'], 'idx_operasional_pihak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_operasional', function (Blueprint $table) {
            $table->dropIndex('idx_operasional_pihak');
            $table->dropMorphs('pihak');
        });
    }
};
