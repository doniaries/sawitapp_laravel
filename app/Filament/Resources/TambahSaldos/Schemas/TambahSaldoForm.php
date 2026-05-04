<?php

namespace App\Filament\Resources\TambahSaldos\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\FileUpload;
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
                Hidden::make('perusahaan_id')
                    ->default(fn() => Auth::user()?->perusahaan_id),
                Hidden::make('user_id')
                    ->default(fn() => Auth::id()),
                DateTimePicker::make('tanggal')
                    ->default(now())
                    ->required()
                    ->readOnly()
                    ->label('Tanggal')
                    ->native(false)
                    ->displayFormat('d/m/Y H:i')
                    ->rules([
                        fn($get) => function (string $attribute, $value, $fail) use ($get) {
                            $perusahaanId = \Filament\Facades\Filament::getTenant()->id;
                            if (!\App\Models\TutupHari::canModify($value, $perusahaanId)) {
                                $fail("Data tidak dapat ditambah/diubah karena hari tersebut sudah ditutup.");
                            }
                        },
                    ]),
                TextInput::make('nominal')
                    ->required()
                    ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                    ->prefix('Rp')
                    ->default(null)
                    ->debounce(500),
                Textarea::make('keterangan')
                    ->maxLength(100)
                    ->debounce(500),
                FileUpload::make('bukti_transfer')
                    ->image()
                    ->directory('bukti-transfer')
                    ->label('Bukti Transfer'),

            ]);
    }
}
