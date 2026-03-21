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
        // 1. Tambah Composite Index untuk Performa Laporan & Tenancy
        Schema::table('transaksi_do', function (Blueprint $table) {
            $table->index(['perusahaan_id', 'tanggal']); 
        });

        Schema::table('jurnal_keuangan', function (Blueprint $table) {
            $table->index(['perusahaan_id', 'tanggal']);
        });

        // 2. Tambah Composite Index untuk Ledger (Mutasi Hutang)
        Schema::table('mutasi_hutang', function (Blueprint $table) {
            $table->index(['perusahaan_id', 'pihak_type', 'pihak_id', 'tanggal'], 'idx_mutasi_ledger_full');
        });

        // 3. Hapus kolom redundan
        Schema::table('penjual', function (Blueprint $table) {
            if (Schema::hasColumn('penjual', 'riwayat_bayar')) {
                $table->dropColumn('riwayat_bayar');
            }
        });

        Schema::table('supir', function (Blueprint $table) {
            if (Schema::hasColumn('supir', 'riwayat_bayar')) {
                $table->dropColumn('riwayat_bayar');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_do', function (Blueprint $table) {
            $table->dropIndex(['perusahaan_id', 'tanggal']);
        });

        Schema::table('jurnal_keuangan', function (Blueprint $table) {
            $table->dropIndex(['perusahaan_id', 'tanggal']);
        });

        Schema::table('mutasi_hutang', function (Blueprint $table) {
            $table->dropIndex('idx_mutasi_ledger_full');
        });

        Schema::table('penjual', function (Blueprint $table) {
            $table->string('riwayat_bayar')->nullable();
        });

        Schema::table('supir', function (Blueprint $table) {
            $table->string('riwayat_bayar')->nullable();
        });
    }
};
