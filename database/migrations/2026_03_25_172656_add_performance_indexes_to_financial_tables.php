<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->addIndexIfNotExists('jurnal_keuangan', ['jenis_transaksi', 'kategori', 'sub_kategori'], 'idx_jurnal_pencarian_fast');
        $this->addIndexIfNotExists('jurnal_keuangan', ['pihak_terkait'], 'jurnal_keuangan_pihak_terkait_index');
        
        $this->addIndexIfNotExists('supir', ['nama'], 'supir_nama_index');
        $this->addIndexIfNotExists('supir', ['is_maintenance'], 'supir_is_maintenance_index');
        
        $this->addIndexIfNotExists('penjual', ['nama'], 'penjual_nama_index');
        $this->addIndexIfNotExists('pekerja', ['nama'], 'pekerja_nama_index');
        
        $this->addIndexIfNotExists('transaksi_do', ['penjual_id'], 'transaksi_do_penjual_id_index');
        $this->addIndexIfNotExists('transaksi_do', ['supir_id'], 'transaksi_do_supir_id_index');
    }

    private function addIndexIfNotExists(string $table, array $columns, string $indexName): void
    {
        $indexes = collect(DB::select("SHOW INDEXES FROM {$table}"))->pluck('Key_name')->unique();
        
        if (!$indexes->contains($indexName)) {
            Schema::table($table, function (Blueprint $table) use ($columns, $indexName) {
                $table->index($columns, $indexName);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexIfExists('jurnal_keuangan', 'idx_jurnal_pencarian_fast');
        $this->dropIndexIfExists('jurnal_keuangan', 'jurnal_keuangan_pihak_terkait_index');
        $this->dropIndexIfExists('supir', 'supir_nama_index');
        $this->dropIndexIfExists('supir', 'supir_is_maintenance_index');
        $this->dropIndexIfExists('penjual', 'penjual_nama_index');
        $this->dropIndexIfExists('pekerja', 'pekerja_nama_index');
        $this->dropIndexIfExists('transaksi_do', 'transaksi_do_penjual_id_index');
        $this->dropIndexIfExists('transaksi_do', 'transaksi_do_supir_id_index');
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        $indexes = collect(DB::select("SHOW INDEXES FROM {$table}"))->pluck('Key_name')->unique();
        
        if ($indexes->contains($indexName)) {
            Schema::table($table, function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        }
    }
};
