<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TransaksiOperasional;
use App\Models\JurnalKeuangan;
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

        $perusahaan1 = \App\Models\Perusahaan::where('name', 'CV SUCCESS MANDIRI')->first();
        $perusahaan2 = \App\Models\Perusahaan::where('name', 'PT Andala Integrasi Global')->first();
        
        $userCV = \App\Models\User::where('perusahaan_id', $perusahaan1?->id)->first();
        $userPT = \App\Models\User::where('perusahaan_id', $perusahaan2?->id)->first();
        $supirCV = \App\Models\Supir::where('perusahaan_id', $perusahaan1?->id)->first();
        $supirPT = \App\Models\Supir::where('perusahaan_id', $perusahaan2?->id)->first();

        foreach ($operasionals as $index => $data) {
            $isCV = ($index % 2 === 0);
            $targetPerusahaan = $isCV ? $perusahaan1 : $perusahaan2;
            
            if ($targetPerusahaan) {
                $data['perusahaan_id'] = $targetPerusahaan->id;
                
                // Map user and supir
                if (isset($data['user_id'])) { // Use isset to check if key exists
                    $data['user_id'] = $isCV ? ($userCV?->id ?? 1) : ($userPT?->id ?? 2);
                }
                if (isset($data['supir_id'])) { // Use isset to check if key exists
                    $data['supir_id'] = $isCV ? ($supirCV?->id ?? 1) : ($supirPT?->id ?? 2);
                }
                
                // Create TransaksiOperasional entry
                $operasional = TransaksiOperasional::create($data);

                // Create corresponding JurnalKeuangan entry
                JurnalKeuangan::create([
                    'tanggal' => $data['tanggal'],
                    'jenis_transaksi' => ucfirst($data['operasional']),
                    'kategori' => 'Operasional',
                    'sub_kategori' => $data['kategori'],
                    'nominal' => $data['nominal'],
                    'sumber_transaksi' => 'Operasional',
                    'referensi_id' => $operasional->id,
                    'pihak_terkait' => $data['tipe_nama'] === 'user' ? 'User: ' . ($isCV ? 'CV' : 'PT') : 'Supir: ' . ($isCV ? 'CV' : 'PT'),
                    'tipe_pihak' => $data['tipe_nama'],
                    'cara_pembayaran' => 'tunai',
                    'keterangan' => $data['keterangan'] . ' (' . ($isCV ? 'CV' : 'PT') . ')',
                    'mempengaruhi_kas' => true,
                    'perusahaan_id' => $targetPerusahaan->id,
                ]);
            }
        }
    }
}
