<?php

define('DB_HOST',    'localhost');
define('DB_NAME',    'paw_stay');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

// Mulai session sekali di sini, aman dari duplikasi
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOST, DB_NAME, DB_CHARSET
    );

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode([
        'status'  => 'error',
        'message' => 'Koneksi database gagal. Silakan hubungi administrator.',
        'detail'  => (ini_get('display_errors') ? $e->getMessage() : null),
    ]));
}

// ─── DB Helpers ──────────────────────────────────────────────────────────────

function dbFetchAll(string $sql, array $params = []): array
{
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function dbFetchOne(string $sql, array $params = []): array|false
{
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}

function dbExecute(string $sql, array $params = []): int
{
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

function dbLastInsertId(): string
{
    global $pdo;
    return $pdo->lastInsertId();
}

// ─── Auth Helpers ─────────────────────────────────────────────────────────────

/**
 * Pastikan pengguna sudah login. Jika belum, redirect ke login.php.
 */
function requireLogin(): void
{
    if (empty($_SESSION['id_pemilik'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Ambil nama pengguna yang sedang login (sudah di-escape untuk HTML).
 */
function namaUser(): string
{
    return htmlspecialchars($_SESSION['nama_pemilik'] ?? 'Pengguna', ENT_QUOTES, 'UTF-8');
}

/**
 * Pastikan petugas sudah login. Jika belum, redirect ke login-petugas.php.
 */
function requireLoginPetugas(): void
{
    if (empty($_SESSION['id_petugas'])) {
        header('Location: login-petugas.php');
        exit;
    }
}

/**
 * Ambil nama petugas yang sedang login (sudah di-escape untuk HTML).
 */
function namaPetugas(): string
{
    return htmlspecialchars($_SESSION['nama_petugas'] ?? 'Petugas', ENT_QUOTES, 'UTF-8');
}
