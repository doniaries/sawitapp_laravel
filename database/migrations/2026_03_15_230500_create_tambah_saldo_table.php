<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tambah_saldo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perusahaan_id')->constrained('perusahaan')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('tanggal');
            $table->decimal('nominal', 15, 0);
            $table->text('keterangan')->nullable();
            $table->string('bukti_transfer')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('perusahaan_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tambah_saldo');
    }
};
