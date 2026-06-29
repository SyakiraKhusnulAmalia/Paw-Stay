<?php
require_once 'koneksi.php';
requireLogin();
// Halaman data hewan ini menampilkan data seluruh pelanggan dan merupakan fitur admin,
// sehingga tidak tersedia untuk sisi pengguna.
header("Location: bookings.php");
exit;
