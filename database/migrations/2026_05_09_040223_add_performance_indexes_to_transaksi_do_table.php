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
            // Index untuk filter tanggal dan tenant
            $table->index(['perusahaan_id', 'tanggal']);
            
            // Index untuk filter kategori (cara_bayar) dan tanggal
            $table->index(['perusahaan_id', 'cara_bayar', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_do', function (Blueprint $table) {
            $table->dropIndex(['perusahaan_id', 'tanggal']);
            $table->dropIndex(['perusahaan_id', 'cara_bayar', 'tanggal']);
        });
    }
};
