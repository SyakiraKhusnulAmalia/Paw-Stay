<?php
require_once 'koneksi.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: add-booking.php');
    exit;
}

// ── Pemilik = akun yang sedang login ──
$id_pemilik = (int) $_SESSION['id_pemilik'];

// ── Ambil data dari form ──
$petName      = trim($_POST['petName']      ?? '');
$petSpecies   = trim($_POST['petSpecies']   ?? '');
$petBreed     = trim($_POST['petBreed']     ?? '');
$petAge       = trim($_POST['petAge']       ?? '');
$petGender    = trim($_POST['petGender']    ?? '');
$petWeight    = trim($_POST['petWeight']    ?? '');
$petNotes     = trim($_POST['petNotes']     ?? '');

$checkIn      = trim($_POST['checkIn']      ?? '');
$checkOut     = trim($_POST['checkOut']     ?? '');
$cageRoom     = trim($_POST['cageRoom']     ?? '');
$packageType  = trim($_POST['packageType']  ?? '');

$errors = [];

if ($petName === '')     $errors[] = 'Nama hewan wajib diisi.';
if ($petSpecies === '')  $errors[] = 'Jenis hewan wajib dipilih.';
if ($checkIn === '')     $errors[] = 'Tanggal masuk wajib diisi.';
if ($checkOut === '')    $errors[] = 'Tanggal pulang wajib diisi.';
if ($packageType === '') $errors[] = 'Paket perawatan wajib dipilih.';

if (!empty($errors)) {
    $_SESSION['booking_errors'] = $errors;
    header('Location: add-booking.php');
    exit;
}

// ── Konversi umur ──
$umur_bulan = null;
if ($petAge !== '') {
    if (preg_match('/(\d+)\s*tahun/i', $petAge, $m))       $umur_bulan = (int)$m[1] * 12;
    elseif (preg_match('/(\d+)\s*bulan/i', $petAge, $m))   $umur_bulan = (int)$m[1];
    elseif (is_numeric($petAge))                            $umur_bulan = (int)$petAge;
}

// ── Konversi berat ──
$berat = null;
if ($petWeight !== '') {
    preg_match('/[\d.]+/', $petWeight, $m);
    $berat = isset($m[0]) ? (float)$m[0] : null;
}

// ── Keterangan hewan ──
$keterangan = '';
if ($petGender !== '') $keterangan .= 'Kelamin: ' . $petGender . '. ';
if ($petNotes  !== '') $keterangan .= $petNotes;
$keterangan = trim($keterangan) ?: null;

// ── Insert hewan ──
dbExecute(
    "INSERT INTO hewan (id_pemilik, nama_hewan, jenis_hewan, ras, umur, berat, keterangan, created_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
    [$id_pemilik, $petName, $petSpecies, $petBreed ?: null, $umur_bulan, $berat, $keterangan]
);
$id_hewan = (int) dbLastInsertId();

// ── Ambil petugas aktif ──
$petugas = dbFetchOne("SELECT id_petugas FROM petugas WHERE status = 'Aktif' LIMIT 1");
if (!$petugas) {
    $_SESSION['booking_errors'] = ['Belum ada petugas aktif di sistem.'];
    header('Location: add-booking.php');
    exit;
}
$id_petugas = $petugas['id_petugas'];

// ── Bersihkan kode kandang ──
$kandang = null;
if ($cageRoom !== '') {
    preg_match('/^([A-Z]-\d+)/', $cageRoom, $m);
    $kandang = $m[1] ?? $cageRoom;
}

// ── Insert penitipan ──
dbExecute(
    "INSERT INTO penitipan (id_hewan, id_petugas, tanggal_masuk, tanggal_keluar, kandang, status, catatan, created_at)
     VALUES (?, ?, ?, ?, ?, 'Aktif', ?, NOW())",
    [$id_hewan, $id_petugas, $checkIn, $checkOut ?: null, $kandang, null]
);
$id_penitipan = (int) dbLastInsertId();

// ── Hitung total tagihan & insert 1 baris tagihan awal dengan status Belum Lunas ──
// jumlah = total tagihan yang harus dibayar, belum ada uang masuk sama sekali
$tarifMap = ['Kucing'=>75000,'Anjing'=>100000,'Kelinci'=>60000,'Burung'=>50000,'Lainnya'=>75000];
$tarif_per_hari = $tarifMap[$petSpecies] ?? 75000;
$tgl_in  = new DateTime($checkIn);
$tgl_out = $checkOut ? new DateTime($checkOut) : (clone $tgl_in)->modify('+1 day');
$jumlah_hari    = max(1, (int)$tgl_in->diff($tgl_out)->days);
$total_tagihan  = $jumlah_hari * $tarif_per_hari;

// 1 baris = tagihan keseluruhan, status Belum Lunas, uang_masuk = 0 (dicatat nanti via form bayar)
dbExecute(
    "INSERT INTO pembayaran (id_penitipan, tanggal_bayar, jumlah, metode, status, keterangan, created_at)
     VALUES (?, ?, ?, 'Tunai', 'Belum Lunas', ?, NOW())",
    [
        $id_penitipan,
        $checkIn,
        $total_tagihan,
        "Tagihan {$jumlah_hari} hari ({$petSpecies}) @ Rp " . number_format($tarif_per_hari, 0, ',', '.') . "/hari. Belum ada pembayaran.",
    ]
);

header('Location: bookings.php?success=1');
exit;
