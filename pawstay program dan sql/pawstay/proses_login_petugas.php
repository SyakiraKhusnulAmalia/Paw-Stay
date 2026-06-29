<?php
session_start();
require_once 'koneksi.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

$petugas = dbFetchOne("
    SELECT *
    FROM petugas
    WHERE username = ?
", [$username]);

if (!$petugas || !password_verify($password, $petugas['password'])) {
    $_SESSION['login_petugas_error'] = 'Username atau password salah.';
    header('Location: login-petugas.php');
    exit;
}

if ($petugas['status'] !== 'Aktif') {
    $_SESSION['login_petugas_error'] = 'Akun petugas ini sedang nonaktif. Hubungi admin.';
    header('Location: login-petugas.php');
    exit;
}

$_SESSION['id_petugas']   = $petugas['id_petugas'];
$_SESSION['nama_petugas'] = $petugas['nama_petugas'];

header('Location: petugas-dashboard.php');
exit;
