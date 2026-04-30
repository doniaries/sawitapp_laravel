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
            $table->boolean('is_mismatch')->default(false)->after('cara_bayar');
            $table->string('bukti_rekap')->nullable()->after('is_mismatch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_do', function (Blueprint $table) {
            $table->dropColumn(['is_mismatch', 'bukti_rekap']);
        });
    }
};
