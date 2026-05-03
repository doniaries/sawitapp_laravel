<?php

namespace App\Filament\Resources\TransaksiDos\Pages;

use App\Filament\Resources\TransaksiDos\TransaksiDoResource;
use App\Models\{Penjual};
use Filament\Notifications\Notification;
use Filament\Actions\Action as NotificationAction;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateTransaksiDo extends CreateRecord
{
    protected static string $resource = TransaksiDoResource::class;

    // Validasi data sebelum disimpan
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        try {
            // Format angka dari input
            $formatNumber = fn($value) => is_numeric($value) ?
                (int)$value : (int)str_replace(['Rp', '.', ',', ' '], '', $value);

            // Format semua field numeric
            $numericFields = [
                'tonase',
                'harga_satuan',
                'total',
                'upah_bongkar',
                'biaya_lain',
                'hutang_awal',
                'pembayaran_hutang',
                'sisa_hutang_penjual',
                'sisa_bayar'
            ];

            foreach ($numericFields as $field) {
                if (isset($data[$field])) {
                    $data[$field] = $formatNumber($data[$field]);
                }
            }

            // PERBAIKAN 6: Set default tanggal jika kosong
            if (!isset($data['tanggal'])) {
                $data['tanggal'] = now();
            }

            // Pastikan Nomor DO selalu tergenerate baru sesuai tanggal saat simpan
            $data['nomor'] = \App\Models\TransaksiDo::generateMonthlyNumber($data['tanggal']);

            // Validasi hutang dan pembayaran
            if ($data['penjual_id']) {
                $penjual = Penjual::find($data['penjual_id']);
                if ($penjual) {
                    // Pastikan hutang_awal sesuai dengan sisa hutang penjual saat ini
                    $data['hutang_awal'] = (float) $penjual->sisa_hutang;

                    // Validasi pembayaran hutang
                    $sisaHutang = (float) $penjual->sisa_hutang;
                    if (($data['pembayaran_hutang'] ?? 0) > $sisaHutang) {
                        throw new \Exception(
                            "Pembayaran hutang melebihi hutang yang ada\n" .
                                "Hutang saat ini: Rp " . number_format($sisaHutang, 0, ',', '.') . "\n" .
                                "Pembayaran: Rp " . number_format($data['pembayaran_hutang'], 0, ',', '.')
                        );
                    }
                }
            }

            // Hitung ulang total dan sisa
            $data['total'] = $data['tonase'] * $data['harga_satuan'];
            $data['sisa_hutang_penjual'] = max(0, $data['hutang_awal'] - ($data['pembayaran_hutang'] ?? 0));
            $data['sisa_bayar'] = max(0, $data['total'] - ($data['upah_bongkar'] ?? 0) - ($data['biaya_lain'] ?? 0) - ($data['pembayaran_hutang'] ?? 0));

            return $data;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error Validasi Data')
                ->body($e->getMessage())
                ->danger()
                ->duration(3000)
                ->persistent(false)
                ->send();

            throw $e;
        }
    }

    // Handle after record is created
    protected function afterCreate(): void
    {
        $record = $this->record;

        try {
            DB::beginTransaction();

            if ($record->pembayaran_hutang > 0) {
                $penjual = $record->penjual;
                if (!$penjual) {
                    throw new \Exception('Data penjual tidak ditemukan');
                }

                // Tampilkan notifikasi detail
                // Notification::make()
                //     ->title('Pembayaran Hutang Berhasil')
                //     ->body(
                //         "DO #{$record->nomor}\n" .
                //             "Hutang awal: Rp " . number_format($record->hutang_awal, 0, ',', '.') . "\n" .
                //             "Pembayaran: Rp " . number_format($record->pembayaran_hutang, 0, ',', '.') . "\n" .
                //             "Sisa hutang: Rp " . number_format($record->sisa_hutang_penjual, 0, ',', '.')
                //     )
                //     ->success()
                //     ->duration(3000)
                //     ->persistent(false)
                //     ->send();
            }

            // Notifikasi ke semua user agar informatif
            $users = \App\Models\User::all();

            $isCairLuar = $record->cara_bayar === 'cair di luar';
            $notif = Notification::make()
                ->title($isCairLuar ? '⚠️ Transaksi Cair di Luar!' : 'Transaksi DO Baru')
                ->body(new \Illuminate\Support\HtmlString("DO #{$record->nomor} oleh " . ($record->penjual?->nama ?? 'N/A') . "<br>Total: " . money($record->total ?? 0, 'IDR')))
                ->success()
                ->icon($isCairLuar ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-document-text')
                ->color($isCairLuar ? 'warning' : 'success')
                ->actions([
                    NotificationAction::make('lihat')
                        ->label('Lihat Detail')
                        ->button()
                        ->url(TransaksiDoResource::getUrl('edit', ['record' => $record, 'tenant' => $record->perusahaan->slug])),
                ]);

            foreach ($users as $user) {
                $notif->sendToDatabase($user);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Error')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->danger()
                ->duration(3000) // Set durasi 3 detik
                ->persistent(false) // Notifikasi akan otomatis hilang
                ->send();

            throw $e;
        }
    }

    // Redirect after successful creation
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        $record = $this->record;

        return Notification::make()
            ->success()
            ->title('Transaksi DO Berhasil')
            ->body(new \Illuminate\Support\HtmlString(
                "DO #{$record->nomor}<br>" .
                "Total: " . money($record->total, 'IDR') . "<br>" .
                "Sisa bayar: " . money($record->sisa_bayar, 'IDR')
            ))
            ->duration(3000);
    }
}
