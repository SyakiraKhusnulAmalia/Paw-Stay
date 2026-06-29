<?php
session_start();
require_once 'koneksi.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

$user = dbFetchOne("
    SELECT *
    FROM pemilik
    WHERE username = ?
", [$username]);

if (!$user || !password_verify($password, $user['password'])) {
    die("Username atau password salah");
}

$_SESSION['id_pemilik']   = $user['id_pemilik'];
$_SESSION['nama_pemilik'] = $user['nama_pemilik'];

header("Location: index.php");
exit;
