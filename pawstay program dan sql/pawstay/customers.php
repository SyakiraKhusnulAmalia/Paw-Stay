<?php
require_once 'koneksi.php';
requireLogin();
// Halaman daftar pelanggan adalah fitur admin dan tidak tersedia untuk sisi pengguna.
header("Location: index.php");
exit;
