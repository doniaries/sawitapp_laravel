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
            if (!Schema::hasIndex('transaksi_do', 'idx_do_perusahaan_tanggal')) {
                $table->index(['perusahaan_id', 'tanggal'], 'idx_do_perusahaan_tanggal');
            }
            if (!Schema::hasIndex('transaksi_do', 'idx_do_penjual')) {
                $table->index('penjual_id', 'idx_do_penjual');
            }
            if (!Schema::hasIndex('transaksi_do', 'idx_do_supir')) {
                $table->index('supir_id', 'idx_do_supir');
            }
            if (!Schema::hasIndex('transaksi_do', 'idx_do_nomor')) {
                $table->index('nomor', 'idx_do_nomor');
            }
        });

        Schema::table('jurnal_keuangan', function (Blueprint $table) {
            if (!Schema::hasIndex('jurnal_keuangan', 'idx_jurnal_perusahaan_tanggal')) {
                $table->index(['perusahaan_id', 'tanggal'], 'idx_jurnal_perusahaan_tanggal');
            }
            if (!Schema::hasIndex('jurnal_keuangan', 'idx_jurnal_source_ref')) {
                $table->index(['sumber_transaksi', 'referensi_id'], 'idx_jurnal_source_ref');
            }
            if (!Schema::hasIndex('jurnal_keuangan', 'idx_jurnal_cat_type')) {
                $table->index(['kategori', 'jenis_transaksi'], 'idx_jurnal_cat_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_do', function (Blueprint $table) {
            $table->dropIndex('idx_do_perusahaan_tanggal');
            $table->dropIndex('idx_do_penjual');
            $table->dropIndex('idx_do_supir');
            $table->dropIndex('idx_do_nomor');
        });

        Schema::table('jurnal_keuangan', function (Blueprint $table) {
            $table->dropIndex('idx_jurnal_perusahaan_tanggal');
            $table->dropIndex('idx_jurnal_source_ref');
            $table->dropIndex('idx_jurnal_cat_type');
        });
    }
};
