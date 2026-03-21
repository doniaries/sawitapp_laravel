# Proposal: Sistem Mutasi Hutang Terpadu (Ledger System)

Tujuan: Menyederhanakan logika proses hutang Penjual & Supir di Laravel agar tidak tersebar di banyak tempat (Controller, Trait, Model) dan memiliki rekam jejak yang transparan (auditable).

## Masalah Saat Ini
- **Logika Terfragmentasi**: Pengukuran hutang ada di `JurnalKeuanganTrait`, penambahan ada di `TransaksiOperasional`, dan pembayaran langsung di `PembayaranHutang`.
- **Manual Overhead**: Controller harus menghitung `sisa_hutang` secara manual sebelum menyimpan data.
- **Kurang Transparan**: Tidak ada satu tabel "buku besar" (ledger) yang mencatat alur hutang secara kronologis untuk semua pihak (Penjual & Supir) dalam satu format yang sama.

## Usulan Solusi: Sistem Mutasi Hutang

### 1. Tabel Baru: `mutasi_hutang`
Mencatat setiap pergerakan hutang secara detail.
- `perusahaan_id`: Tenant/Perusahaan.
- `pihak_id`, `pihak_type`: Polymorphic (Penjual atau Supir).
- `tipe`: `HUTANG_MASUK` (Hutang Bertambah) atau `HUTANG_KELUAR` (Hutang Berkurang/Bayar).
- `nominal`: Jumlah mutasi.
- `saldo_akhir`: Saldo hutang setelah mutasi ini (untuk audit).
- `referensi_id`, `referensi_type`: Link ke Transaksi DO, Operasional, atau Pembayaran Hutang.

### 2. Centralized `DebtService`
Logika hutang dipusatkan dalam satu Service Class:
- `increaseDebt(Model $pihak, $nominal, $ref, $desc)`
- `recordPayment(Model $pihak, $nominal, $ref, $desc)`

### 3. Model Observers (Otomatisasi)
Menggunakan **Laravel Observers** agar logika tidak perlu ditulis berulang di Controller:
- **TransaksiDoObserver**: Saat transaksi DO disimpan dengan `pembayaran_hutang > 0`, otomatis panggil `DebtService::recordPayment`.
- **OperasionalObserver**: Saat ada pengeluaran kategori "Pinjaman", otomatis panggil `DebtService::increaseDebt`.

## Keuntungan
- **DRY (Don't Repeat Yourself)**: Logika pengurangan hutang tidak perlu ditulis lagi di Controller Transaksi DO.
- **Kemudahan Laporan**: Untuk menampilkan riwayat hutang (seperti timeline di Flutter), cukup tarik dari tabel `mutasi_hutang`.
- **Integritas Data**: Saldo hutang di tabel `penjual` atau `supir` hanya berfungsi sebagai "cache", sumber kebenaran datanya ada di tabel mutasi.

## Rencana Implementasi
1. Buat migrasi tabel `mutasi_hutang`.
2. Buat `App\Services\DebtService`.
3. Pasang Observer pada `TransaksiDo` dan `TransaksiOperasional`.
4. Refactor `TransaksiDoController` untuk menghapus logika manual perhitungan hutang.

> [!IMPORTANT]
> Dengan sistem ini, Anda tidak perlu lagi pusing menghitung sisa hutang di Controller. Cukup simpan transaksinya, dan sistem akan mengurus mutasi hutangnya secara otomatis.
