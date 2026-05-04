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
        // 1. Jurnal Keuangan
        Schema::table('jurnal_keuangan', function (Blueprint $table) {
            // Index untuk sinkronisasi Job (Sumber + Referensi) per tenant
            $table->index(['perusahaan_id', 'sumber_transaksi', 'referensi_id'], 'idx_jurnal_tenant_ref');
        });

        // 2. Penjual
        Schema::table('penjual', function (Blueprint $table) {
            $table->index(['perusahaan_id', 'nama'], 'idx_penjual_perusahaan_nama');
        });

        // 3. Supir
        Schema::table('supir', function (Blueprint $table) {
            $table->index(['perusahaan_id', 'nama'], 'idx_supir_perusahaan_nama');
        });

        // 4. Tambah Saldo
        Schema::table('tambah_saldo', function (Blueprint $table) {
            $table->index(['perusahaan_id', 'tanggal'], 'idx_tambah_saldo_tenant_tgl');
        });

        // 5. Transaksi Operasional
        Schema::table('transaksi_operasional', function (Blueprint $table) {
            $table->index(['perusahaan_id', 'tanggal'], 'idx_operasional_tenant_tgl');
        });

        // 6. Tutup Hari
        Schema::table('tutup_hari', function (Blueprint $table) {
            $table->index(['perusahaan_id', 'tanggal'], 'idx_tutup_hari_tenant_tgl');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jurnal_keuangan', function (Blueprint $table) { $table->dropIndex('idx_jurnal_tenant_ref'); });
        Schema::table('penjual', function (Blueprint $table) { $table->dropIndex('idx_penjual_perusahaan_nama'); });
        Schema::table('supir', function (Blueprint $table) { $table->dropIndex('idx_supir_perusahaan_nama'); });
        Schema::table('tambah_saldo', function (Blueprint $table) { $table->dropIndex('idx_tambah_saldo_tenant_tgl'); });
        Schema::table('transaksi_operasional', function (Blueprint $table) { $table->dropIndex('idx_operasional_tenant_tgl'); });
        Schema::table('tutup_hari', function (Blueprint $table) { $table->dropIndex('idx_tutup_hari_tenant_tgl'); });
    }
};
