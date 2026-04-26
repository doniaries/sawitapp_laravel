<?php

namespace App\Filament\Resources\Penjuals\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\CreateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
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
                    ->formatStateUsing(fn(string $state): string => mb_strtoupper($state))
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

                TextColumn::make('sisa_hutang')
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
                            ->numeric()
                            ->required()
                            ->prefix('Rp')
                            ->default(fn($record) => $record->sisa_hutang),
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
                            ->placeholder('Opsional'),
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
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(function ($record) {
                                if ($record->sisa_hutang > 0) {
                                    Notification::make()
                                        ->title('Gagal menghapus')
                                        ->body("Penjual {$record->nama} masih memiliki hutang. Tidak dapat dihapus.")
                                        ->danger()
                                        ->send();
                                    return;
                                }
                                $record->delete();
                            });
                            Notification::make()->title('Data berhasil dihapus')->success()->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s');
    }
}
