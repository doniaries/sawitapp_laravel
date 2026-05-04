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
        Schema::table('jurnal_keuangan', function (Blueprint $table) {
            // Composite index untuk filter tenant + tanggal (untuk laporan & Tutup Hari)
            $table->index(['perusahaan_id', 'tanggal'], 'idx_jurnal_perusahaan_tanggal');
            
            // Composite index untuk pencarian referensi per tenant (untuk sinkronisasi Job)
            $table->index(['perusahaan_id', 'sumber_transaksi', 'referensi_id'], 'idx_jurnal_tenant_ref');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jurnal_keuangan', function (Blueprint $table) {
            $table->dropIndex('idx_jurnal_perusahaan_tanggal');
            $table->dropIndex('idx_jurnal_tenant_ref');
        });
    }
};
