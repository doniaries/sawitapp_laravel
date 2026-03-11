-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 13 Des 2024 pada 17.04
-- Versi server: 8.0.30
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `success-mandiri6`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kendaraan`
--

CREATE TABLE `kendaraan` (
  `id` bigint UNSIGNED NOT NULL,
  `no_polisi` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `supir_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan_keuangan`
--

CREATE TABLE `laporan_keuangan` (
  `id` bigint UNSIGNED NOT NULL,
  `tanggal` datetime NOT NULL,
  `jenis_transaksi` enum('Pemasukan','Pengeluaran') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Kategori transaksi (DO/Operasional)',
  `sub_kategori` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Sub kategori seperti upah_bongkar, biaya_lain, dll',
  `nominal` decimal(15,0) NOT NULL,
  `sumber_transaksi` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'DO/Operasional',
  `referensi_id` bigint UNSIGNED NOT NULL COMMENT 'ID dari tabel sumber (transaksi_do/operasional)',
  `nomor_referensi` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nomor DO jika dari transaksi DO',
  `pihak_terkait` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nama penjual/user terkait',
  `tipe_pihak` enum('penjual','pekerja','user','supir') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cara_pembayaran` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tunai/Transfer/cair di luar',
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `mempengaruhi_kas` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Apakah transaksi ini mempengaruhi kas atau tidak',
  `saldo_sebelum` decimal(15,0) DEFAULT NULL COMMENT 'Saldo sebelum transaksi',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `laporan_keuangan`
--

INSERT INTO `laporan_keuangan` (`id`, `tanggal`, `jenis_transaksi`, `kategori`, `sub_kategori`, `nominal`, `sumber_transaksi`, `referensi_id`, `nomor_referensi`, `pihak_terkait`, `tipe_pihak`, `cara_pembayaran`, `keterangan`, `mempengaruhi_kas`, `saldo_sebelum`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '2024-12-10 00:34:04', 'Pemasukan', 'Operasional', 'Tambah Saldo', 3592000, 'Operasional', 1, 'OP-00001', 'Wendy', 'user', 'Tunai', 'SISA SALDO KEMAREN	', 1, NULL, '2024-12-09 17:34:47', '2024-12-09 17:34:47', NULL),
(2, '2024-12-10 00:35:15', 'Pemasukan', 'Operasional', 'Tambah Saldo', 250000000, 'Operasional', 2, 'OP-00002', 'Wendy', 'user', 'Tunai', 'JEMPUT KE KAMANG	', 1, NULL, '2024-12-09 17:35:52', '2024-12-09 17:35:52', NULL),
(3, '2024-12-10 00:36:40', 'Pemasukan', 'Operasional', 'Tambah Saldo', 12025000, 'Operasional', 3, 'OP-00003', 'Wendy', 'user', 'Tunai', 'SISKA MINTA TRANFER', 1, NULL, '2024-12-09 17:37:11', '2024-12-09 17:37:11', NULL),
(4, '2024-12-10 00:37:11', 'Pemasukan', 'Operasional', 'Tambah Saldo', 30000, 'Operasional', 4, 'OP-00004', 'Kasir 1', 'user', 'Tunai', 'SISA DITEG', 1, NULL, '2024-12-09 17:37:41', '2024-12-09 17:37:41', NULL),
(5, '2024-12-10 00:43:29', 'Pengeluaran', 'DO', 'Pembayaran DO', 13809510, 'DO', 4, 'DO-20241210-0002', 'FURQON', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241210-0002', 1, NULL, '2024-12-09 17:43:56', '2024-12-09 17:43:56', NULL),
(6, '2024-12-10 00:44:41', 'Pemasukan', 'DO', 'Biaya Lain', 200000, 'DO', 5, 'DO-20241210-0003', 'EPI', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241210-0003', 1, NULL, '2024-12-09 17:45:33', '2024-12-09 17:52:05', '2024-12-09 17:52:05'),
(7, '2024-12-10 00:44:41', 'Pemasukan', 'DO', 'Biaya Lain', 200000, 'DO', 5, 'DO-20241210-0003', 'EPI', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241210-0003', 1, NULL, '2024-12-09 17:52:05', '2024-12-09 17:52:05', NULL),
(8, '2024-12-10 00:44:41', 'Pengeluaran', 'DO', 'Pembayaran DO', 8613970, 'DO', 5, 'DO-20241210-0003', 'EPI', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241210-0003', 1, NULL, '2024-12-09 17:52:05', '2024-12-09 17:52:05', NULL),
(9, '2024-12-10 01:10:25', 'Pengeluaran', 'DO', 'Pembayaran DO', 1774440, 'DO', 6, 'DO-20241210-0004', 'ANDES', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241210-0004', 1, NULL, '2024-12-09 18:10:52', '2024-12-09 18:10:52', NULL),
(10, '2024-12-10 01:10:52', 'Pemasukan', 'DO', 'Biaya Lain', 116000, 'DO', 7, 'DO-20241210-0005', 'LOPON', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241210-0005', 1, NULL, '2024-12-09 18:12:00', '2024-12-09 18:12:00', NULL),
(11, '2024-12-10 01:10:52', 'Pengeluaran', 'DO', 'Pembayaran DO', 29152720, 'DO', 7, 'DO-20241210-0005', 'LOPON', 'penjual', 'cair di luar', 'Pembayaran DO via cair di luar', 1, NULL, '2024-12-09 18:12:00', '2024-12-09 18:12:00', NULL),
(12, '2024-12-10 01:23:15', 'Pemasukan', 'DO', 'Biaya Lain', 50000, 'DO', 8, 'DO-20241210-0006', 'HERMAN', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241210-0006', 1, NULL, '2024-12-09 18:24:43', '2024-12-09 18:24:43', NULL),
(13, '2024-12-10 01:23:15', 'Pengeluaran', 'DO', 'Pembayaran DO', 2939200, 'DO', 8, 'DO-20241210-0006', 'HERMAN', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241210-0006', 1, NULL, '2024-12-09 18:24:43', '2024-12-09 18:24:43', NULL),
(14, '2024-12-10 01:25:02', 'Pemasukan', 'DO', 'Biaya Lain', 100000, 'DO', 9, 'DO-20241210-0007', 'SIIT', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241210-0007', 1, NULL, '2024-12-09 18:29:47', '2024-12-09 18:29:47', NULL),
(15, '2024-12-10 01:25:02', 'Pengeluaran', 'DO', 'Pembayaran DO', 6714740, 'DO', 9, 'DO-20241210-0007', 'SIIT', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241210-0007', 1, NULL, '2024-12-09 18:29:47', '2024-12-09 18:29:47', NULL),
(16, '2024-12-10 01:29:55', 'Pemasukan', 'DO', 'Biaya Lain', 400000, 'DO', 10, 'DO-20241210-0008', 'ETI SUSANA', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241210-0008', 1, NULL, '2024-12-09 18:32:36', '2024-12-09 18:32:36', NULL),
(17, '2024-12-10 01:29:55', 'Pemasukan', 'DO', 'Bayar Hutang', 1000000, 'DO', 10, 'DO-20241210-0008', 'ETI SUSANA', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241210-0008', 1, NULL, '2024-12-09 18:32:36', '2024-12-09 18:32:36', NULL),
(18, '2024-12-10 01:29:55', 'Pengeluaran', 'DO', 'Pembayaran DO', 23709280, 'DO', 10, 'DO-20241210-0008', 'ETI SUSANA', 'penjual', 'Transfer', 'Pembayaran DO via Transfer', 1, NULL, '2024-12-09 18:32:36', '2024-12-09 18:32:36', NULL),
(19, '2024-12-10 01:34:02', 'Pengeluaran', 'DO', 'Pembayaran DO', 3666540, 'DO', 11, 'DO-20241210-0009', 'SISKA', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241210-0009', 1, NULL, '2024-12-09 18:34:57', '2024-12-09 18:34:57', NULL),
(20, '2024-12-10 01:39:52', 'Pemasukan', 'DO', 'Biaya Lain', 20000, 'DO', 12, 'DO-20241210-0010', 'JEKI', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241210-0010', 1, NULL, '2024-12-09 18:42:24', '2024-12-09 18:42:24', NULL),
(21, '2024-12-10 01:39:52', 'Pengeluaran', 'DO', 'Pembayaran DO', 4692760, 'DO', 12, 'DO-20241210-0010', 'JEKI', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241210-0010', 1, NULL, '2024-12-09 18:42:24', '2024-12-09 18:42:24', NULL),
(22, '2024-12-10 01:42:24', 'Pengeluaran', 'DO', 'Pembayaran DO', 4401120, 'DO', 13, 'DO-20241210-0011', 'AGUS', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241210-0011', 1, NULL, '2024-12-09 18:43:41', '2024-12-12 05:04:08', '2024-12-12 05:04:08'),
(23, '2024-12-12 00:28:25', 'Pengeluaran', 'DO', 'Pembayaran DO', 3234060, 'DO', 14, 'DO-20241212-0001', 'JOKO', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241212-0001', 1, NULL, '2024-12-11 17:29:16', '2024-12-11 17:29:16', NULL),
(24, '2024-12-12 00:29:16', 'Pemasukan', 'DO', 'Biaya Lain', 124000, 'DO', 15, 'DO-20241212-0002', 'LOPON', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241212-0002', 1, NULL, '2024-12-11 17:30:36', '2024-12-11 17:30:36', NULL),
(25, '2024-12-12 00:29:16', 'Pengeluaran', 'DO', 'Pembayaran DO', 31068620, 'DO', 15, 'DO-20241212-0002', 'LOPON', 'penjual', 'cair di luar', 'Pembayaran DO via cair di luar', 1, NULL, '2024-12-11 17:30:36', '2024-12-11 17:30:36', NULL),
(26, '2024-12-12 00:30:36', 'Pemasukan', 'DO', 'Biaya Lain', 300000, 'DO', 16, 'DO-20241212-0003', 'SUKARMIN', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241212-0003', 1, NULL, '2024-12-11 17:31:52', '2024-12-11 17:31:52', NULL),
(27, '2024-12-12 00:30:36', 'Pemasukan', 'DO', 'Bayar Hutang', 1000000, 'DO', 16, 'DO-20241212-0003', 'SUKARMIN', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241212-0003', 1, NULL, '2024-12-11 17:31:52', '2024-12-11 17:31:52', NULL),
(28, '2024-12-12 00:30:36', 'Pengeluaran', 'DO', 'Pembayaran DO', 24609180, 'DO', 16, 'DO-20241212-0003', 'SUKARMIN', 'penjual', 'Transfer', 'Pembayaran DO via Transfer', 1, NULL, '2024-12-11 17:31:52', '2024-12-11 17:31:52', NULL),
(29, '2024-12-12 00:31:52', 'Pemasukan', 'DO', 'Biaya Lain', 350000, 'DO', 17, 'DO-20241212-0004', 'DITEG', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241212-0004', 1, NULL, '2024-12-11 17:33:07', '2024-12-11 17:33:07', NULL),
(30, '2024-12-12 00:31:52', 'Pengeluaran', 'DO', 'Pembayaran DO', 31383220, 'DO', 17, 'DO-20241212-0004', 'DITEG', 'penjual', 'Transfer', 'Pembayaran DO via Transfer', 1, NULL, '2024-12-11 17:33:07', '2024-12-11 17:33:07', NULL),
(31, '2024-12-12 00:33:07', 'Pemasukan', 'DO', 'Biaya Lain', 650000, 'DO', 18, 'DO-20241212-0005', 'KELOMPOK 1', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241212-0005', 1, NULL, '2024-12-11 17:34:08', '2024-12-11 17:34:08', NULL),
(32, '2024-12-12 00:33:07', 'Pengeluaran', 'DO', 'Pembayaran DO', 26870130, 'DO', 18, 'DO-20241212-0005', 'KELOMPOK 1', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241212-0005', 1, NULL, '2024-12-11 17:34:08', '2024-12-11 17:34:08', NULL),
(33, '2024-12-12 00:34:08', 'Pemasukan', 'DO', 'Biaya Lain', 650000, 'DO', 19, 'DO-20241212-0006', 'KELOMPOK 2', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241212-0006', 1, NULL, '2024-12-11 17:35:21', '2024-12-11 17:35:21', NULL),
(34, '2024-12-12 00:34:08', 'Pengeluaran', 'DO', 'Pembayaran DO', 25574990, 'DO', 19, 'DO-20241212-0006', 'KELOMPOK 2', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241212-0006', 1, NULL, '2024-12-11 17:35:21', '2024-12-11 17:35:21', NULL),
(35, '2024-12-12 00:35:21', 'Pengeluaran', 'DO', 'Pembayaran DO', 19765240, 'DO', 20, 'DO-20241212-0007', 'HERMAN', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241212-0007', 1, NULL, '2024-12-11 17:36:06', '2024-12-11 17:36:06', NULL),
(36, '2024-12-12 00:36:06', 'Pengeluaran', 'DO', 'Pembayaran DO', 5469600, 'DO', 21, 'DO-20241212-0008', 'UCOK', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241212-0008', 1, NULL, '2024-12-11 17:36:48', '2024-12-12 06:43:04', '2024-12-12 06:43:04'),
(37, '2024-12-12 00:36:48', 'Pemasukan', 'DO', 'Biaya Lain', 80000, 'DO', 22, 'DO-20241212-0009', 'ARI WAHYU', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241212-0009', 1, NULL, '2024-12-11 17:37:47', '2024-12-11 17:37:47', NULL),
(38, '2024-12-12 00:36:48', 'Pengeluaran', 'DO', 'Pembayaran DO', 24205660, 'DO', 22, 'DO-20241212-0009', 'ARI WAHYU', 'penjual', 'Transfer', 'Pembayaran DO via Transfer', 1, NULL, '2024-12-11 17:37:47', '2024-12-11 17:37:47', NULL),
(39, '2024-12-12 00:39:31', 'Pengeluaran', 'Operasional', 'Pijak Gas', 76000, 'Operasional', 5, 'OP-00005', NULL, 'supir', 'Tunai', '-', 1, NULL, '2024-12-11 17:40:00', '2024-12-11 17:40:00', NULL),
(40, '2024-12-12 00:40:00', 'Pengeluaran', 'Operasional', 'Lain-lain', 50000, 'Operasional', 6, 'OP-00006', 'Kasir 1', 'user', 'Tunai', 'Belanja', 1, NULL, '2024-12-11 17:40:36', '2024-12-11 17:40:36', NULL),
(41, '2024-12-12 00:40:36', 'Pengeluaran', 'Operasional', 'Pijak Gas', 78000, 'Operasional', 7, 'OP-00007', NULL, 'supir', 'Tunai', '-', 1, NULL, '2024-12-11 17:41:10', '2024-12-11 17:41:10', NULL),
(42, '2024-12-10 01:42:24', 'Pemasukan', 'DO', 'Biaya Lain', 15000, 'DO', 13, 'DO-20241210-0011', 'AGUS', 'penjual', 'Tunai', 'Pemasukan tunai DO #DO-20241210-0011', 1, NULL, '2024-12-12 05:04:08', '2024-12-12 05:04:08', NULL),
(43, '2024-12-10 01:42:24', 'Pengeluaran', 'DO', 'Pembayaran DO', 4386120, 'DO', 13, 'DO-20241210-0011', 'AGUS', 'penjual', 'Tunai', 'Pembayaran DO #DO-20241210-0011', 1, NULL, '2024-12-12 05:04:08', '2024-12-12 05:04:08', NULL),
(44, '2024-12-12 00:36:06', 'Pengeluaran', 'DO', 'Pembayaran DO', 5469600, 'DO', 21, 'DO-20241212-0008', 'UCOK', 'penjual', 'belum dibayar', 'Pembayaran DO via belum dibayar', 1, NULL, '2024-12-12 06:43:04', '2024-12-12 07:12:25', '2024-12-12 07:12:25'),
(45, '2024-12-12 00:36:06', 'Pengeluaran', 'DO', 'Pembayaran DO', 5469600, 'DO', 21, 'DO-20241212-0008', 'UCOK', 'penjual', 'tunai', 'Pembayaran DO #DO-20241212-0008', 1, NULL, '2024-12-12 07:12:25', '2024-12-12 07:12:25', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2024_01_01_000000_create_cache_table', 1),
(2, '2024_01_01_000000_create_jobs_table', 1),
(3, '2024_01_01_000001_create_perusahaans_table', 1),
(4, '2024_01_01_000002_create_users_table', 1),
(5, '2024_01_01_000003_create_penjuals_table', 1),
(6, '2024_01_01_000004_create_supir_table', 1),
(7, '2024_01_01_000005_create_kendaraan_table', 1),
(8, '2024_01_01_000005_create_transaksi_do_table', 1),
(9, '2024_01_01_000007_create_operasional_table', 1),
(10, '2024_01_01_000008_create_laporan_keuangan_table', 1),
(11, '2024_01_01_000009_create_pekerjas_table', 1),
(12, '2024_03_19_000001_add_mempengaruhi_kas_to_laporan_keuangan', 1),
(13, '2024_11_18_182348_create_riwayat_pembayaran_hutangs_table', 1),
(14, '2024_12_05_230524_add_sisa_saldo', 1),
(15, '2024_01_10_000001_add_indexes_for_performance', 2),
(16, '2024_01_10_000003_add_quick_indexes', 3),
(19, '2024_12_12_143441_create_permission_tables', 4);

-- --------------------------------------------------------

--
-- Struktur dari tabel `operasional`
--

CREATE TABLE `operasional` (
  `id` bigint UNSIGNED NOT NULL,
  `tanggal` datetime NOT NULL,
  `operasional` enum('pemasukan','pengeluaran') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipe_nama` enum('penjual','user','supir','pekerja') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `penjual_id` bigint UNSIGNED DEFAULT NULL,
  `pekerja_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `supir_id` bigint UNSIGNED DEFAULT NULL,
  `nominal` decimal(15,0) NOT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `file_bukti` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `operasional`
--

INSERT INTO `operasional` (`id`, `tanggal`, `operasional`, `kategori`, `tipe_nama`, `penjual_id`, `pekerja_id`, `user_id`, `supir_id`, `nominal`, `keterangan`, `file_bukti`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '2024-12-10 00:34:04', 'pemasukan', 'tambah_saldo', 'user', NULL, NULL, 3, NULL, 3592000, 'SISA SALDO KEMAREN	', NULL, '2024-12-09 17:34:47', '2024-12-09 17:34:47', NULL),
(2, '2024-12-10 00:35:15', 'pemasukan', 'tambah_saldo', 'user', NULL, NULL, 3, NULL, 250000000, 'JEMPUT KE KAMANG	', NULL, '2024-12-09 17:35:52', '2024-12-09 17:35:52', NULL),
(3, '2024-12-10 00:36:40', 'pemasukan', 'tambah_saldo', 'user', NULL, NULL, 3, NULL, 12025000, 'SISKA MINTA TRANFER', NULL, '2024-12-09 17:37:11', '2024-12-09 17:37:11', NULL),
(4, '2024-12-10 00:37:11', 'pemasukan', 'tambah_saldo', 'user', NULL, NULL, 4, NULL, 30000, 'SISA DITEG', NULL, '2024-12-09 17:37:41', '2024-12-09 17:37:41', NULL),
(5, '2024-12-12 00:39:31', 'pengeluaran', 'pijakan_gas', 'supir', NULL, NULL, NULL, 19, 76000, NULL, NULL, '2024-12-11 17:40:00', '2024-12-11 17:40:00', NULL),
(6, '2024-12-12 00:40:00', 'pengeluaran', 'lain_lain', 'user', NULL, NULL, 4, NULL, 50000, 'Belanja', NULL, '2024-12-11 17:40:36', '2024-12-11 17:40:36', NULL),
(7, '2024-12-12 00:40:36', 'pengeluaran', 'pijakan_gas', 'supir', NULL, NULL, NULL, 8, 78000, NULL, NULL, '2024-12-11 17:41:10', '2024-12-11 17:41:10', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pekerja`
--

CREATE TABLE `pekerja` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telepon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pendapatan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `hutang` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `riwayat_bayar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `penjuals`
--

CREATE TABLE `penjuals` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telepon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hutang` decimal(15,0) DEFAULT NULL,
  `riwayat_bayar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `penjuals`
--

INSERT INTO `penjuals` (`id`, `nama`, `alamat`, `telepon`, `hutang`, `riwayat_bayar`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'FURQON', NULL, NULL, 0, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(2, 'EPI', NULL, NULL, 0, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(3, 'ANDES', NULL, NULL, 0, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(4, 'LOPON', NULL, NULL, 0, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(5, 'HERMAN', NULL, NULL, 0, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(6, 'SIIT', NULL, NULL, 0, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(7, 'ETI SUSANA', NULL, NULL, 500000, NULL, '2024-12-09 17:25:56', '2024-12-09 18:32:36', NULL),
(8, 'SEBON', NULL, NULL, 0, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(9, 'SISKA', NULL, NULL, 0, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(10, 'JEKI', NULL, NULL, 0, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(11, 'AGUS', NULL, NULL, 0, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(12, 'JOKO', NULL, NULL, 0, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(13, 'SUKARMIN', NULL, NULL, 500000, NULL, '2024-12-09 17:25:56', '2024-12-11 17:31:52', NULL),
(14, 'DITEG', NULL, NULL, 0, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(15, 'KELOMPOK 1', NULL, NULL, 0, NULL, '2024-12-09 17:25:56', '2024-12-09 17:53:36', NULL),
(16, 'UCOK', NULL, NULL, 0, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(17, 'ARI WAHYU', NULL, NULL, 0, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(18, 'KELOMPOK 2', NULL, NULL, 0, NULL, '2024-12-09 17:53:59', '2024-12-09 17:53:59', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `perusahaans`
--

CREATE TABLE `perusahaans` (
  `id` bigint UNSIGNED NOT NULL,
  `sisa_saldo_kemarin` decimal(15,2) NOT NULL DEFAULT '0.00',
  `tanggal_sisa_saldo` date DEFAULT NULL,
  `sudah_diproses` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `saldo` decimal(15,0) NOT NULL DEFAULT '0',
  `alamat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telepon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pimpinan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Pimpinan Perusahaan',
  `npwp` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Logo Perusahaan',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Status aktif perusahaan',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `perusahaans`
--

INSERT INTO `perusahaans` (`id`, `sisa_saldo_kemarin`, `tanggal_sisa_saldo`, `sudah_diproses`, `name`, `saldo`, `alamat`, `telepon`, `email`, `pimpinan`, `npwp`, `logo`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 0.00, '2024-12-10', 0, 'CV SUCCESS MANDIRI', 134876700, 'Dusun Sungai Moran Nagari Kamang', '+62 823-8921-9670', NULL, 'Yondra', '12.345.678.9-123.000', 'successw.png', 1, '2024-12-09 17:25:56', '2024-12-13 09:47:07', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_pembayaran_hutangs`
--

CREATE TABLE `riwayat_pembayaran_hutangs` (
  `id` bigint UNSIGNED NOT NULL,
  `tanggal` timestamp NOT NULL,
  `nominal` decimal(15,0) NOT NULL,
  `tipe` enum('penjual','pekerja') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `penjual_id` bigint UNSIGNED DEFAULT NULL,
  `pekerja_id` bigint UNSIGNED DEFAULT NULL,
  `operasional_id` bigint UNSIGNED NOT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('D0BA4bqMeC1NgZPvRj5oHOBUHrUZM6m7YT44Smux', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:133.0) Gecko/20100101 Firefox/133.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicTF5YVZDRk1NcWdIaUoxV3pEVWV4NTlabUpRbGVNTlNUcnVDWHp4aSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjg6Imh0dHA6Ly8xMjcuMC4wLjEvYWRtaW4vbG9naW4iO319', 1733992745),
('lybxV5an8avJpvM8BYEb9oDyIC2FGMPzgr1Gr8Ih', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiVWxhdnBUMGljS25GTld0ZXNhM1ZmYzJ4V0s0Um9CQVV0Q04xU0J5OCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly9sb2NhbGhvc3QvYWRtaW4vdHJhbnNha3NpLWRvcyI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjA6IiQyeSQxMiR3MmJiRUpUMTFQcksvdjlPcUNuaWZlUXBYV0lvV1htcXdyNVlFRmE3clk5c2dTRnhLcVd0UyI7czo4OiJmaWxhbWVudCI7YTowOnt9fQ==', 1734084230),
('ni1c7wrdWDqqTmmFeOQJLeje6q2y5zG9E3iX7oYX', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiTUpZWGtUa3BXVnYxNDd3dlpiUkNhM3puSTlXaThrQ3VFaG9VeVFzQiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1733992754),
('sfm5swdVkFDbhmMM64ksIPuV3uWeLJjYXIsyrcBa', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiMExxYlNHZHMyWENEN05uRG1MSk9jVW9LajRINTl3dGRsQXZSN3ZRQiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjI6Imh0dHA6Ly8xMjcuMC4wLjEvYWRtaW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjYwOiIkMnkkMTIkdzJiYkVKVDExUHJLL3Y5T3FDbmlmZVFwWFdJb1dYbXF3cjVZRUZhN3JZOXNnU0Z4S3FXdFMiO3M6ODoiZmlsYW1lbnQiO2E6MDp7fXM6NjoidGFibGVzIjthOjY6e3M6MjE6Ikxpc3RUcmFuc2Frc2lEb3Nfc29ydCI7YToyOntzOjY6ImNvbHVtbiI7TjtzOjk6ImRpcmVjdGlvbiI7Tjt9czoxNzoiTGlzdFBlbmp1YWxzX3NvcnQiO2E6Mjp7czo2OiJjb2x1bW4iO047czo5OiJkaXJlY3Rpb24iO047fXM6MjE6Ikxpc3RQZW5qdWFsc19wZXJfcGFnZSI7czozOiJhbGwiO3M6MjQ6Ikxpc3RUcmFuc2Frc2lEb3NfZmlsdGVycyI7YToyOntzOjEwOiJjcmVhdGVkX2F0IjthOjI6e3M6MTI6ImNyZWF0ZWRfZnJvbSI7TjtzOjEwOiJjcmVhdGVkX3RvIjtOO31zOjc6InRyYXNoZWQiO2E6MTp7czo1OiJ2YWx1ZSI7Tjt9fXM6MjU6Ikxpc3RUcmFuc2Frc2lEb3NfcGVyX3BhZ2UiO3M6MzoiYWxsIjtzOjI5OiJMaXN0TGFwb3JhbktldWFuZ2Fuc19wZXJfcGFnZSI7czozOiJhbGwiO319', 1733991876),
('uh9Ssvju2z6kgrCIThV08qSzw8oeRTMXDZsJvTD0', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36', 'YTo4OntzOjY6Il90b2tlbiI7czo0MDoiVzFSR1JUa01mbkdHcmMzbE14RGYzdVRFTVVYa1N0VERTdVYwS1JUTyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDA6Imh0dHA6Ly9sb2NhbGhvc3QvYWRtaW4vbGFwb3Jhbi1rZXVhbmdhbnMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjM6InVybCI7YTowOnt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2MDoiJDJ5JDEyJHcyYmJFSlQxMVBySy92OU9xQ25pZmVRcFhXSW9XWG1xd3I1WUVGYTdyWTlzZ1NGeEtxV3RTIjtzOjY6InRhYmxlcyI7YToyOntzOjI0OiJMaXN0VHJhbnNha3NpRG9zX2ZpbHRlcnMiO2E6Mjp7czoxMDoiY3JlYXRlZF9hdCI7YToyOntzOjEyOiJjcmVhdGVkX2Zyb20iO047czoxMDoiY3JlYXRlZF90byI7Tjt9czo3OiJ0cmFzaGVkIjthOjE6e3M6NToidmFsdWUiO047fX1zOjIxOiJMaXN0VHJhbnNha3NpRG9zX3NvcnQiO2E6Mjp7czo2OiJjb2x1bW4iO047czo5OiJkaXJlY3Rpb24iO047fX1zOjg6ImZpbGFtZW50IjthOjA6e319', 1734074343);

-- --------------------------------------------------------

--
-- Struktur dari tabel `supir`
--

CREATE TABLE `supir` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telepon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hutang` decimal(15,0) DEFAULT NULL,
  `riwayat_bayar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `supir`
--

INSERT INTO `supir` (`id`, `nama`, `alamat`, `telepon`, `hutang`, `riwayat_bayar`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'FURQON', NULL, NULL, NULL, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(2, 'FURQONS', NULL, NULL, NULL, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(3, 'EPI', NULL, NULL, NULL, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(4, 'ANDES', NULL, NULL, NULL, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(5, 'ICAN', NULL, NULL, NULL, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(6, 'HERMAN', NULL, NULL, NULL, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(7, 'SIIT', NULL, NULL, NULL, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(8, 'NARO', NULL, NULL, NULL, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(9, 'AGUS', NULL, NULL, NULL, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(10, 'JEKI', NULL, NULL, NULL, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(11, 'JOKO', NULL, NULL, NULL, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(12, 'WILCO', NULL, NULL, NULL, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(13, 'KOMBET', NULL, NULL, NULL, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(14, 'DODY', NULL, NULL, NULL, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(15, 'KELOMPOK', NULL, NULL, NULL, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(16, 'AGUNG', NULL, NULL, NULL, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(17, 'UCOK', '', '', NULL, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(18, 'ARI WAHYU', '', '', NULL, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(19, 'EKO', NULL, NULL, NULL, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(20, 'LOPON', '', '', NULL, NULL, '2024-12-09 18:11:01', '2024-12-09 18:11:01', NULL),
(21, 'KELOMPOK 1', '', '', NULL, NULL, '2024-12-11 17:33:27', '2024-12-11 17:33:27', NULL),
(22, 'KELOMPOK 2', '', '', NULL, NULL, '2024-12-11 17:34:45', '2024-12-11 17:34:45', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi_do`
--

CREATE TABLE `transaksi_do` (
  `id` bigint UNSIGNED NOT NULL,
  `nomor` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal` datetime NOT NULL,
  `penjual_id` bigint UNSIGNED NOT NULL,
  `supir_id` bigint UNSIGNED DEFAULT NULL,
  `kendaraan_id` bigint UNSIGNED DEFAULT NULL,
  `tonase` decimal(10,2) NOT NULL,
  `harga_satuan` decimal(15,0) NOT NULL,
  `sub_total` decimal(15,0) NOT NULL,
  `upah_bongkar` decimal(15,0) NOT NULL,
  `biaya_lain` decimal(15,0) NOT NULL DEFAULT '0',
  `keterangan_biaya_lain` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hutang_awal` decimal(15,0) NOT NULL,
  `pembayaran_hutang` decimal(12,0) NOT NULL,
  `sisa_hutang_penjual` decimal(12,0) NOT NULL,
  `sisa_bayar` decimal(15,0) NOT NULL,
  `cara_bayar` enum('tunai','transfer','cair di luar','belum dibayar') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tunai',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `transaksi_do`
--

INSERT INTO `transaksi_do` (`id`, `nomor`, `tanggal`, `penjual_id`, `supir_id`, `kendaraan_id`, `tonase`, `harga_satuan`, `sub_total`, `upah_bongkar`, `biaya_lain`, `keterangan_biaya_lain`, `hutang_awal`, `pembayaran_hutang`, `sisa_hutang_penjual`, `sisa_bayar`, `cara_bayar`, `created_at`, `updated_at`, `deleted_at`) VALUES
(4, 'DO-20241210-0002', '2024-12-10 00:43:29', 1, 1, NULL, 4329.00, 3190, 13809510, 0, 0, NULL, 0, 0, 0, 13809510, 'tunai', '2024-12-09 17:43:56', '2024-12-09 17:43:56', NULL),
(5, 'DO-20241210-0003', '2024-12-10 00:44:41', 2, 3, NULL, 2763.00, 3190, 8813970, 0, 200000, NULL, 0, 0, 0, 8613970, 'tunai', '2024-12-09 17:45:33', '2024-12-09 17:52:05', NULL),
(6, 'DO-20241210-0004', '2024-12-10 01:10:25', 3, 4, NULL, 558.00, 3180, 1774440, 0, 0, NULL, 0, 0, 0, 1774440, 'tunai', '2024-12-09 18:10:52', '2024-12-09 18:10:52', NULL),
(7, 'DO-20241210-0005', '2024-12-10 01:10:52', 4, 5, NULL, 9204.00, 3180, 29268720, 0, 116000, NULL, 0, 0, 0, 29152720, 'cair di luar', '2024-12-09 18:12:00', '2024-12-09 18:12:00', NULL),
(8, 'DO-20241210-0006', '2024-12-10 01:23:15', 5, 6, NULL, 940.00, 3180, 2989200, 0, 50000, NULL, 0, 0, 0, 2939200, 'tunai', '2024-12-09 18:24:43', '2024-12-09 18:24:43', NULL),
(9, 'DO-20241210-0007', '2024-12-10 01:25:02', 6, 7, NULL, 2143.00, 3180, 6814740, 0, 100000, NULL, 0, 0, 0, 6714740, 'tunai', '2024-12-09 18:29:47', '2024-12-09 18:29:47', NULL),
(10, 'DO-20241210-0008', '2024-12-10 01:29:55', 7, 8, NULL, 7896.00, 3180, 25109280, 0, 400000, NULL, 1500000, 1000000, 500000, 23709280, 'transfer', '2024-12-09 18:32:36', '2024-12-09 18:32:36', NULL),
(11, 'DO-20241210-0009', '2024-12-10 01:34:02', 9, 9, NULL, 1153.00, 3180, 3666540, 0, 0, NULL, 0, 0, 0, 3666540, 'tunai', '2024-12-09 18:34:57', '2024-12-09 18:34:57', NULL),
(12, 'DO-20241210-0010', '2024-12-10 01:39:52', 10, 10, NULL, 1482.00, 3180, 4712760, 0, 20000, NULL, 0, 0, 0, 4692760, 'tunai', '2024-12-09 18:42:24', '2024-12-09 18:42:24', NULL),
(13, 'DO-20241210-0011', '2024-12-10 01:42:24', 11, 9, NULL, 1384.00, 3180, 4401120, 0, 15000, NULL, 0, 0, 0, 4386120, 'tunai', '2024-12-09 18:43:41', '2024-12-12 05:04:08', NULL),
(14, 'DO-20241212-0001', '2024-12-12 00:28:25', 12, 11, NULL, 1017.00, 3180, 3234060, 0, 0, NULL, 0, 0, 0, 3234060, 'tunai', '2024-12-11 17:29:16', '2024-12-11 17:29:16', NULL),
(15, 'DO-20241212-0002', '2024-12-12 00:29:16', 4, 12, NULL, 9809.00, 3180, 31192620, 0, 124000, NULL, 0, 0, 0, 31068620, 'cair di luar', '2024-12-11 17:30:36', '2024-12-11 17:30:36', NULL),
(16, 'DO-20241212-0003', '2024-12-12 00:30:36', 13, 13, NULL, 8122.00, 3190, 25909180, 0, 300000, NULL, 1500000, 1000000, 500000, 24609180, 'transfer', '2024-12-11 17:31:52', '2024-12-11 17:31:52', NULL),
(17, 'DO-20241212-0004', '2024-12-12 00:31:52', 14, 14, NULL, 9979.00, 3180, 31733220, 0, 350000, NULL, 0, 0, 0, 31383220, 'transfer', '2024-12-11 17:33:07', '2024-12-11 17:33:07', NULL),
(18, 'DO-20241212-0005', '2024-12-12 00:33:07', 15, 21, NULL, 8627.00, 3190, 27520130, 0, 650000, NULL, 0, 0, 0, 26870130, 'tunai', '2024-12-11 17:34:08', '2024-12-11 17:34:08', NULL),
(19, 'DO-20241212-0006', '2024-12-12 00:34:08', 18, 22, NULL, 8221.00, 3190, 26224990, 0, 650000, NULL, 0, 0, 0, 25574990, 'tunai', '2024-12-11 17:35:21', '2024-12-11 17:35:21', NULL),
(20, 'DO-20241212-0007', '2024-12-12 00:35:21', 5, 16, NULL, 6196.00, 3190, 19765240, 0, 0, NULL, 0, 0, 0, 19765240, 'tunai', '2024-12-11 17:36:06', '2024-12-11 17:36:06', NULL),
(21, 'DO-20241212-0008', '2024-12-12 00:36:06', 16, 17, NULL, 1720.00, 3180, 5469600, 0, 0, NULL, 0, 0, 0, 5469600, 'tunai', '2024-12-11 17:36:48', '2024-12-12 07:12:25', NULL),
(22, 'DO-20241212-0009', '2024-12-12 00:36:48', 17, 19, NULL, 7637.00, 3180, 24285660, 0, 80000, NULL, 0, 0, 0, 24205660, 'transfer', '2024-12-11 17:37:47', '2024-12-11 17:37:47', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `perusahaan_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `perusahaan_id`, `name`, `email`, `is_active`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, NULL, 'Super Admin', 'superadmin@gmail.com', 1, '2024-12-09 17:25:55', '$2y$12$w2bbEJT11PrK/v9OqCnifeQpXWIoWXmqwr5YEFa7rY9sgSFxKqWtS', NULL, '2024-12-09 17:25:55', '2024-12-09 17:25:55', NULL),
(2, 1, 'Yondra', 'yondra@gmail.com', 1, '2024-12-09 17:25:55', '$2y$12$FrAhtI6xaOyN77vPFOajxumznq4FXOskxQzPg/jrDWYY/f5Sg1PY.', NULL, '2024-12-09 17:25:55', '2024-12-12 08:30:28', NULL),
(3, 1, 'Wendy', 'wendy@gmail.com', 1, '2024-12-09 17:25:55', '$2y$12$lKXWvI4jNS5ttoUyFkt5uetSengOJ9exxeNGI56AjanJC7ieguECK', '61Ltls3bWO4l4HDwZGU7PJ67mKOZ6Oj4cgVxOqGU3vtDYHtiAiQpWTHhTamK', '2024-12-09 17:25:55', '2024-12-12 08:32:38', NULL),
(4, 1, 'Topit', 'kasir1@gmail.com', 1, '2024-12-09 17:25:55', '$2y$12$sxBlKYT26Pg6HFSQnd4YF.Ii.P1HaXDmAfzBpZ6wtgyp8Zkqlc/sO', 'uL7ECLf0aeL9WxbXYye43PIdqXvQWnKWzRPLDM3wUu42aZb2Z0qMMDCZUrlD', '2024-12-09 17:25:55', '2024-12-12 08:31:05', NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kendaraan`
--
ALTER TABLE `kendaraan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kendaraan_supir_id_foreign` (`supir_id`);

--
-- Indeks untuk tabel `laporan_keuangan`
--
ALTER TABLE `laporan_keuangan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `laporan_keuangan_tanggal_index` (`tanggal`),
  ADD KEY `laporan_keuangan_jenis_transaksi_index` (`jenis_transaksi`),
  ADD KEY `laporan_keuangan_kategori_index` (`kategori`),
  ADD KEY `laporan_keuangan_sumber_transaksi_referensi_id_index` (`sumber_transaksi`,`referensi_id`),
  ADD KEY `laporan_keuangan_nominal_index` (`nominal`),
  ADD KEY `laporan_keuangan_created_at_index` (`created_at`),
  ADD KEY `laporan_keuangan_tanggal_jenis_transaksi_index` (`tanggal`,`jenis_transaksi`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `operasional`
--
ALTER TABLE `operasional`
  ADD PRIMARY KEY (`id`),
  ADD KEY `operasional_penjual_id_foreign` (`penjual_id`),
  ADD KEY `operasional_user_id_foreign` (`user_id`),
  ADD KEY `operasional_supir_id_foreign` (`supir_id`),
  ADD KEY `operasional_tanggal_index` (`tanggal`),
  ADD KEY `operasional_operasional_index` (`operasional`),
  ADD KEY `operasional_nominal_index` (`nominal`),
  ADD KEY `operasional_tipe_nama_index` (`tipe_nama`),
  ADD KEY `operasional_tanggal_operasional_index` (`tanggal`,`operasional`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `pekerja`
--
ALTER TABLE `pekerja`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pekerja_nama_index` (`nama`),
  ADD KEY `pekerja_telepon_index` (`telepon`);

--
-- Indeks untuk tabel `penjuals`
--
ALTER TABLE `penjuals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `penjuals_nama_index` (`nama`),
  ADD KEY `penjuals_telepon_index` (`telepon`);

--
-- Indeks untuk tabel `perusahaans`
--
ALTER TABLE `perusahaans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `perusahaans_name_unique` (`name`),
  ADD KEY `perusahaans_telepon_index` (`telepon`),
  ADD KEY `perusahaans_email_index` (`email`),
  ADD KEY `perusahaans_npwp_index` (`npwp`);

--
-- Indeks untuk tabel `riwayat_pembayaran_hutangs`
--
ALTER TABLE `riwayat_pembayaran_hutangs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `riwayat_pembayaran_hutangs_penjual_id_foreign` (`penjual_id`),
  ADD KEY `riwayat_pembayaran_hutangs_pekerja_id_foreign` (`pekerja_id`),
  ADD KEY `riwayat_pembayaran_hutangs_operasional_id_foreign` (`operasional_id`),
  ADD KEY `riwayat_pembayaran_hutangs_tanggal_index` (`tanggal`),
  ADD KEY `riwayat_pembayaran_hutangs_tipe_penjual_id_pekerja_id_index` (`tipe`,`penjual_id`,`pekerja_id`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indeks untuk tabel `supir`
--
ALTER TABLE `supir`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supir_nama_index` (`nama`),
  ADD KEY `supir_telepon_index` (`telepon`);

--
-- Indeks untuk tabel `transaksi_do`
--
ALTER TABLE `transaksi_do`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaksi_do_nomor_unique` (`nomor`),
  ADD KEY `transaksi_do_supir_id_foreign` (`supir_id`),
  ADD KEY `transaksi_do_kendaraan_id_foreign` (`kendaraan_id`),
  ADD KEY `transaksi_do_tanggal_penjual_id_index` (`tanggal`,`penjual_id`),
  ADD KEY `transaksi_do_tanggal_cara_bayar_index` (`tanggal`,`cara_bayar`),
  ADD KEY `transaksi_do_nomor_index` (`nomor`),
  ADD KEY `transaksi_do_tanggal_index` (`tanggal`),
  ADD KEY `transaksi_do_penjual_id_index` (`penjual_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_perusahaan_id_email_index` (`perusahaan_id`,`email`),
  ADD KEY `users_perusahaan_id_name_index` (`perusahaan_id`,`name`),
  ADD KEY `users_name_index` (`name`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `kendaraan`
--
ALTER TABLE `kendaraan`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `laporan_keuangan`
--
ALTER TABLE `laporan_keuangan`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `operasional`
--
ALTER TABLE `operasional`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `pekerja`
--
ALTER TABLE `pekerja`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `penjuals`
--
ALTER TABLE `penjuals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `perusahaans`
--
ALTER TABLE `perusahaans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `riwayat_pembayaran_hutangs`
--
ALTER TABLE `riwayat_pembayaran_hutangs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `supir`
--
ALTER TABLE `supir`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `transaksi_do`
--
ALTER TABLE `transaksi_do`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `kendaraan`
--
ALTER TABLE `kendaraan`
  ADD CONSTRAINT `kendaraan_supir_id_foreign` FOREIGN KEY (`supir_id`) REFERENCES `supir` (`id`);

--
-- Ketidakleluasaan untuk tabel `operasional`
--
ALTER TABLE `operasional`
  ADD CONSTRAINT `operasional_penjual_id_foreign` FOREIGN KEY (`penjual_id`) REFERENCES `penjuals` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `operasional_supir_id_foreign` FOREIGN KEY (`supir_id`) REFERENCES `supir` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `operasional_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `riwayat_pembayaran_hutangs`
--
ALTER TABLE `riwayat_pembayaran_hutangs`
  ADD CONSTRAINT `riwayat_pembayaran_hutangs_operasional_id_foreign` FOREIGN KEY (`operasional_id`) REFERENCES `operasional` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `riwayat_pembayaran_hutangs_pekerja_id_foreign` FOREIGN KEY (`pekerja_id`) REFERENCES `pekerja` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `riwayat_pembayaran_hutangs_penjual_id_foreign` FOREIGN KEY (`penjual_id`) REFERENCES `penjuals` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `transaksi_do`
--
ALTER TABLE `transaksi_do`
  ADD CONSTRAINT `transaksi_do_kendaraan_id_foreign` FOREIGN KEY (`kendaraan_id`) REFERENCES `kendaraan` (`id`),
  ADD CONSTRAINT `transaksi_do_penjual_id_foreign` FOREIGN KEY (`penjual_id`) REFERENCES `penjuals` (`id`),
  ADD CONSTRAINT `transaksi_do_supir_id_foreign` FOREIGN KEY (`supir_id`) REFERENCES `supir` (`id`);

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_perusahaan_id_foreign` FOREIGN KEY (`perusahaan_id`) REFERENCES `perusahaans` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
