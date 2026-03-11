<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesForPerformance extends Migration
{
    public function up()
    {
        Schema::table('laporan_keuangan', function (Blueprint $table) {
            if (!$this->hasIndex('laporan_keuangan', 'laporan_keuangan_tanggal_jenis_transaksi_index')) {
                $table->index(['tanggal', 'jenis_transaksi']);
            }
            if (!$this->hasIndex('laporan_keuangan', 'laporan_keuangan_kategori_index')) {
                $table->index('kategori');
            }
            if (!$this->hasIndex('laporan_keuangan', 'laporan_keuangan_sumber_transaksi_referensi_id_index')) {
                $table->index(['sumber_transaksi', 'referensi_id']);
            }
        });

        Schema::table('transaksi_do', function (Blueprint $table) {
            if (!$this->hasIndex('transaksi_do', 'transaksi_do_tanggal_cara_bayar_index')) {
                $table->index(['tanggal', 'cara_bayar']);
            }
            if (!$this->hasIndex('transaksi_do', 'transaksi_do_nomor_index')) {
                $table->index('nomor');
            }
        });
    }

    public function down()
    {
        Schema::table('laporan_keuangan', function (Blueprint $table) {
            $table->dropIndex(['tanggal', 'jenis_transaksi']);
            $table->dropIndex(['sumber_transaksi', 'referensi_id']);
            $table->dropIndex(['kategori']);
        });

        Schema::table('transaksi_do', function (Blueprint $table) {
            $table->dropIndex(['tanggal', 'cara_bayar']);
            $table->dropIndex(['nomor']);
        });
    }

    private function hasIndex($table, $indexName)
    {
        return collect(\DB::select("SHOW INDEXES FROM {$table}"))->pluck('Key_name')->contains($indexName);
    }
}
