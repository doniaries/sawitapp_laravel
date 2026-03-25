<?php

namespace App\Filament\Resources\TambahSaldos\Pages;

use App\Filament\Resources\TambahSaldos\TambahSaldoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTambahSaldo extends CreateRecord
{
    protected static string $resource = TambahSaldoResource::class;

    protected function afterCreate(): void
    {
        $record = $this->record;
        
        $users = \App\Models\User::whereHas('roles', fn($q) => $q->whereIn('name', ['super_admin', 'pimpinan']))->get();
        
        $notif = \Filament\Notifications\Notification::make()
            ->title('Pengajuan Tambah Saldo Baru')
            ->body("Pengajuan sebesar Rp " . number_format($record->nominal, 0, ',', '.') . " oleh " . $record->user->name)
            ->info()
            ->actions([
                \Filament\Notifications\Actions\Action::make('lihat')
                    ->button()
                    ->url(TambahSaldoResource::getUrl('index', ['tenant' => $record->perusahaan->slug])),
            ]);

        foreach ($users as $user) {
            $notif->sendToDatabase($user);
        }
    }
}
