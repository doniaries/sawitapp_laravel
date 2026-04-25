<?php

namespace App\Filament\Resources\Penjuals\RelationManagers;

use App\Actions\Finance\ProcessDebtPayment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;

class RiwayatPembayaranHutangRelationManager extends RelationManager
{
    // Gunakan relasi hasMany riwayatPembayaran yang ada di model Penjual
    protected static string $relationship = 'riwayatPembayaran';

    protected static ?string $title = '💳 Riwayat Pembayaran Hutang';
    protected static ?string $modelLabel = 'Pembayaran';
    protected static ?string $pluralModelLabel = 'Pembayaran Hutang';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nominal')
                    ->label('Jumlah Bayar')
                    ->numeric(0, ',', '.')
                    ->prefix('Rp ')
                    ->alignEnd()
                    ->color('success')
                    ->weight('bold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->wrap()
                    ->limit(60),
            ])
            ->defaultSort('tanggal', 'desc')
            ->headerActions([
                // Tombol Bayar Hutang langsung di dalam tab ini
                Action::make('bayar_hutang')
                    ->label('+ Bayar Hutang')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn() => $this->getOwnerRecord()->sisa_hutang > 0)
                    ->form([
                        TextInput::make('sisa_hutang_info')
                            ->label('Sisa Hutang Saat Ini')
                            ->disabled()
                            ->dehydrated(false)
                            ->prefix('Rp')
                            ->default(fn() => number_format($this->getOwnerRecord()->sisa_hutang, 0, ',', '.')),

                        TextInput::make('nominal')
                            ->label('Nominal Pembayaran')
                            ->numeric()
                            ->required()
                            ->prefix('Rp')
                            ->default(fn() => $this->getOwnerRecord()->sisa_hutang),

                        DatePicker::make('tanggal')
                            ->label('Tanggal Pembayaran')
                            ->default(now())
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        Select::make('cara_pembayaran')
                            ->label('Cara Pembayaran')
                            ->options([
                                'tunai'    => 'Tunai',
                                'transfer' => 'Transfer Bank',
                            ])
                            ->default('tunai')
                            ->required(),

                        Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->placeholder('Opsional')
                            ->rows(2),
                    ])
                    ->action(function (array $data) {
                        $penjual = $this->getOwnerRecord();
                        try {
                            app(ProcessDebtPayment::class)->execute(
                                $penjual,
                                (float) $data['nominal'],
                                $data['tanggal'],
                                $data['cara_pembayaran'],
                                $data['keterangan'] ?? null,
                            );
                            Notification::make()
                                ->title('Pembayaran Berhasil')
                                ->body("Hutang {$penjual->nama} berkurang Rp " . number_format($data['nominal'], 0, ',', '.'))
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->actions([])
            ->bulkActions([])
            ->emptyStateHeading('Belum ada pembayaran')
            ->emptyStateDescription('Klik tombol "Bayar Hutang" untuk mencatat pembayaran.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }
}
