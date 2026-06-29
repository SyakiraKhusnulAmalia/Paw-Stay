<?php
require_once 'koneksi.php';
requireLogin();
$namaUser = namaUser();
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Titipan Saya | PawStay</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="admin-shell">
  <div class="sidebar-backdrop"></div>

  <aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
      <a class="brand-mark" href="index.php">
        <span class="brand-icon">🐾</span>
        <span class="brand-copy"><span class="brand-title">PawStay</span><span class="brand-subtitle">Penitipan Hewan</span></span>
      </a>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-section-label">Menu</div>
      <a class="nav-link" href="index.php"><span class="nav-icon"><i class="bi bi-speedometer2"></i></span><span class="nav-text">Dashboard</span></a>
      <a class="nav-link active" href="bookings.php"><span class="nav-icon"><i class="bi bi-calendar-check"></i></span><span class="nav-text">Titipan Saya</span></a>
      <a class="nav-link" href="add-booking.php"><span class="nav-icon"><i class="bi bi-plus-circle"></i></span><span class="nav-text">Tambah Titipan</span></a>
      <a class="nav-link" href="payments.php"><span class="nav-icon"><i class="bi bi-credit-card"></i></span><span class="nav-text">Pembayaran</span></a>
      <a class="nav-link" href="profile.php"><span class="nav-icon"><i class="bi bi-person"></i></span><span class="nav-text">Profil</span></a>
    </nav>
    <div class="sidebar-user">
      <div class="avatar-placeholder avatar-md">👤</div>
      <div><strong><?= $namaUser ?></strong><small>Pemilik</small></div>
    </div>
    <div class="sidebar-footer"><span class="status-dot"></span><span>Sistem berjalan normal</span></div>
  </aside>

  <div class="admin-main">
    <nav class="admin-navbar">
      <button class="sidebar-toggle" data-sidebar-toggle><span></span><span></span><span></span></button>
      <input class="search-input ms-3" type="search" placeholder="Cari titipan…" style="flex:1;max-width:300px">
      <div class="navbar-actions">
        <button class="icon-button" data-theme-toggle><i class="bi bi-moon-stars" data-theme-icon></i></button>
        <div class="dropdown">
          <button class="profile-button dropdown-toggle">
            <div class="avatar-placeholder avatar-sm">👤</div>
            <span class="profile-name"><?= $namaUser ?></span>
          </button>
          <div class="dropdown-menu">
            <a class="dropdown-item" href="profile.php"><i class="bi bi-person-gear"></i> Profil Saya</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="login.php"><i class="bi bi-box-arrow-right"></i> Keluar</a>
          </div>
        </div>
      </div>
    </nav>

    <main class="dashboard-content">
      <div class="page-heading">
        <div class="page-heading-copy">
          <span class="page-icon"><i class="bi bi-calendar-check"></i></span>
          <div>
            <p class="eyebrow mb-1">Penitipan</p>
            <h1 style="font-size:22px;font-weight:700;margin-bottom:2px">Titipan Saya</h1>
            <p class="text-muted mb-0" style="font-size:13px">Lihat status penitipan hewan peliharaan Anda di PawStay.</p>
          </div>
        </div>
        <div class="heading-actions">
          <a class="btn btn-primary btn-sm" href="add-booking.php"><i class="bi bi-plus-lg"></i> Titipan Baru</a>
        </div>
      </div>

      <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success mb-3" style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:12px 16px;border-radius:8px;font-size:13px;">
          <i class="bi bi-check-circle"></i> Titipan berhasil ditambahkan!
        </div>
      <?php endif; ?>

      <div class="alert mb-3" style="background:#eff6ff;border:1px solid #93c5fd;color:#1e40af;padding:12px 16px;border-radius:8px;font-size:13px;">
        <i class="bi bi-info-circle"></i> Setelah mengisi formulir penitipan, harap melakukan pembayaran secara tunai di PawStay, lalu catat di halaman <a href="payments.php" style="color:#1e40af;text-decoration:underline;">Pembayaran</a>.
      </div>

      <?php
        $idPemilik = (int) $_SESSION['id_pemilik'];

        // Otomatis set Selesai kalau tanggal keluar sudah lewat
        dbExecute(
            "UPDATE penitipan pt
             JOIN hewan h ON h.id_hewan = pt.id_hewan
             SET pt.status = 'Selesai'
             WHERE pt.status = 'Aktif'
               AND pt.tanggal_keluar < CURDATE()
               AND h.id_pemilik = ?",
            [$idPemilik]
        );

        $bookings = dbFetchAll("
          SELECT
            pt.id_penitipan,
            pt.tanggal_masuk,
            pt.tanggal_keluar,
            pt.kandang,
            pt.status,
            pt.catatan,
            h.nama_hewan,
            h.jenis_hewan,
            h.ras,
            pm.nama_pemilik,
            pg.nama_petugas
          FROM penitipan pt
          JOIN hewan h    ON h.id_hewan    = pt.id_hewan
          JOIN pemilik pm ON pm.id_pemilik = h.id_pemilik
          JOIN petugas pg ON pg.id_petugas = pt.id_petugas
          WHERE h.id_pemilik = ?
          ORDER BY pt.id_penitipan DESC
        ", [$idPemilik]);
      ?>

      <div class="panel">
        <div class="panel-header" style="flex-wrap:wrap;gap:12px">
          <div>
            <h2 class="section-title h5 mb-1"><i class="bi bi-table"></i> Titipan Hewan Saya</h2>
            <p class="text-muted mb-0" style="font-size:13px">Klik filter di bawah untuk menyaring berdasarkan status.</p>
          </div>
          <input class="table-search" type="search" placeholder="Cari nama / hewan…" data-table-search="bookingsTable">
        </div>

        <div class="chip-tabs" data-table-target="bookingsTable">
          <button class="chip active" data-filter="all">Semua</button>
          <button class="chip" data-filter="aktif">Aktif</button>
          <button class="chip" data-filter="selesai">Selesai</button>
          <button class="chip" data-filter="batal">Dibatalkan</button>
        </div>

        <div class="table-responsive">
          <table id="bookingsTable">
            <thead>
              <tr>
                <th>ID</th>
                <th>Hewan</th>
                <th>Jenis</th>
                <th>Kandang</th>
                <th>Masuk</th>
                <th>Keluar</th>
                <th>Petugas</th>
                <th>Status</th>
                <th class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($bookings) === 0): ?>
              <tr>
                <td colspan="9" style="text-align:center;padding:40px;color:var(--text-muted);">
                  <i class="bi bi-inbox" style="font-size:32px;display:block;margin-bottom:8px;"></i>
                  Belum ada data titipan. <a href="add-booking.php">Tambah titipan baru</a>
                </td>
              </tr>
              <?php else: foreach ($bookings as $b):
                $spesiesEmoji = ['kucing'=>'🐱','anjing'=>'🐶','burung'=>'🐦','kelinci'=>'🐰','lainnya'=>'🐾'];
                $spesiesClass = ['kucing'=>'species-cat','anjing'=>'species-dog','burung'=>'species-bird','kelinci'=>'species-rabbit'];
                $key   = strtolower($b['jenis_hewan']);
                $emoji = $spesiesEmoji[$key] ?? '🐾';
                $spCls = $spesiesClass[$key] ?? '';
                $statusMap = [
                  'aktif'   => ['label'=>'Aktif',      'badge'=>'badge-success'],
                  'selesai' => ['label'=>'Selesai',     'badge'=>'badge-secondary'],
                  'batal'   => ['label'=>'Dibatalkan',  'badge'=>'badge-danger'],
                ];
                $st = $statusMap[strtolower($b['status'])] ?? ['label'=>$b['status'],'badge'=>''];
              ?>
              <tr data-status="<?= strtolower($b['status']) ?>">
                <td class="fw-semibold">#PS-<?= str_pad($b['id_penitipan'], 3, '0', STR_PAD_LEFT) ?></td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div class="avatar-placeholder avatar-sm"><?= $emoji ?></div>
                    <div>
                      <p class="fw-semibold mb-0"><?= htmlspecialchars($b['nama_hewan']) ?></p>
                      <p class="text-muted small mb-0"><?= htmlspecialchars($b['ras'] ?? '-') ?></p>
                    </div>
                  </div>
                </td>
                <td><span class="species-badge <?= $spCls ?>"><?= $emoji ?> <?= htmlspecialchars($b['jenis_hewan']) ?></span></td>
                <td><?= htmlspecialchars($b['kandang'] ?? '-') ?></td>
                <td><?= htmlspecialchars($b['tanggal_masuk']) ?></td>
                <td><?= $b['tanggal_keluar'] ? htmlspecialchars($b['tanggal_keluar']) : '-' ?></td>
                <td><?= htmlspecialchars($b['nama_petugas']) ?></td>
                <td><span class="badge <?= $st['badge'] ?>"><?= $st['label'] ?></span></td>
                <td class="text-end">
                  <a href="detail-booking.php?id=<?= $b['id_penitipan'] ?>" class="btn btn-light btn-sm">Detail</a>
                  <a href="add-payment.php?id_penitipan=<?= $b['id_penitipan'] ?>" class="btn btn-primary btn-sm"><i class="bi bi-credit-card"></i> Bayar</a>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </main>
    <footer class="admin-footer">
      <span>© 2026 PawStay — Aplikasi Manajemen Penitipan Hewan</span>
      <span>Titipan Saya</span>
    </footer>
  </div>
</div>
<script src="main.php"></script>
</body>
</html>