<?php

namespace App\Filament\Resources;

// Model Imports
use Closure;

use Filament\Forms;
use Filament\Tables;
use App\Enums\TipeNama;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Enums\KategoriOperasional;
use Filament\Support\Colors\Color;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Badge;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OperasionalResource\Pages;
use App\Models\{Operasional, Penjual, Pekerja, User, Perusahaan};
use App\Enums\TipePihak;
// use App\Filament\Resources\OperasionalResource\Traits\HasOperationalHelpers;

class OperasionalResource extends Resource
{

    // use HasOperationalHelpers;


    protected static ?string $model = Operasional::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Op';
    // protected static ?string $navigationGroup = 'Operasional dan Laporan';
    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()
            ->whereDate('created_at', today())
            ->count();
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                // Kolom Kiri
                                Forms\Components\Group::make([
                                    Forms\Components\DateTimePicker::make('tanggal')
                                        ->label('Tanggal')
                                        ->native(false)
                                        ->displayFormat('d/m/Y H:i')
                                        ->default(now())
                                        ->required(),

                                    Forms\Components\Select::make('operasional')
                                        ->label('Jenis')
                                        ->options([
                                            'pemasukan' => 'Pemasukan',
                                            'pengeluaran' => 'Pengeluaran',
                                        ])
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(fn($state, Forms\Set $set) => $set('kategori', null)),

                                    Forms\Components\Select::make('kategori')
                                        ->label('Kategori')
                                        ->options(function ($get) {
                                            return match ($get('operasional')) {
                                                'pemasukan' => KategoriOperasional::forPemasukan(),
                                                'pengeluaran' => KategoriOperasional::forPengeluaran(),
                                                default => []
                                            };
                                        })
                                        ->required()
                                        ->live(),

                                    Forms\Components\Select::make('tipe_nama')
                                        ->label('Tipe')
                                        ->options([
                                            'penjual' => 'Penjual',
                                            'supir' => 'Supir',
                                            'pekerja' => 'Pekerja',
                                            'user' => 'Karyawan'
                                        ])
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(fn($state, Forms\Set $set) => [
                                            $set('penjual_id', null),
                                            $set('supir_id', null),
                                            $set('pekerja_id', null),
                                            $set('user_id', null)
                                        ])
                                ])->columnSpan(1),

                                // Kolom Kanan
                                Forms\Components\Group::make([
                                    Forms\Components\Select::make('penjual_id')
                                        ->label('Pilih Pihak')
                                        ->relationship('penjual', 'nama')
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->visible(fn($get) => $get('tipe_nama') === 'penjual'),

                                    Forms\Components\Select::make('supir_id')
                                        ->label('Pilih Pihak')
                                        ->relationship('supir', 'nama')
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn($get) => $get('tipe_nama') === 'supir'),

                                    Forms\Components\Select::make('pekerja_id')
                                        ->label('Pilih Pihak')
                                        ->relationship('pekerja', 'nama')
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn($get) => $get('tipe_nama') === 'pekerja'),

                                    Forms\Components\Select::make('user_id')
                                        ->label('Pilih Pihak')
                                        ->relationship('user', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn($get) => $get('tipe_nama') === 'user'),

                                    Forms\Components\TextInput::make('nominal')
                                        ->label('Nominal')
                                        ->required()
                                        ->currencyMask(
                                            thousandSeparator: ',',
                                            decimalSeparator: '.',
                                            precision: 0
                                        )
                                        ->prefix('Rp')
                                        ->numeric()
                                        ->live(),

                                    Forms\Components\TextInput::make('keterangan')
                                        ->label('Keterangan')
                                ])->columnSpan(1)
                            ])
                            ->columns(2)
                    ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('operasional')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pemasukan' => 'success',
                        'pengeluaran' => 'danger',
                        default => 'gray'
                    }),

                Tables\Columns\TextColumn::make('kategori')
                    ->label('Kategori')
                    ->formatStateUsing(fn($record) => $record->kategoriLabel)
                    ->searchable(),

                // Tables\Columns\TextColumn::make('tipe_nama')
                //     ->label('Tipe')
                //     ->description(function ($record) {
                //         return match ($record->tipe_nama) {
                //             'supir' => $record->supir?->nama ?? '-',
                //             'penjual' => $record->penjual?->nama ?? '-',
                //             'user' => $record->user?->name ?? '-',
                //             'pekerja' => $record->pekerja?->nama ?? '-',
                //             default => ucfirst($record->tipe_nama)
                //         };
                //     })
                //     ->searchable(),

                Tables\Columns\TextColumn::make('user_id')
                    ->label('Karyawan')
                    ->searchable()
                    ->formatStateUsing(fn($record) => $record->user?->name ?? '-'),

                Tables\Columns\TextColumn::make('penjual_id')
                    ->label('Penjual')
                    ->searchable()
                    ->formatStateUsing(fn($record) => $record->penjual?->nama ?? '-'),

                Tables\Columns\TextColumn::make('supir_id')
                    ->label('Supir')
                    ->searchable()
                    ->formatStateUsing(fn($record) => $record->supir?->nama ?? '-'),

                Tables\Columns\TextColumn::make('pekerja_id')
                    ->label('Pekerja')
                    ->searchable()
                    ->formatStateUsing(fn($record) => $record->pekerja?->nama ?? '-'),

                Tables\Columns\TextColumn::make('nominal')
                    ->label('Nominal')
                    // Changed from $record->sub_total to $record->nominal
                    ->formatStateUsing(fn($record) => 'Rp ' . number_format($record->nominal, 0, ',', '.'))
                    ->alignRight()
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->currency('IDR')
                    ]),

                // Tables\Columns\ImageColumn::make('file_bukti')
                //     ->label('Bukti')
                //     ->disk('public')
                //     ->square() // ganti circular() jadi square()
                //     ->width(100),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('operasional')
                    ->options(Operasional::JENIS_OPERASIONAL),
                Tables\Filters\TrashedFilter::make()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOperasionals::route('/'),
            'create' => Pages\CreateOperasional::route('/create'),
            'edit' => Pages\EditOperasional::route('/{record}/edit'),
        ];
    }

    private static function formatCurrency($number): int
    {
        if (empty($number)) return 0;
        // Handle string format currency
        if (is_string($number)) {
            return (int) str_replace(['.', ','], ['', '.'], $number);
        }
        return (int) $number;
    }


    // public static function getHeaderActions(): array
    // {
    //     return [
    //         \Filament\Actions\Action::make('record_balance')
    //             ->label('Catat Saldo')
    //             ->icon('heroicon-o-currency-dollar')
    //             ->color('success')
    //             ->requiresConfirmation()
    //             ->modalHeading('Catat Saldo Perusahaan')
    //             ->modalDescription('Saldo perusahaan saat ini akan dicatat sebagai catatan operasional.')
    //             ->modalSubmitActionLabel('Ya, Catat Saldo')
    //             ->action(function () {
    //                 try {
    //                     $perusahaan = Perusahaan::first();

    //                     if (!$perusahaan) {
    //                         Notification::make()
    //                             ->title('Error')
    //                             ->body('Tidak ada data perusahaan ditemukan')
    //                             ->danger()
    //                             ->send();
    //                         return;
    //                     }

    //                     // Buat entri operasional untuk saldo
    //                     Operasional::create([
    //                         'tanggal' => now(),
    //                         'nominal' => $perusahaan->saldo,
    //                         'kategori' => KategoriOperasional::TAMBAH_SALDO,
    //                         'operasional' => 'pemasukan',
    //                         'keterangan' => 'Pencatatan saldo perusahaan per tanggal ' . now()->format('d/m/Y H:i'),
    //                         'is_from_transaksi' => false,
    //                     ]);

    //                     Notification::make()
    //                         ->title('Berhasil')
    //                         ->body('Saldo perusahaan sebesar Rp ' . number_format($perusahaan->saldo, 0, ',', '.') . ' berhasil dicatat')
    //                         ->success()
    //                         ->send();
    //                 } catch (\Exception $e) {
    //                     Notification::make()
    //                         ->title('Error')
    //                         ->body('Terjadi kesalahan: ' . $e->getMessage())
    //                         ->danger()
    //                         ->send();
    //                 }
    //             }),
    //         // ... action lainnya
    //     ];
    // }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
