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
        Schema::create('kendaraan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perusahaan_id')->nullable()->constrained('perusahaan')->cascadeOnDelete();
            $table->string('nama');
            $table->string('no_polisi', 15)->unique();
            $table->boolean('is_maintenance')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('perusahaan_id');
            $table->index('no_polisi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kendaraan');
    }
};
