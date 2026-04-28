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
        Schema::create('lansirs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perusahaan_id')->constrained('perusahaan')->cascadeOnDelete();
            $table->date('tanggal_lansir');
            $table->string('nama_supir');
            $table->string('nama_penjual');
            $table->decimal('tonase', 10, 2);
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('total', 15, 2);
            $table->decimal('upah', 15, 2);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['perusahaan_id', 'tanggal_lansir']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lansirs');
    }
};
