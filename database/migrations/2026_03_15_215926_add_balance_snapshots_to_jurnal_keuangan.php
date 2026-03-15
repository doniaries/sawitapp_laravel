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
            $table->decimal('saldo_awal', 20, 0)->default(0)->after('nominal');
            $table->decimal('saldo_akhir', 20, 0)->default(0)->after('saldo_awal');
            $table->index(['perusahaan_id', 'tanggal'], 'idx_jurnal_perusahaan_tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jurnal_keuangan', function (Blueprint $table) {
            $table->dropIndex('idx_jurnal_perusahaan_tanggal');
            $table->dropColumn(['saldo_awal', 'saldo_akhir']);
        });
    }
};
