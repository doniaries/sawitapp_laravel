<?php

namespace App\Filament\Resources\Penjuals\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\CreateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Action as BulkAction;
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

                TextColumn::make('sisa_hutang')
                    ->label('Total Hutang')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
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
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label('Tambah Penjual'),
            ])
            ->actions([
                Action::make('bayar_hutang')
                    ->label('Bayar Hutang')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn($record) => $record->sisa_hutang > 0)
                    ->form([
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
                EditAction::make(),
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('delete')
                        ->label('Hapus Terpilih')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $records->each(function ($record) {
                                if ($record->sisa_hutang > 0) {
                                    throw new \Exception("Penjual {$record->nama} masih memiliki hutang. Tidak dapat dihapus.");
                                }
                            });
                            $records->each->delete();
                        }),
                ]),
            ])
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s');
    }
}
