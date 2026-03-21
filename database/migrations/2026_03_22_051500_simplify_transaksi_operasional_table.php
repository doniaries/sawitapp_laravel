<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // Tahap 1: Hapus Foreign Keys & Indexes
        Schema::table('transaksi_operasional', function (Blueprint $table) {
            // Drop foreign keys
            $foreignKeys = [
                'transaksi_operasional_penjual_id_foreign',
                'transaksi_operasional_user_id_foreign',
                'transaksi_operasional_supir_id_foreign'
            ];

            foreach ($foreignKeys as $fk) {
                $exists = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                                    WHERE TABLE_NAME = 'transaksi_operasional' 
                                    AND CONSTRAINT_NAME = '{$fk}' 
                                    AND TABLE_SCHEMA = DATABASE()");
                
                if (!empty($exists)) {
                    $table->dropForeign($fk);
                }
            }

            // Drop indexes for columns that will be dropped
            $indexes = [
                'transaksi_operasional_tipe_nama_index',
                'transaksi_operasional_pekerja_id_index'
            ];

            foreach ($indexes as $idx) {
                $exists = DB::select("SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS 
                                    WHERE TABLE_NAME = 'transaksi_operasional' 
                                    AND INDEX_NAME = '{$idx}' 
                                    AND TABLE_SCHEMA = DATABASE()");
                
                if (!empty($exists)) {
                    $table->dropIndex($idx);
                }
            }
        });

        // Tahap 2: Hapus Kolom
        Schema::table('transaksi_operasional', function (Blueprint $table) {
            $cols = ['penjual_id', 'user_id', 'supir_id', 'pekerja_id', 'tipe_nama'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('transaksi_operasional', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::table('transaksi_operasional', function (Blueprint $table) {
            $table->enum('tipe_nama', ['penjual', 'user', 'supir', 'pekerja'])->nullable();
            $table->foreignId('penjual_id')->nullable()->constrained('penjual')->nullOnDelete();
            $table->unsignedBigInteger('pekerja_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('supir_id')->nullable()->constrained('supir')->nullOnDelete();
        });
    }
};
