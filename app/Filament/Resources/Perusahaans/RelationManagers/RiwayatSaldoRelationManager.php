<?php

namespace App\Filament\Resources\Perusahaans\RelationManagers;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class RiwayatSaldoRelationManager extends RelationManager
{
    protected static string $relationship = 'riwayatSaldo';
    protected static ?string $title = 'Riwayat Tambah Saldo';
    protected static ?string $recordTitleAttribute = 'nomor_referensi';
    protected static ?string $modelLabel = 'Tambah Saldo';
    protected static ?string $pluralModelLabel = 'Tambah Saldo';

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                //
            ]);
    }

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
                ViewAction::make(),
                Action::make('batalkan')
                    ->label('Batalkan')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->created_at->isToday()) // Hanya bisa dibatalkan di hari yang sama
                    ->action(function ($record) {
                        try {
                            \Illuminate\Support\Facades\DB::beginTransaction();

                            // Ambil data perusahaan
                            $perusahaan = \App\Models\Perusahaan::find($record->referensi_id);

                            if (!$perusahaan) {
                                throw new \Exception('Data perusahaan tidak ditemukan');
                            }

                            // Rollback saldo jika cara pembayaran tunai
                            if ($record->cara_pembayaran === 'tunai') {
                                $perusahaan->rollbackSaldo($record->nominal);
                            }

                            // Tambah catatan pembatalan di laporan keuangan
                            \App\Models\JurnalKeuangan::create([
                                'perusahaan_id' => $record->perusahaan_id,
                                'tanggal' => now(),
                                'jenis_transaksi' => 'Pengeluaran',
                                'kategori' => 'Saldo',
                                'sub_kategori' => 'Pembatalan Tambah Saldo',
                                'nominal' => $record->nominal,
                                'sumber_transaksi' => 'Perusahaan',
                                'referensi_id' => $record->referensi_id,
                                'nomor_referensi' => 'BTL-' . $record->nomor_referensi,
                                'pihak_terkait' => $perusahaan->pimpinan,
                                'tipe_pihak' => 'user',
                                'cara_pembayaran' => $record->cara_pembayaran,
                                'keterangan' => "Pembatalan transaksi {$record->nomor_referensi}",
                                'mempengaruhi_kas' => $record->cara_pembayaran === 'tunai'
                            ]);

                            // Soft delete record tambah saldo
                            $record->delete();

                            \Illuminate\Support\Facades\DB::commit();

                            // Refresh semua widget terkait
                            $this->dispatch('refresh-widgets');

                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->duration(3000) // Set durasi 3 detik
                                ->title('Transaksi Dibatalkan')
                                ->body(sprintf(
                                    "Pembatalan tambah saldo Rp %s berhasil.\nSaldo terkini: Rp %s",
                                    number_format((float) $record->nominal, 0, ',', '.'),
                                    number_format((float) $perusahaan->fresh()->saldo, 0, ',', '.')
                                ))
                                ->send();
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\DB::rollBack();

                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->duration(3000) // Set durasi 3 detik
                                ->title('Gagal Membatalkan')
                                ->body('Terjadi kesalahan: ' . $e->getMessage())
                                ->send();
                        }
                    })
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
