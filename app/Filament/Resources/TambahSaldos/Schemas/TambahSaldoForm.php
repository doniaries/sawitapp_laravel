<?php

namespace App\Filament\Resources\TambahSaldos\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Schema;

class TambahSaldoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('perusahaan_id')
                    ->relationship('perusahaan', 'name')
                    ->default(fn() => Auth::user()?->perusahaan_id)
                    ->required()
                    ->disabled() // Sesuai yang aktif
                    ->dehydrated(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->default(fn() => Auth::id())
                    ->required()
                    ->disabled() // Sesuai yang aktif
                    ->dehydrated(),
                DateTimePicker::make('tanggal_pengajuan')
                    ->default(now())
                    ->required()
                    ->disabled() // Tidak bisa diedit sesuai request
                    ->dehydrated(),
                TextInput::make('nominal')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0),
                Textarea::make('keperluan')
                    ->required()
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak'
                    ])
                    ->default(function () {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        if ($user && ($user->email === 'superadmin@gmail.com' || $user->hasRole(['admin', 'pimpinan']))) {
                            return 'disetujui';
                        }
                        return 'pending';
                    })
                    ->disabled() // Tidak bisa diubah manual
                    ->dehydrated()
                    ->required(),
                TextInput::make('bukti_transfer')
                    ->placeholder('Diisi setelah disetujui')
                    ->disabled(),
            ]);
    }
}
