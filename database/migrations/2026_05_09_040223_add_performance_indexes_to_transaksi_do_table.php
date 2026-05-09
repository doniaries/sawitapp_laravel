<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Transaksi DO Indexes
        Schema::table('transaksi_do', function (Blueprint $table) {
            $indexes = collect(DB::select("SHOW INDEXES FROM transaksi_do"))->pluck('Key_name');
            
            if (!$indexes->contains('idx_do_perusahaan_tanggal')) {
                $table->index(['perusahaan_id', 'tanggal'], 'idx_do_perusahaan_tanggal');
            }
            
            if (!$indexes->contains('idx_do_perusahaan_bayar_tanggal')) {
                $table->index(['perusahaan_id', 'cara_bayar', 'tanggal'], 'idx_do_perusahaan_bayar_tanggal');
            }
        });

        // Transaksi Operasional Indexes
        Schema::table('transaksi_operasional', function (Blueprint $table) {
            $indexes = collect(DB::select("SHOW INDEXES FROM transaksi_operasional"))->pluck('Key_name');
            
            if (!$indexes->contains('idx_ops_perusahaan_tanggal')) {
                $table->index(['perusahaan_id', 'tanggal'], 'idx_ops_perusahaan_tanggal');
            }
            
            if (!$indexes->contains('idx_ops_perusahaan_type_tanggal')) {
                $table->index(['perusahaan_id', 'operasional', 'tanggal'], 'idx_ops_perusahaan_type_tanggal');
            }
        });

        // Tambah Saldo Indexes
        Schema::table('tambah_saldo', function (Blueprint $table) {
            $indexes = collect(DB::select("SHOW INDEXES FROM tambah_saldo"))->pluck('Key_name');
            
            if (!$indexes->contains('idx_saldo_perusahaan_tanggal')) {
                $table->index(['perusahaan_id', 'tanggal'], 'idx_saldo_perusahaan_tanggal');
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
            $table->dropIndex('idx_do_perusahaan_bayar_tanggal');
        });

        Schema::table('transaksi_operasional', function (Blueprint $table) {
            $table->dropIndex('idx_ops_perusahaan_tanggal');
            $table->dropIndex('idx_ops_perusahaan_type_tanggal');
        });

        Schema::table('tambah_saldo', function (Blueprint $table) {
            $table->dropIndex('idx_saldo_perusahaan_tanggal');
        });
    }
};
