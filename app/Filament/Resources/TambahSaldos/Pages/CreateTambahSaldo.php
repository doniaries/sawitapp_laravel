<?php

namespace App\Filament\Resources\TambahSaldos\Pages;

use App\Filament\Resources\TambahSaldos\TambahSaldoResource;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTambahSaldo extends CreateRecord
{
    protected static string $resource = TambahSaldoResource::class;

    protected function afterCreate(): void
    {
        $record = $this->record;
        
        $record->loadMissing(['user', 'perusahaan']);
        
        // Notify Pimpinan for awareness
        $recipients = \App\Models\User::whereHas('roles', fn($query) => 
            $query->whereIn('name', ['pimpinan'])
        )->get();
        
        Notification::make()
            ->title('Saldo Perusahaan Ditambahkan')
            ->body(sprintf(
                'Saldo sebesar Rp %s ditambahkan oleh %s',
                number_format($record->nominal, 0, ',', '.'),
                $record->user?->name ?? 'Admin'
            ))
            ->success()
            // ->actions([
            //     NotificationAction::make('lihat')
            //         ->label('Lihat Detail')
            //         ->button()
            //         ->url(TambahSaldoResource::getUrl('index', ['tenant' => $record->perusahaan])),
            // ])
            ->sendToDatabase($recipients);
    }
}
