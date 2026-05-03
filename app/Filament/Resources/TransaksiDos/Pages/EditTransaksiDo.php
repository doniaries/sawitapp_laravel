<?php

namespace App\Filament\Resources\TransaksiDos\Pages;

use App\Filament\Resources\TransaksiDos\TransaksiDoResource;
use App\Models\Penjual;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Filament\Actions;

class EditTransaksiDo extends EditRecord
{
    protected static string $resource = TransaksiDoResource::class;

    // Set initial form data
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Pastikan data hutang yang ditampilkan adalah hutang_awal saat transaksi
        $data['hutang_awal'] = $this->record->hutang_awal;

        // Set info hutang terkini dari penjual
        if ($this->record->penjual_id) {
            $data['info_hutang_terkini'] = $this->record->penjual->sisa_hutang ?? 0;
        }

        return $data;
    }

    // Validate data before save
    protected function mutateFormDataBeforeSave(array $data): array
    {
        try {
            // Format angka
            $formatNumber = fn($value) => is_numeric($value) ?
                (int)$value : (int)str_replace(['Rp', '.', ',', ' '], '', $value);

            // Format numeric fields
            $numericFields = [
                'tonase',
                'harga_satuan',
                'total',
                'upah_bongkar',
                'biaya_lain',
                'pembayaran_hutang',
                'sisa_hutang_penjual',
                'sisa_bayar'
            ];

            foreach ($numericFields as $field) {
                if (isset($data[$field])) {
                    $data[$field] = $formatNumber($data[$field]);
                }
            }

            // Validasi pembayaran hutang
            $hutangAwal = $this->record->hutang_awal;
            if (($data['pembayaran_hutang'] ?? 0) > $hutangAwal) {
                throw new \Exception(
                    "Pembayaran hutang melebihi hutang awal transaksi\n" .
                        "Hutang awal: Rp " . number_format($hutangAwal, 0, ',', '.') . "\n" .
                        "Pembayaran: Rp " . number_format($data['pembayaran_hutang'], 0, ',', '.')
                );
            }

            // Hitung ulang total dan sisa
            $data['total'] = $data['tonase'] * $data['harga_satuan'];
            $data['sisa_hutang_penjual'] = max(0, $hutangAwal - ($data['pembayaran_hutang'] ?? 0));
            $data['sisa_bayar'] = max(0, $data['total'] - ($data['upah_bongkar'] ?? 0) - ($data['biaya_lain'] ?? 0) - ($data['pembayaran_hutang'] ?? 0));

            return $data;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error Validasi Data')
                ->body($e->getMessage())
                ->danger()
                ->duration(3000) // Set durasi 3 detik
                ->persistent(false) // Notifikasi akan otomatis hilang
                ->send();

            throw $e;
        }
    }

    // Handle after record is saved
    protected function afterSave(): void
    {
        // Logika pengkinian hutang dan jurnal sudah ditangani oleh TransaksiDoObserver
    }

    // Configure header actions
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // Redirect after save
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        $record = $this->record;

        return Notification::make()
            ->success()
            ->title('Perubahan DO Disimpan')
            ->body(new \Illuminate\Support\HtmlString(
                "DO #{$record->nomor}<br>" .
                "Total: " . money($record->total, 'IDR') . "<br>" .
                "Sisa bayar: " . money($record->sisa_bayar, 'IDR')
            ))
            ->duration(3000);
    }
}
