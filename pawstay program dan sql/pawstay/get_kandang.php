<?php
require_once 'koneksi.php';
requireLogin();

// Daftar semua kandang per jenis hewan
$semuaKandang = [
    'Anjing' => ['A-01', 'A-02', 'A-03'],
    'Kucing'  => ['B-01', 'B-02', 'B-03'],
    'Burung'  => ['C-01', 'C-02'],
];

$species   = $_GET['species']   ?? '';
$checkIn   = $_GET['checkIn']   ?? '';
$checkOut  = $_GET['checkOut']  ?? '';

// Validasi input
if (!isset($semuaKandang[$species]) || !$checkIn || !$checkOut) {
    echo json_encode(['error' => 'Parameter tidak lengkap']);
    exit;
}

// Ambil kandang yang sudah terpakai pada rentang tanggal tersebut
// Overlap: booking lain aktif yang tanggalnya overlap dengan checkIn–checkOut baru
$terpakai = dbFetchAll(
    "SELECT kandang FROM penitipan
     WHERE kandang LIKE ?
       AND status = 'Aktif'
       AND tanggal_masuk  < ?
       AND tanggal_keluar > ?",
    [$semuaKandang[$species][0][0] . '%', $checkOut, $checkIn]
    // misal species=Anjing → LIKE 'A%'
);

$terpakaiList = array_column($terpakai, 'kandang');

// Susun response
$result = [];
foreach ($semuaKandang[$species] as $kode) {
    $result[] = [
        'kode'      => $kode,
        'tersedia'  => !in_array($kode, $terpakaiList),
    ];
}

header('Content-Type: application/json');
echo json_encode($result);