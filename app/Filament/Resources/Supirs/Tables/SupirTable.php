<?php

namespace App\Filament\Resources\Supirs\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\CreateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use App\Actions\Finance\ProcessDebtPayment;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class SupirTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                TextColumn::make('nama')
                    ->formatStateUsing(fn(string $state): string => mb_strtoupper($state))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('alamat')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('telepon')
                    ->searchable(),

                TextColumn::make('sisa_hutang')
                    ->label('Sisa Hutang')
                    ->numeric(0, ',', '.')
                    ->prefix('Rp ')
                    ->alignment('right')
                    ->sortable()
                    ->color(fn($state) => $state > 0 ? 'danger' : 'success')
                    ->weight('bold'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TrashedFilter::make(),
                Filter::make('has_hutang')
                    ->query(fn(Builder $query): Builder => $query->whereRaw('(hutang - (SELECT COALESCE(SUM(nominal), 0) FROM pembayaran_hutang WHERE supir_id = supir.id)) > 0'))
                    ->label('Ada Hutang')
                    ->toggle()
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label('Tambah Supir'),
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
                            ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                            ->required()
                            ->prefix('Rp')
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
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation(),
                    ForceDeleteBulkAction::make()
                        ->requiresConfirmation(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s');
    }
}
