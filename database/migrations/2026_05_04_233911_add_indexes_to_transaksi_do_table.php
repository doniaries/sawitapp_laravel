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
        Schema::table('transaksi_do', function (Blueprint $table) {
            // Composite index untuk filter tenant + tanggal (paling sering digunakan)
            $table->index(['perusahaan_id', 'tanggal'], 'idx_perusahaan_tanggal');
            
            // Composite index untuk filter tenant + penjual (untuk laporan per penjual)
            $table->index(['perusahaan_id', 'penjual_id'], 'idx_perusahaan_penjual');
            
            // Index tambahan untuk supir
            $table->index(['perusahaan_id', 'supir_id'], 'idx_perusahaan_supir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_do', function (Blueprint $table) {
            $table->dropIndex('idx_perusahaan_tanggal');
            $table->dropIndex('idx_perusahaan_penjual');
            $table->dropIndex('idx_perusahaan_supir');
        });
    }
};
