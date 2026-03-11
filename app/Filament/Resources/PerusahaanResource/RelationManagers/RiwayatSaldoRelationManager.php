<?php

namespace App\Filament\Resources\PerusahaanResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Perusahaan;
use Filament\Tables\Table;
use App\Models\LaporanKeuangan;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;

class RiwayatSaldoRelationManager extends RelationManager
{
    protected static string $relationship = 'riwayatSaldo';
    protected static ?string $title = 'Riwayat Tambah Saldo';
    protected static ?string $recordTitleAttribute = 'nomor_referensi';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                // Tables\Columns\TextColumn::make('oleh')
                //     ->label('oleh')
                //     ->searchable(),
                Tables\Columns\TextColumn::make('nominal')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cara_pembayaran')
                    ->badge(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->limit(30)
                    ->tooltip(function ($record) {
                        return $record->keterangan;
                    }),
                // Tables\Columns\ImageColumn::make('bukti_tambah_saldo')
                //     ->label('Bukti')
                //     ->disk('public')
                //     ->circular(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                // Optional: Add filters if needed
            ])
            ->headerActions([
                // No actions needed as adding is done via main resource
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('batalkan')
                    ->label('Batalkan')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->created_at->isToday()) // Hanya bisa dibatalkan di hari yang sama
                    ->action(function ($record) {
                        try {
                            DB::beginTransaction();

                            // Ambil data perusahaan
                            $perusahaan = Perusahaan::find($record->referensi_id);

                            if (!$perusahaan) {
                                throw new \Exception('Data perusahaan tidak ditemukan');
                            }

                            // Rollback saldo jika cara pembayaran tunai
                            if ($record->cara_pembayaran === 'tunai') {
                                $perusahaan->rollbackSaldo($record->nominal);
                            }

                            // Tambah catatan pembatalan di laporan keuangan
                            LaporanKeuangan::create([
                                'tanggal' => now(),
                                'jenis_transaksi' => 'Pengeluaran',
                                'kategori' => 'Saldo',
                                'sub_kategori' => 'Pembatalan Tambah Saldo',
                                'nominal' => $record->nominal,
                                'sumber_transaksi' => 'Perusahaan',
                                'referensi_id' => $record->referensi_id,
                                'nomor_referensi' => 'BTL-' . $record->nomor_referensi,
                                'pihak_terkait' => $perusahaan->pimpinan,
                                'cara_pembayaran' => $record->cara_pembayaran,
                                'keterangan' => "Pembatalan transaksi {$record->nomor_referensi}",
                                'mempengaruhi_kas' => $record->cara_pembayaran === 'tunai'
                            ]);

                            // Soft delete record tambah saldo
                            $record->delete();

                            DB::commit();

                            // Refresh semua widget terkait
                            $this->dispatch('refresh-widgets');

                            Notification::make()
                                ->success()
                                ->duration(3000) // Set durasi 3 detik
                                ->persistent(false) // Notifikasi akan otomatis hilang
                                ->title('Transaksi Dibatalkan')
                                ->body(sprintf(
                                    "Pembatalan tambah saldo Rp %s berhasil.\nSaldo terkini: Rp %s",
                                    number_format($record->nominal, 0, ',', '.'),
                                    number_format($perusahaan->fresh()->saldo, 0, ',', '.')
                                ))
                                ->send();
                        } catch (\Exception $e) {
                            DB::rollBack();

                            Notification::make()
                                ->danger()
                                ->duration(3000) // Set durasi 3 detik
                                ->persistent(false) // Notifikasi akan otomatis hilang
                                ->title('Gagal Membatalkan')
                                ->body('Terjadi kesalahan: ' . $e->getMessage())
                                ->send();
                        }
                    })
            ])
            ->bulkActions([
                // Optional: Add bulk actions if needed
            ]);
    }
}
