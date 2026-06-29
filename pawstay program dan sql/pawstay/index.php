<?php
session_start();
if (!isset($_SESSION['id_pemilik'])) {
    header("Location: login.php");
    exit;
}
require_once 'koneksi.php';

$idPemilik = (int) $_SESSION['id_pemilik'];

dbExecute(
    "UPDATE penitipan pt
     JOIN hewan h ON h.id_hewan = pt.id_hewan
     SET pt.status = 'Selesai'
     WHERE pt.status = 'Aktif'
       AND pt.tanggal_keluar < CURDATE()
       AND h.id_pemilik = ?",
    [$idPemilik]
);

$sedangDititip = dbFetchOne("
    SELECT COUNT(*) AS total
    FROM penitipan p
    JOIN hewan h ON h.id_hewan = p.id_hewan
    WHERE h.id_pemilik = ?
      AND LOWER(p.status) = 'aktif'
", [$idPemilik])['total'] ?? 0;

$masukHariIni = dbFetchOne("
    SELECT COUNT(*) AS total
    FROM penitipan p
    JOIN hewan h ON h.id_hewan = p.id_hewan
    WHERE h.id_pemilik = ?
      AND DATE(p.tanggal_masuk) = CURDATE()
", [$idPemilik])['total'] ?? 0;

$pulangHariIni = dbFetchOne("
    SELECT COUNT(*) AS total
    FROM penitipan p
    JOIN hewan h ON h.id_hewan = p.id_hewan
    WHERE h.id_pemilik = ?
      AND DATE(p.tanggal_keluar) = CURDATE()
", [$idPemilik])['total'] ?? 0;

$totalHewanSaya = dbFetchOne("
    SELECT COUNT(*) AS total
    FROM hewan
    WHERE id_pemilik = ?
", [$idPemilik])['total'] ?? 0;

$recentBookings = dbFetchAll("
    SELECT
        p.id_penitipan,
        p.tanggal_masuk,
        p.tanggal_keluar,
        p.status,
        h.nama_hewan,
        h.jenis_hewan,
        h.ras,
        pm.nama_pemilik
    FROM penitipan p
    JOIN hewan h  ON p.id_hewan  = h.id_hewan
    JOIN pemilik pm ON h.id_pemilik = pm.id_pemilik
    WHERE h.id_pemilik = ?
    ORDER BY p.created_at DESC
    LIMIT 5
", [$idPemilik]);

// Badge warna status
function statusBadge(string $status): string
{
    return match (strtolower($status)) {
        'aktif'   => 'badge-success',
        'selesai' => 'badge-secondary',
        'batal'   => 'badge-danger',
        default   => 'badge-warning',
    };
}

$namaUser = htmlspecialchars($_SESSION['nama_pemilik'] ?? 'Pengguna');
$initials = strtoupper(substr($namaUser, 0, 1));
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | PawStay</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* ── Avatar inisial ── */
        .avatar-initials {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--brand-primary, #f97316);
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            user-select: none;
        }

        /* ── Dropdown profil ── */
        .profile-dropdown {
            position: relative;
        }

        .profile-trigger {
            display: flex;
            align-items: center;
            gap: 8px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 8px;
            transition: background .15s;
            color: var(--text-body);
        }

        .profile-trigger:hover {
            background: var(--bg-hover, rgba(0, 0, 0, .06));
        }

        .profile-trigger .profile-name {
            font-size: 13px;
            font-weight: 600;
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .profile-trigger .bi-chevron-down {
            font-size: 11px;
            color: var(--text-muted);
            transition: transform .2s;
        }

        .profile-dropdown.open .bi-chevron-down {
            transform: rotate(180deg);
        }

        .profile-menu {
            display: none;
            position: absolute;
            right: 0;
            top: calc(100% + 8px);
            width: 200px;
            background: var(--bg-card, #fff);
            border: 1px solid var(--border-color, #e5e7eb);
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .10);
            z-index: 999;
            overflow: hidden;
        }

        .profile-dropdown.open .profile-menu {
            display: block;
        }

        .profile-menu-header {
            padding: 12px 14px 10px;
            border-bottom: 1px solid var(--border-color, #e5e7eb);
        }

        .profile-menu-header strong {
            display: block;
            font-size: 13px;
        }

        .profile-menu-header small {
            font-size: 11px;
            color: var(--text-muted);
        }

        .profile-menu a,
        .profile-menu button {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            padding: 9px 14px;
            font-size: 13px;
            color: var(--text-body);
            background: none;
            border: none;
            text-decoration: none;
            cursor: pointer;
            transition: background .12s;
        }

        .profile-menu a:hover,
        .profile-menu button:hover {
            background: var(--bg-hover, rgba(0, 0, 0, .05));
        }

        .profile-menu .menu-divider {
            height: 1px;
            background: var(--border-color, #e5e7eb);
            margin: 4px 0;
        }

        .profile-menu .logout-btn {
            color: #ef4444;
        }

        /* ── Tabel rapih ── */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            font-size: 11.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--text-muted);
            padding: 10px 12px;
            border-bottom: 1px solid var(--border-color, #e5e7eb);
            white-space: nowrap;
        }

        tbody td {
            font-size: 13px;
            padding: 11px 12px;
            border-bottom: 1px solid var(--border-color, #e5e7eb);
            vertical-align: middle;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background: var(--bg-hover, rgba(0, 0, 0, .025));
        }

        tbody td small {
            color: var(--text-muted);
            font-size: 11.5px;
        }

        /* ── Badge ── */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .badge-success {
            background: #dcfce7;
            color: #166534;
        }

        .badge-warning {
            background: #fef9c3;
            color: #854d0e;
        }

        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-secondary {
            background: #f1f5f9;
            color: #475569;
        }
    </style>
</head>

<body>
    <div class="admin-shell">
        <div class="sidebar-backdrop"></div>

        <!-- ── SIDEBAR ── -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <a class="brand-mark" href="index.php">
                    <span class="brand-icon">🐾</span>
                    <span class="brand-copy">
                        <span class="brand-title">PawStay</span>
                        <span class="brand-subtitle">Penitipan Hewan</span>
                    </span>
                </a>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-section-label">Menu</div>
                <a class="nav-link active" href="index.php"><span class="nav-icon"><i class="bi bi-speedometer2"></i></span><span class="nav-text">Dashboard</span></a>
                <a class="nav-link" href="bookings.php"><span class="nav-icon"><i class="bi bi-calendar-check"></i></span><span class="nav-text">Titipan Saya</span></a>
                <a class="nav-link" href="add-booking.php"><span class="nav-icon"><i class="bi bi-plus-circle"></i></span><span class="nav-text">Tambah Titipan</span></a>
                <a class="nav-link" href="payments.php"><span class="nav-icon"><i class="bi bi-credit-card"></i></span><span class="nav-text">Pembayaran</span></a>
                <a class="nav-link" href="profile.php"><span class="nav-icon"><i class="bi bi-person"></i></span><span class="nav-text">Profil</span></a>
            </nav>
            <div class="sidebar-user">
                <div class="avatar-initials"><?= $initials ?></div>
                <div>
                    <strong><?= $namaUser ?></strong>
                    <small>Pemilik</small>
                </div>
            </div>
            <div class="sidebar-footer">
                <span class="status-dot"></span>
                <span>Sistem berjalan normal</span>
            </div>
        </aside>

        <!-- ── MAIN ── -->
        <div class="admin-main">

            <!-- NAVBAR -->
            <nav class="admin-navbar">
                <button class="sidebar-toggle" data-sidebar-toggle>
                    <span></span><span></span><span></span>
                </button>

                <input class="search-input ms-3" type="search"
                    placeholder="Cari titipan, hewan, pelanggan…"
                    style="flex:1; max-width:320px">

                <div class="navbar-actions">
                    <!-- Ganti tema -->
                    <button class="icon-button" data-theme-toggle title="Ganti tema">
                        <i class="bi bi-moon-stars" data-theme-icon></i>
                    </button>

                    <!-- Notifikasi -->
                    <div class="dropdown">
                        <button class="icon-button" type="button">
                            <span class="notification-dot"></span>
                            <i class="bi bi-bell"></i>
                        </button>
                        <div class="dropdown-menu notification-menu">
                            <div class="dropdown-header">Notifikasi</div>
                            <a class="dropdown-item" href="bookings.php">
                                <span class="notification-title">Titipan baru masuk</span>
                                <span class="notification-time">5 menit lalu</span>
                            </a>
                            <a class="dropdown-item" href="bookings.php">
                                <span class="notification-title">Rocky (Anjing) selesai diperiksa</span>
                                <span class="notification-time">30 menit lalu</span>
                            </a>
                            <a class="dropdown-item" href="bookings.php">
                                <span class="notification-title">3 hewan dijadwalkan pulang hari ini</span>
                                <span class="notification-time">1 jam lalu</span>
                            </a>
                        </div>
                    </div>

                    <!-- Profil dropdown (ganti tombol Login) -->
                    <div class="profile-dropdown" id="profileDropdown">
                        <button class="profile-trigger" id="profileTrigger" type="button" aria-expanded="false">
                            <div class="avatar-initials"><?= $initials ?></div>
                            <span class="profile-name"><?= $namaUser ?></span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="profile-menu" id="profileMenu" role="menu">
                            <div class="profile-menu-header">
                                <strong><?= $namaUser ?></strong>
                                <small><?= htmlspecialchars($_SESSION['username'] ?? '') ?></small>
                            </div>
                            <a href="profile.php"><i class="bi bi-person"></i> Profil Saya</a>
                            <div class="menu-divider"></div>
                            <a href="logout.php" class="logout-btn"><i class="bi bi-box-arrow-right"></i> Keluar</a>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- CONTENT -->
            <main class="dashboard-content">

                <!-- Page heading -->
                <div class="page-heading">
                    <div class="page-heading-copy">
                        <span class="page-icon"><i class="bi bi-speedometer2"></i></span>
                        <div>
                            <p class="eyebrow mb-1">Ringkasan</p>
                            <h1 style="font-size:22px; font-weight:700; margin-bottom:2px">Dashboard</h1>
                            <p class="text-muted mb-0" style="font-size:13px">
                                Selamat datang, <?= $namaUser ?>! Pantau aktivitas penitipan hari ini.
                            </p>
                        </div>
                    </div>
                    <div class="heading-actions">
                        <a class="btn btn-outline btn-sm" href="bookings.php">
                            <i class="bi bi-list-ul"></i> Semua Titipan
                        </a>
                        <a class="btn btn-primary btn-sm" href="add-booking.php">
                            <i class="bi bi-plus-lg"></i> Titipan Baru
                        </a>
                    </div>
                </div>

                <!-- Metric cards -->
                <div class="row g-3 mt-1">
                    <div class="col-xl-3 col-md-6">
                        <article class="metric-card metric-primary">
                            <div class="metric-top">
                                <span class="metric-label">Sedang Dititip</span>
                                <span class="metric-icon"><i class="bi bi-house-heart"></i></span>
                            </div>
                            <div class="metric-value"><?= $sedangDititip ?></div>
                            <div class="metric-meta">
                                <span>hewan sedang dititip</span>
                            </div>
                        </article>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <article class="metric-card metric-success">
                            <div class="metric-top">
                                <span class="metric-label">Masuk Hari Ini</span>
                                <span class="metric-icon"><i class="bi bi-box-arrow-in-down"></i></span>
                            </div>
                            <div class="metric-value"><?= $masukHariIni ?></div>
                            <div class="metric-meta">
                                <span>check-in hari ini</span>
                            </div>
                        </article>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <article class="metric-card metric-warning">
                            <div class="metric-top">
                                <span class="metric-label">Pulang Hari Ini</span>
                                <span class="metric-icon"><i class="bi bi-box-arrow-right"></i></span>
                            </div>
                            <div class="metric-value"><?= $pulangHariIni ?></div>
                            <div class="metric-meta">
                                <span>siap dijemput</span>
                            </div>
                        </article>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <article class="metric-card metric-danger">
                            <div class="metric-top">
                                <span class="metric-label">Total Hewan Saya</span>
                                <span class="metric-icon"><i class="bi bi-heart"></i></span>
                            </div>
                            <div class="metric-value"><?= $totalHewanSaya ?></div>
                            <div class="metric-meta">
                                <span>terdaftar di PawStay</span>
                            </div>
                        </article>
                    </div>
                </div>

                <!-- Main panels -->
                <div class="row g-3 mt-1">

                    <!-- Tabel titipan terkini -->
                    <div class="col-xl-8">
                        <div class="panel">
                            <div class="panel-header">
                                <div>
                                    <h2 class="section-title h5 mb-1">
                                        <i class="bi bi-calendar-check"></i> Titipan Terkini Saya
                                    </h2>
                                    <p class="text-muted mb-0" style="font-size:13px">
                                        Aktivitas penitipan hewan Anda yang terbaru.
                                    </p>
                                </div>
                                <a class="btn btn-light btn-sm" href="bookings.php">Lihat Semua</a>
                            </div>
                            <div class="table-responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Hewan</th>
                                            <th>Spesies</th>
                                            <th>Masuk</th>
                                            <th>Pulang</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($recentBookings)): ?>
                                            <tr>
                                                <td colspan="5" style="text-align:center; padding:40px; color:var(--text-muted)">
                                                    <i class="bi bi-inbox" style="font-size:24px; display:block; margin-bottom:8px"></i>
                                                    Belum ada data penitipan.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($recentBookings as $r): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= htmlspecialchars($r['nama_hewan']) ?></strong>
                                                        <br>
                                                        <small><?= htmlspecialchars($r['ras'] ?? '-') ?></small>
                                                    </td>
                                                    <td><?= htmlspecialchars($r['jenis_hewan']) ?></td>
                                                    <td><?= date('d M Y', strtotime($r['tanggal_masuk'])) ?></td>
                                                    <td><?= date('d M Y', strtotime($r['tanggal_keluar'])) ?></td>
                                                    <td>
                                                        <span class="badge <?= statusBadge($r['status']) ?>">
                                                            <?= htmlspecialchars($r['status']) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom kanan -->
                    <div class="col-xl-4 d-flex" style="flex-direction:column; gap:16px">



                        <!-- Aktivitas terbaru -->
                        <div class="panel">
                            <h2 class="section-title h5 mb-3">
                                <i class="bi bi-activity"></i> Aktivitas Terbaru
                            </h2>
                            <div class="activity-list">
                                <?php if (empty($recentBookings)): ?>
                                    <p class="text-muted" style="font-size:13px">Belum ada aktivitas penitipan.</p>
                                <?php else: ?>
                                    <?php foreach (array_slice($recentBookings, 0, 4) as $r):
                                        $dotClass = match (strtolower($r['status'])) {
                                            'aktif'   => 'bg-primary',
                                            'selesai' => 'bg-success',
                                            'batal'   => 'bg-danger',
                                            default   => 'bg-warning',
                                        };
                                        $aktivitasLabel = match (strtolower($r['status'])) {
                                            'aktif'   => 'Sedang dititip',
                                            'selesai' => 'Penitipan selesai',
                                            'batal'   => 'Penitipan dibatalkan',
                                            default   => 'Status diperbarui',
                                        };
                                    ?>
                                        <div class="activity-item">
                                            <span class="activity-dot <?= $dotClass ?>"></span>
                                            <div>
                                                <p class="fw-semibold mb-1" style="font-size:13px"><?= htmlspecialchars($r['nama_hewan']) ?> &mdash; <?= $aktivitasLabel ?></p>
                                                <p class="text-muted small mb-0">Masuk <?= date('d M Y', strtotime($r['tanggal_masuk'])) ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <footer class="admin-footer">
                <span>© 2026 PawStay — Aplikasi Manajemen Penitipan Hewan</span>
                <span>Halaman Dashboard</span>
            </footer>
        </div>
    </div>

    <script src="main.php"></script>
    <script>
        // ── Profile dropdown toggle ──
        const profileDropdown = document.getElementById('profileDropdown');
        const profileTrigger = document.getElementById('profileTrigger');

        profileTrigger?.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = profileDropdown.classList.toggle('open');
            profileTrigger.setAttribute('aria-expanded', isOpen);
        });

        // Tutup kalau klik di luar
        document.addEventListener('click', () => {
            profileDropdown?.classList.remove('open');
            profileTrigger?.setAttribute('aria-expanded', 'false');
        });
    </script>
</body>

</html>