-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 07 Jan 2025 pada 11.45
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

--
-- Dumping data untuk tabel `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('a17961fa74e9275d529f489537f179c05d50c2f3', 'i:1;', 1736221982),
('a17961fa74e9275d529f489537f179c05d50c2f3:timer', 'i:1736221982;', 1736221982),
('a5953fb5e1c86b5792734a0b4775a77519f2794f', 'i:1;', 1734114753),
('a5953fb5e1c86b5792734a0b4775a77519f2794f:timer', 'i:1734114753;', 1734114753),
('spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:118:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:14:\"view_kendaraan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:18:\"view_any_kendaraan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:16:\"create_kendaraan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:16:\"update_kendaraan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:17:\"restore_kendaraan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:21:\"restore_any_kendaraan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:19:\"replicate_kendaraan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:17:\"reorder_kendaraan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:16:\"delete_kendaraan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:20:\"delete_any_kendaraan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:22:\"force_delete_kendaraan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:26:\"force_delete_any_kendaraan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:22:\"view_laporan::keuangan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:26:\"view_any_laporan::keuangan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:24:\"create_laporan::keuangan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:24:\"update_laporan::keuangan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:25:\"restore_laporan::keuangan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:29:\"restore_any_laporan::keuangan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:27:\"replicate_laporan::keuangan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:25:\"reorder_laporan::keuangan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:20;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:24:\"delete_laporan::keuangan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:21;a:4:{s:1:\"a\";i:22;s:1:\"b\";s:28:\"delete_any_laporan::keuangan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:22;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:30:\"force_delete_laporan::keuangan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:23;a:4:{s:1:\"a\";i:24;s:1:\"b\";s:34:\"force_delete_any_laporan::keuangan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:24;a:4:{s:1:\"a\";i:25;s:1:\"b\";s:16:\"view_operasional\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:25;a:4:{s:1:\"a\";i:26;s:1:\"b\";s:20:\"view_any_operasional\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:26;a:4:{s:1:\"a\";i:27;s:1:\"b\";s:18:\"create_operasional\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:27;a:4:{s:1:\"a\";i:28;s:1:\"b\";s:18:\"update_operasional\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:28;a:4:{s:1:\"a\";i:29;s:1:\"b\";s:19:\"restore_operasional\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:29;a:4:{s:1:\"a\";i:30;s:1:\"b\";s:23:\"restore_any_operasional\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:30;a:4:{s:1:\"a\";i:31;s:1:\"b\";s:21:\"replicate_operasional\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:31;a:4:{s:1:\"a\";i:32;s:1:\"b\";s:19:\"reorder_operasional\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:32;a:4:{s:1:\"a\";i:33;s:1:\"b\";s:18:\"delete_operasional\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:33;a:4:{s:1:\"a\";i:34;s:1:\"b\";s:22:\"delete_any_operasional\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:34;a:4:{s:1:\"a\";i:35;s:1:\"b\";s:24:\"force_delete_operasional\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:35;a:4:{s:1:\"a\";i:36;s:1:\"b\";s:28:\"force_delete_any_operasional\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:36;a:4:{s:1:\"a\";i:37;s:1:\"b\";s:12:\"view_pekerja\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:37;a:4:{s:1:\"a\";i:38;s:1:\"b\";s:16:\"view_any_pekerja\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:38;a:4:{s:1:\"a\";i:39;s:1:\"b\";s:14:\"create_pekerja\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:39;a:4:{s:1:\"a\";i:40;s:1:\"b\";s:14:\"update_pekerja\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:40;a:4:{s:1:\"a\";i:41;s:1:\"b\";s:15:\"restore_pekerja\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:41;a:4:{s:1:\"a\";i:42;s:1:\"b\";s:19:\"restore_any_pekerja\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:42;a:4:{s:1:\"a\";i:43;s:1:\"b\";s:17:\"replicate_pekerja\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:43;a:4:{s:1:\"a\";i:44;s:1:\"b\";s:15:\"reorder_pekerja\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:44;a:4:{s:1:\"a\";i:45;s:1:\"b\";s:14:\"delete_pekerja\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:45;a:4:{s:1:\"a\";i:46;s:1:\"b\";s:18:\"delete_any_pekerja\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:46;a:4:{s:1:\"a\";i:47;s:1:\"b\";s:20:\"force_delete_pekerja\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:47;a:4:{s:1:\"a\";i:48;s:1:\"b\";s:24:\"force_delete_any_pekerja\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:48;a:4:{s:1:\"a\";i:49;s:1:\"b\";s:12:\"view_penjual\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:49;a:4:{s:1:\"a\";i:50;s:1:\"b\";s:16:\"view_any_penjual\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:50;a:4:{s:1:\"a\";i:51;s:1:\"b\";s:14:\"create_penjual\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:51;a:4:{s:1:\"a\";i:52;s:1:\"b\";s:14:\"update_penjual\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:52;a:4:{s:1:\"a\";i:53;s:1:\"b\";s:15:\"restore_penjual\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:53;a:4:{s:1:\"a\";i:54;s:1:\"b\";s:19:\"restore_any_penjual\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:54;a:4:{s:1:\"a\";i:55;s:1:\"b\";s:17:\"replicate_penjual\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:55;a:4:{s:1:\"a\";i:56;s:1:\"b\";s:15:\"reorder_penjual\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:56;a:4:{s:1:\"a\";i:57;s:1:\"b\";s:14:\"delete_penjual\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:57;a:4:{s:1:\"a\";i:58;s:1:\"b\";s:18:\"delete_any_penjual\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:58;a:4:{s:1:\"a\";i:59;s:1:\"b\";s:20:\"force_delete_penjual\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:59;a:4:{s:1:\"a\";i:60;s:1:\"b\";s:24:\"force_delete_any_penjual\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:60;a:4:{s:1:\"a\";i:61;s:1:\"b\";s:15:\"view_perusahaan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:61;a:4:{s:1:\"a\";i:62;s:1:\"b\";s:19:\"view_any_perusahaan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:62;a:4:{s:1:\"a\";i:63;s:1:\"b\";s:17:\"create_perusahaan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:63;a:4:{s:1:\"a\";i:64;s:1:\"b\";s:17:\"update_perusahaan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:64;a:4:{s:1:\"a\";i:65;s:1:\"b\";s:18:\"restore_perusahaan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:65;a:4:{s:1:\"a\";i:66;s:1:\"b\";s:22:\"restore_any_perusahaan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:66;a:4:{s:1:\"a\";i:67;s:1:\"b\";s:20:\"replicate_perusahaan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:67;a:4:{s:1:\"a\";i:68;s:1:\"b\";s:18:\"reorder_perusahaan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:68;a:4:{s:1:\"a\";i:69;s:1:\"b\";s:17:\"delete_perusahaan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:69;a:4:{s:1:\"a\";i:70;s:1:\"b\";s:21:\"delete_any_perusahaan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:70;a:4:{s:1:\"a\";i:71;s:1:\"b\";s:23:\"force_delete_perusahaan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:71;a:4:{s:1:\"a\";i:72;s:1:\"b\";s:27:\"force_delete_any_perusahaan\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:72;a:4:{s:1:\"a\";i:73;s:1:\"b\";s:9:\"view_role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:73;a:4:{s:1:\"a\";i:74;s:1:\"b\";s:13:\"view_any_role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:74;a:4:{s:1:\"a\";i:75;s:1:\"b\";s:11:\"create_role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:75;a:4:{s:1:\"a\";i:76;s:1:\"b\";s:11:\"update_role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:76;a:4:{s:1:\"a\";i:77;s:1:\"b\";s:11:\"delete_role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:77;a:4:{s:1:\"a\";i:78;s:1:\"b\";s:15:\"delete_any_role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:78;a:4:{s:1:\"a\";i:79;s:1:\"b\";s:10:\"view_supir\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:79;a:4:{s:1:\"a\";i:80;s:1:\"b\";s:14:\"view_any_supir\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:80;a:4:{s:1:\"a\";i:81;s:1:\"b\";s:12:\"create_supir\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:81;a:4:{s:1:\"a\";i:82;s:1:\"b\";s:12:\"update_supir\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:82;a:4:{s:1:\"a\";i:83;s:1:\"b\";s:13:\"restore_supir\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:83;a:4:{s:1:\"a\";i:84;s:1:\"b\";s:17:\"restore_any_supir\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:84;a:4:{s:1:\"a\";i:85;s:1:\"b\";s:15:\"replicate_supir\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:85;a:4:{s:1:\"a\";i:86;s:1:\"b\";s:13:\"reorder_supir\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:86;a:4:{s:1:\"a\";i:87;s:1:\"b\";s:12:\"delete_supir\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:87;a:4:{s:1:\"a\";i:88;s:1:\"b\";s:16:\"delete_any_supir\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:88;a:4:{s:1:\"a\";i:89;s:1:\"b\";s:18:\"force_delete_supir\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:89;a:4:{s:1:\"a\";i:90;s:1:\"b\";s:22:\"force_delete_any_supir\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:90;a:4:{s:1:\"a\";i:91;s:1:\"b\";s:18:\"view_transaksi::do\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:91;a:4:{s:1:\"a\";i:92;s:1:\"b\";s:22:\"view_any_transaksi::do\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:92;a:4:{s:1:\"a\";i:93;s:1:\"b\";s:20:\"create_transaksi::do\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:93;a:4:{s:1:\"a\";i:94;s:1:\"b\";s:20:\"update_transaksi::do\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:94;a:4:{s:1:\"a\";i:95;s:1:\"b\";s:21:\"restore_transaksi::do\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:95;a:4:{s:1:\"a\";i:96;s:1:\"b\";s:25:\"restore_any_transaksi::do\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:96;a:4:{s:1:\"a\";i:97;s:1:\"b\";s:23:\"replicate_transaksi::do\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:97;a:4:{s:1:\"a\";i:98;s:1:\"b\";s:21:\"reorder_transaksi::do\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:98;a:4:{s:1:\"a\";i:99;s:1:\"b\";s:20:\"delete_transaksi::do\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:99;a:4:{s:1:\"a\";i:100;s:1:\"b\";s:24:\"delete_any_transaksi::do\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:100;a:4:{s:1:\"a\";i:101;s:1:\"b\";s:26:\"force_delete_transaksi::do\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:101;a:4:{s:1:\"a\";i:102;s:1:\"b\";s:30:\"force_delete_any_transaksi::do\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:102;a:4:{s:1:\"a\";i:103;s:1:\"b\";s:9:\"view_user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:103;a:4:{s:1:\"a\";i:104;s:1:\"b\";s:13:\"view_any_user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:104;a:4:{s:1:\"a\";i:105;s:1:\"b\";s:11:\"create_user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:105;a:4:{s:1:\"a\";i:106;s:1:\"b\";s:11:\"update_user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:106;a:4:{s:1:\"a\";i:107;s:1:\"b\";s:12:\"restore_user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:107;a:4:{s:1:\"a\";i:108;s:1:\"b\";s:16:\"restore_any_user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:108;a:4:{s:1:\"a\";i:109;s:1:\"b\";s:14:\"replicate_user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:109;a:4:{s:1:\"a\";i:110;s:1:\"b\";s:12:\"reorder_user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:110;a:4:{s:1:\"a\";i:111;s:1:\"b\";s:11:\"delete_user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:111;a:4:{s:1:\"a\";i:112;s:1:\"b\";s:15:\"delete_any_user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:112;a:4:{s:1:\"a\";i:113;s:1:\"b\";s:17:\"force_delete_user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:113;a:4:{s:1:\"a\";i:114;s:1:\"b\";s:21:\"force_delete_any_user\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:114;a:4:{s:1:\"a\";i:115;s:1:\"b\";s:27:\"widget_DashboardStatsWidget\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:115;a:4:{s:1:\"a\";i:116;s:1:\"b\";s:30:\"widget_DailyFinanceChartWidget\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:116;a:4:{s:1:\"a\";i:117;s:1:\"b\";s:24:\"widget_TransaksiTerakhir\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:117;a:4:{s:1:\"a\";i:118;s:1:\"b\";s:32:\"widget_MonthlyFinanceChartWidget\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}}s:5:\"roles\";a:2:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:11:\"super_admin\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:5:\"kasir\";s:1:\"c\";s:3:\"web\";}}}', 1736308324),
('transaksi-stats', 'a:4:{i:0;O:41:\"Filament\\Widgets\\StatsOverviewWidget\\Stat\":17:{s:9:\"\0*\0except\";a:0:{}s:13:\"componentName\";N;s:10:\"attributes\";N;s:8:\"\0*\0chart\";N;s:13:\"\0*\0chartColor\";N;s:8:\"\0*\0color\";s:7:\"success\";s:7:\"\0*\0icon\";N;s:14:\"\0*\0description\";s:37:\"Total saldo masuk - Total pengeluaran\";s:18:\"\0*\0descriptionIcon\";s:20:\"heroicon-m-banknotes\";s:26:\"\0*\0descriptionIconPosition\";N;s:19:\"\0*\0descriptionColor\";N;s:18:\"\0*\0extraAttributes\";a:0:{}s:24:\"\0*\0shouldOpenUrlInNewTab\";b:0;s:6:\"\0*\0url\";N;s:5:\"\0*\0id\";N;s:8:\"\0*\0label\";s:10:\"Sisa Saldo\";s:8:\"\0*\0value\";s:14:\"Rp 134.876.700\";}i:1;O:41:\"Filament\\Widgets\\StatsOverviewWidget\\Stat\":17:{s:9:\"\0*\0except\";a:0:{}s:13:\"componentName\";N;s:10:\"attributes\";N;s:8:\"\0*\0chart\";N;s:13:\"\0*\0chartColor\";N;s:8:\"\0*\0color\";s:7:\"success\";s:7:\"\0*\0icon\";N;s:14:\"\0*\0description\";s:101:\"Pembayaran Hutang: Rp 2.000.000\nPembayaran Sisa: Rp 164.128.680\nPemasukan Operasional: Rp 265.647.000\";s:18:\"\0*\0descriptionIcon\";s:28:\"heroicon-m-arrow-trending-up\";s:26:\"\0*\0descriptionIconPosition\";N;s:19:\"\0*\0descriptionColor\";N;s:18:\"\0*\0extraAttributes\";a:0:{}s:24:\"\0*\0shouldOpenUrlInNewTab\";b:0;s:6:\"\0*\0url\";N;s:5:\"\0*\0id\";N;s:8:\"\0*\0label\";s:22:\"Total Saldo/Uang Masuk\";s:8:\"\0*\0value\";s:14:\"Rp 431.775.680\";}i:2;O:41:\"Filament\\Widgets\\StatsOverviewWidget\\Stat\":17:{s:9:\"\0*\0except\";a:0:{}s:13:\"componentName\";N;s:10:\"attributes\";N;s:8:\"\0*\0chart\";N;s:13:\"\0*\0chartColor\";N;s:8:\"\0*\0color\";s:6:\"danger\";s:7:\"\0*\0icon\";N;s:14:\"\0*\0description\";s:54:\"Total DO: Rp 296.694.980\nTotal Operasional: Rp 204.000\";s:18:\"\0*\0descriptionIcon\";s:30:\"heroicon-m-arrow-trending-down\";s:26:\"\0*\0descriptionIconPosition\";N;s:19:\"\0*\0descriptionColor\";N;s:18:\"\0*\0extraAttributes\";a:0:{}s:24:\"\0*\0shouldOpenUrlInNewTab\";b:0;s:6:\"\0*\0url\";N;s:5:\"\0*\0id\";N;s:8:\"\0*\0label\";s:23:\"Pengeluaran/Uang Keluar\";s:8:\"\0*\0value\";s:14:\"Rp 296.898.980\";}i:3;O:41:\"Filament\\Widgets\\StatsOverviewWidget\\Stat\":17:{s:9:\"\0*\0except\";a:0:{}s:13:\"componentName\";N;s:10:\"attributes\";N;s:8:\"\0*\0chart\";N;s:13:\"\0*\0chartColor\";N;s:8:\"\0*\0color\";s:7:\"primary\";s:7:\"\0*\0icon\";N;s:14:\"\0*\0description\";s:54:\"tunai: 13\ntransfer: 4\ncair di luar: 2\nbelum dibayar: 0\";s:18:\"\0*\0descriptionIcon\";s:24:\"heroicon-m-document-text\";s:26:\"\0*\0descriptionIconPosition\";N;s:19:\"\0*\0descriptionColor\";N;s:18:\"\0*\0extraAttributes\";a:0:{}s:24:\"\0*\0shouldOpenUrlInNewTab\";b:0;s:6:\"\0*\0url\";N;s:5:\"\0*\0id\";N;s:8:\"\0*\0label\";s:15:\"Total Transaksi\";s:8:\"\0*\0value\";i:19;}}', 1736018525),
('transaksi-stats-2024-12-14-2024-12-14', 'a:4:{i:0;O:41:\"Filament\\Widgets\\StatsOverviewWidget\\Stat\":17:{s:9:\"\0*\0except\";a:0:{}s:13:\"componentName\";N;s:10:\"attributes\";N;s:8:\"\0*\0chart\";N;s:13:\"\0*\0chartColor\";N;s:8:\"\0*\0color\";s:7:\"success\";s:7:\"\0*\0icon\";N;s:14:\"\0*\0description\";s:23:\"Data tanggal 14/12/2024\";s:18:\"\0*\0descriptionIcon\";s:20:\"heroicon-m-banknotes\";s:26:\"\0*\0descriptionIconPosition\";N;s:19:\"\0*\0descriptionColor\";N;s:18:\"\0*\0extraAttributes\";a:0:{}s:24:\"\0*\0shouldOpenUrlInNewTab\";b:0;s:6:\"\0*\0url\";N;s:5:\"\0*\0id\";N;s:8:\"\0*\0label\";s:10:\"Sisa Saldo\";s:8:\"\0*\0value\";s:4:\"Rp 0\";}i:1;O:41:\"Filament\\Widgets\\StatsOverviewWidget\\Stat\":17:{s:9:\"\0*\0except\";a:0:{}s:13:\"componentName\";N;s:10:\"attributes\";N;s:8:\"\0*\0chart\";N;s:13:\"\0*\0chartColor\";N;s:8:\"\0*\0color\";s:7:\"success\";s:7:\"\0*\0icon\";N;s:14:\"\0*\0description\";s:73:\"Pembayaran Hutang: Rp 0\nPembayaran Sisa: Rp 0\nPemasukan Operasional: Rp 0\";s:18:\"\0*\0descriptionIcon\";s:28:\"heroicon-m-arrow-trending-up\";s:26:\"\0*\0descriptionIconPosition\";N;s:19:\"\0*\0descriptionColor\";N;s:18:\"\0*\0extraAttributes\";a:0:{}s:24:\"\0*\0shouldOpenUrlInNewTab\";b:0;s:6:\"\0*\0url\";N;s:5:\"\0*\0id\";N;s:8:\"\0*\0label\";s:22:\"Total Saldo/Uang Masuk\";s:8:\"\0*\0value\";s:4:\"Rp 0\";}i:2;O:41:\"Filament\\Widgets\\StatsOverviewWidget\\Stat\":17:{s:9:\"\0*\0except\";a:0:{}s:13:\"componentName\";N;s:10:\"attributes\";N;s:8:\"\0*\0chart\";N;s:13:\"\0*\0chartColor\";N;s:8:\"\0*\0color\";s:6:\"danger\";s:7:\"\0*\0icon\";N;s:14:\"\0*\0description\";s:38:\"Total DO: Rp 0\nTotal Operasional: Rp 0\";s:18:\"\0*\0descriptionIcon\";s:30:\"heroicon-m-arrow-trending-down\";s:26:\"\0*\0descriptionIconPosition\";N;s:19:\"\0*\0descriptionColor\";N;s:18:\"\0*\0extraAttributes\";a:0:{}s:24:\"\0*\0shouldOpenUrlInNewTab\";b:0;s:6:\"\0*\0url\";N;s:5:\"\0*\0id\";N;s:8:\"\0*\0label\";s:23:\"Pengeluaran/Uang Keluar\";s:8:\"\0*\0value\";s:4:\"Rp 0\";}i:3;O:41:\"Filament\\Widgets\\StatsOverviewWidget\\Stat\":17:{s:9:\"\0*\0except\";a:0:{}s:13:\"componentName\";N;s:10:\"attributes\";N;s:8:\"\0*\0chart\";N;s:13:\"\0*\0chartColor\";N;s:8:\"\0*\0color\";s:7:\"primary\";s:7:\"\0*\0icon\";N;s:14:\"\0*\0description\";s:53:\"tunai: 0\ntransfer: 0\ncair di luar: 0\nbelum dibayar: 0\";s:18:\"\0*\0descriptionIcon\";s:24:\"heroicon-m-document-text\";s:26:\"\0*\0descriptionIconPosition\";N;s:19:\"\0*\0descriptionColor\";N;s:18:\"\0*\0extraAttributes\";a:0:{}s:24:\"\0*\0shouldOpenUrlInNewTab\";b:0;s:6:\"\0*\0url\";N;s:5:\"\0*\0id\";N;s:8:\"\0*\0label\";s:15:\"Total Transaksi\";s:8:\"\0*\0value\";i:0;}}', 1734119288);

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
(45, '2024-12-12 00:36:06', 'Pengeluaran', 'DO', 'Pembayaran DO', 5469600, 'DO', 21, 'DO-20241212-0008', 'UCOK', 'penjual', 'tunai', 'Pembayaran DO #DO-20241212-0008', 1, NULL, '2024-12-12 07:12:25', '2024-12-12 07:12:25', NULL),
(46, '2025-01-07 11:31:22', 'Pengeluaran', 'Operasional', 'Pinjaman', 10000, 'Operasional', 8, 'OP-00008', 'AGUS', 'penjual', 'tunai', '-', 1, NULL, '2025-01-07 04:31:52', '2025-01-07 04:39:38', '2025-01-07 04:39:38');

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
(20, '2024_12_12_143441_create_permission_tables', 4);

-- --------------------------------------------------------

--
-- Struktur dari tabel `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1);

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
(7, '2024-12-12 00:40:36', 'pengeluaran', 'pijakan_gas', 'supir', NULL, NULL, NULL, 8, 78000, NULL, NULL, '2024-12-11 17:41:10', '2024-12-11 17:41:10', NULL),
(8, '2025-01-07 11:31:22', 'pengeluaran', 'pinjaman', 'penjual', 11, NULL, NULL, NULL, 10000, NULL, NULL, '2025-01-07 04:31:52', '2025-01-07 04:39:38', '2025-01-07 04:39:38'),
(9, '2025-01-07 11:39:50', 'pengeluaran', 'pinjaman', 'penjual', 11, NULL, NULL, NULL, 20000, 'dgdfdgdg', NULL, '2025-01-07 04:40:26', '2025-01-07 04:40:26', NULL),
(10, '2025-01-07 11:42:13', 'pengeluaran', 'pinjaman', 'penjual', 11, NULL, NULL, NULL, 10000, 'xcvxv', NULL, '2025-01-07 04:42:47', '2025-01-07 04:42:47', NULL),
(11, '2025-01-07 11:43:48', 'pengeluaran', 'pinjaman', 'penjual', 11, NULL, NULL, NULL, 10000, 'cccccc', NULL, '2025-01-07 04:44:12', '2025-01-07 04:44:12', NULL);

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
(11, 'AGUS', NULL, NULL, -10000, NULL, '2024-12-09 17:25:56', '2025-01-07 04:39:38', NULL),
(12, 'JOKO', NULL, NULL, 0, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(13, 'SUKARMIN', NULL, NULL, 0, NULL, '2024-12-09 17:25:56', '2025-01-04 19:05:21', NULL),
(14, 'DITEG', NULL, NULL, 0, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(15, 'KELOMPOK 1', NULL, NULL, 0, NULL, '2024-12-09 17:25:56', '2024-12-09 17:53:36', NULL),
(16, 'UCOK', NULL, NULL, 0, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(17, 'ARI WAHYU', NULL, NULL, 0, NULL, '2024-12-09 17:25:56', '2024-12-09 17:25:56', NULL),
(18, 'KELOMPOK 2', NULL, NULL, 0, NULL, '2024-12-09 17:53:59', '2024-12-09 17:53:59', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'view_kendaraan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(2, 'view_any_kendaraan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(3, 'create_kendaraan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(4, 'update_kendaraan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(5, 'restore_kendaraan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(6, 'restore_any_kendaraan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(7, 'replicate_kendaraan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(8, 'reorder_kendaraan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(9, 'delete_kendaraan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(10, 'delete_any_kendaraan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(11, 'force_delete_kendaraan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(12, 'force_delete_any_kendaraan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(13, 'view_laporan::keuangan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(14, 'view_any_laporan::keuangan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(15, 'create_laporan::keuangan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(16, 'update_laporan::keuangan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(17, 'restore_laporan::keuangan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(18, 'restore_any_laporan::keuangan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(19, 'replicate_laporan::keuangan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(20, 'reorder_laporan::keuangan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(21, 'delete_laporan::keuangan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(22, 'delete_any_laporan::keuangan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(23, 'force_delete_laporan::keuangan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(24, 'force_delete_any_laporan::keuangan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(25, 'view_operasional', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(26, 'view_any_operasional', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(27, 'create_operasional', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(28, 'update_operasional', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(29, 'restore_operasional', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(30, 'restore_any_operasional', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(31, 'replicate_operasional', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(32, 'reorder_operasional', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(33, 'delete_operasional', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(34, 'delete_any_operasional', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(35, 'force_delete_operasional', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(36, 'force_delete_any_operasional', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(37, 'view_pekerja', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(38, 'view_any_pekerja', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(39, 'create_pekerja', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(40, 'update_pekerja', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(41, 'restore_pekerja', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(42, 'restore_any_pekerja', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(43, 'replicate_pekerja', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(44, 'reorder_pekerja', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(45, 'delete_pekerja', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(46, 'delete_any_pekerja', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(47, 'force_delete_pekerja', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(48, 'force_delete_any_pekerja', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(49, 'view_penjual', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(50, 'view_any_penjual', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(51, 'create_penjual', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(52, 'update_penjual', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(53, 'restore_penjual', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(54, 'restore_any_penjual', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(55, 'replicate_penjual', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(56, 'reorder_penjual', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(57, 'delete_penjual', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(58, 'delete_any_penjual', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(59, 'force_delete_penjual', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(60, 'force_delete_any_penjual', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(61, 'view_perusahaan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(62, 'view_any_perusahaan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(63, 'create_perusahaan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(64, 'update_perusahaan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(65, 'restore_perusahaan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(66, 'restore_any_perusahaan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(67, 'replicate_perusahaan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(68, 'reorder_perusahaan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(69, 'delete_perusahaan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(70, 'delete_any_perusahaan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(71, 'force_delete_perusahaan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(72, 'force_delete_any_perusahaan', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(73, 'view_role', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(74, 'view_any_role', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(75, 'create_role', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(76, 'update_role', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(77, 'delete_role', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(78, 'delete_any_role', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(79, 'view_supir', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(80, 'view_any_supir', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(81, 'create_supir', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(82, 'update_supir', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(83, 'restore_supir', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(84, 'restore_any_supir', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(85, 'replicate_supir', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(86, 'reorder_supir', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(87, 'delete_supir', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(88, 'delete_any_supir', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(89, 'force_delete_supir', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(90, 'force_delete_any_supir', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(91, 'view_transaksi::do', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(92, 'view_any_transaksi::do', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(93, 'create_transaksi::do', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(94, 'update_transaksi::do', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(95, 'restore_transaksi::do', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(96, 'restore_any_transaksi::do', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(97, 'replicate_transaksi::do', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(98, 'reorder_transaksi::do', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(99, 'delete_transaksi::do', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(100, 'delete_any_transaksi::do', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(101, 'force_delete_transaksi::do', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(102, 'force_delete_any_transaksi::do', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(103, 'view_user', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(104, 'view_any_user', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(105, 'create_user', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(106, 'update_user', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(107, 'restore_user', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(108, 'restore_any_user', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(109, 'replicate_user', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(110, 'reorder_user', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(111, 'delete_user', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(112, 'delete_any_user', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(113, 'force_delete_user', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(114, 'force_delete_any_user', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(115, 'widget_DashboardStatsWidget', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(116, 'widget_DailyFinanceChartWidget', 'web', '2024-12-13 18:36:00', '2024-12-13 18:36:00'),
(117, 'widget_TransaksiTerakhir', 'web', '2024-12-13 18:36:00', '2024-12-13 18:36:00'),
(118, 'widget_MonthlyFinanceChartWidget', 'web', '2024-12-13 18:36:00', '2024-12-13 18:36:00');

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
(1, 0.00, '2024-12-10', 0, 'CV SUCCESS MANDIRI', 134876700, 'Dusun Sungai Moran Nagari Kamang', '+62 823-8921-9670', NULL, 'Yondra', '12.345.678.9-123.000', 'successw.png', 1, '2024-12-09 17:25:56', '2025-01-07 04:39:38', NULL);

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
-- Struktur dari tabel `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', 'web', '2024-12-13 18:35:59', '2024-12-13 18:35:59'),
(2, 'kasir', 'web', '2024-12-13 18:37:40', '2024-12-13 18:37:40');

-- --------------------------------------------------------

--
-- Struktur dari tabel `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(35, 1),
(36, 1),
(37, 1),
(38, 1),
(39, 1),
(40, 1),
(41, 1),
(42, 1),
(43, 1),
(44, 1),
(45, 1),
(46, 1),
(47, 1),
(48, 1),
(49, 1),
(50, 1),
(51, 1),
(52, 1),
(53, 1),
(54, 1),
(55, 1),
(56, 1),
(57, 1),
(58, 1),
(59, 1),
(60, 1),
(61, 1),
(62, 1),
(63, 1),
(64, 1),
(65, 1),
(66, 1),
(67, 1),
(68, 1),
(69, 1),
(70, 1),
(71, 1),
(72, 1),
(73, 1),
(74, 1),
(75, 1),
(76, 1),
(77, 1),
(78, 1),
(79, 1),
(80, 1),
(81, 1),
(82, 1),
(83, 1),
(84, 1),
(85, 1),
(86, 1),
(87, 1),
(88, 1),
(89, 1),
(90, 1),
(91, 1),
(92, 1),
(93, 1),
(94, 1),
(95, 1),
(96, 1),
(97, 1),
(98, 1),
(99, 1),
(100, 1),
(101, 1),
(102, 1),
(103, 1),
(104, 1),
(105, 1),
(106, 1),
(107, 1),
(108, 1),
(109, 1),
(110, 1),
(111, 1),
(112, 1),
(113, 1),
(114, 1),
(115, 1),
(116, 1),
(117, 1),
(118, 1),
(1, 2),
(2, 2),
(3, 2),
(4, 2),
(5, 2),
(6, 2),
(7, 2),
(8, 2),
(9, 2),
(10, 2),
(11, 2),
(12, 2),
(13, 2),
(14, 2),
(15, 2),
(16, 2),
(17, 2),
(18, 2),
(19, 2),
(20, 2),
(21, 2),
(22, 2),
(23, 2),
(24, 2),
(25, 2),
(26, 2),
(27, 2),
(28, 2),
(29, 2),
(30, 2),
(31, 2),
(32, 2),
(33, 2),
(34, 2),
(35, 2),
(36, 2),
(37, 2),
(38, 2),
(39, 2),
(40, 2),
(41, 2),
(42, 2),
(43, 2),
(44, 2),
(45, 2),
(46, 2),
(47, 2),
(48, 2),
(49, 2),
(50, 2),
(51, 2),
(52, 2),
(53, 2),
(54, 2),
(55, 2),
(56, 2),
(57, 2),
(58, 2),
(59, 2),
(60, 2),
(79, 2),
(80, 2),
(81, 2),
(82, 2),
(83, 2),
(84, 2),
(85, 2),
(86, 2),
(87, 2),
(88, 2),
(89, 2),
(90, 2),
(91, 2),
(92, 2),
(93, 2),
(94, 2),
(95, 2),
(96, 2),
(97, 2),
(98, 2),
(99, 2),
(100, 2),
(101, 2),
(102, 2),
(115, 2),
(116, 2),
(117, 2),
(118, 2);

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
('7JzyoGAdqGJQiodd84JLQmTcVjexHM7bZdXiAlP6', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36', 'YTo4OntzOjY6Il90b2tlbiI7czo0MDoiZUYzMGNwNE5ObUlEOHJYWlZadzRWU3lLTkU2bkhIQmJQcmZkUldCUyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9vcGVyYXNpb25hbHMvY3JlYXRlIjt9czozOiJ1cmwiO2E6MDp7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjA6IiQyeSQxMiR3MmJiRUpUMTFQcksvdjlPcUNuaWZlUXBYV0lvV1htcXdyNVlFRmE3clk5c2dTRnhLcVd0UyI7czo2OiJ0YWJsZXMiO2E6MTp7czoxNzoiTGlzdFBlbmp1YWxzX3NvcnQiO2E6Mjp7czo2OiJjb2x1bW4iO047czo5OiJkaXJlY3Rpb24iO047fX1zOjg6ImZpbGFtZW50IjthOjE6e3M6MTM6Im5vdGlmaWNhdGlvbnMiO2E6MTp7aTowO2E6MTE6e3M6MjoiaWQiO3M6MzY6IjlkZThhM2MwLTZmNDctNGQyMi05MGFiLTczNzM1MWE0NzkwNyI7czo3OiJhY3Rpb25zIjthOjA6e31zOjQ6ImJvZHkiO3M6NDE3OiJUZXJqYWRpIGtlc2FsYWhhbiBzYWF0IGNyZWF0ZWQgdHJhbnNha3NpOiBTUUxTVEFURVswMTAwMF06IFdhcm5pbmc6IDEyNjUgRGF0YSB0cnVuY2F0ZWQgZm9yIGNvbHVtbiAndGlwZScgYXQgcm93IDEgKENvbm5lY3Rpb246IG15c3FsLCBTUUw6IGluc2VydCBpbnRvIGByaXdheWF0X3BlbWJheWFyYW5faHV0YW5nc2AgKGB0YW5nZ2FsYCwgYG5vbWluYWxgLCBgdGlwZWAsIGBvcGVyYXNpb25hbF9pZGAsIGBrZXRlcmFuZ2FuYCwgYHBlbmp1YWxfaWRgLCBgdXBkYXRlZF9hdGAsIGBjcmVhdGVkX2F0YCkgdmFsdWVzICgyMDI1LTAxLTA3IDExOjQzOjQ4LCAxMDAwMCwgcGluamFtYW4sIDExLCBQZW5hbWJhaGFuIGh1dGFuZyB2aWEgb3BlcmFzaW9uYWwsIDExLCAyMDI1LTAxLTA3IDExOjQ0OjEyLCAyMDI1LTAxLTA3IDExOjQ0OjEyKSkiO3M6NToiY29sb3IiO047czo4OiJkdXJhdGlvbiI7aTozMDAwO3M6NDoiaWNvbiI7czoxOToiaGVyb2ljb24tby14LWNpcmNsZSI7czo5OiJpY29uQ29sb3IiO3M6NjoiZGFuZ2VyIjtzOjY6InN0YXR1cyI7czo2OiJkYW5nZXIiO3M6NToidGl0bGUiO3M6NjoiRXJyb3IhIjtzOjQ6InZpZXciO3M6MzY6ImZpbGFtZW50LW5vdGlmaWNhdGlvbnM6Om5vdGlmaWNhdGlvbiI7czo4OiJ2aWV3RGF0YSI7YTowOnt9fX19fQ==', 1736225054),
('bWuhSTN4wWCwucdiTUFdxetlbVZZz3C1x6D4ZABC', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiaWRiN0U3RXJIUEFoNDkyVGJkRlpaZ3FZRDZ3azljTk03RGNIZnVQMiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjg6Imh0dHA6Ly9sb2NhbGhvc3QvYWRtaW4vbG9naW4iO31zOjM6InVybCI7YToxOntzOjg6ImludGVuZGVkIjtzOjM2OiJodHRwOi8vbG9jYWxob3N0L2FkbWluL3RyYW5zYWtzaS1kb3MiO319', 1734119381),
('D0BA4bqMeC1NgZPvRj5oHOBUHrUZM6m7YT44Smux', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:133.0) Gecko/20100101 Firefox/133.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicTF5YVZDRk1NcWdIaUoxV3pEVWV4NTlabUpRbGVNTlNUcnVDWHp4aSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjg6Imh0dHA6Ly8xMjcuMC4wLjEvYWRtaW4vbG9naW4iO319', 1733992745),
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
-- Indeks untuk tabel `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indeks untuk tabel `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

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
-- Indeks untuk tabel `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

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
-- Indeks untuk tabel `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indeks untuk tabel `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `operasional`
--
ALTER TABLE `operasional`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
-- AUTO_INCREMENT untuk tabel `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

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
-- AUTO_INCREMENT untuk tabel `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
-- Ketidakleluasaan untuk tabel `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

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
-- Ketidakleluasaan untuk tabel `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

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
