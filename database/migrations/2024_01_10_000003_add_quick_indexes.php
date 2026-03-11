<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuickIndexes extends Migration
{
    public function up()
    {
        Schema::table('transaksi_do', function (Blueprint $table) {
            // Index untuk kolom yang sering difilter atau diurutkan
            if (!Schema::hasIndex('transaksi_do', 'transaksi_do_tanggal_index')) {
                $table->index('tanggal');
            }
            
            if (!Schema::hasIndex('transaksi_do', 'transaksi_do_penjual_id_index')) {
                $table->index('penjual_id');
            }
        });
    }

    public function down()
    {
        Schema::table('transaksi_do', function (Blueprint $table) {
            $table->dropIndex('transaksi_do_tanggal_index');
            $table->dropIndex('transaksi_do_penjual_id_index');
        });
    }
}
