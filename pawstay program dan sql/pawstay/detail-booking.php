<?php
require_once 'koneksi.php';
requireLogin();
$namaUser  = namaUser();
$idPemilik = (int) $_SESSION['id_pemilik'];

$id = (int) ($_GET['id'] ?? 0);
if (!$id) { header('Location: bookings.php'); exit; }

// Ambil data penitipan + hewan + petugas, pastikan milik pemilik yang login
$b = dbFetchOne("
    SELECT
        pt.id_penitipan, pt.tanggal_masuk, pt.tanggal_keluar,
        pt.kandang, pt.status, pt.catatan, pt.created_at,
        h.nama_hewan, h.jenis_hewan, h.ras, h.umur, h.berat, h.keterangan,
        pm.nama_pemilik, pm.no_telepon, pm.email, pm.alamat,
        pg.nama_petugas
    FROM penitipan pt
    JOIN hewan   h  ON h.id_hewan    = pt.id_hewan
    JOIN pemilik pm ON pm.id_pemilik = h.id_pemilik
    JOIN petugas pg ON pg.id_petugas = pt.id_petugas
    WHERE pt.id_penitipan = ? AND h.id_pemilik = ?
", [$id, $idPemilik]);

if (!$b) { header('Location: bookings.php'); exit; }

// Ambil data pembayaran
$payments = dbFetchAll("
    SELECT tanggal_bayar, jumlah, metode, status, keterangan
    FROM pembayaran
    WHERE id_penitipan = ?
    ORDER BY tanggal_bayar ASC
", [$id]);

// Hitung durasi
$tglIn  = new DateTime($b['tanggal_masuk']);
$tglOut = $b['tanggal_keluar'] ? new DateTime($b['tanggal_keluar']) : null;
$durasi = $tglOut ? (int)$tglIn->diff($tglOut)->days : '-';

// Status badge
$statusMap = [
    'aktif'   => ['label' => 'Aktif',      'badge' => 'badge-success'],
    'selesai' => ['label' => 'Selesai',     'badge' => 'badge-secondary'],
    'batal'   => ['label' => 'Dibatalkan',  'badge' => 'badge-danger'],
];
$st = $statusMap[strtolower($b['status'])] ?? ['label' => $b['status'], 'badge' => ''];

$spesiesEmoji = ['kucing'=>'🐱','anjing'=>'🐶','burung'=>'🐦','kelinci'=>'🐰','hamster'=>'🐹','reptil'=>'🦎'];
$emoji = $spesiesEmoji[strtolower($b['jenis_hewan'])] ?? '🐾';

$idLabel = '#PS-' . str_pad($id, 3, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detail Titipan <?= $idLabel ?> | PawStay</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="style.css">
  <style>
    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 13px; }
    .detail-grid dt { color: var(--text-secondary); font-weight: 500; }
    .detail-grid dd { font-weight: 600; margin: 0; }
    .info-block { background: var(--bg-body); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 14px 16px; margin-bottom: 0; }
    .section-label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--text-secondary); margin-bottom: 12px; }
    .stat-box { background: var(--bg-body); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; text-align: center; }
    .stat-box .stat-val { font-size: 22px; font-weight: 700; color: var(--brand-primary); }
    .stat-box .stat-lbl { font-size: 12px; color: var(--text-secondary); margin-top: 2px; }
    .pay-row { display: flex; justify-content: space-between; align-items: center; font-size: 13px; padding: 10px 0; border-bottom: 1px solid var(--border-color); }
    .pay-row:last-child { border-bottom: none; }
  </style>
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
          <span class="page-icon"><?= $emoji ?></span>
          <div>
            <p class="eyebrow mb-1">Penitipan / <?= $idLabel ?></p>
            <h1 style="font-size:22px;font-weight:700;margin-bottom:4px">
              <?= htmlspecialchars($b['nama_hewan']) ?>
              <span class="badge <?= $st['badge'] ?>" style="font-size:13px;vertical-align:middle"><?= $st['label'] ?></span>
            </h1>
            <p class="text-muted mb-0" style="font-size:13px">
              <?= htmlspecialchars($b['jenis_hewan']) ?>
              <?= $b['ras'] ? '· ' . htmlspecialchars($b['ras']) : '' ?>
              · Didaftarkan <?= date('d M Y', strtotime($b['created_at'])) ?>
            </p>
          </div>
        </div>
        <div class="d-flex gap-2">
          <a class="btn btn-outline btn-sm" href="bookings.php"><i class="bi bi-arrow-left"></i> Kembali</a>
          <a class="btn btn-primary btn-sm" href="add-payment.php?id_penitipan=<?= $id ?>"><i class="bi bi-credit-card"></i> Bayar</a>
        </div>
      </div>

      <!-- Stat boxes -->
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px">
        <div class="stat-box">
          <div class="stat-val"><?= $durasi !== '-' ? $durasi . ' hr' : '-' ?></div>
          <div class="stat-lbl">Durasi Menginap</div>
        </div>
        <div class="stat-box">
          <div class="stat-val"><?= htmlspecialchars($b['kandang'] ?? '-') ?></div>
          <div class="stat-lbl">Nomor Kandang</div>
        </div>
        <div class="stat-box">
          <div class="stat-val" style="font-size:15px"><?= htmlspecialchars($b['nama_petugas']) ?></div>
          <div class="stat-lbl">Petugas Perawat</div>
        </div>
      </div>

      <div class="row g-3">
        <!-- Kolom kiri -->
        <div class="col-xl-8" style="display:flex;flex-direction:column;gap:16px">

          <!-- Jadwal -->
          <div class="panel">
            <div class="section-label"><i class="bi bi-calendar3"></i> Jadwal Penitipan</div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;font-size:13px">
              <div class="info-block">
                <div style="color:var(--text-secondary);font-size:12px;margin-bottom:4px">Tanggal Masuk</div>
                <div style="font-weight:700"><?= date('d M Y', strtotime($b['tanggal_masuk'])) ?></div>
              </div>
              <div class="info-block">
                <div style="color:var(--text-secondary);font-size:12px;margin-bottom:4px">Tanggal Keluar</div>
                <div style="font-weight:700"><?= $b['tanggal_keluar'] ? date('d M Y', strtotime($b['tanggal_keluar'])) : '-' ?></div>
              </div>
              <div class="info-block">
                <div style="color:var(--text-secondary);font-size:12px;margin-bottom:4px">Kandang</div>
                <div style="font-weight:700"><?= htmlspecialchars($b['kandang'] ?? '-') ?></div>
              </div>
            </div>
            <?php if ($b['catatan']): ?>
            <div style="margin-top:12px;padding:10px 12px;background:var(--bg-body);border:1px solid var(--border-color);border-radius:var(--radius-sm);font-size:13px;">
              <span style="color:var(--text-secondary)">Catatan:</span> <?= htmlspecialchars($b['catatan']) ?>
            </div>
            <?php endif; ?>
          </div>

          <!-- Data Hewan -->
          <div class="panel">
            <div class="section-label"><i class="bi bi-heart"></i> Data Hewan</div>
            <dl class="detail-grid">
              <dt>Nama</dt>
              <dd><?= htmlspecialchars($b['nama_hewan']) ?></dd>
              <dt>Jenis</dt>
              <dd><?= $emoji ?> <?= htmlspecialchars($b['jenis_hewan']) ?></dd>
              <dt>Ras</dt>
              <dd><?= htmlspecialchars($b['ras'] ?? '-') ?></dd>
              <dt>Usia</dt>
              <dd><?php
                if ($b['umur']) {
                  $th = intdiv($b['umur'], 12);
                  $bl = $b['umur'] % 12;
                  echo $th > 0 ? "{$th} tahun" . ($bl > 0 ? " {$bl} bulan" : '') : "{$bl} bulan";
                } else { echo '-'; }
              ?></dd>
              <dt>Berat</dt>
              <dd><?= $b['berat'] ? $b['berat'] . ' kg' : '-' ?></dd>
              <dt>Keterangan</dt>
              <dd><?= htmlspecialchars($b['keterangan'] ?? '-') ?></dd>
            </dl>
          </div>

          <!-- Riwayat Pembayaran -->
          <div class="panel">
            <div class="section-label"><i class="bi bi-receipt"></i> Riwayat Pembayaran</div>
            <?php if (empty($payments)): ?>
              <p class="text-muted" style="font-size:13px">Belum ada data pembayaran.</p>
            <?php else: ?>
              <?php
                $totalDibayar = 0;
                $totalTagihan = 0;
              ?>
              <?php foreach ($payments as $p): ?>
                <?php $totalTagihan = (float)$p['jumlah']; // ambil nilai tagihan dari baris terakhir ?>
                <div class="pay-row">
                  <div>
                    <div style="font-weight:600"><?= date('d M Y', strtotime($p['tanggal_bayar'])) ?></div>
                    <div style="font-size:12px;color:var(--text-secondary)"><?= htmlspecialchars($p['metode']) ?> · <?= htmlspecialchars($p['keterangan'] ?? '') ?></div>
                  </div>
                  <div style="text-align:right">
                    <div style="font-weight:700">Rp <?= number_format($p['jumlah'], 0, ',', '.') ?></div>
                    <span class="badge <?= strtolower($p['status']) === 'lunas' ? 'badge-success' : 'badge-warning' ?>" style="font-size:11px">
                      <?= htmlspecialchars($p['status']) ?>
                    </span>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
            <div style="margin-top:12px">
              <a href="add-payment.php?id_penitipan=<?= $id ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> Catat Pembayaran</a>
            </div>
          </div>

        </div>

        <!-- Kolom kanan -->
        <div class="col-xl-4" style="display:flex;flex-direction:column;gap:16px">

          <!-- Info Pemilik -->
          <div class="panel">
            <div class="section-label"><i class="bi bi-person"></i> Data Pemilik</div>
            <div style="display:flex;flex-direction:column;gap:8px;font-size:13px">
              <div><span style="color:var(--text-secondary)">Nama:</span> <strong><?= htmlspecialchars($b['nama_pemilik']) ?></strong></div>
              <div><span style="color:var(--text-secondary)">No. HP:</span> <?= htmlspecialchars($b['no_telepon']) ?></div>
              <?php if ($b['email']): ?>
              <div><span style="color:var(--text-secondary)">Email:</span> <?= htmlspecialchars($b['email']) ?></div>
              <?php endif; ?>
              <?php if ($b['alamat']): ?>
              <div><span style="color:var(--text-secondary)">Alamat:</span> <?= htmlspecialchars($b['alamat']) ?></div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Info Petugas -->
          <div class="panel">
            <div class="section-label"><i class="bi bi-person-badge"></i> Petugas Perawat</div>
            <div style="display:flex;align-items:center;gap:12px;font-size:13px">
              <div class="avatar-placeholder avatar-md">👤</div>
              <div>
                <div style="font-weight:700"><?= htmlspecialchars($b['nama_petugas']) ?></div>
              </div>
            </div>
          </div>

          <!-- Aksi -->
          <div class="panel">
            <div class="section-label"><i class="bi bi-gear"></i> Aksi</div>
            <div style="display:flex;flex-direction:column;gap:8px">
              <a href="add-payment.php?id_penitipan=<?= $id ?>" class="btn btn-primary btn-sm" style="width:100%;justify-content:center">
                <i class="bi bi-credit-card"></i> Catat Pembayaran
              </a>
              <a href="bookings.php" class="btn btn-outline btn-sm" style="width:100%;justify-content:center">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar
              </a>
            </div>
          </div>

        </div>
      </div>

    </main>
    <footer class="admin-footer">
      <span>© 2026 PawStay — Aplikasi Manajemen Penitipan Hewan</span>
      <span>Detail Titipan <?= $idLabel ?></span>
    </footer>
  </div>
</div>
<script src="main.php"></script>
</body>
</html>