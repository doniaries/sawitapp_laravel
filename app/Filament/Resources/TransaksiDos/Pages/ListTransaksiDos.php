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

                    TutupHari::performClosing($data, $perusahaanId);

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
        $filter = $this->tableFilters['tanggal_range'] ?? null;
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
        if ($this->activeTab === 'hari_ini') {
            $this->tableFilters['tanggal_range'] = [
                'dari_tanggal' => today()->toDateString(),
                'sampai_tanggal' => today()->toDateString(),
            ];
        } elseif ($this->activeTab === 'kemarin') {
            $this->tableFilters['tanggal_range'] = [
                'dari_tanggal' => now()->subDay()->toDateString(),
                'sampai_tanggal' => now()->subDay()->toDateString(),
            ];
        }

        // Dispatch filter event to sync widgets
        $filter = $this->tableFilters['tanggal_range'] ?? null;
        if ($filter && isset($filter['dari_tanggal'], $filter['sampai_tanggal'])) {
            $this->dispatch('filter-transaksi', [
                'startDate' => $filter['dari_tanggal'],
                'endDate' => $filter['sampai_tanggal'],
            ]);
        }

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
        return 'semua';
    }

    public function getTabs(): array
    {
        return [
            'hari_ini' => Tab::make('Hari Ini')
                ->icon('heroicon-o-calendar')
                ->badge($this->getTabCount('hari_ini'))
                ->badgeColor('success'),

            'kemarin' => Tab::make('Kemarin')
                ->icon('heroicon-o-calendar-days')
                ->badge($this->getTabCount('kemarin'))
                ->badgeColor('warning'),

            'semua' => Tab::make('Semua Transaksi')
                ->icon('heroicon-o-clipboard-document-list')
                ->badge($this->getTabCount('semua'))
                ->badgeColor('primary'),

            'tunai' => Tab::make('Tunai')
                ->icon('heroicon-o-banknotes')
                ->badge($this->getTabCount('tunai'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_bayar', 'tunai'))
                ->badgeColor('success'),

            'transfer' => Tab::make('Transfer')
                ->icon('heroicon-o-credit-card')
                ->badge($this->getTabCount('transfer'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_bayar', 'transfer'))
                ->badgeColor('info'),

            'cair_luar' => Tab::make('Cair di Luar')
                ->icon('heroicon-o-banknotes')
                ->badge($this->getTabCount('cair_luar'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_bayar', 'cair di luar'))
                ->badgeColor('warning'),

            'belum_dibayar' => Tab::make('Belum Dibayar')
                ->icon('heroicon-o-banknotes')
                ->badge($this->getTabCount('belum_dibayar'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('cara_bayar', 'belum dibayar'))
                ->badgeColor('danger'),
        ];
    }

    protected function getTabCount(string $tab): int
    {
        $tenantId = Filament::getTenant()?->id;
        $baseQuery = TransaksiDo::query()->when($tenantId, fn($q) => $q->where('perusahaan_id', $tenantId));
        
        $filter = $this->tableFilters['tanggal_range'] ?? null;
        $hasFilter = !empty($filter['dari_tanggal']) || !empty($filter['sampai_tanggal']);

        // Cache or calculate based on tab
        return match ($tab) {
            'hari_ini' => (clone $baseQuery)->whereDate('tanggal', today())->count(),
            'kemarin' => (clone $baseQuery)->whereDate('tanggal', now()->subDay()->toDateString())->count(),
            'semua' => (clone $baseQuery)->count(), // "count di semua transaksi seharusnya untuk semua tanggal"
            default => (clone $baseQuery)
                ->when($tab === 'tunai', fn($q) => $q->where('cara_bayar', 'tunai'))
                ->when($tab === 'transfer', fn($q) => $q->where('cara_bayar', 'transfer'))
                ->when($tab === 'cair_luar', fn($q) => $q->where('cara_bayar', 'cair di luar'))
                ->when($tab === 'belum_dibayar', fn($q) => $q->where('cara_bayar', 'belum dibayar'))
                ->when($hasFilter, function ($q) use ($filter) {
                    return $q->when($filter['dari_tanggal'] ?? null, fn($q, $date) => $q->whereDate('tanggal', '>=', $date))
                             ->when($filter['sampai_tanggal'] ?? null, fn($q, $date) => $q->whereDate('tanggal', '<=', $date));
                })
                ->count(),
        };
    }
}
