<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\{Pekerja, Perusahaan, TransaksiOperasional};
use App\Enums\KategoriOperasional;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PekerjaSeeder extends Seeder
{
    public function run(): void
    {
        $perusahaan = Perusahaan::where('name', 'CV SUCCESS MANDIRI')->first() 
                   ?? Perusahaan::where('name', 'Sukses Mandiri')->first()
                   ?? Perusahaan::first();

        if (!$perusahaan) return;

        // Bersihkan data lama agar bersih (opsional tapi disarankan untuk simulasi)
        Pekerja::where('perusahaan_id', $perusahaan->id)->delete();

        $pekerjas = [
            [
                'nama' => 'Ahmad Zaki',
                'pendapatan' => 3500000,
                'hutang' => 1200000, // Hutang berjalan
                'alamat' => 'Sijunjung, Sumatera Barat',
                'telepon' => '081234567890',
            ],
            [
                'nama' => 'Budi Hermawan',
                'pendapatan' => 3200000,
                'hutang' => 0, // Sudah lunas
                'alamat' => 'Muaro Sijunjung',
                'telepon' => '081234567891',
            ],
            [
                'nama' => 'Citra Lestari',
                'pendapatan' => 3000000,
                'hutang' => 450000,
                'alamat' => 'Solok, Sumatera Barat',
                'telepon' => '081234567892',
            ],
            [
                'nama' => 'Doni Saputra',
                'pendapatan' => 3800000,
                'hutang' => 2500000,
                'alamat' => 'Sijunjung Tengah',
                'telepon' => '081234567893',
            ],
        ];

        foreach ($pekerjas as $data) {
            $pekerja = Pekerja::create(array_merge($data, [
                'perusahaan_id' => $perusahaan->id,
            ]));

            // Simulasi Riwayat Hutang & Bayar (Agar User bisa melihat history di detail)
            $this->createSimulationHistory($pekerja, $perusahaan->id);
        }
    }

    private function createSimulationHistory(Pekerja $pekerja, int $perusahaanId): void
    {
        $now = Carbon::now();

        // 1. Catatan Kas Bon (Hutang) - 5 hari lalu
        TransaksiOperasional::create([
            'perusahaan_id' => $perusahaanId,
            'tanggal' => $now->copy()->subDays(5),
            'pihak_type' => Pekerja::class,
            'pihak_id' => $pekerja->id,
            'kategori' => KategoriOperasional::PINJAMAN,
            'nominal' => 500000,
            'keterangan' => 'Pinjaman Kas Bon Awal - ' . $pekerja->nama,
        ]);

        // 2. Pembayaran Hutang - 2 hari lalu
        if ($pekerja->hutang < 500000) {
            TransaksiOperasional::create([
                'perusahaan_id' => $perusahaanId,
                'tanggal' => $now->copy()->subDays(2),
                'pihak_type' => Pekerja::class,
                'pihak_id' => $pekerja->id,
                'kategori' => KategoriOperasional::BAYAR_HUTANG,
                'nominal' => 200000,
                'keterangan' => 'Cicilan Hutang Ke-1 - ' . $pekerja->nama,
            ]);
        }
    }
}
