<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penjuals', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->index();
            $table->string('alamat')->nullable();
            $table->string('telepon')->nullable()->index();
            $table->decimal('hutang', 15, 0)->nullable();
            $table->string('riwayat_bayar')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjuals');
    }
};
