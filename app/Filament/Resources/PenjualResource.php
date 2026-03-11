<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Penjual;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Pagination\Pagination;
use App\Filament\Resources\PenjualResource\Pages;
use App\Filament\Resources\PenjualResource\RelationManagers;
use App\Filament\Resources\PenjualResource\Widgets\PenjualStatsOverview;
use App\Filament\Resources\PenjualResource\RelationManagers\RiwayatHutangPinjamanRelationManager;
use App\Filament\Resources\PenjualResource\RelationManagers\RiwayatPembayaranHutangPinjamanRelationManager;

use App\Filament\Resources\PenjualResource\RelationManagers\RiwayatPembayaranHutangRelationManager;

class PenjualResource extends Resource
{
    protected static ?string $model = Penjual::class;
    // protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 4;



    public static function getRelations(): array
    {
        return [
            RiwayatHutangPinjamanRelationManager::class,
            RiwayatPembayaranHutangRelationManager::class,
        ];
    }

    //---form----//

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Penjual')
                    ->description(fn($context) => $context === 'create' ?
                        'Input data penjual baru & hutang awal' :
                        'Edit informasi penjual')
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Penjual')
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('alamat')
                            ->label('Alamat')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('telepon')
                            ->tel()
                            ->label('Nomor Telepon'),

                        // Hutang awal hanya muncul saat create
                        Forms\Components\TextInput::make('hutang')
                            ->label(fn($context) => $context === 'create' ?
                                'Hutang Awal' : 'Total Hutang')
                            ->helperText(fn($context) => $context === 'create' ?
                                'Masukkan hutang awal jika ada. Input ini hanya bisa dilakukan sekali saat pendaftaran penjual.' : '')
                            // ->disabled(fn($context) => $context !== 'create')
                            ->dehydrated()
                            ->prefix('Rp')
                            ->numeric()
                            ->default(0)
                            ->currencyMask(
                                thousandSeparator: ',',
                                decimalSeparator: '.',
                                precision: 0
                            ),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('alamat')
                    ->label('Alamat')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('telepon')
                    ->label('Telepon')
                    ->searchable(),

                // Tables\Columns\TextColumn::make('hutang')
                //     ->label('Total Hutang')
                //     ->money('IDR')
                //     ->alignment('right')
                //     ->sortable()
                //     ->color(fn($state) => $state > 0 ? 'danger' : 'success')
                //     ->weight('bold')
                //     ->summarize([
                //         Tables\Columns\Summarizers\Sum::make()
                //             ->money('IDR')
                //     ]),

                // Tampilkan hutang awal di tooltip/hover

                // Tables\Columns\TextColumn::make('total_pinjaman')
                //     ->label('Total Pinjaman')
                //     ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                //     ->sortable(),

                Tables\Columns\TextColumn::make('hutang')
                    ->label('Total Hutang')
                    // ->tooltip(fn($record) =>
                    // "Hutang Awal: Rp " . number_format($record->getOriginal('hutang'), 0, ',', '.'))
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->alignment('right')
                    ->sortable()
                    ->color(fn($state) => $state > 0 ? 'danger' : 'success')
                    ->weight('bold'),


                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('nama', 'asc')
            ->filters([

                // Tables\Filters\SelectFilter::make('hutang_status')
                //     ->label('Status Hutang')
                //     ->options([
                //         'dengan_hutang' => 'Dengan Hutang',
                //         'tanpa_hutang' => 'Tanpa Hutang',
                //     ])
                //     ->query(function (Builder $query, array $data) {
                //         return $query->when($data['value'], function (Builder $query, string $value) {
                //             return match ($value) {
                //                 'dengan_hutang' => $query->where('hutang', '>', 0),
                //                 'tanpa_hutang' => $query->where('hutang', '=', 0),
                //                 default => $query
                //             };
                //         });
                //     }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),

                /* Tambah Action untuk input hutang baru*/
                // Action::make('tambah_hutang')
                //     ->label('Tambah Hutang')
                //     ->icon('heroicon-o-plus-circle')
                //     ->color('danger')
                //     ->form([
                //         Forms\Components\DateTimePicker::make('tanggal')
                //             ->label('Tanggal')
                //             ->native(false)
                //             ->timezone('Asia/Jakarta')
                //             ->displayFormat('d/m/Y H:i')
                //             ->required()
                //             ->default(now()),

                //         Forms\Components\TextInput::make('nominal')
                //             ->label('Nominal')
                //             ->required()
                //             ->numeric()
                //             ->prefix('Rp')
                //             ->currencyMask(
                //                 thousandSeparator: ',',
                //                 decimalSeparator: '.',
                //                 precision: 0
                //             ),

                //         Forms\Components\Textarea::make('keterangan')
                //             ->label('Keterangan')
                //             ->required(),
                //     ])
                //     ->action(function (Penjual $record, array $data): void {
                //         try {
                //             DB::beginTransaction();

                //             // Format nominal
                //             $nominal = (int)str_replace([',', '.'], '', $data['nominal']);

                //             // Update hutang penjual
                //             $hutangSebelum = $record->hutang;
                //             $record->increment('hutang', $nominal);



                //             DB::commit();

                //             Notification::make()
                //                 ->title('Hutang Berhasil Ditambahkan')
                //                 ->success()
                //                 ->body(
                //                     "Hutang awal: Rp " . number_format($hutangSebelum, 0, ',', '.') . "\n" .
                //                         "Penambahan: Rp " . number_format($nominal, 0, ',', '.') . "\n" .
                //                         "Total hutang: Rp " . number_format($record->fresh()->hutang, 0, ',', '.')
                //                 )
                //                 ->send();
                //         } catch (\Exception $e) {
                //             DB::rollBack();
                //             Notification::make()
                //                 ->title('Error')
                //                 ->danger()
                //                 ->body('Terjadi kesalahan: ' . $e->getMessage())
                //                 ->send();
                //         }
                //     }),

                /* Action untuk bayar hutang */

                // Action::make('bayar_hutang')
                //     ->label('Bayar Hutang')
                //     ->icon('heroicon-o-banknotes')
                //     ->color('success')
                //     ->hidden(fn(Penjual $record): bool => $record->hutang <= 0)
                //     ->form([
                //         Forms\Components\DateTimePicker::make('tanggal')
                //             ->label('Tanggal')
                //             ->required()
                //             ->default(now()),

                //         Forms\Components\TextInput::make('nominal')
                //             ->label('Nominal')
                //             ->required()
                //             ->numeric()
                //             ->prefix('Rp')
                //             ->currencyMask(
                //                 thousandSeparator: ',',
                //                 decimalSeparator: '.',
                //                 precision: 0
                //             )
                //             ->live()
                //             ->afterStateUpdated(function ($state, $record, Forms\Set $set) {
                //                 $nominal = (int)str_replace([',', '.'], '', $state);
                //                 if ($nominal > $record->hutang) {
                //                     $set('nominal', number_format($record->hutang, 0, ',', ','));
                //                     Notification::make()
                //                         ->warning()
                //                         ->title('Pembayaran disesuaikan')
                //                         ->body('Nominal pembayaran disesuaikan dengan total hutang')
                //                         ->send();
                //                 }
                //             }),

                //         Forms\Components\Textarea::make('keterangan')
                //             ->label('Keterangan')
                //             ->required(),
                //     ])
                //     ->action(function (Penjual $record, array $data): void {
                //         try {
                //             DB::beginTransaction();

                //             // Format nominal
                //             $nominal = (int)str_replace([',', '.'], '', $data['nominal']);

                //             // Validasi pembayaran tidak melebihi hutang
                //             if ($nominal > $record->hutang) {
                //                 throw new \Exception('Pembayaran melebihi total hutang');
                //             }

                //             // Update hutang penjual
                //             $hutangSebelum = $record->hutang;
                //             $record->decrement('hutang', $nominal);



                //             DB::commit();

                //             Notification::make()
                //                 ->title('Pembayaran Hutang Berhasil')
                //                 ->success()
                //                 ->body(
                //                     "Hutang awal: Rp " . number_format($hutangSebelum, 0, ',', '.') . "\n" .
                //                         "Pembayaran: Rp " . number_format($nominal, 0, ',', '.') . "\n" .
                //                         "Sisa hutang: Rp " . number_format($record->fresh()->hutang, 0, ',', '.')
                //                 )
                //                 ->send();
                //         } catch (\Exception $e) {
                //             DB::rollBack();
                //             Notification::make()
                //                 ->title('Error')
                //                 ->danger()
                //                 ->body('Terjadi kesalahan: ' . $e->getMessage())
                //                 ->send();
                //         }
                //     }),
            ])
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->deferLoading()
            ->poll('30s')
            ->persistSortInSession()
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $records->each(function ($record) {
                            if ($record->hutang > 0) {
                                throw new \Exception("Penjual {$record->nama} masih memiliki hutang. Tidak dapat dihapus.");
                            }
                        });
                        $records->each->delete();
                    }),
            ]);
    }



    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenjuals::route('/'),
            'create' => Pages\CreatePenjual::route('/create'),
            'view' => Pages\ViewPenjual::route('/{record}'),
            'edit' => Pages\EditPenjual::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}