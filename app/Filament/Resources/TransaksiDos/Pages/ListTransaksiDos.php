<?php

namespace App\Filament\Resources\TransaksiDos\Pages;

use App\Filament\Resources\TransaksiDos\TransaksiDoResource;
use App\Models\TransaksiDo;
use App\Models\TutupHari;
use App\Models\JurnalKeuangan;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class ListTransaksiDos extends ListRecords
{
    protected static string $resource = TransaksiDoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('tutup_hari')
                ->label('Tutup Hari')
                ->icon('heroicon-o-lock-closed')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Penutupan Hari')
                ->modalDescription('Setelah hari ditutup, transaksi tidak dapat diubah kecuali oleh Superadmin/Admin.')
                ->form([
                    DatePicker::make('tanggal')
                        ->label('Tanggal Tutup')
                        ->default(now())
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y'),
                    TextInput::make('saldo_akhir_fisik')
                        ->label('Saldo Kas Fisik')
                        ->numeric()
                        ->prefix('Rp')
                        ->required()
                        ->default(0),
                    Textarea::make('catatan')
                        ->label('Catatan'),
                ])
                ->action(function (array $data) {
                    $tanggal = $data['tanggal'];
                    $perusahaanId = Filament::getTenant()->id;
                    
                    if (TutupHari::isClosed($tanggal, $perusahaanId)) {
                        Notification::make()
                            ->title('Gagal')
                            ->body('Tanggal ini sudah ditutup sebelumnya.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $totalTonase = TransaksiDo::whereDate('tanggal', '=', $tanggal, 'and')->sum('tonase');
                    $totalRupiah = TransaksiDo::whereDate('tanggal', '=', $tanggal, 'and')->sum('sub_total');
                    $totalMasuk = JurnalKeuangan::whereDate('tanggal', '=', $tanggal, 'and')->where('jenis_transaksi', '=', 'Pemasukan', 'and')->sum('nominal');
                    $totalKeluar = JurnalKeuangan::whereDate('tanggal', '=', $tanggal, 'and')->where('jenis_transaksi', '=', 'Pengeluaran', 'and')->sum('nominal');
                    
                    $saldoSistem = $totalMasuk - $totalKeluar; 
                    
                    TutupHari::create([
                        'perusahaan_id' => $perusahaanId,
                        'tanggal' => $tanggal,
                        'total_do_tonase' => $totalTonase,
                        'total_do_rupiah' => $totalRupiah,
                        'total_pemasukan' => $totalMasuk,
                        'total_pengeluaran' => $totalKeluar,
                        'saldo_akhir_sistem' => $saldoSistem,
                        'saldo_akhir_fisik' => $data['saldo_akhir_fisik'],
                        'selisih' => $data['saldo_akhir_fisik'] - $saldoSistem,
                        'catatan' => $data['catatan'],
                        'user_id' => auth()->id(),
                        'status' => 'closed',
                    ]);

                    Notification::make()
                        ->title('Berhasil')
                        ->body("Hari ini (" . \Carbon\Carbon::parse($tanggal)->format('d/m/Y') . ") telah ditutup.")
                        ->success()
                        ->send();
                }),
        ];
    }

    // Handle filter date changes
    public function updatedTableFilters(): void
    {
        $filter = $this->tableFilters['tanggal'] ?? null;
        if ($filter && isset($filter['dari_tanggal'], $filter['sampai_tanggal'])) {
            $this->dispatch('filter-transaksi', [
                'startDate' => $filter['dari_tanggal'],
                'endDate' => $filter['sampai_tanggal'],
            ]);
        }
    }

    // Handle tab changes
    public function updatedActiveTab(): void
    {
        $this->dispatch('tab-changed', [
            'tab' => $this->activeTab
        ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\JurnalKeuangans\Widgets\JurnalKeuanganDoStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            //
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'hari_ini';
    }

    public function getTabs(): array
    {
        return [
            'hari_ini' => Tab::make('Hari Ini')
                ->icon('heroicon-o-calendar')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereDate('tanggal', '=', today(), 'and'))
                ->badge($this->getTabCount('hari_ini'))
                ->badgeColor('success'),

            'kemarin' => Tab::make('Kemarin')
                ->icon('heroicon-o-calendar-days')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereDate('tanggal', '=', now()->subDay()->toDateString(), 'and'))
                ->badge($this->getTabCount('kemarin'))
                ->badgeColor('warning'),

            'semua' => Tab::make('Semua Transaksi')
                ->icon('heroicon-o-clipboard-document-list')
                ->badge($this->getTabCount('semua'))
                ->modifyQueryUsing(fn(Builder $query) => $query)
                ->badgeColor('primary'),

            'tunai' => Tab::make('Tunai')
                ->icon('heroicon-o-banknotes')
                ->badge($this->getTabCount('tunai'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_bayar', '=', 'tunai', 'and'))
                ->badgeColor('success'),

            'transfer' => Tab::make('Transfer')
                ->icon('heroicon-o-credit-card')
                ->badge($this->getTabCount('transfer'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_bayar', '=', 'transfer', 'and'))
                ->badgeColor('info'),

            'cair_luar' => Tab::make('Cair di Luar')
                ->icon('heroicon-o-banknotes')
                ->badge($this->getTabCount('cair_luar'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_bayar', '=', 'cair di luar', 'and'))
                ->badgeColor('warning'),

            'belum_dibayar' => Tab::make('Belum Dibayar')
                ->icon('heroicon-o-banknotes')
                ->badge($this->getTabCount('belum_dibayar'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_bayar', '=', 'belum dibayar', 'and'))
                ->badgeColor('danger'),
        ];
    }

    protected function getTabCount(string $tab): int
    {
        $query = TransaksiDo::query();
        $filter = $this->tableFilters['tanggal'] ?? null;

        if (in_array($tab, ['semua', 'tunai', 'transfer', 'cair_luar', 'belum_dibayar'])) {
            if ($filter && !empty($filter['dari_tanggal']) && !empty($filter['sampai_tanggal'])) {
                $query->whereBetween('tanggal', [$filter['dari_tanggal'], $filter['sampai_tanggal']], 'and', false);
            } else {
                $query->currentMonth();
            }
        }

        return match ($tab) {
            'hari_ini' => $query->whereDate('tanggal', '=', today(), 'and')->count('*'),
            'kemarin' => $query->whereDate('tanggal', '=', now()->subDay()->toDateString(), 'and')->count('*'),
            'tunai' => $query->where('cara_bayar', '=', 'tunai', 'and')->count('*'),
            'transfer' => $query->where('cara_bayar', '=', 'transfer', 'and')->count('*'),
            'cair_luar' => $query->where('cara_bayar', '=', 'cair di luar', 'and')->count('*'),
            'belum_dibayar' => $query->where('cara_bayar', '=', 'belum dibayar', 'and')->count('*'),
            default => $query->count('*'),
        };
    }
}
