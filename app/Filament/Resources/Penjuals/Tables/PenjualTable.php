<?php

namespace App\Filament\Resources\Penjuals\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\Action;
use Filament\Facades\Filament;

use Filament\Tables\Columns\TextColumn;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use App\Actions\Finance\ProcessDebtPayment;
use Filament\Notifications\Notification;

class PenjualTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('alamat')
                    ->label('Alamat')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('telepon')
                    ->label('Telepon')
                    ->searchable(),

                TextColumn::make('sisa_hutang_sum')
                    ->label('Sisa Hutang')
                    ->numeric(0, ',', '.')
                    ->prefix('Rp ')
                    ->alignment('right')
                    ->sortable()
                    ->color(fn($state) => $state > 0 ? 'danger' : 'success')
                    ->weight('bold'),

                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\TrashedFilter::make(),
                \Filament\Tables\Filters\Filter::make('has_hutang')
                    ->query(fn(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder => $query->having('sisa_hutang_sum', '>', 0))
                    ->label('Ada Hutang')
                    ->toggle()
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label('Tambah Penjual'),
            ])
            ->recordActions([
                Action::make('bayar_hutang')
                    ->label('Bayar Hutang')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn($record) => $record->sisa_hutang > 0)
                    ->schema([
                        TextInput::make('sisa_hutang_info')
                            ->label('Sisa Hutang Saat Ini')
                            ->disabled()
                            ->dehydrated(false)
                            ->prefix('Rp')
                            ->default(fn($record) => number_format($record->sisa_hutang, 0, ',', '.')),
                        TextInput::make('nominal')
                            ->label('Nominal Pembayaran')
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                            ->required()
                            ->prefix('Rp')
                            ->default(null)
                            ->placeholder('Masukkan nominal pembayaran')
                            ->debounce(500),
                        DatePicker::make('tanggal')
                            ->label('Tanggal Pembayaran')
                            ->default(now())
                            ->required(),
                        Select::make('cara_pembayaran')
                            ->label('Cara Pembayaran')
                            ->options([
                                'tunai' => 'Tunai',
                                'transfer' => 'Transfer Bank',
                            ])
                            ->default('tunai')
                            ->required(),
                        TextInput::make('keterangan')
                            ->label('Keterangan')
                            ->placeholder('Opsional')
                            ->debounce(500),
                    ])
                    ->action(function ($record, array $data) {
                        try {
                            app(ProcessDebtPayment::class)->execute(
                                $record,
                                (float) $data['nominal'],
                                $data['tanggal'],
                                $data['cara_pembayaran'],
                                $data['keterangan']
                            );

                            Notification::make()
                                ->title('Pembayaran Berhasil')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                EditAction::make()->label('Edit'),
                ViewAction::make()->label('Lihat Detail'),
                DeleteAction::make()
                    ->label('Hapus')
                    ->before(function (DeleteAction $action, $record) {
                        if ($record->sisa_hutang > 0 && !Filament::auth()->user()->hasRole('super_admin')) {
                            Notification::make()
                                ->title('Gagal menghapus')
                                ->body("Penjual masih memiliki hutang. Hanya Super Admin yang dapat menghapus data ini.")
                                ->danger()
                                ->send();
                            
                            $action->halt();
                        }
                    }),
                RestoreAction::make()->label('Pulihkan'),
                ForceDeleteAction::make()
                    ->label('Hapus Selamanya')
                    ->before(function (ForceDeleteAction $action, $record) {
                        if ($record->sisa_hutang > 0 && !Filament::auth()->user()->hasRole('super_admin')) {
                            Notification::make()
                                ->title('Gagal menghapus selamanya')
                                ->body("Penjual masih memiliki hutang. Hanya Super Admin yang dapat menghapus permanen data ini.")
                                ->danger()
                                ->send();
                            $action->halt();
                        }
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $isSuperAdmin = Filament::auth()->user()->hasRole('super_admin');
                            $records->each(function ($record) use ($isSuperAdmin) {
                                if ($record->sisa_hutang > 0 && !$isSuperAdmin) {
                                    Notification::make()
                                        ->title('Gagal menghapus')
                                        ->body("Penjual {$record->nama} masih memiliki hutang. Hanya Super Admin yang dapat menghapus.")
                                        ->danger()
                                        ->send();
                                    return;
                                }
                                $record->delete();
                            });
                            Notification::make()->title('Data berhasil diproses')->success()->send();
                        })
                        ->requiresConfirmation(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll(null);
    }
}
