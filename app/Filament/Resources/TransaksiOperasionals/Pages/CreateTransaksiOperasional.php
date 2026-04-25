<?php

namespace App\Filament\Resources\TransaksiOperasionals\Pages;

use App\Filament\Resources\TransaksiOperasionals\TransaksiOperasionalResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
 
class CreateTransaksiOperasional extends CreateRecord
{
    protected static string $resource = TransaksiOperasionalResource::class;

    protected function afterCreate(): void
    {
        $record = $this->record;
        
        $users = \App\Models\User::whereHas('roles', fn($q) => $q->whereIn('name', ['super_admin', 'pimpinan']))->get();
        
        $notif = \Filament\Notifications\Notification::make()
            ->title('Transaksi Operasional Baru')
            ->body("Transaksi sebesar Rp " . number_format($record->nominal, 0, ',', '.') . " untuk {$record->kategori}")
            ->success()
            ->actions([
                \Filament\Actions\Action::make('lihat')
                    ->button()
                    ->url(TransaksiOperasionalResource::getUrl('index', ['tenant' => $record->perusahaan->slug])),
            ]);

        foreach ($users as $user) {
            $notif->sendToDatabase($user);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
