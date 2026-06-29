<?php
require_once 'koneksi.php';
requireLogin();
$idPemilik = (int) $_SESSION['id_pemilik'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: payments.php');
    exit;
}

$id_penitipan       = (int)($_POST['id_penitipan']       ?? 0);
$id_tagihan         = (int)($_POST['id_tagihan']          ?? 0);
$total_tagihan      = (float)($_POST['total_tagihan']     ?? 0);
$total_dibayar_lama = (float)($_POST['total_dibayar_lama'] ?? 0);
$tanggal_bayar      = trim($_POST['tanggal_bayar']        ?? '');
$jumlah             = (float)($_POST['jumlah']            ?? 0);
$metode             = trim($_POST['metode']               ?? '');
$keterangan         = trim($_POST['keterangan']           ?? '');

// ── Pastikan titipan ini benar-benar milik pemilik yang login ──
$milikSendiri = dbFetchOne("
    SELECT pt.id_penitipan
    FROM penitipan pt
    JOIN hewan h ON h.id_hewan = pt.id_hewan
    WHERE pt.id_penitipan = ? AND h.id_pemilik = ?
", [$id_penitipan, $idPemilik]);

if (!$milikSendiri) {
    header('Location: payments.php');
    exit;
}

$errors = [];

if ($id_penitipan <= 0)   $errors[] = 'ID penitipan tidak valid.';
if ($tanggal_bayar === '') $errors[] = 'Tanggal bayar wajib diisi.';
if ($jumlah <= 0)          $errors[] = 'Jumlah bayar harus lebih dari 0.';
if ($metode === '')        $errors[] = 'Metode pembayaran wajib dipilih.';

// Pastikan tidak melebihi sisa tagihan
$sisa = $total_tagihan - $total_dibayar_lama;
if ($jumlah > $sisa + 0.01) { // toleransi pembulatan
    $errors[] = 'Jumlah bayar melebihi sisa tagihan (Rp ' . number_format($sisa, 0, ',', '.') . ').';
}

if (!empty($errors)) {
    $_SESSION['payment_errors'] = $errors;
    header("Location: add-payment.php?id_penitipan={$id_penitipan}");
    exit;
}

// Hitung total sesudah pembayaran ini
$total_baru = $total_dibayar_lama + $jumlah;

// Tentukan status baru
if ($total_baru >= $total_tagihan - 0.01) {
    $status_baru = 'Lunas';
} elseif ($total_baru > 0) {
    $status_baru = 'DP';
} else {
    $status_baru = 'Belum Lunas';
}

// 1. UPDATE baris tagihan awal: update status-nya agar mencerminkan kondisi terkini
if ($id_tagihan > 0) {
    dbExecute(
        "UPDATE pembayaran SET status = ? WHERE id_pembayaran = ?",
        [$status_baru, $id_tagihan]
    );
}

// 2. INSERT baris transaksi pembayaran baru (uang yang masuk)
dbExecute(
    "INSERT INTO pembayaran (id_penitipan, tanggal_bayar, jumlah, metode, status, keterangan, created_at)
     VALUES (?, ?, ?, ?, ?, ?, NOW())",
    [
        $id_penitipan,
        $tanggal_bayar,
        $jumlah,
        $metode,
        $status_baru,
        $keterangan ?: "Pembayaran masuk via {$metode}.",
    ]
);

header("Location: add-payment.php?id_penitipan={$id_penitipan}&bayar_success=1");
exit;
