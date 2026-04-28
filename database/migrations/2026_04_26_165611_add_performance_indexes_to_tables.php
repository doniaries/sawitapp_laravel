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
        // Composite Index for Transactions
        Schema::table('transaksi_operasional', function (Blueprint $table) {
            $indexes = collect(\Illuminate\Support\Facades\DB::select("SHOW INDEXES FROM transaksi_operasional"))->pluck('Key_name');
            if (!$indexes->contains('idx_ops_perusahaan_tanggal')) {
                $table->index(['perusahaan_id', 'tanggal'], 'idx_ops_perusahaan_tanggal');
            }
        });

        Schema::table('tambah_saldo', function (Blueprint $table) {
            $indexes = collect(\Illuminate\Support\Facades\DB::select("SHOW INDEXES FROM tambah_saldo"))->pluck('Key_name');
            if (!$indexes->contains('idx_saldo_perusahaan_tanggal')) {
                $table->index(['perusahaan_id', 'tanggal'], 'idx_saldo_perusahaan_tanggal');
            }
        });

        Schema::table('pembayaran_hutang', function (Blueprint $table) {
            $indexes = collect(\Illuminate\Support\Facades\DB::select("SHOW INDEXES FROM pembayaran_hutang"))->pluck('Key_name');
            if (!$indexes->contains('idx_hutang_perusahaan_tanggal')) {
                $table->index(['perusahaan_id', 'tanggal'], 'idx_hutang_perusahaan_tanggal');
            }
        });

        // Single Index for Master Data to speed up tenant scoping
        Schema::table('penjual', function (Blueprint $table) {
            $indexes = collect(\Illuminate\Support\Facades\DB::select("SHOW INDEXES FROM penjual"))->pluck('Key_name');
            if (!$indexes->contains('idx_penjual_perusahaan')) {
                $table->index('perusahaan_id', 'idx_penjual_perusahaan');
            }
        });

        Schema::table('supir', function (Blueprint $table) {
            $indexes = collect(\Illuminate\Support\Facades\DB::select("SHOW INDEXES FROM supir"))->pluck('Key_name');
            if (!$indexes->contains('idx_supir_perusahaan')) {
                $table->index('perusahaan_id', 'idx_supir_perusahaan');
            }
        });

        Schema::table('pekerja', function (Blueprint $table) {
            $indexes = collect(\Illuminate\Support\Facades\DB::select("SHOW INDEXES FROM pekerja"))->pluck('Key_name');
            if (!$indexes->contains('idx_pekerja_perusahaan')) {
                $table->index('perusahaan_id', 'idx_pekerja_perusahaan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_operasional', function (Blueprint $table) {
            $table->dropIndex('idx_ops_perusahaan_tanggal');
        });

        Schema::table('tambah_saldo', function (Blueprint $table) {
            $table->dropIndex('idx_saldo_perusahaan_tanggal');
        });

        Schema::table('pembayaran_hutang', function (Blueprint $table) {
            $table->dropIndex('idx_hutang_perusahaan_tanggal');
        });

        Schema::table('penjual', function (Blueprint $table) {
            $table->dropIndex('idx_penjual_perusahaan');
        });

        Schema::table('supir', function (Blueprint $table) {
            $table->dropIndex('idx_supir_perusahaan');
        });

        Schema::table('pekerja', function (Blueprint $table) {
            $table->dropIndex('idx_pekerja_perusahaan');
        });
    }
};
