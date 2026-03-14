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
        Schema::create('supir', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perusahaan_id')->nullable()->constrained('perusahaans')->cascadeOnDelete();
            $table->string('slug')->nullable()->index();
            $table->string('nama')->index();
            $table->string('alamat')->nullable();
            $table->string('telepon')->nullable()->index();
            $table->decimal('hutang', 15, 0)->nullable();
            $table->string('riwayat_bayar')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('perusahaan_id');
            $table->index('slug');
            $table->index('nama');
            $table->index('telepon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supir');
    }
};