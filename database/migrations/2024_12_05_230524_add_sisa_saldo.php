<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('perusahaans', function (Blueprint $table) {
            $table->decimal('sisa_saldo_kemarin', 15, 2)->default(0)->after('id');
            $table->date('tanggal_sisa_saldo')->nullable()->after('sisa_saldo_kemarin');
            $table->boolean('sudah_diproses')->default(false)->after('tanggal_sisa_saldo');
        });
    }

    public function down()
    {
        Schema::table('perusahaans', function (Blueprint $table) {
            $table->dropColumn('sisa_saldo_kemarin');
            $table->dropColumn('tanggal_sisa_saldo');
            $table->dropColumn('sudah_diproses');
        });
    }
};
