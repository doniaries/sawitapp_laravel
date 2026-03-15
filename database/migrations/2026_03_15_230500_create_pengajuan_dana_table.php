<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_dana', function (Blueprint $col) {
            $col->id();
            $col->foreignId('perusahaan_id')->constrained('perusahaan')->onDelete('cascade');
            $col->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Yang mengajukan
            $col->dateTime('tanggal_pengajuan');
            $col->decimal('nominal', 15, 0);
            $col->text('keperluan');
            $col->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $col->dateTime('tanggal_proses')->nullable(); // Kapan disetujui/ditolak
            $col->foreignId('proses_by')->nullable()->constrained('users')->nullOnDelete(); // Siapa pimpinannya
            $col->text('catatan_pimpinan')->nullable();
            $col->string('bukti_transfer')->nullable();
            $col->timestamps();
            $col->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_dana');
    }
};
