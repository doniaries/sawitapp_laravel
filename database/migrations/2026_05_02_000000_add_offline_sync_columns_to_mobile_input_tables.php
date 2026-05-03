<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['transaksi_do', 'transaksi_operasional', 'tambah_saldo'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'client_uuid')) {
                    $table->uuid('client_uuid')->nullable()->after('id');
                }

                if (! Schema::hasColumn($tableName, 'client_created_at')) {
                    $table->timestamp('client_created_at')->nullable()->after('client_uuid');
                }

                if (! Schema::hasColumn($tableName, 'client_updated_at')) {
                    $table->timestamp('client_updated_at')->nullable()->after('client_created_at');
                }

                if (! Schema::hasColumn($tableName, 'synced_at')) {
                    $table->timestamp('synced_at')->nullable()->after('client_updated_at');
                }

                if ($tableName === 'tambah_saldo' && ! Schema::hasColumn($tableName, 'bukti_transfer')) {
                    $table->string('bukti_transfer')->nullable()->after('keterangan');
                }
            });

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->unique('client_uuid', "{$tableName}_client_uuid_unique");
                $table->index('synced_at', "{$tableName}_synced_at_index");
            });
        }
    }

    public function down(): void
    {
        foreach (['transaksi_do', 'transaksi_operasional', 'tambah_saldo'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropUnique("{$tableName}_client_uuid_unique");
                $table->dropIndex("{$tableName}_synced_at_index");
                $columns = [
                    'client_uuid',
                    'client_created_at',
                    'client_updated_at',
                    'synced_at',
                ];

                if ($tableName === 'tambah_saldo' && Schema::hasColumn($tableName, 'bukti_transfer')) {
                    $columns[] = 'bukti_transfer';
                }

                $table->dropColumn($columns);
            });
        }
    }
};
