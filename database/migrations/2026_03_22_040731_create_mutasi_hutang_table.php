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
        Schema::create('mutasi_hutang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perusahaan_id')->constrained('perusahaan')->onDelete('cascade');
            $table->morphs('pihak');
            $table->date('tanggal');
            $table->enum('tipe', ['HUTANG_MASUK', 'HUTANG_KELUAR']);
            $table->decimal('nominal', 15, 2);
            $table->decimal('saldo_akhir', 15, 2);
            $table->nullableMorphs('referensi');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasi_hutang');
    }
};
