-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 14, 2025 at 04:33 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.1.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_pengajuan_dokumen`
--

-- --------------------------------------------------------

--
-- Table structure for table `t_berkas_dokumen`
--

CREATE TABLE `t_berkas_dokumen` (
  `id_berkas` int(11) NOT NULL,
  `id_pengajuan` int(11) NOT NULL,
  `nama_file` varchar(255) DEFAULT NULL,
  `path_file` varchar(255) DEFAULT NULL,
  `ukuran_file` int(11) DEFAULT NULL,
  `tipe_file` varchar(10) DEFAULT NULL,
  `tanggal_upload` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `t_berkas_dokumen`
--

INSERT INTO `t_berkas_dokumen` (`id_berkas`, `id_pengajuan`, `nama_file`, `path_file`, `ukuran_file`, `tipe_file`, `tanggal_upload`) VALUES
(1, 1, 'Minggu 4 - PERCABANGAN DENGAN MATCH CASE.pdf', 'DOC_1_1759642317.pdf', 348635, 'pdf', '2025-10-05 05:31:57'),
(2, 2, 'Lembar Aktivitas Tugas 2.pdf', 'DOC_2_1759643447.pdf', 163558, 'pdf', '2025-10-05 05:50:47'),
(3, 3, 'Lembar Aktivitas Tugas 2.pdf', 'DOC_3_1759643450.pdf', 163558, 'pdf', '2025-10-05 05:50:50'),
(4, 4, 'Lembar Aktivitas Tugas 2.pdf', 'DOC_4_1759643475.pdf', 163558, 'pdf', '2025-10-05 05:51:15'),
(5, 5, 'Lembar Aktivitas Tugas 2.pdf', 'DOC_5_1759643521.pdf', 163558, 'pdf', '2025-10-05 05:52:01'),
(6, 6, 'Minggu 4 - PERCABANGAN DENGAN MATCH CASE.pdf', 'DOC_6_1759643916.pdf', 348635, 'pdf', '2025-10-05 05:58:36'),
(7, 7, 'Lembar Aktivitas Tugas 2.pdf', 'DOC_7_1759644495.pdf', 163558, 'pdf', '2025-10-05 06:08:15'),
(8, 8, 'Format laporan.pdf', 'DOC_8_1759733706.pdf', 700816, 'pdf', '2025-10-06 06:55:06'),
(9, 10, '4-Desain.pdf', 'DOC_10_1759931562.pdf', 715311, 'pdf', '2025-10-08 13:52:42'),
(10, 11, 'kelompok pbl 1.pdf', 'DOC_11_1760209312.pdf', 764355, 'pdf', '2025-10-11 19:01:52'),
(11, 12, 'Lembar Aktivitas Tugas 5.pdf', 'DOC_12_1760342378.pdf', 325109, 'pdf', '2025-10-13 07:59:38'),
(12, 13, 'Lembar Aktivitas Tugas 5.pdf', 'DOC_13_1760342419.pdf', 325109, 'pdf', '2025-10-13 08:00:19'),
(13, 14, 'P5. Javascript.pdf', 'DOC_14_1760349304.pdf', 473766, 'pdf', '2025-10-13 09:55:04'),
(14, 15, 'P6. PRAKTIKUM 6 Bootstrap.pdf', 'DOC_15_1760363896.pdf', 429495, 'pdf', '2025-10-13 13:58:16');

-- --------------------------------------------------------

--
-- Table structure for table `t_jenis_dokumen`
--

CREATE TABLE `t_jenis_dokumen` (
  `id_jenis` int(11) NOT NULL,
  `nama_dokumen` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `tanggal_dibuat` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `t_jenis_dokumen`
--

INSERT INTO `t_jenis_dokumen` (`id_jenis`, `nama_dokumen`, `deskripsi`, `status`, `tanggal_dibuat`) VALUES
(1, 'Surat Keterangan Tidak Mampu (SKTM)', 'Untuk keperluan bantuan sosial dan pendidikan.', 'aktif', '2025-10-05 05:24:58'),
(2, 'Surat Keterangan Usaha (SKU)', 'Untuk keperluan administrasi pelaku usaha.', 'aktif', '2025-10-05 05:24:58'),
(3, 'Surat Keterangan Domisili (SKD)', 'Untuk kebutuhan data kependudukan.', 'aktif', '2025-10-05 05:24:58');

-- --------------------------------------------------------

--
-- Table structure for table `t_pengajuan`
--

CREATE TABLE `t_pengajuan` (
  `id_pengajuan` int(11) NOT NULL,
  `nomor_pengajuan` varchar(50) NOT NULL,
  `id_pengguna` int(11) NOT NULL,
  `id_jenis` int(11) NOT NULL,
  `keperluan` varchar(200) DEFAULT NULL,
  `keterangan` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`keterangan`)),
  `id_status` int(11) DEFAULT 1,
  `tanggal_pengajuan` timestamp NOT NULL DEFAULT current_timestamp(),
  `tanggal_validasi` datetime DEFAULT NULL,
  `validasi_oleh` int(11) DEFAULT NULL,
  `catatan_validasi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `t_pengajuan`
--

INSERT INTO `t_pengajuan` (`id_pengajuan`, `nomor_pengajuan`, `id_pengguna`, `id_jenis`, `keperluan`, `keterangan`, `id_status`, `tanggal_pengajuan`, `tanggal_validasi`, `validasi_oleh`, `catatan_validasi`) VALUES
(1, 'DOC-20251005-6801', 4, 3, 'pindah tempat', '{\"alamat_asal\":\"jakarta\",\"alamat_tujuan\":\"batam\",\"lama_tinggal\":\"12\",\"alasan_pindah\":\"pengen aja\"}', 2, '2025-10-05 05:31:57', '2025-10-05 12:32:52', 2, 'ok'),
(2, 'DOC-20251005-8790', 4, 1, 'Bantuan ukt', '{\"nama_anak\":\"eka\",\"nama_sekolah\":\"smk1 jakarta\",\"penghasilan_orangtua\":\"2000000\",\"alasan_permohonan\":\"apaaja\"}', 2, '2025-10-05 05:50:47', '2025-10-05 12:52:37', 2, 'oke'),
(3, 'DOC-20251005-5750', 4, 1, 'Bantuan ukt', '{\"nama_anak\":\"eka\",\"nama_sekolah\":\"smk1 jakarta\",\"penghasilan_orangtua\":\"2000000\",\"alasan_permohonan\":\"apaaja\"}', 3, '2025-10-05 05:50:50', '2025-10-13 15:51:43', 2, 'cek lagi yaa...'),
(4, 'DOC-20251005-6855', 4, 3, 'pindah tempat', '{\"alamat_asal\":\"e\",\"alamat_tujuan\":\"e\",\"lama_tinggal\":\"12\",\"alasan_pindah\":\"ga\"}', 3, '2025-10-05 05:51:15', '2025-10-13 15:48:26', 2, 'periksa kembali berkas nya'),
(5, 'DOC-20251005-5928', 4, 1, 'Quas eligendi cum se', '{\"nama_anak\":\"eka\",\"nama_sekolah\":\"smk1 jakarta\",\"penghasilan_orangtua\":\"999999\",\"alasan_permohonan\":\"apaaja\"}', 3, '2025-10-05 05:52:01', '2025-10-05 13:10:34', 2, 'ditolak!'),
(6, 'DOC-20251005-9088', 5, 3, 'pindah tempat', '{\"alamat_asal\":\"Aceh\",\"alamat_tujuan\":\"jawa timut\",\"lama_tinggal\":\"12\",\"alasan_pindah\":\"urusan\"}', 2, '2025-10-05 05:58:36', '2025-10-05 13:01:35', 2, 'ok'),
(7, 'DOC-20251005-2056', 6, 1, 'bantuan penurunan ukt', '{\"nama_anak\":\"romaryo\",\"nama_sekolah\":\"kampus politeknik negeri batam\",\"penghasilan_orangtua\":\"1000000\",\"alasan_permohonan\":\"penurunan ukt\"}', 2, '2025-10-05 06:08:15', '2025-10-05 13:10:17', 2, 'okeyy diterima ya!'),
(8, 'DOC-20251006-1335', 4, 2, 'pepindahan izin usaha', '{\"nama_usaha\":\"eka jaya\",\"jenis_usaha\":\"gas\",\"alamat_usaha\":\"kalimantan\",\"lama_usaha\":\"-4\"}', 2, '2025-10-06 06:55:06', '2025-10-06 13:55:53', 2, 'ok'),
(9, 'DOC-20251006-5399', 6, 1, '2', '{\"nama_anak\":\"2\",\"nama_sekolah\":\"2\",\"penghasilan_orangtua\":\"2\",\"alasan_permohonan\":\"2\"}', 2, '2025-10-06 09:16:11', '2025-10-06 19:25:01', 2, 'okey'),
(10, 'DOC-20251008-1686', 12, 1, 'Mendapatkan beasiswa', '{\"nama_anak\":\"andy trwininarko\",\"nama_sekolah\":\"universitas indonesia\",\"penghasilan_orangtua\":\"51000000\",\"alasan_permohonan\":\"meringankan ukt\"}', 2, '2025-10-08 13:52:42', '2025-10-08 20:54:43', 2, 'lulus sensor'),
(11, 'DOC-20251011-5440', 4, 3, 'Qui facilis commodo', '{\"alamat_asal\":\"tes\",\"alamat_tujuan\":\"tes\",\"lama_tinggal\":\"-1\",\"alasan_pindah\":\"tes\"}', 2, '2025-10-11 19:01:52', '2025-10-12 02:02:29', 2, 'okey'),
(12, 'DOC-20251013-7948', 4, 3, 'pindah rumah', '{\"alamat_asal\":\"bangka belitung\",\"alamat_tujuan\":\"kalimantan\",\"lama_tinggal\":\"2\",\"alasan_pindah\":\"pindah dinas\"}', 3, '2025-10-13 07:59:38', '2025-10-13 15:48:06', 2, 'ditolak ya'),
(13, 'DOC-20251013-9791', 4, 2, 't', '{\"nama_usaha\":\"t\",\"jenis_usaha\":\"t\",\"alamat_usaha\":\"t\",\"lama_usaha\":\"2\"}', 2, '2025-10-13 08:00:19', '2025-10-13 15:01:16', 2, 'ok'),
(14, 'DOC-20251013-2813', 12, 2, 'Izin pengerjaan proyek', '{\"nama_usaha\":\"PT. tri sakri\",\"jenis_usaha\":\"batu bara\",\"alamat_usaha\":\"sulawesi tengah blok m, no 20\",\"lama_usaha\":\"14\"}', 1, '2025-10-13 09:55:04', NULL, NULL, NULL),
(15, 'DOC-20251013-9838', 4, 1, 'bantuan ukt', '{\"nama_anak\":\"iqbal\",\"nama_sekolah\":\"kampus politeknik negeri batam\",\"penghasilan_orangtua\":\"50000000\",\"alasan_permohonan\":\"kurang mampu\"}', 3, '2025-10-13 13:58:16', '2025-10-13 20:59:08', 2, 'ditolak ya. kamu orang kaya');

-- --------------------------------------------------------

--
-- Table structure for table `t_pengguna`
--

CREATE TABLE `t_pengguna` (
  `id_pengguna` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `nik` varchar(16) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `role` enum('admin','petugas','warga') DEFAULT 'warga',
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `tanggal_daftar` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `t_pengguna`
--

INSERT INTO `t_pengguna` (`id_pengguna`, `username`, `password`, `nama_lengkap`, `nik`, `email`, `no_hp`, `alamat`, `role`, `status`, `tanggal_daftar`) VALUES
(1, 'admin', '$2y$10$7HiQaWM4a3hZTPTWJtvJR.wrucXb/vW7pfv5ijpM2Vx6VVBOmnOla', 'Administrator Sistem', '1234567890123456', 'admin@desa.id', '081234567890', NULL, 'admin', 'aktif', '2025-10-05 05:24:58'),
(2, 'petugas1', '$2y$10$7HiQaWM4a3hZTPTWJtvJR.wrucXb/vW7pfv5ijpM2Vx6VVBOmnOla', 'alif', '1234567890123457', 'petugas1@desa.id', '081234567891', NULL, 'petugas', 'aktif', '2025-10-05 05:24:58'),
(3, 'petugas2', '$2y$10$7HiQaWM4a3hZTPTWJtvJR.wrucXb/vW7pfv5ijpM2Vx6VVBOmnOla', 'Petugas Dua', '1234567890123458', 'petugas2@desa.id', '081234567892', NULL, 'petugas', 'aktif', '2025-10-05 05:24:58'),
(4, 'warga1', '$2y$10$7HiQaWM4a3hZTPTWJtvJR.wrucXb/vW7pfv5ijpM2Vx6VVBOmnOla', 'Budi Santoso', '1234567890123459', 'budi@email.com', '081234567893', 'Jl. Contoh No. 123, Jakarta', 'warga', 'aktif', '2025-10-05 05:24:58'),
(5, 'Dimas', '$2y$10$bQpBcVcfO9KXUOgMWLwjOeB.mVcReiC5Va7gOyXvN4o8eBsaQjhgy', 'Dimas Dwi Prasetiyo', '1234567891011234', 'tesss@gmail.com', '082287446410', 'Batam', 'warga', 'aktif', '2025-10-05 05:54:37'),
(6, 'rian', '$2y$10$xpU0mW59CJvZvGMR4QN3D.weSADf3NHAC2BXxgtv87ohi36N69I7e', 'rian', '2171061102070016', 'rian123@gmail.com', '082287440877', 'batam, sekupang', 'warga', 'aktif', '2025-10-05 06:06:23'),
(12, 'iqbal123', '$2y$10$caDiAh.BBja1lQG51KXbuuAXeRgkRpWnB.55TesEN6mdv2TOYY7He', 'iqbal kurniawan', '2171061102079982', 'iqbal123@gmail.com', '082287446498', 'batam, sekupang', 'warga', 'aktif', '2025-10-08 13:49:24'),
(14, 'Reyvon', '$2y$10$YuDfCF8ywoBt.4WZ7gVK2uKqWgiKiNPS.kF7vUZwtYj8Jbd30h9l.', 'Muhammad Reyvon Ali', '2171123006069010', 'muhammadreyvon06@gmail.com', '082162736023', 'Mandalay f no. 03', 'warga', 'aktif', '2025-10-13 14:28:34'),
(23, 'hypuqu', '$2y$10$VevALhk2eMOmjWG3.DE0A.DaP.vGUc724He.w2k4gZeYswWTpgp12', 'Atque nisi praesenti', '2171061102070343', 'vyfih@mailinator.com', '082299446434', NULL, 'petugas', 'aktif', '2025-10-14 09:45:05');

-- --------------------------------------------------------

--
-- Table structure for table `t_riwayat_status`
--

CREATE TABLE `t_riwayat_status` (
  `id_riwayat` int(11) NOT NULL,
  `id_pengajuan` int(11) NOT NULL,
  `id_status` int(11) NOT NULL,
  `catatan` text DEFAULT NULL,
  `tanggal_perubahan` timestamp NOT NULL DEFAULT current_timestamp(),
  `diubah_oleh` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `t_riwayat_status`
--

INSERT INTO `t_riwayat_status` (`id_riwayat`, `id_pengajuan`, `id_status`, `catatan`, `tanggal_perubahan`, `diubah_oleh`) VALUES
(1, 1, 1, 'Pengajuan dibuat', '2025-10-05 05:31:57', 4),
(2, 1, 2, 'ok', '2025-10-05 05:32:52', 2),
(3, 2, 1, 'Pengajuan dibuat', '2025-10-05 05:50:47', 4),
(4, 3, 1, 'Pengajuan dibuat', '2025-10-05 05:50:50', 4),
(5, 4, 1, 'Pengajuan dibuat', '2025-10-05 05:51:15', 4),
(6, 5, 1, 'Pengajuan dibuat', '2025-10-05 05:52:01', 4),
(7, 2, 2, 'oke', '2025-10-05 05:52:37', 2),
(8, 6, 1, 'Pengajuan dibuat', '2025-10-05 05:58:36', 5),
(9, 6, 2, 'ok', '2025-10-05 06:01:35', 2),
(10, 7, 1, 'Pengajuan dibuat', '2025-10-05 06:08:15', 6),
(11, 7, 2, 'okeyy diterima ya!', '2025-10-05 06:10:17', 2),
(12, 5, 3, 'ditolak!', '2025-10-05 06:10:34', 2),
(13, 8, 1, 'Pengajuan dibuat', '2025-10-06 06:55:06', 4),
(14, 8, 2, 'ok', '2025-10-06 06:55:53', 2),
(15, 9, 1, 'Pengajuan dibuat', '2025-10-06 09:16:11', 6),
(16, 9, 2, 'okey', '2025-10-06 12:25:01', 2),
(17, 10, 1, 'Pengajuan dibuat', '2025-10-08 13:52:42', 12),
(18, 10, 2, 'lulus sensor', '2025-10-08 13:54:43', 2),
(19, 11, 1, 'Pengajuan dibuat', '2025-10-11 19:01:52', 4),
(20, 11, 2, 'okey', '2025-10-11 19:02:29', 2),
(21, 12, 1, 'Pengajuan dibuat', '2025-10-13 07:59:38', 4),
(22, 13, 1, 'Pengajuan dibuat', '2025-10-13 08:00:19', 4),
(23, 13, 2, 'ok', '2025-10-13 08:01:16', 2),
(24, 12, 3, 'ditolak ya', '2025-10-13 08:26:03', 2),
(25, 12, 3, 'ditolak ya', '2025-10-13 08:26:52', 2),
(26, 12, 3, 'ditolak ya', '2025-10-13 08:27:06', 2),
(27, 12, 3, 'ditolak ya', '2025-10-13 08:27:08', 2),
(28, 12, 3, 'ditolak ya', '2025-10-13 08:27:14', 2),
(29, 12, 3, 'ditolak ya', '2025-10-13 08:27:20', 2),
(30, 12, 3, 'ditolak ya', '2025-10-13 08:27:21', 2),
(31, 12, 3, 'ditolak ya', '2025-10-13 08:27:24', 2),
(32, 12, 3, 'ditolak ya', '2025-10-13 08:27:25', 2),
(33, 12, 3, 'ditolak ya', '2025-10-13 08:27:26', 2),
(34, 12, 3, 'ditolak ya', '2025-10-13 08:27:40', 2),
(35, 12, 3, 'ditolak ya', '2025-10-13 08:29:11', 2),
(36, 12, 3, 'ditolak ya', '2025-10-13 08:29:29', 2),
(37, 12, 3, 'ditolak ya', '2025-10-13 08:29:40', 2),
(38, 12, 3, 'ditolak ya', '2025-10-13 08:30:15', 2),
(39, 12, 3, 'ditolak ya', '2025-10-13 08:48:06', 2),
(40, 4, 3, 'periksa kembali berkas nya', '2025-10-13 08:48:26', 2),
(41, 3, 3, 'cek lagi yaa...', '2025-10-13 08:51:43', 2),
(42, 14, 1, 'Pengajuan dibuat', '2025-10-13 09:55:04', 12),
(43, 15, 1, 'Pengajuan dibuat', '2025-10-13 13:58:16', 4),
(44, 15, 3, 'ditolak ya. kamu orang kaya', '2025-10-13 13:59:08', 2);

-- --------------------------------------------------------

--
-- Table structure for table `t_status_pengajuan`
--

CREATE TABLE `t_status_pengajuan` (
  `id_status` int(11) NOT NULL,
  `nama_status` varchar(50) NOT NULL,
  `warna_badge` varchar(20) DEFAULT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `t_status_pengajuan`
--

INSERT INTO `t_status_pengajuan` (`id_status`, `nama_status`, `warna_badge`, `keterangan`) VALUES
(1, 'Pending', 'warning', 'Menunggu validasi petugas'),
(2, 'Disetujui', 'success', 'Pengajuan disetujui'),
(3, 'Ditolak', 'danger', 'Pengajuan ditolak');

-- --------------------------------------------------------

--
-- Table structure for table `t_surat_terbit`
--

CREATE TABLE `t_surat_terbit` (
  `id_surat` int(11) NOT NULL,
  `id_pengajuan` int(11) NOT NULL,
  `nomor_surat` varchar(50) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `tanggal_terbit` timestamp NOT NULL DEFAULT current_timestamp(),
  `diterbitkan_oleh` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `t_surat_terbit`
--

INSERT INTO `t_surat_terbit` (`id_surat`, `id_pengajuan`, `nomor_surat`, `file_path`, `tanggal_terbit`, `diterbitkan_oleh`) VALUES
(1, 1, '575/SKD/2025/10', 'surat_1_1759642372.pdf', '2025-10-05 05:32:52', 2),
(2, 2, '594/SKTM/2025/10', 'surat_2_1759643557.pdf', '2025-10-05 05:52:37', 2),
(3, 6, '918/SKD/2025/10', 'surat_6_1759644095.pdf', '2025-10-05 06:01:35', 2),
(4, 7, '389/SKTM/2025/10', 'surat_7_1759644617.pdf', '2025-10-05 06:10:17', 2),
(5, 8, '185/SKU/2025/10', 'surat_8_1759733753.pdf', '2025-10-06 06:55:53', 2),
(6, 9, '312/SKTM/2025/10', 'surat_9_1759753501.pdf', '2025-10-06 12:25:01', 2),
(7, 10, '794/SKTM/2025/10', 'surat_10_1759931683.pdf', '2025-10-08 13:54:43', 2),
(8, 11, '577/SKD/2025/10', 'surat_11_1760209349.pdf', '2025-10-11 19:02:29', 2),
(9, 13, '965/SKU/2025/10', 'surat_13_1760342476.pdf', '2025-10-13 08:01:16', 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `t_berkas_dokumen`
--
ALTER TABLE `t_berkas_dokumen`
  ADD PRIMARY KEY (`id_berkas`),
  ADD KEY `id_pengajuan` (`id_pengajuan`);

--
-- Indexes for table `t_jenis_dokumen`
--
ALTER TABLE `t_jenis_dokumen`
  ADD PRIMARY KEY (`id_jenis`);

--
-- Indexes for table `t_pengajuan`
--
ALTER TABLE `t_pengajuan`
  ADD PRIMARY KEY (`id_pengajuan`),
  ADD UNIQUE KEY `nomor_pengajuan` (`nomor_pengajuan`),
  ADD KEY `id_pengguna` (`id_pengguna`),
  ADD KEY `id_jenis` (`id_jenis`),
  ADD KEY `id_status` (`id_status`),
  ADD KEY `validasi_oleh` (`validasi_oleh`);

--
-- Indexes for table `t_pengguna`
--
ALTER TABLE `t_pengguna`
  ADD PRIMARY KEY (`id_pengguna`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `nik` (`nik`);

--
-- Indexes for table `t_riwayat_status`
--
ALTER TABLE `t_riwayat_status`
  ADD PRIMARY KEY (`id_riwayat`),
  ADD KEY `id_pengajuan` (`id_pengajuan`),
  ADD KEY `id_status` (`id_status`),
  ADD KEY `diubah_oleh` (`diubah_oleh`);

--
-- Indexes for table `t_status_pengajuan`
--
ALTER TABLE `t_status_pengajuan`
  ADD PRIMARY KEY (`id_status`);

--
-- Indexes for table `t_surat_terbit`
--
ALTER TABLE `t_surat_terbit`
  ADD PRIMARY KEY (`id_surat`),
  ADD UNIQUE KEY `nomor_surat` (`nomor_surat`),
  ADD KEY `id_pengajuan` (`id_pengajuan`),
  ADD KEY `diterbitkan_oleh` (`diterbitkan_oleh`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `t_berkas_dokumen`
--
ALTER TABLE `t_berkas_dokumen`
  MODIFY `id_berkas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `t_jenis_dokumen`
--
ALTER TABLE `t_jenis_dokumen`
  MODIFY `id_jenis` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `t_pengajuan`
--
ALTER TABLE `t_pengajuan`
  MODIFY `id_pengajuan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `t_pengguna`
--
ALTER TABLE `t_pengguna`
  MODIFY `id_pengguna` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `t_riwayat_status`
--
ALTER TABLE `t_riwayat_status`
  MODIFY `id_riwayat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `t_status_pengajuan`
--
ALTER TABLE `t_status_pengajuan`
  MODIFY `id_status` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `t_surat_terbit`
--
ALTER TABLE `t_surat_terbit`
  MODIFY `id_surat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `t_berkas_dokumen`
--
ALTER TABLE `t_berkas_dokumen`
  ADD CONSTRAINT `t_berkas_dokumen_ibfk_1` FOREIGN KEY (`id_pengajuan`) REFERENCES `t_pengajuan` (`id_pengajuan`);

--
-- Constraints for table `t_pengajuan`
--
ALTER TABLE `t_pengajuan`
  ADD CONSTRAINT `t_pengajuan_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `t_pengguna` (`id_pengguna`),
  ADD CONSTRAINT `t_pengajuan_ibfk_2` FOREIGN KEY (`id_jenis`) REFERENCES `t_jenis_dokumen` (`id_jenis`),
  ADD CONSTRAINT `t_pengajuan_ibfk_3` FOREIGN KEY (`id_status`) REFERENCES `t_status_pengajuan` (`id_status`),
  ADD CONSTRAINT `t_pengajuan_ibfk_4` FOREIGN KEY (`validasi_oleh`) REFERENCES `t_pengguna` (`id_pengguna`);

--
-- Constraints for table `t_riwayat_status`
--
ALTER TABLE `t_riwayat_status`
  ADD CONSTRAINT `t_riwayat_status_ibfk_1` FOREIGN KEY (`id_pengajuan`) REFERENCES `t_pengajuan` (`id_pengajuan`),
  ADD CONSTRAINT `t_riwayat_status_ibfk_2` FOREIGN KEY (`id_status`) REFERENCES `t_status_pengajuan` (`id_status`),
  ADD CONSTRAINT `t_riwayat_status_ibfk_3` FOREIGN KEY (`diubah_oleh`) REFERENCES `t_pengguna` (`id_pengguna`);

--
-- Constraints for table `t_surat_terbit`
--
ALTER TABLE `t_surat_terbit`
  ADD CONSTRAINT `t_surat_terbit_ibfk_1` FOREIGN KEY (`id_pengajuan`) REFERENCES `t_pengajuan` (`id_pengajuan`),
  ADD CONSTRAINT `t_surat_terbit_ibfk_2` FOREIGN KEY (`diterbitkan_oleh`) REFERENCES `t_pengguna` (`id_pengguna`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
