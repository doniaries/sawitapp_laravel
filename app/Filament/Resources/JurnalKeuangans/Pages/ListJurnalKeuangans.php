<?php

namespace App\Filament\Resources\JurnalKeuangans\Pages;

use App\Filament\Resources\JurnalKeuangans\JurnalKeuanganResource;
use App\Filament\Resources\JurnalKeuangans\Widgets\JurnalKeuanganDoStatsWidget;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Models\JurnalKeuangan;
use App\Models\TutupHari;
use App\Models\TransaksiDo;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Carbon\Carbon;

class ListJurnalKeuangans extends ListRecords
{
    protected static string $resource = JurnalKeuanganResource::class;

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

                    $totalTonase = TransaksiDo::whereDate('tanggal', '=', $tanggal)->sum('tonase');
                    $totalRupiah = TransaksiDo::whereDate('tanggal', '=', $tanggal)->sum('sub_total');
                    $totalMasuk = JurnalKeuangan::whereDate('tanggal', '=', $tanggal)->where('jenis_transaksi', '=', 'Pemasukan')->sum('nominal');
                    $totalKeluar = JurnalKeuangan::whereDate('tanggal', '=', $tanggal)->where('jenis_transaksi', '=', 'Pengeluaran')->sum('nominal');
                    
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
                        ->body("Hari ini (" . Carbon::parse($tanggal)->format('d/m/Y') . ") telah ditutup.")
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            JurnalKeuanganDoStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'hari_ini' => Tab::make('Hari Ini')
                ->icon('heroicon-o-calendar')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereDate('tanggal', '=', today(), 'and'))
                ->badge($this->getTabCount('hari_ini')),

            'kemarin' => Tab::make('Kemarin')
                ->icon('heroicon-o-calendar')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereDate('tanggal', '=', today()->subDay(), 'and'))
                ->badge($this->getTabCount('kemarin')),

            'pemasukan' => Tab::make('Pemasukan')
                ->icon('heroicon-o-arrow-down-circle')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('jenis_transaksi', '=', 'Pemasukan', 'and'))
                ->badge($this->getTabCount('pemasukan')),

            'pengeluaran' => Tab::make('Pengeluaran')
                ->icon('heroicon-o-arrow-up-circle')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('jenis_transaksi', '=', 'Pengeluaran', 'and'))
                ->badge($this->getTabCount('pengeluaran')),

            'bulan_ini' => Tab::make('Bulan Ini')
                ->icon('heroicon-o-calendar-days')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereMonth('tanggal', '=', now()->month, 'and')->whereYear('tanggal', '=', now()->year, 'and'))
                ->badge($this->getTabCount('bulan_ini')),
        ];
    }

    public function updatedActiveTab(): void
    {
        $this->dispatch('tab-changed', tab: $this->activeTab)->to(JurnalKeuanganDoStatsWidget::class);
    }

    protected function getTabCount(string $tab): int
    {
        $query = JurnalKeuangan::query();

        return match ($tab) {
            'hari_ini' => $query->whereDate('tanggal', '=', today(), 'and')->count('*'),
            'kemarin' => $query->whereDate('tanggal', '=', today()->subDay(), 'and')->count('*'),
            'pemasukan' => $query->where('jenis_transaksi', '=', 'Pemasukan', 'and')->count('*'),
            'pengeluaran' => $query->where('jenis_transaksi', '=', 'Pengeluaran', 'and')->count('*'),
            'bulan_ini' => $query->whereMonth('tanggal', '=', now()->month, 'and')->whereYear('tanggal', '=', now()->year, 'and')->count('*'),
            default => $query->count('*'),
        };
    }
}
