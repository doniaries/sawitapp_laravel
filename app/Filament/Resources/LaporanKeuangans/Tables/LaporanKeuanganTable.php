<?php

namespace App\Filament\Resources\LaporanKeuangans\Tables;

use App\Models\LaporanKeuangan;
use App\Models\Operasional;
use App\Observers\LaporanKeuanganObserver;
use App\Services\LaporanKeuanganService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class LaporanKeuanganTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->dateTime('d/M/Y H:i')
                    ->label('Tanggal Transaksi')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenis_transaksi')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Pemasukan' => 'success',
                        'Pengeluaran' => 'danger',
                        default => 'primary',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('kategori')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sub_kategori')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nominal')
                    ->numeric()
                    ->prefix('Rp. ')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->prefix('Rp. ')
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('sumber_transaksi')
                    ->label('Kategori')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nomor_referensi')
                    ->label('Nomor')
                    ->badge()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Nomor DO berhasil disalin')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pihak_terkait')
                    ->label('Nama')
                    ->formatStateUsing(function ($record) {
                        return match ($record->tipe_pihak?->value) {
                            'supir' => $record->supir?->nama ?? $record->pihak_terkait,
                            'pekerja' => $record->pekerja?->nama ?? $record->pihak_terkait,
                            'penjual' => $record->penjual?->nama ?? $record->pihak_terkait,
                            'user' => $record->user?->name ?? $record->pihak_terkait,
                            default => $record->pihak_terkait
                        };
                    })
                    ->searchable([
                        'pihak_terkait',
                        'supir.nama',
                        'pekerja.nama',
                        'penjual.nama',
                        'user.name'
                    ])
                    ->badge(),
                Tables\Columns\TextColumn::make('tipe_pihak')
                    ->label('Tipe')
                    ->formatStateUsing(fn($state) => $state?->getLabel())
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('cara_pembayaran')
                    ->searchable(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('cetakRekap')
                    ->label(fn($livewire) => 'Cetak Rekap ' . ($livewire->activeTab === 'hari_ini' ? 'Hari Ini' : ($livewire->activeTab === 'bulan_ini' ? 'Bulan Ini' : 'Terpilih')))
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->action(function (LaporanKeuanganService $service, $livewire) {
                        $startDate = now();
                        $endDate = now();

                        if ($livewire->activeTab === 'bulan_ini') {
                            $startDate = now()->startOfMonth();
                            $endDate = now()->endOfMonth();
                        } elseif ($livewire->activeTab === 'tahun_ini') {
                            $startDate = now()->startOfYear();
                            $endDate = now()->endOfYear();
                        } elseif ($livewire->activeTab === 'semua' || empty($livewire->activeTab)) {
                            $startDate = now()->startOfMonth();
                            $endDate = now()->endOfMonth();
                        }

                        try {
                            $viewData = $service->generatePdfReport($startDate, $endDate);
                            $pdf = Pdf::loadView('laporan.keuangan-harian', $viewData);
                            $pdf->setPaper('a4', 'landscape');

                            return response()->streamDownload(
                                fn() => print($pdf->output()),
                                "rekap-{$livewire->activeTab}-" . now()->format('Y-m-d') . ".pdf"
                            );
                        } catch (\Exception $e) {
                            Log::error('Quick Print Error: ' . $e->getMessage());
                        }
                    })
                    ->hidden(fn($livewire) => $livewire->activeTab === 'semua'),
                Tables\Actions\Action::make('syncSaldo')
                    ->label('Sync Saldo')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Sinkronisasi Saldo')
                    ->modalDescription('Yakin ingin mensinkronkan ulang saldo?')
                    ->modalSubmitActionLabel('Ya, Sinkronkan')
                    ->action(function () {
                        try {
                            app(LaporanKeuanganObserver::class)->syncSaldoPerusahaan();
                            Notification::make()
                                ->title('Saldo Berhasil Disinkronkan')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal Sinkronisasi')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('downloadPdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->form([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\DatePicker::make('start_date')
                                    ->label('Dari Tanggal')
                                    ->required()
                                    ->default(now())
                                    ->displayFormat('d/m/Y')
                                    ->native(false),
                                Forms\Components\DatePicker::make('end_date')
                                    ->label('Sampai Tanggal')
                                    ->required()
                                    ->default(now())
                                    ->displayFormat('d/m/Y')
                                    ->native(false),
                            ])
                            ->columns(2)
                    ])
                    ->action(function (array $data) {
                        try {
                            $startDate = Carbon::parse($data['start_date'])->startOfDay();
                            $endDate = Carbon::parse($data['end_date'])->endOfDay();
                            $service = app(LaporanKeuanganService::class);
                            $viewData = $service->generatePdfReport($startDate, $endDate);
                            $pdf = Pdf::loadView('laporan.keuangan-harian', $viewData);
                            $pdf->setPaper('a4', 'landscape');

                            return response()->streamDownload(
                                fn() => print($pdf->output()),
                                "laporan-keuangan-{$startDate->format('Y-m-d')}-{$endDate->format('Y-m-d')}.pdf"
                            );
                        } catch (\Exception $e) {
                            Log::error('Error generating PDF:', [
                                'error' => $e->getMessage()
                            ]);
                            Notification::make()
                                ->danger()
                                ->title('Error')
                                ->body('Gagal membuat laporan: ' . $e->getMessage())
                                ->send();
                        }
                    })
            ])
            ->filters([
                SelectFilter::make('jenis_transaksi')
                    ->options([
                        'Pemasukan' => 'Pemasukan',
                        'Pengeluaran' => 'Pengeluaran'
                    ])
                    ->placeholder('Pilih Jenis Transaksi')
                    ->label('Jenis Transaksi'),

                Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal')
                            ->displayFormat('d/m/Y')
                            ->format('Y-m-d')
                            ->native(false)
                            ->default(now()->startOfMonth()),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Ke Tanggal')
                            ->displayFormat('d/m/Y')
                            ->format('Y-m-d')
                            ->native(false)
                            ->default(now())
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['dari_tanggal'], fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '>=', $date))
                            ->when($data['sampai_tanggal'], fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '<=', $date));
                    }),

                Tables\Filters\TrashedFilter::make()
            ], layout: FiltersLayout::Modal)
            ->filtersTriggerAction(fn(Tables\Actions\Action $action) => $action->button()->label('Filter Tanggal'))
            ->defaultSort('created_at', 'desc')
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->striped()
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->deferLoading()
            ->poll('15s');
    }
}
