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
        $tables = [
            'penjuals',
            'supir',
            'kendaraan',
            'transaksi_do',
            'operasional',
            'laporan_keuangan',
            'pekerja',
            'riwayat_pembayaran_hutangs',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'perusahaan_id')) {
                        $table->foreignId('perusahaan_id')->nullable()->constrained('perusahaans')->cascadeOnDelete();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'penjuals',
            'supir',
            'kendaraan',
            'transaksi_do',
            'operasional',
            'laporan_keuangan',
            'pekerja',
            'riwayat_pembayaran_hutangs',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'perusahaan_id')) {
                        $table->dropConstrainedForeignId('perusahaan_id');
                    }
                });
            }
        }
    }
};
