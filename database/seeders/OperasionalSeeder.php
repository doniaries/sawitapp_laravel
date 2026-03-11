<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Operasional;
use App\Models\LaporanKeuangan;
use Carbon\Carbon;

class OperasionalSeeder extends Seeder
{
    public function run(): void
    {
        $operasionals = [
            [
                'tanggal' => '2024-12-03 18:01:54',
                'operasional' => 'pemasukan',
                'kategori' => 'tambah_saldo',
                'tipe_nama' => 'user',
                'user_id' => 3,
                'nominal' => 3592000,
                'keterangan' => 'Sisa Saldo kemaren',
            ],
            [
                'tanggal' => '2024-12-03 18:01:52',
                'operasional' => 'pemasukan',
                'kategori' => 'tambah_saldo',
                'tipe_nama' => 'user',
                'user_id' => 3,
                'nominal' => 250000000,
                'keterangan' => 'Jemput Ke Kamang',
            ],
            [
                'tanggal' => '2024-12-03 18:03:05',
                'operasional' => 'pemasukan',
                'kategori' => 'tambah_saldo',
                'tipe_nama' => 'user',
                'user_id' => 3,
                'nominal' => 12025000,
                'keterangan' => 'siska Minta transfer',
            ],
            [
                'tanggal' => '2024-12-03 18:04:05',
                'operasional' => 'pemasukan',
                'kategori' => 'tambah_saldo',
                'tipe_nama' => 'user',
                'user_id' => 3,
                'nominal' => 30000,
                'keterangan' => 'Sisa DITEG',
            ],
            [
                'tanggal' => '2024-12-03 18:20:34',
                'operasional' => 'pengeluaran',
                'kategori' => 'pijakan_gas',
                'tipe_nama' => 'supir',
                'supir_id' => 1,
                'nominal' => 76000,
                'keterangan' => 'Biaya pijakan gas Eko',
            ],
            [
                'tanggal' => '2024-12-03 18:30:19',
                'operasional' => 'pengeluaran',
                'kategori' => 'lain_lain',
                'tipe_nama' => 'user',
                'user_id' => 4,
                'nominal' => 50000,
                'keterangan' => 'Belanja operasional',
            ],
            [
                'tanggal' => '2024-12-03 18:31:05',
                'operasional' => 'pengeluaran',
                'kategori' => 'pijakan_gas',
                'tipe_nama' => 'supir',
                'user_id' => 4,
                'nominal' => 78000,
                'keterangan' => 'Biaya pijakan gas Naro',
            ],
        ];

        foreach ($operasionals as $data) {
            // Create Operasional entry
            $operasional = Operasional::create($data);

            // Create corresponding LaporanKeuangan entry
            LaporanKeuangan::create([
                'tanggal' => $data['tanggal'],
                'jenis_transaksi' => ucfirst($data['operasional']), // Convert to 'Pemasukan' or 'Pengeluaran'
                'kategori' => 'Operasional',
                'sub_kategori' => $data['kategori'],
                'nominal' => $data['nominal'],
                'sumber_transaksi' => 'Operasional',
                'referensi_id' => $operasional->id,
                'pihak_terkait' => $data['tipe_nama'] === 'user' ? 'User ID: ' . $data['user_id'] : 'Supir ID: ' . ($data['supir_id'] ?? ''),
                'tipe_pihak' => $data['tipe_nama'],
                'cara_pembayaran' => 'tunai',
                'keterangan' => $data['keterangan'],
                'mempengaruhi_kas' => true,
            ]);
        }
    }
}
