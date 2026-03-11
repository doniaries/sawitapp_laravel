<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_pembayaran_hutangs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('tanggal');
            $table->decimal('nominal', 15, 0);
            $table->string('tipe_nama')->nullable();
            $table->foreignId('penjual_id')->nullable()->constrained('penjuals')->cascadeOnDelete();
            $table->foreignId('pekerja_id')->nullable()->constrained('pekerja')->cascadeOnDelete();
            $table->foreignId('operasional_id')->constrained('operasional')->cascadeOnDelete();
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
        Schema::dropIfExists('riwayat_pembayaran_hutangs');
    }
};
