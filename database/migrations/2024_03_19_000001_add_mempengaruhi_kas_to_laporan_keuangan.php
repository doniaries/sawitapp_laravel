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
        if (!Schema::hasColumn('laporan_keuangan', 'mempengaruhi_kas')) {
            Schema::table('laporan_keuangan', function (Blueprint $table) {
                $table->boolean('mempengaruhi_kas')->default(true)->comment('Apakah transaksi ini mempengaruhi kas atau tidak')->after('keterangan');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('laporan_keuangan', 'mempengaruhi_kas')) {
            Schema::table('laporan_keuangan', function (Blueprint $table) {
                $table->dropColumn('mempengaruhi_kas');
            });
        }
    }
};
