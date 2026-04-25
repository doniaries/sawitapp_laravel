<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tambah_saldo', function (Blueprint $col) {
            $col->id();
            $col->foreignId('perusahaan_id')->constrained('perusahaan')->onDelete('cascade');
            $col->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Kasir/Admin yang input
            $col->dateTime('tanggal');
            $col->decimal('nominal', 15, 0);
            $col->text('keterangan')->nullable();
            $col->string('bukti_transfer')->nullable();
            $col->timestamps();
            $col->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tambah_saldo');
    }
};
