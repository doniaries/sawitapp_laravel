<?php

namespace App\Filament\Resources\TutupHaris\Pages;

use App\Filament\Resources\TutupHariResource;
use App\Models\JurnalKeuangan;
use App\Models\TransaksiDo;
use Filament\Resources\Pages\CreateRecord;
use Filament\Facades\Filament;

class CreateTutupHari extends CreateRecord
{
    protected static string $resource = TutupHariResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tanggal = $data['tanggal'];
        $perusahaanId = Filament::getTenant()->id;

        // Hitung Tonase dan Rupiah dari Transaksi DO
        $totalTonase = TransaksiDo::whereDate('tanggal', $tanggal)->sum('tonase');
        $totalRupiah = TransaksiDo::whereDate('tanggal', $tanggal)->sum('sub_total');

        // Hitung Pemasukan dan Pengeluaran dari Jurnal Keuangan
        $totalMasuk = JurnalKeuangan::whereDate('tanggal', $tanggal)
            ->where('jenis_transaksi', 'Pemasukan')
            ->sum('nominal');
        $totalKeluar = JurnalKeuangan::whereDate('tanggal', $tanggal)
            ->where('jenis_transaksi', 'Pengeluaran')
            ->sum('nominal');

        $saldoSistem = $totalMasuk - $totalKeluar;

        $data['perusahaan_id'] = $perusahaanId;
        $data['total_do_tonase'] = $totalTonase;
        $data['total_do_rupiah'] = $totalRupiah;
        $data['total_pemasukan'] = $totalMasuk;
        $data['total_pengeluaran'] = $totalKeluar;
        $data['saldo_akhir_sistem'] = $saldoSistem;
        $data['selisih'] = ($data['saldo_akhir_fisik'] ?? 0) - $saldoSistem;
        $data['user_id'] = auth()->id();
        $data['status'] = 'closed';

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
