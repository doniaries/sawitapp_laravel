<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran_hutang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perusahaan_id')->nullable()->constrained('perusahaan')->cascadeOnDelete();
            $table->timestamp('tanggal');
            $table->decimal('nominal', 15, 0);
            $table->string('tipe_nama')->nullable();
            $table->foreignId('penjual_id')->nullable()->constrained('penjual')->cascadeOnDelete();
            $table->foreignId('pekerja_id')->nullable()->constrained('pekerja')->cascadeOnDelete();
            $table->foreignId('operasional_id')->constrained('transaksi_operasional')->cascadeOnDelete();
            $table->foreignId('supir_id')->nullable()->constrained('supir')->cascadeOnDelete();
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('tanggal');
            $table->index(['tipe_nama', 'penjual_id', 'pekerja_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_hutang');
    }
};
