<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('transaksi_operasional', function (Blueprint $table) {
            $cols = ['penjual_id', 'user_id', 'supir_id', 'pekerja_id', 'tipe_nama'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('transaksi_operasional', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::table('transaksi_operasional', function (Blueprint $table) {
            $table->enum('tipe_nama', ['penjual', 'user', 'supir', 'pekerja'])->nullable();
            $table->foreignId('penjual_id')->nullable()->constrained('penjual')->nullOnDelete();
            $table->unsignedBigInteger('pekerja_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('supir_id')->nullable()->constrained('supir')->nullOnDelete();
        });
    }
};
