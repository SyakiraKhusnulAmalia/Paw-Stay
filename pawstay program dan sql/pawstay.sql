-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql112.infinityfree.com
-- Generation Time: Jun 20, 2026 at 04:51 PM
-- Server version: 11.4.12-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_42231329_pawstay`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(10) UNSIGNED NOT NULL,
  `nama_admin` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `nama_admin`, `username`, `password`, `email`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'admin', '$2y$10$mmmxGv4EQqBjQOoFD9KJj.tlc6Eu8sylrD27fkq5tFRQtj.fbzJmi', 'admin@pawstay.com', '2026-06-21 00:04:01', '2026-06-21 00:07:40');

-- --------------------------------------------------------

--
-- Table structure for table `hewan`
--

CREATE TABLE `hewan` (
  `id_hewan` int(10) UNSIGNED NOT NULL,
  `id_pemilik` int(10) UNSIGNED NOT NULL,
  `nama_hewan` varchar(100) NOT NULL,
  `jenis_hewan` varchar(50) NOT NULL,
  `ras` varchar(100) DEFAULT NULL,
  `umur` int(11) DEFAULT NULL,
  `berat` decimal(5,2) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hewan`
--

INSERT INTO `hewan` (`id_hewan`, `id_pemilik`, `nama_hewan`, `jenis_hewan`, `ras`, `umur`, `berat`, `keterangan`, `foto`, `created_at`, `updated_at`) VALUES
(5, 4, 'Judith', 'Hamster', 'Campbell', 6, '200.00', 'Kelamin: Jantan. -', NULL, '2026-06-21 00:12:17', '2026-06-21 00:12:17'),
(6, 4, 'pipi', 'Anjing', 'golden retiever', 3, '5.00', 'Kelamin: Jantan. Tidak ada', NULL, '2026-06-20 12:50:14', '2026-06-20 12:50:14');

-- --------------------------------------------------------

--
-- Table structure for table `kondisi_hewan`
--

CREATE TABLE `kondisi_hewan` (
  `id_kondisi` int(10) UNSIGNED NOT NULL,
  `id_penitipan` int(10) UNSIGNED NOT NULL,
  `id_petugas` int(10) UNSIGNED NOT NULL,
  `tanggal_catat` datetime NOT NULL DEFAULT current_timestamp(),
  `kondisi` enum('Sehat','Kurang Sehat','Sakit','Perlu Perhatian Khusus') NOT NULL DEFAULT 'Sehat',
  `nafsu_makan` enum('Baik','Menurun','Tidak Mau Makan') DEFAULT 'Baik',
  `catatan` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` int(10) UNSIGNED NOT NULL,
  `id_penitipan` int(10) UNSIGNED NOT NULL,
  `tanggal_bayar` date NOT NULL,
  `jumlah` decimal(12,2) NOT NULL,
  `metode` varchar(30) NOT NULL DEFAULT 'Tunai',
  `status` enum('Lunas','DP','Belum Lunas') NOT NULL DEFAULT 'Belum Lunas',
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`id_pembayaran`, `id_penitipan`, `tanggal_bayar`, `jumlah`, `metode`, `status`, `keterangan`, `created_at`, `updated_at`) VALUES
(8, 5, '2026-06-21', '375000.00', 'Tunai', 'Lunas', 'Tagihan 5 hari (Hamster) @ Rp 75.000/hari. Belum ada pembayaran.', '2026-06-21 00:12:17', '2026-06-21 00:13:05'),
(9, 5, '2026-06-20', '375000.00', 'Transfer', 'Lunas', 'Pembayaran masuk via Transfer.', '2026-06-21 00:13:05', '2026-06-21 00:13:05'),
(10, 6, '2026-06-21', '200000.00', 'Tunai', 'Lunas', 'Tagihan 2 hari (Anjing) @ Rp 100.000/hari. Belum ada pembayaran.', '2026-06-20 12:50:14', '2026-06-20 12:50:39'),
(11, 6, '2026-06-20', '200000.00', 'QRIS', 'Lunas', 'pelunasan', '2026-06-20 12:50:39', '2026-06-20 12:50:39');

-- --------------------------------------------------------

--
-- Table structure for table `pemilik`
--

CREATE TABLE `pemilik` (
  `id_pemilik` int(10) UNSIGNED NOT NULL,
  `nama_pemilik` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `no_hp` varchar(20) NOT NULL,
  `alamat` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pemilik`
--

INSERT INTO `pemilik` (`id_pemilik`, `nama_pemilik`, `username`, `password`, `email`, `no_telepon`, `no_hp`, `alamat`, `foto`, `created_at`, `updated_at`) VALUES
(4, 'Hikari Kanata Mizuko', 'hikari123', '$2y$10$/Lm5FOa3G/lJ.hwF0ZB9HO/wnBVap7nj.fX3XCeljQqFBQJtBWxgm', 'hikamizuc@gmail.com', '087758554597', '087758554597', 'Jalan Raya Rungkut Madya, Gunung Anyar, Surabaya, Jawa Timur', NULL, '2026-06-21 00:11:09', '2026-06-21 00:11:09');

-- --------------------------------------------------------

--
-- Table structure for table `penitipan`
--

CREATE TABLE `penitipan` (
  `id_penitipan` int(10) UNSIGNED NOT NULL,
  `id_hewan` int(10) UNSIGNED NOT NULL,
  `id_petugas` int(10) UNSIGNED DEFAULT NULL,
  `tanggal_masuk` date NOT NULL,
  `tanggal_keluar` date DEFAULT NULL,
  `kandang` varchar(20) DEFAULT NULL,
  `status` enum('Menunggu Verifikasi','Aktif','Selesai','Batal') NOT NULL DEFAULT 'Menunggu Verifikasi',
  `catatan` text DEFAULT NULL,
  `terverifikasi_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penitipan`
--

INSERT INTO `penitipan` (`id_penitipan`, `id_hewan`, `id_petugas`, `tanggal_masuk`, `tanggal_keluar`, `kandang`, `status`, `catatan`, `terverifikasi_at`, `created_at`, `updated_at`) VALUES
(5, 5, 1, '2026-06-21', '2026-06-26', NULL, 'Aktif', NULL, NULL, '2026-06-21 00:12:17', '2026-06-21 00:12:17'),
(6, 6, 1, '2026-06-21', '2026-06-23', 'A-02', 'Aktif', NULL, NULL, '2026-06-20 12:50:14', '2026-06-20 12:50:14');

-- --------------------------------------------------------

--
-- Table structure for table `penjemputan`
--

CREATE TABLE `penjemputan` (
  `id_penjemputan` int(10) UNSIGNED NOT NULL,
  `id_penitipan` int(10) UNSIGNED NOT NULL,
  `id_petugas` int(10) UNSIGNED DEFAULT NULL,
  `tanggal_dijadwalkan` date DEFAULT NULL,
  `tanggal_jemput` datetime DEFAULT NULL,
  `dijemput_oleh` varchar(100) DEFAULT NULL,
  `status` enum('Dijadwalkan','Sudah Dijemput','Terlambat') NOT NULL DEFAULT 'Dijadwalkan',
  `catatan` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penjemputan`
--

INSERT INTO `penjemputan` (`id_penjemputan`, `id_penitipan`, `id_petugas`, `tanggal_dijadwalkan`, `tanggal_jemput`, `dijemput_oleh`, `status`, `catatan`, `created_at`, `updated_at`) VALUES
(4, 5, NULL, '2026-06-26', NULL, NULL, 'Dijadwalkan', NULL, '2026-06-21 00:14:39', '2026-06-21 00:14:39');

-- --------------------------------------------------------

--
-- Table structure for table `petugas`
--

CREATE TABLE `petugas` (
  `id_petugas` int(10) UNSIGNED NOT NULL,
  `nama_petugas` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `jabatan` varchar(50) DEFAULT 'Petugas Penitipan',
  `status` enum('Aktif','Nonaktif') NOT NULL DEFAULT 'Aktif',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `petugas`
--

INSERT INTO `petugas` (`id_petugas`, `nama_petugas`, `username`, `password`, `email`, `no_hp`, `jabatan`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Budi Santoso', 'budi.petugas', '$2y$10$mmmxGv4EQqBjQOoFD9KJj.tlc6Eu8sylrD27fkq5tFRQtj.fbzJmi', 'budi@pawstay.com', '081234567801', 'Petugas Penitipan', 'Aktif', '2026-06-21 00:04:01', '2026-06-21 00:07:40'),
(2, 'Sri Wahyuni', 'sri.petugas', '$2y$10$mmmxGv4EQqBjQOoFD9KJj.tlc6Eu8sylrD27fkq5tFRQtj.fbzJmi', 'sri@pawstay.com', '081234567802', 'Petugas Penitipan', 'Aktif', '2026-06-21 00:04:01', '2026-06-21 00:07:40'),
(3, 'Agus Prasetyo', 'agus.petugas', '$2y$10$mmmxGv4EQqBjQOoFD9KJj.tlc6Eu8sylrD27fkq5tFRQtj.fbzJmi', 'agus@pawstay.com', '081234567803', 'Petugas Kebersihan Kandang', 'Nonaktif', '2026-06-21 00:04:01', '2026-06-21 00:07:40');

-- --------------------------------------------------------

--
-- Table structure for table `tarif`
--

CREATE TABLE `tarif` (
  `id_tarif` int(10) UNSIGNED NOT NULL,
  `jenis_hewan` varchar(50) NOT NULL,
  `harga_per_hari` decimal(10,2) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tarif`
--

INSERT INTO `tarif` (`id_tarif`, `jenis_hewan`, `harga_per_hari`, `keterangan`, `created_at`, `updated_at`) VALUES
(1, 'Kucing', '75000.00', 'Termasuk pakan standar & pembersihan kandang harian', '2026-06-21 00:04:01', '2026-06-21 00:04:01'),
(2, 'Anjing', '100000.00', 'Termasuk pakan standar & jalan pagi/sore', '2026-06-21 00:04:01', '2026-06-21 00:04:01'),
(3, 'Kelinci', '60000.00', 'Termasuk pakan standar', '2026-06-21 00:04:01', '2026-06-21 00:04:01'),
(4, 'Burung', '50000.00', 'Termasuk pakan standar', '2026-06-21 00:04:01', '2026-06-21 00:04:01'),
(5, 'Lainnya', '75000.00', 'Tarif umum, dapat disesuaikan petugas/admin', '2026-06-21 00:04:01', '2026-06-21 00:04:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `uq_admin_username` (`username`);

--
-- Indexes for table `hewan`
--
ALTER TABLE `hewan`
  ADD PRIMARY KEY (`id_hewan`),
  ADD KEY `idx_hewan_pemilik` (`id_pemilik`);

--
-- Indexes for table `kondisi_hewan`
--
ALTER TABLE `kondisi_hewan`
  ADD PRIMARY KEY (`id_kondisi`),
  ADD KEY `idx_kondisi_penitipan` (`id_penitipan`),
  ADD KEY `idx_kondisi_petugas` (`id_petugas`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD KEY `idx_pembayaran_penitipan` (`id_penitipan`);

--
-- Indexes for table `pemilik`
--
ALTER TABLE `pemilik`
  ADD PRIMARY KEY (`id_pemilik`),
  ADD UNIQUE KEY `uq_pemilik_username` (`username`),
  ADD UNIQUE KEY `uq_pemilik_email` (`email`);

--
-- Indexes for table `penitipan`
--
ALTER TABLE `penitipan`
  ADD PRIMARY KEY (`id_penitipan`),
  ADD KEY `idx_penitipan_hewan` (`id_hewan`),
  ADD KEY `idx_penitipan_petugas` (`id_petugas`);

--
-- Indexes for table `penjemputan`
--
ALTER TABLE `penjemputan`
  ADD PRIMARY KEY (`id_penjemputan`),
  ADD KEY `idx_penjemputan_penitipan` (`id_penitipan`),
  ADD KEY `idx_penjemputan_petugas` (`id_petugas`);

--
-- Indexes for table `petugas`
--
ALTER TABLE `petugas`
  ADD PRIMARY KEY (`id_petugas`),
  ADD UNIQUE KEY `uq_petugas_username` (`username`);

--
-- Indexes for table `tarif`
--
ALTER TABLE `tarif`
  ADD PRIMARY KEY (`id_tarif`),
  ADD UNIQUE KEY `uq_tarif_jenis` (`jenis_hewan`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `hewan`
--
ALTER TABLE `hewan`
  MODIFY `id_hewan` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `kondisi_hewan`
--
ALTER TABLE `kondisi_hewan`
  MODIFY `id_kondisi` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `pemilik`
--
ALTER TABLE `pemilik`
  MODIFY `id_pemilik` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `penitipan`
--
ALTER TABLE `penitipan`
  MODIFY `id_penitipan` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `penjemputan`
--
ALTER TABLE `penjemputan`
  MODIFY `id_penjemputan` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `petugas`
--
ALTER TABLE `petugas`
  MODIFY `id_petugas` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tarif`
--
ALTER TABLE `tarif`
  MODIFY `id_tarif` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `hewan`
--
ALTER TABLE `hewan`
  ADD CONSTRAINT `fk_hewan_pemilik` FOREIGN KEY (`id_pemilik`) REFERENCES `pemilik` (`id_pemilik`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kondisi_hewan`
--
ALTER TABLE `kondisi_hewan`
  ADD CONSTRAINT `fk_kondisi_penitipan` FOREIGN KEY (`id_penitipan`) REFERENCES `penitipan` (`id_penitipan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kondisi_petugas` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `fk_pembayaran_penitipan` FOREIGN KEY (`id_penitipan`) REFERENCES `penitipan` (`id_penitipan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `penitipan`
--
ALTER TABLE `penitipan`
  ADD CONSTRAINT `fk_penitipan_hewan` FOREIGN KEY (`id_hewan`) REFERENCES `hewan` (`id_hewan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_penitipan_petugas` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `penjemputan`
--
ALTER TABLE `penjemputan`
  ADD CONSTRAINT `fk_penjemputan_penitipan` FOREIGN KEY (`id_penitipan`) REFERENCES `penitipan` (`id_penitipan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_penjemputan_petugas` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
