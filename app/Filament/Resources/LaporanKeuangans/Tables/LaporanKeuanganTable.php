<?php

namespace App\Filament\Resources\LaporanKeuangans\Tables;

use App\Models\LaporanKeuangan;
use App\Models\Operasional;
use App\Observers\LaporanKeuanganObserver;
use App\Services\LaporanKeuanganService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class LaporanKeuanganTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')
                    ->dateTime('d/M/Y H:i')
                    ->label('Tanggal Transaksi')
                    ->sortable(),
                TextColumn::make('jenis_transaksi')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Pemasukan' => 'success',
                        'Pengeluaran' => 'danger',
                        default => 'primary',
                    })
                    ->searchable(),

                TextColumn::make('kategori')
                    ->searchable(),
                TextColumn::make('sub_kategori')
                    ->searchable(),
                TextColumn::make('nominal')
                    ->numeric()
                    ->prefix('Rp. ')
                    ->summarize([
                        Sum::make()
                            ->prefix('Rp. ')
                    ])
                    ->sortable(),
                TextColumn::make('sumber_transaksi')
                    ->label('Kategori')
                    ->searchable(),
                TextColumn::make('nomor_referensi')
                    ->label('Nomor')
                    ->badge()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Nomor DO berhasil disalin')
                    ->searchable(),
                TextColumn::make('pihak_terkait')
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
                TextColumn::make('tipe_pihak')
                    ->label('Tipe')
                    ->formatStateUsing(fn($state) => $state?->getLabel())
                    ->badge()
                    ->searchable(),

                TextColumn::make('cara_pembayaran')
                    ->searchable(),
            ])
            ->headerActions([
                Action::make('cetakRekap')
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
                Action::make('syncSaldo')
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
                Action::make('downloadPdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->schema([
                        Grid::make()
                            ->schema([
                                DatePicker::make('start_date')
                                    ->label('Dari Tanggal')
                                    ->required()
                                    ->default(now())
                                    ->displayFormat('d/m/Y')
                                    ->native(false),
                                DatePicker::make('end_date')
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
                    ->schema([
                        DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal')
                            ->displayFormat('d/m/Y')
                            ->format('Y-m-d')
                            ->native(false)
                            ->default(now()->startOfMonth()),
                        DatePicker::make('sampai_tanggal')
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
                TrashedFilter::make()
            ], layout: FiltersLayout::Modal)
            ->filtersTriggerAction(fn(Action $action) => $action->button()->label('Filter Tanggal'))
            ->defaultSort('created_at', 'desc')
            ->filtersFormColumns(2)
            ->recordActions([
                ViewAction::make(),
            ])
            ->striped()
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->deferLoading()
            ->poll('15s');
    }
}
