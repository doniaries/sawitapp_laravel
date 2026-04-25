<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Jika tabel pengajuan_dana ada, rename menjadi tambah_saldo
        if (Schema::hasTable('pengajuan_dana') && !Schema::hasTable('tambah_saldo')) {
            Schema::rename('pengajuan_dana', 'tambah_saldo');
        }

        // 2. Jika tabel tambah_saldo sudah ada, sesuaikan kolomnya
        if (Schema::hasTable('tambah_saldo')) {
            Schema::table('tambah_saldo', function (Blueprint $col) {
                // Rename tanggal_pengajuan ke tanggal jika ada
                if (Schema::hasColumn('tambah_saldo', 'tanggal_pengajuan') && !Schema::hasColumn('tambah_saldo', 'tanggal')) {
                    $col->renameColumn('tanggal_pengajuan', 'tanggal');
                }
                
                // Rename keperluan ke keterangan jika ada
                if (Schema::hasColumn('tambah_saldo', 'keperluan') && !Schema::hasColumn('tambah_saldo', 'keterangan')) {
                    $col->renameColumn('keperluan', 'keterangan');
                }

                // Hapus kolom status karena tidak dibutuhkan lagi
                if (Schema::hasColumn('tambah_saldo', 'status')) {
                    $col->dropColumn('status');
                }

                
            });
        } else {
            // 3. Jika tidak ada sama sekali, buat baru
            Schema::create('tambah_saldo', function (Blueprint $col) {
                $col->id();
                $col->foreignId('perusahaan_id')->constrained('perusahaan')->onDelete('cascade');
                $col->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Kasir/Admin yang input
                $col->dateTime('tanggal');
                $col->decimal('nominal', 15, 0);
                $col->text('keterangan')->nullable();
                $col->timestamps();
                $col->softDeletes();
            });
        }
    }

    public function down(): void
    {
        // Untuk rollback, kita kembalikan saja nama tabelnya ke pengajuan_dana jika mau
        // dan kembalikan struktur kolomnya, atau hapus jika memang tabel ini dibuat dari awal.
        if (Schema::hasTable('tambah_saldo')) {
            Schema::table('tambah_saldo', function (Blueprint $col) {
                if (Schema::hasColumn('tambah_saldo', 'tanggal') && !Schema::hasColumn('tambah_saldo', 'tanggal_pengajuan')) {
                    $col->renameColumn('tanggal', 'tanggal_pengajuan');
                }
                if (Schema::hasColumn('tambah_saldo', 'keterangan') && !Schema::hasColumn('tambah_saldo', 'keperluan')) {
                    $col->renameColumn('keterangan', 'keperluan');
                }
                if (!Schema::hasColumn('tambah_saldo', 'status')) {
                    $col->string('status')->default('pending');
                }
            });

            Schema::rename('tambah_saldo', 'pengajuan_dana');
        }
    }
};
