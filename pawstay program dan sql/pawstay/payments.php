<?php
require_once 'koneksi.php';
requireLogin();
$namaUser  = namaUser();
$idPemilik = (int) $_SESSION['id_pemilik'];
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pembayaran | PawStay</title>
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
      <a class="nav-link" href="bookings.php"><span class="nav-icon"><i class="bi bi-calendar-check"></i></span><span class="nav-text">Titipan Saya</span></a>
      <a class="nav-link" href="add-booking.php"><span class="nav-icon"><i class="bi bi-plus-circle"></i></span><span class="nav-text">Tambah Titipan</span></a>
      <a class="nav-link active" href="payments.php"><span class="nav-icon"><i class="bi bi-credit-card"></i></span><span class="nav-text">Pembayaran</span></a>
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
      <input class="search-input ms-3" type="search" placeholder="Cari pembayaran…" style="flex:1;max-width:300px">
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
            <a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Keluar</a>
          </div>
        </div>
      </div>
    </nav>

    <main class="dashboard-content">
      <div class="page-heading">
        <div class="page-heading-copy">
          <span class="page-icon"><i class="bi bi-credit-card"></i></span>
          <div>
            <p class="eyebrow mb-1">Pembayaran</p>
            <h1 style="font-size:22px;font-weight:700;margin-bottom:2px">Pembayaran Saya</h1>
            <p class="text-muted mb-0" style="font-size:13px">Lihat dan lakukan pembayaran untuk titipan hewan Anda di PawStay.</p>
          </div>
        </div>
      </div>

      <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success mb-3" style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:12px 16px;border-radius:8px;font-size:13px;">
          <i class="bi bi-check-circle"></i> Pembayaran berhasil dicatat!
        </div>
      <?php endif; ?>

      <?php
        // Ringkasan statistik pembayaran — HANYA milik pemilik yang login
        $totalLunas = dbFetchOne("
          SELECT COUNT(*) as c, COALESCE(SUM(pb.jumlah),0) as total
          FROM pembayaran pb
          JOIN penitipan pt ON pt.id_penitipan = pb.id_penitipan
          JOIN hewan h      ON h.id_hewan       = pt.id_hewan
          WHERE pb.status='Lunas' AND h.id_pemilik = ?
        ", [$idPemilik]);
        $totalBelum = dbFetchOne("
          SELECT COUNT(*) as c, COALESCE(SUM(pb.jumlah),0) as total
          FROM pembayaran pb
          JOIN penitipan pt ON pt.id_penitipan = pb.id_penitipan
          JOIN hewan h      ON h.id_hewan       = pt.id_hewan
          WHERE pb.status='Belum Lunas' AND h.id_pemilik = ?
        ", [$idPemilik]);
        $totalDP = dbFetchOne("
          SELECT COUNT(*) as c, COALESCE(SUM(pb.jumlah),0) as total
          FROM pembayaran pb
          JOIN penitipan pt ON pt.id_penitipan = pb.id_penitipan
          JOIN hewan h      ON h.id_hewan       = pt.id_hewan
          WHERE pb.status='DP' AND h.id_pemilik = ?
        ", [$idPemilik]);

        // Ambil 1 baris per penitipan (tagihan awal) milik pemilik yang login saja
        $payments = dbFetchAll("
          SELECT
            pb.id_pembayaran,
            pb.tanggal_bayar,
            pb.jumlah            AS total_tagihan,
            pb.status,
            pb.keterangan,
            pt.id_penitipan,
            pt.tanggal_masuk,
            pt.tanggal_keluar,
            h.nama_hewan,
            h.jenis_hewan,
            pm.nama_pemilik,
            pm.no_telepon,
            COALESCE(bayar.total_masuk, 0) AS total_dibayar
          FROM pembayaran pb
          JOIN penitipan pt ON pt.id_penitipan = pb.id_penitipan
          JOIN hewan h      ON h.id_hewan       = pt.id_hewan
          JOIN pemilik pm   ON pm.id_pemilik     = h.id_pemilik
          LEFT JOIN (
            SELECT id_penitipan, SUM(jumlah) AS total_masuk
            FROM pembayaran
            WHERE id_pembayaran NOT IN (
              SELECT MIN(id_pembayaran) FROM pembayaran GROUP BY id_penitipan
            )
            GROUP BY id_penitipan
          ) bayar ON bayar.id_penitipan = pb.id_penitipan
          WHERE pb.id_pembayaran IN (
            SELECT MIN(id_pembayaran) FROM pembayaran GROUP BY id_penitipan
          )
          AND h.id_pemilik = ?
          ORDER BY pb.id_penitipan DESC
        ", [$idPemilik]);
      ?>

      <!-- Kartu Statistik -->
      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <div class="stat-card" style="border-left:4px solid #22c55e;">
            <div class="stat-label"><i class="bi bi-check-circle text-success"></i> Lunas</div>
            <div class="stat-value"><?= $totalLunas['c'] ?> transaksi</div>
            <div class="stat-sub">Rp <?= number_format($totalLunas['total'], 0, ',', '.') ?></div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card" style="border-left:4px solid #f59e0b;">
            <div class="stat-label"><i class="bi bi-clock text-warning"></i> DP / Cicilan</div>
            <div class="stat-value"><?= $totalDP['c'] ?> transaksi</div>
            <div class="stat-sub">Rp <?= number_format($totalDP['total'], 0, ',', '.') ?></div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card" style="border-left:4px solid #ef4444;">
            <div class="stat-label"><i class="bi bi-x-circle text-danger"></i> Belum Lunas</div>
            <div class="stat-value"><?= $totalBelum['c'] ?> transaksi</div>
            <div class="stat-sub">Rp <?= number_format($totalBelum['total'], 0, ',', '.') ?></div>
          </div>
        </div>
      </div>

      <div class="alert mb-3" style="background:#eff6ff;border:1px solid #93c5fd;color:#1e40af;padding:12px 16px;border-radius:8px;font-size:13px;">
        <i class="bi bi-info-circle"></i> Pembayaran dilakukan secara tunai langsung di PawStay. Klik <strong>Bayar</strong> untuk mencatat pembayaran yang sudah Anda lakukan.
      </div>

      <div class="panel">
        <div class="panel-header" style="flex-wrap:wrap;gap:12px">
          <div>
            <h2 class="section-title h5 mb-1"><i class="bi bi-table"></i> Tagihan Saya</h2>
            <p class="text-muted mb-0" style="font-size:13px">Klik filter untuk menyaring berdasarkan status pembayaran.</p>
          </div>
          <input class="table-search" type="search" placeholder="Cari nama hewan…" data-table-search="paymentsTable">
        </div>

        <div class="chip-tabs" data-table-target="paymentsTable">
          <button class="chip active" data-filter="all">Semua</button>
          <button class="chip" data-filter="lunas">Lunas</button>
          <button class="chip" data-filter="dp">DP</button>
          <button class="chip" data-filter="belum lunas">Belum Lunas</button>
        </div>

        <div class="table-responsive">
          <table id="paymentsTable">
            <thead>
              <tr>
                <th>Titipan</th>
                <th>Hewan</th>
                <th>Total Tagihan</th>
                <th>Dibayar</th>
                <th>Sisa</th>
                <th>Status</th>
                <th class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($payments) === 0): ?>
              <tr>
                <td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted);">
                  <i class="bi bi-inbox" style="font-size:32px;display:block;margin-bottom:8px;"></i>
                  Belum ada data pembayaran.
                </td>
              </tr>
              <?php else: foreach ($payments as $p):
                $statusMap = [
                  'lunas'       => ['label'=>'Lunas',       'badge'=>'badge-success'],
                  'dp'          => ['label'=>'DP',           'badge'=>'badge-warning'],
                  'belum lunas' => ['label'=>'Belum Lunas',  'badge'=>'badge-danger'],
                ];
                $st  = $statusMap[strtolower($p['status'])] ?? ['label'=>$p['status'],'badge'=>''];
                $sisa_p = max(0, (float)$p['total_tagihan'] - (float)$p['total_dibayar']);
              ?>
              <tr data-status="<?= strtolower($p['status']) ?>">
                <td class="fw-semibold">#PS-<?= str_pad($p['id_penitipan'], 3, '0', STR_PAD_LEFT) ?></td>
                <td><?= htmlspecialchars($p['nama_hewan']) ?> <span class="text-muted small">(<?= htmlspecialchars($p['jenis_hewan']) ?>)</span></td>
                <td class="fw-semibold">Rp <?= number_format($p['total_tagihan'], 0, ',', '.') ?></td>
                <td style="color:#16a34a;font-weight:600;">Rp <?= number_format($p['total_dibayar'], 0, ',', '.') ?></td>
                <td style="color:<?= $sisa_p > 0 ? '#dc2626' : '#16a34a' ?>;font-weight:600;">Rp <?= number_format($sisa_p, 0, ',', '.') ?></td>
                <td><span class="badge <?= $st['badge'] ?>"><?= $st['label'] ?></span></td>
                <td class="text-end">
                  <a href="add-payment.php?id_penitipan=<?= $p['id_penitipan'] ?>" class="btn btn-<?= $p['status'] === 'Lunas' ? 'light' : 'primary' ?> btn-sm">
                    <i class="bi bi-<?= $p['status'] === 'Lunas' ? 'eye' : 'credit-card' ?>"></i>
                    <?= $p['status'] === 'Lunas' ? 'Lihat' : 'Bayar' ?>
                  </a>
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
      <span>Pembayaran</span>
    </footer>
  </div>
</div>
<script src="main.php"></script>
</body>
</html>
