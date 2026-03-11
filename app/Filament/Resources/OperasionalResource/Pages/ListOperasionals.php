<?php

namespace App\Filament\Resources\OperasionalResource\Pages;

use App\Filament\Resources\OperasionalResource;
use App\Models\Operasional;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Perusahaan;
use App\Enums\KategoriOperasional;
use App\Filament\Resources\OperasionalResource\Widgets\OperasionalStatsWidget;

class ListOperasionals extends ListRecords
{
    protected static string $resource = OperasionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->label('Tambah Operasional'),
            // Actions\Action::make('record_balance')
            //     ->label('Catat Saldo')
            //     ->icon('heroicon-o-currency-dollar')
            //     ->color('success')
            //     ->requiresConfirmation()
            //     ->modalHeading('Catat Saldo Perusahaan')
            //     ->modalDescription('Saldo perusahaan saat ini akan dicatat sebagai catatan operasional.')
            //     ->modalSubmitActionLabel('Ya, Catat Saldo')
            //     ->action(function () {
            //         try {
            //             $perusahaan = Perusahaan::first();

            //             if (!$perusahaan) {
            //                 Notification::make()
            //                     ->title('Error')
            //                     ->body('Tidak ada data perusahaan ditemukan')
            //                     ->danger()
            //                     ->send();
            //                 return;
            //             }

            //             // Buat entri operasional untuk saldo
            //             Operasional::create([
            //                 'tanggal' => now(),
            //                 'nominal' => $perusahaan->saldo,
            //                 'kategori' => KategoriOperasional::TAMBAH_SALDO,
            //                 'operasional' => 'pemasukan',
            //                 'keterangan' => 'Pencatatan saldo perusahaan per tanggal ' . now()->format('d/m/Y H:i'),
            //                 'is_from_transaksi' => false,
            //             ]);

            //             Notification::make()
            //                 ->title('Berhasil')
            //                 ->body('Saldo perusahaan sebesar Rp ' . number_format($perusahaan->saldo, 0, ',', '.') . ' berhasil dicatat')
            //                 ->success()
            //                 ->send();
            //         } catch (\Exception $e) {
            //             Notification::make()
            //                 ->title('Error')
            //                 ->body('Terjadi kesalahan: ' . $e->getMessage())
            //                 ->danger()
            //                 ->send();
            //         }
            //     }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->icon('heroicon-o-list-bullet')
                ->badge(fn() => Operasional::count())
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereDate('tanggal', '>=', now()->subDays(30))
                ),

            'pemasukan' => Tab::make('Pemasukan')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('operasional', 'pemasukan')
                )
                ->icon('heroicon-o-arrow-trending-up')
                ->badge(fn() => Operasional::where('operasional', 'pemasukan')->count())
                ->badgeColor('success'),

            'pengeluaran' => Tab::make('Pengeluaran')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->where('operasional', 'pengeluaran')
                )
                ->icon('heroicon-o-arrow-trending-down')
                ->badge(fn() => Operasional::where('operasional', 'pengeluaran')->count())
                ->badgeColor('danger'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            OperasionalStatsWidget::class,
        ];
    }
}
