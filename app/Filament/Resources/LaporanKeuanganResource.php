<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanKeuanganResource\Pages;
use App\Filament\Resources\LaporanKeuanganResource\RelationManagers;
use App\Filament\Resources\LaporanKeuanganResource\Widgets\LaporanKeuanganDoStatsWidget;
use App\Filament\Resources\TransaksiDoResource\Widgets\TransaksiDoStatWidget;
use App\Models\LaporanKeuangan;
use App\Observers\LaporanKeuanganObserver;
use App\Services\LaporanKeuanganService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Exceptions\Halt;
use Filament\Tables;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class LaporanKeuanganResource extends Resource
{
    protected static ?string $model = LaporanKeuangan::class;
    // protected static ?string $navigationGroup = 'Operasional dan Laporan';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Laporan';
    protected static ?int $navigationSort = 2;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('tanggal')
                    ->required(),
                Forms\Components\TextInput::make('jenis_transaksi')
                    ->required(),
                Forms\Components\TextInput::make('kategori')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('sub_kategori')
                    ->maxLength(50),
                Forms\Components\TextInput::make('nominal')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('sumber_transaksi')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('referensi_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('nomor_referensi')
                    ->maxLength(50),
                Forms\Components\TextInput::make('pihak_terkait')
                    ->maxLength(100),
                Forms\Components\TextInput::make('tipe_pihak'),
                Forms\Components\TextInput::make('cara_pembayaran')
                    ->maxLength(20),
                Forms\Components\Textarea::make('keterangan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->dateTime()
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
                Tables\Columns\TextColumn::make('referensi_id')
                    // ->numeric()
                    ->hidden()
                    ->sortable(),
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
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            //download PDF//
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
                            // Default to month if "Semua" is active to avoid massive PDFs
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
                            \Illuminate\Support\Facades\Log::error('Quick Print Error: ' . $e->getMessage());
                        }
                    })
                    ->hidden(fn($livewire) => $livewire->activeTab === 'semua'),
                Tables\Actions\Action::make('syncSaldo')
                    ->label('Sync Saldo')
                    ->icon('heroicon-o-arrow-path') // Icon sync
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Sinkronisasi Saldo')
                    ->modalDescription('Yakin ingin mensinkronkan ulang saldo?')
                    ->modalSubmitActionLabel('Ya, Sinkronkan')
                    ->action(function () {
                        try {
                            // Jalankan sync
                            app(LaporanKeuanganObserver::class)->syncSaldoPerusahaan();

                            // Notifikasi sukses
                            Notification::make()
                                ->title('Saldo Berhasil Disinkronkan')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            // Notifikasi error
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
                    // Action download PDF
                    ->action(function (array $data) {
                        try {
                            $startDate = Carbon::parse($data['start_date'])->startOfDay();
                            $endDate = Carbon::parse($data['end_date'])->endOfDay();

                            // Get report data from service
                            $service = app(LaporanKeuanganService::class);
                            $viewData = $service->generatePdfReport($startDate, $endDate);

                            // Generate PDF
                            $pdf = Pdf::loadView('laporan.keuangan-harian', $viewData);
                            $pdf->setPaper('a4', 'landscape');

                            return response()->streamDownload(
                                fn() => print($pdf->output()),
                                "laporan-keuangan-{$startDate->format('Y-m-d')}-{$endDate->format('Y-m-d')}.pdf"
                            );
                        } catch (\Exception $e) {
                            Log::error('Error generating PDF:', [
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);

                            Notification::make()
                                ->danger()
                                ->duration(3000)
                                ->persistent()
                                ->title('Error')
                                ->body('Gagal membuat laporan: ' . $e->getMessage())
                                ->send();
                        }
                    })
            ])
            // Filter tanggal
            ->filters([
                // Jenis Transaksi Filter
                SelectFilter::make('jenis_transaksi')
                    ->options([
                        'Pemasukan' => 'Pemasukan',
                        'Pengeluaran' => 'Pengeluaran'
                    ])
                    ->placeholder('Pilih Jenis Transaksi')
                    ->label('Jenis Transaksi'),

                // Date Range Filter
                Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal')
                            ->displayFormat('d/m/Y') // Format tampilan
                            ->format('Y-m-d') // Format yang disimpan
                            ->native(false)
                            ->default(now()->startOfMonth()),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Ke Tanggal')
                            ->displayFormat('d/m/Y') // Format tampilan
                            ->format('Y-m-d') // Format yang disimpan
                            ->native(false)
                            ->default(now())
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '<=', $date),
                            );
                    }),

                // Trash Filter
                Tables\Filters\TrashedFilter::make()
            ], layout: FiltersLayout::Modal)
            // ->filtersFormColumns(2)
            ->filtersTriggerAction(
                fn(Tables\Actions\Action $action) => $action
                    ->button()
                    ->label('Filter Tanggal')
            )
            ->defaultSort('created_at', 'desc')
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                    // Tables\Actions\ForceDeleteBulkAction::make(),
                    // Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([5, 10, 25, 50, 100, 'all'])
            ->deferLoading()
            ->poll('15s');
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
            'index' => Pages\ListLaporanKeuangans::route('/'),
            // 'create' => Pages\CreateLaporanKeuangan::route('/create'),
            // 'edit' => Pages\EditLaporanKeuangan::route('/{record}/edit'),
            'view' => Pages\ViewLaporanKeuangan::route('/{record}'),
        ];
    }


    // Override canCreate untuk mencegah pembuatan data
    public static function canCreate(): bool
    {
        return false;
    }

    public static function getWidgets(): array
    {
        return [
            LaporanKeuanganDoStatsWidget::class,
        ];
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['supir', 'pekerja', 'penjual', 'user'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
