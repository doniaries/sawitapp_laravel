<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_do', function (Blueprint $table) {
            $table->id();
            $table->string('nomor', 20)->unique();
            $table->dateTime('tanggal');
            $table->foreignId('penjual_id')->constrained('penjuals');
            $table->foreignId('supir_id')->nullable()->constrained('supir');
            $table->foreignId('kendaraan_id')->nullable()->constrained('kendaraan');
            $table->decimal('tonase', 10, 2);
            $table->decimal('harga_satuan', 15, 0);
            $table->decimal('sub_total', 15, 0);
            $table->decimal('upah_bongkar', 15, 0);
            $table->decimal('biaya_lain', 15, 0)->default(0);
            $table->string('keterangan_biaya_lain')->nullable();
            $table->decimal('hutang_awal', 15, 0);
            $table->decimal('pembayaran_hutang', 12, 0);
            $table->decimal('sisa_hutang_penjual', 12, 0);
            $table->decimal('sisa_bayar', 15, 0);
            // $table->string('file_do')->nullable();
            $table->enum('cara_bayar', ['tunai', 'transfer', 'cair di luar', 'belum dibayar'])->default('tunai');
            // $table->enum('status_bayar', ['Belum Lunas', 'Lunas']);
            // $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tanggal', 'penjual_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_do');
    }
};
