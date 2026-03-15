<?php

namespace App\Observers;

use App\Models\Supir;
use App\Enums\KategoriOperasional;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\{DB, Log};
use Filament\Notifications\Actions\Action;
use App\Models\{TransaksiOperasional, Penjual, Perusahaan, JurnalKeuangan, PembayaranHutang};


class TransaksiOperasionalObserver
{
    protected $financeAction;

    public function __construct(\App\Actions\Finance\RecordFinanceTransactionAction $financeAction)
    {
        $this->financeAction = $financeAction;
    }





    public function created(TransaksiOperasional $operasional): void
    {
        try {
            DB::beginTransaction();

            // Process loan/debt if applicable
            $this->processHutang($operasional);

            // Create financial report entry & update balance via Action (Best Practice)
            $this->createLaporanKeuangan($operasional);

            DB::commit();

            $this->showTransactionNotification($operasional, 'created');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logAndNotifyError('created', $e, $operasional);
            throw $e;
        }
    }


    public function updated(TransaksiOperasional $operasional): void
    {
        try {
            DB::beginTransaction();

            // 1. Hapus laporan keuangan yang lama
            JurnalKeuangan::where([
                'kategori' => 'Operasional',
                'referensi_id' => $operasional->id
            ])->delete();

            // 2. Buat laporan keuangan baru & update saldo via Action
            $this->createLaporanKeuangan($operasional);

            // 3. Proses perubahan hutang jika ada
            if ($operasional->isDirty(['nominal', 'kategori'])) {
                $this->rollbackHutang($operasional); 
                $this->processHutang($operasional);
            }

            DB::commit();

            $this->showTransactionNotification($operasional, 'updated');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logAndNotifyError('updated', $e, $operasional);
            throw $e;
        }
    }

    public function deleted(TransaksiOperasional $operasional): void
    {
        try {
            DB::beginTransaction();

            // 1. Hapus laporan keuangan terkait
            JurnalKeuangan::where([
                'kategori' => 'Operasional',
                'referensi_id' => $operasional->id
            ])->delete();

            // 2. Rollback hutang jika ada
            $this->rollbackHutang($operasional);

            // 3. Kembalikan saldo perusahaan
            $this->rollbackSaldoPerusahaan($operasional);

            DB::commit();

            $this->showTransactionNotification($operasional, 'deleted');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logAndNotifyError('deleted', $e, $operasional);
            throw $e;
        }
    }

    /**
     * Handle the Operasional "restored" event.
     * Dipanggil setelah data di-restore dari soft delete
     */
    public function restored(TransaksiOperasional $operasional): void
    {
        try {
            DB::beginTransaction();

            // 1. Catat lagi ke laporan keuangan
            $this->createLaporanKeuangan($operasional);

            // 2. Proses ulang perubahan hutang jika ada
            $this->processHutang($operasional);

            DB::commit();

            // 3. Tampilkan notifikasi restore berhasil
            Notification::make()
                ->title('Data Berhasil Dipulihkan')
                ->icon('heroicon-o-check-circle')
                ->iconColor('success')
                ->body(
                    "Data operasional berikut telah berhasil dipulihkan:\n" .
                        "• Jenis: {$operasional->operasional}\n" .
                        "• Kategori: {$operasional->kategoriLabel}\n" .
                        "• Nominal: Rp " . number_format($operasional->nominal, 0, ',', '.')
                )
                ->actions([
                    Action::make('view')
                        ->label('Lihat Data')
                        ->url(route('filament.admin.resources.transaksi-operasionals.edit', $operasional))
                        ->button()
                ])
                ->success()
                ->duration(3000)
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logAndNotifyError('restored', $e, $operasional);
            throw $e;
        }
    }

    /**
     * Handle the Operasional "force deleted" event.
     * Dipanggil saat data dihapus permanen
     */
    public function forceDeleted(TransaksiOperasional $operasional): void
    {
        try {
            DB::beginTransaction();

            // 1. Hapus data terkait di laporan keuangan
            JurnalKeuangan::where([
                'sumber_transaksi' => 'Operasional',
                'referensi_id' => $operasional->id
            ])->delete();

            // 2. Rollback hutang jika ada
            $this->rollbackHutang($operasional);

            DB::commit();

            // 3. Tampilkan notifikasi
            Notification::make()
                ->title('Data Terhapus Permanen')
                ->icon('heroicon-o-trash')
                ->iconColor('danger')
                ->body(
                    "Data operasional berikut telah dihapus permanen:\n" .
                        "• Jenis: {$operasional->operasional}\n" .
                        "• Kategori: {$operasional->kategoriLabel}\n" .
                        "• Nominal: Rp " . number_format($operasional->nominal, 0, ',', '.')
                )
                ->warning()
                ->duration(3000)
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logAndNotifyError('force deleted', $e, $operasional);
            throw $e;
        }
    }

    private function processHutang(TransaksiOperasional $operasional): void
    {
        if ($operasional->kategori === KategoriOperasional::PINJAMAN) {
            try {
                DB::beginTransaction();

                // Process driver loan
                if ($operasional->tipe_nama === 'supir') {
                    $supir = Supir::lockForUpdate()->findOrFail($operasional->supir_id);
                    $hutangSebelum = $supir->hutang ?? 0;

                    // Update driver debt
                    $supir->update([
                        'hutang' => $hutangSebelum + $operasional->nominal
                    ]);

                    // Create payment history
                    PembayaranHutang::create([
                        'tanggal' => $operasional->tanggal,
                        'nominal' => $operasional->nominal,
                        'tipe' => 'supir',
                        'supir_id' => $supir->id,
                        'operasional_id' => $operasional->id,
                        'keterangan' => "Penambahan pinjaman supir: " . ($operasional->keterangan ?: 'Via operasional')
                    ]);

                    Log::info('Pinjaman Supir diproses:', [
                        'supir' => $supir->nama,
                        'hutang_sebelum' => $hutangSebelum,
                        'nominal' => $operasional->nominal,
                        'hutang_setelah' => $supir->fresh()->hutang
                    ]);
                }
                // Process seller loan
                elseif ($operasional->tipe_nama === 'penjual') {
                    $penjual = Penjual::lockForUpdate()->findOrFail($operasional->penjual_id);
                    $hutangSebelum = $penjual->hutang;

                    $penjual->increment('hutang', $operasional->nominal);

                    PembayaranHutang::create([
                        'tanggal' => $operasional->tanggal,
                        'nominal' => $operasional->nominal,
                        'tipe' => 'penjual',
                        'penjual_id' => $penjual->id,
                        'operasional_id' => $operasional->id,
                        'keterangan' => "Penambahan pinjaman penjual: " . ($operasional->keterangan ?: 'Via operasional')
                    ]);
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error memproses pinjaman:', [
                    'error' => $e->getMessage(),
                    'operasional_id' => $operasional->id
                ]);
                throw $e;
            }
        }
    }


    // Modifikasi method rollbackHutang juga agar konsisten
    private function rollbackHutang(TransaksiOperasional $operasional): void
    {
        if ($operasional->kategori === KategoriOperasional::PINJAMAN) {
            DB::transaction(function () use ($operasional) {
                // Handle driver loan rollback
                if ($operasional->tipe_nama === 'supir' && $operasional->supir) {
                    $supir = $operasional->supir;
                    $hutangSebelum = $supir->hutang;

                    // Reverse the driver's debt
                    $supir->decrement('hutang', $operasional->nominal);

                    // Delete related payment history
                    PembayaranHutang::where([
                        'supir_id' => $supir->id,
                        'operasional_id' => $operasional->id
                    ])->delete();

                    Log::info('Pinjaman Supir dibatalkan:', [
                        'supir' => $supir->nama,
                        'hutang_sebelum' => $hutangSebelum,
                        'nominal_dibatalkan' => $operasional->nominal,
                        'hutang_setelah' => $supir->fresh()->hutang
                    ]);
                }
                // Handle seller loan rollback
                elseif ($operasional->tipe_nama === 'penjual' && $operasional->penjual) {
                    $penjual = $operasional->penjual;
                    $hutangSebelum = $penjual->hutang;

                    // Reverse the seller's debt
                    $penjual->decrement('hutang', $operasional->nominal);

                    // Delete related payment history
                    PembayaranHutang::where([
                        'penjual_id' => $penjual->id,
                        'operasional_id' => $operasional->id
                    ])->delete();

                    Log::info('Pinjaman Penjual dibatalkan:', [
                        'penjual' => $penjual->nama,
                        'hutang_sebelum' => $hutangSebelum,
                        'nominal_dibatalkan' => $operasional->nominal,
                        'hutang_setelah' => $penjual->fresh()->hutang
                    ]);
                }
            });
        }
    }

    private function updateSaldoPerusahaan(TransaksiOperasional $operasional): void
    {
        $perusahaan = Perusahaan::first();
        if (!$perusahaan) {
            throw new \Exception('Data perusahaan tidak ditemukan');
        }

        if ($operasional->operasional === 'pemasukan') {
            $perusahaan->increment('saldo', $operasional->nominal);
        } else {
            // Validasi saldo cukup untuk pengeluaran
            if ($operasional->nominal > $perusahaan->saldo) {
                throw new \Exception(
                    "Saldo tidak mencukupi untuk pengeluaran.\n" .
                        "Saldo: Rp " . number_format($perusahaan->saldo, 0, ',', '.') . "\n" .
                        "Dibutuhkan: Rp " . number_format($operasional->nominal, 0, ',', '.')
                );
            }
            $perusahaan->decrement('saldo', $operasional->nominal);
        }

        Log::info('Saldo perusahaan diupdate:', [
            'jenis' => $operasional->operasional,
            'nominal' => $operasional->nominal,
            'saldo_akhir' => $perusahaan->fresh()->saldo
        ]);
    }


    private function rollbackSaldoPerusahaan(TransaksiOperasional $operasional): void
    {
        $perusahaan = Perusahaan::first();
        if (!$perusahaan) return;

        // Reverse the effect on saldo
        if ($operasional->operasional === 'pemasukan') {
            $perusahaan->decrement('saldo', $operasional->nominal);
        } else {
            $perusahaan->increment('saldo', $operasional->nominal);
        }

        Log::info('Saldo dikembalikan:', [
            'operasional_id' => $operasional->id,
            'nominal' => $operasional->nominal,
            'saldo_akhir' => $perusahaan->fresh()->saldo
        ]);
    }



    private function showTransactionNotification(TransaksiOperasional $operasional, string $action): void
    {
        $nominal = number_format($operasional->nominal, 0, ',', '.');

        Notification::make()
            ->title($this->getNotificationTitle($operasional, $action))
            ->success()
            ->icon('heroicon-o-check-circle')
            ->body(
                "Detail Transaksi:\n" .
                    "• Tanggal: {$operasional->tanggal->format('d/m/Y H:i')}\n" .
                    "• Jenis: " . ucfirst($operasional->operasional) . "\n" .
                    "• Kategori: " . ($operasional->kategori ? $operasional->kategori->label() : '-') . "\n" .  // Tambahkan null check
                    "• Nominal: Rp {$nominal}" .
                    ($operasional->keterangan ? "\n• Keterangan: {$operasional->keterangan}" : "")
            )
            ->duration(5000)
            ->send();
    }


    private function createLaporanKeuangan(TransaksiOperasional $operasional): void
    {
        $this->financeAction->execute([
            'perusahaan_id' => $operasional->perusahaan_id,
            'tanggal' => $operasional->tanggal,
            'jenis_transaksi' => ucfirst($operasional->operasional), // Pemasukan/Pengeluaran
            'kategori' => 'Operasional',
            'sub_kategori' => $operasional->kategori?->label() ?? '-',
            'nominal' => $operasional->nominal,
            'sumber_transaksi' => 'Operasional',
            'referensi_id' => $operasional->id,
            'nomor_referensi' => sprintf('OP-%s', str_pad($operasional->id, 5, '0', STR_PAD_LEFT)),
            'pihak_terkait' => $operasional->nama,
            'tipe_pihak' => $operasional->pihak_type, 
            'cara_pembayaran' => 'tunai',
            'keterangan' => $operasional->keterangan ?: '-',
            'mempengaruhi_kas' => true,
        ]);
    }

    private function getNotificationTitle(TransaksiOperasional $operasional, string $action): string
    {
        $actionText = match ($action) {
            'created' => 'Berhasil Dibuat',
            'updated' => 'Berhasil Diupdate',
            'deleted' => 'Berhasil Dihapus',
            'restored' => 'Berhasil Dipulihkan',
            default => 'Berhasil Diproses'
        };

        return "Transaksi Operasional {$actionText}";
    }

    //----------------///

    private function showNotification(string $title, string $body, string $message = '', string $type = 'success'): void
    {
        // Filament 3 style notification
        Notification::make()
            ->title($title)
            ->icon($this->getNotificationIcon($type))  // Tambahkan icon
            ->iconColor($type)  // Gunakan warna sesuai type
            ->body($message ?: $body)  // Body wajib di Filament 3
            ->persistent(false)
            ->duration(3000)    // Durasi tampil 5 detik
            ->send();
    }

    private function logAndNotifyError(string $action, \Exception $e, TransaksiOperasional $operasional): void
    {
        Log::error("Error {$action} Operasional:", [
            'error' => $e->getMessage(),
            'operasional' => $operasional->toArray()
        ]);

        // Sederhanakan notifikasi error, hapus bagian modalContent
        Notification::make()
            ->title('Error!')
            ->icon('heroicon-o-x-circle')
            ->iconColor('danger')
            ->body("Terjadi kesalahan saat {$action} transaksi: " . $e->getMessage()) // Tambahkan pesan error langsung di body
            ->danger()
            ->persistent(false)
            ->duration(3000) // Durasi lebih lama untuk error (10 detik)
            ->send();
    }

    // Tambahkan method helper untuk icon
    private function getNotificationIcon(string $type): string
    {
        return match ($type) {
            'success' => 'heroicon-o-check-circle',
            'danger' => 'heroicon-o-x-circle',
            'warning' => 'heroicon-o-exclamation-triangle',
            default => 'heroicon-o-information-circle'
        };
    }
}
