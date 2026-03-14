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
        Schema::create('pekerja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perusahaan_id')->nullable()->constrained('perusahaan')->cascadeOnDelete();
            $table->string('slug')->nullable()->index();
            $table->string('nama')->index();
            $table->string('alamat')->nullable();
            $table->string('telepon')->nullable()->index();
            $table->string('pendapatan')->default('0');
            $table->string('hutang')->default('0');
            $table->string('riwayat_bayar')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pekerja');
    }
};
