<?php
/**
 * SCRIPT SEKALI PAKAI — Reset password semua akun dummy ke "password123"
 * dengan hash bcrypt yang BENAR (di-generate langsung di server Anda).
 *
 * Cara pakai:
 *  1. Taruh file ini di folder EASPEMWEB (sejajar dengan koneksi.php).
 *  2. Buka di browser: http://localhost/EASPEMWEB/reset-password-dummy.php
 *  3. Setelah berhasil, HAPUS file ini (jangan dibiarkan ada di server).
 */

require_once 'koneksi.php';

$passwordBaru = 'password123';
$hash = password_hash($passwordBaru, PASSWORD_BCRYPT);

echo "<pre>";
echo "Hash baru yang digenerate: " . $hash . "\n";
echo "Verifikasi hash: " . (password_verify($passwordBaru, $hash) ? 'VALID ✅' : 'INVALID ❌') . "\n\n";

// Update semua tabel yang punya kolom password
$tables = [
    'petugas' => 'id_petugas',
    'admin'   => 'id_admin',
    'pemilik' => 'id_pemilik',
];

foreach ($tables as $table => $pk) {
    try {
        $affected = dbExecute("UPDATE `$table` SET password = ?", [$hash]);
        echo "Tabel `$table`: $affected baris berhasil diupdate.\n";
    } catch (Exception $e) {
        echo "Tabel `$table`: gagal — " . $e->getMessage() . "\n";
    }
}

echo "\nSemua akun (petugas, admin, pemilik) sekarang passwordnya: $passwordBaru\n";
echo "</pre>";
echo "<p style='color:red;font-weight:bold'>PENTING: hapus file reset-password-dummy.php ini sekarang dari server Anda.</p>";
