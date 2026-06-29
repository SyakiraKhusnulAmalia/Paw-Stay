<?php
require_once 'koneksi.php';
requireLogin();
$namaUser  = namaUser();
$idPemilik = (int) $_SESSION['id_pemilik'];

$id_penitipan = (int)($_GET['id_penitipan'] ?? 0);
if ($id_penitipan <= 0) {
    header('Location: payments.php');
    exit;
}

// Ambil data titipan + hewan + pemilik — HARUS milik pemilik yang login
$titipan = dbFetchOne("
  SELECT
    pt.id_penitipan,
    pt.tanggal_masuk,
    pt.tanggal_keluar,
    pt.kandang,
    pt.status AS status_titipan,
    h.nama_hewan,
    h.jenis_hewan,
    h.ras,
    pm.nama_pemilik,
    pm.no_telepon,
    pm.email
  FROM penitipan pt
  JOIN hewan h    ON h.id_hewan    = pt.id_hewan
  JOIN pemilik pm ON pm.id_pemilik = h.id_pemilik
  WHERE pt.id_penitipan = ? AND h.id_pemilik = ?
", [$id_penitipan, $idPemilik]);

// Kalau titipan tidak ditemukan ATAU bukan milik pemilik yang login -> tolak akses
if (!$titipan) {
    header('Location: payments.php');
    exit;
}

// Ambil SEMUA baris pembayaran untuk titipan ini
$riwayat = dbFetchAll("
  SELECT * FROM pembayaran
  WHERE id_penitipan = ?
  ORDER BY id_pembayaran ASC
", [$id_penitipan]);

// Baris pertama = tagihan awal (jumlah = total_tagihan, status = Belum Lunas / DP / Lunas)
// Baris berikutnya = transaksi pembayaran aktual (uang yang masuk)
$tagihan_awal   = $riwayat[0] ?? null;
$total_tagihan  = $tagihan_awal ? (float)$tagihan_awal['jumlah'] : 0;
$id_tagihan     = $tagihan_awal ? (int)$tagihan_awal['id_pembayaran'] : null;

// Hitung total uang yang sudah masuk = SUM baris ke-2 dst (transaksi pembayaran nyata)
$transaksi = array_slice($riwayat, 1); // buang baris tagihan awal
$total_dibayar  = array_sum(array_column($transaksi, 'jumlah'));
$sisa_bayar     = max(0, $total_tagihan - $total_dibayar);

// Hitung info titipan
$tgl_masuk  = new DateTime($titipan['tanggal_masuk']);
$tgl_keluar = $titipan['tanggal_keluar'] ? new DateTime($titipan['tanggal_keluar']) : new DateTime();
$jumlah_hari = max(1, (int)$tgl_masuk->diff($tgl_keluar)->days);

$tarif_map      = ['Kucing'=>75000,'Anjing'=>100000,'Kelinci'=>60000,'Burung'=>50000,'Lainnya'=>75000];
$tarif_per_hari = $tarif_map[$titipan['jenis_hewan']] ?? 75000;

// Jika belum ada tagihan awal (data lama), hitung dari durasi
if ($total_tagihan == 0) {
    $total_tagihan = $jumlah_hari * $tarif_per_hari;
    $sisa_bayar    = max(0, $total_tagihan - $total_dibayar);
}

// Status tagihan saat ini
$status_bayar = 'Belum Lunas';
if ($total_dibayar >= $total_tagihan && $total_tagihan > 0) {
    $status_bayar = 'Lunas';
} elseif ($total_dibayar > 0) {
    $status_bayar = 'DP';
}

$errors = $_SESSION['payment_errors'] ?? [];
unset($_SESSION['payment_errors']);
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bayar Titipan | PawStay</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="style.css">
  <style>
    .invalid-feedback { display:none; color:var(--danger); font-size:11.5px; margin-top:4px; }
    form.was-validated .form-control:invalid,
    form.was-validated .form-select:invalid { border-color: var(--danger); }
    form.was-validated .form-control:invalid + .invalid-feedback,
    form.was-validated .form-select:invalid + .invalid-feedback { display: block; }
    form.was-validated .form-control:valid { border-color: #52C41A; }
    .summary-box { background:var(--bg-body); border:1px solid var(--border-color); border-radius:var(--radius-md); padding:16px; }
    .summary-row { display:flex; justify-content:space-between; align-items:center; font-size:13px; padding:6px 0; border-bottom:1px solid var(--border-color); }
    .summary-row:last-child { border-bottom:none; }
    .summary-total { font-weight:700; font-size:15px; }
    .history-item { display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid var(--border-color); font-size:13px; }
    .history-item:last-child { border-bottom:none; }
    .tagihan-box { background:linear-gradient(135deg,#eff6ff,#dbeafe); border:1px solid #93c5fd; border-radius:var(--radius-md); padding:16px; margin-bottom:16px; }
    .lunas-box   { background:linear-gradient(135deg,#f0fdf4,#dcfce7); border:1px solid #86efac; border-radius:var(--radius-md); padding:16px; }
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
            <h1 style="font-size:22px;font-weight:700;margin-bottom:2px">Bayar Titipan</h1>
            <p class="text-muted mb-0" style="font-size:13px">
              Titipan #PS-<?= str_pad($id_penitipan, 3, '0', STR_PAD_LEFT) ?>
              — <?= htmlspecialchars($titipan['nama_hewan']) ?>
            </p>
          </div>
        </div>
        <a class="btn btn-outline btn-sm" href="payments.php"><i class="bi bi-arrow-left"></i> Kembali</a>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="alert mb-3" style="background:#fef2f2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:8px;font-size:13px;">
          <ul class="mb-0" style="padding-left:18px;">
            <?php foreach ($errors as $e): ?>
              <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if (isset($_GET['bayar_success'])): ?>
        <div class="alert alert-success mb-3" style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:12px 16px;border-radius:8px;font-size:13px;">
          <i class="bi bi-check-circle"></i> Pembayaran berhasil dicatat!
        </div>
      <?php endif; ?>

      <div class="row g-3">
        <!-- Kolom Kiri: Form Bayar + Riwayat -->
        <div class="col-xl-8">

          <?php if ($status_bayar === 'Lunas'): ?>
            <div class="panel">
              <div class="lunas-box">
                <p class="fw-semibold mb-1" style="font-size:15px;color:#166534;"><i class="bi bi-check-circle-fill"></i> Titipan ini sudah lunas.</p>
                <p class="text-muted mb-0" style="font-size:13px;">Tidak ada lagi tagihan yang perlu dibayar untuk titipan ini.</p>
              </div>
            </div>
          <?php else: ?>
            <div class="panel">
              <div class="panel-header">
                <h2 class="section-title h5 mb-0"><i class="bi bi-cash-coin"></i> Catat Pembayaran</h2>
              </div>

              <div class="tagihan-box">
                <div class="summary-row" style="border-bottom:none;">
                  <span>Sisa yang harus dibayar</span>
                  <span class="fw-bold" style="font-size:18px;color:#1e40af;">Rp <?= number_format($sisa_bayar, 0, ',', '.') ?></span>
                </div>
              </div>

              <p class="text-muted" style="font-size:12.5px;margin-bottom:16px;">
                <i class="bi bi-info-circle"></i> Pembayaran dilakukan secara tunai langsung di PawStay. Catat pembayaran yang sudah Anda lakukan di sini.
              </p>

              <form class="needs-validation" method="POST" action="proses_payment.php" novalidate>
                <input type="hidden" name="id_penitipan" value="<?= $id_penitipan ?>">
                <input type="hidden" name="id_tagihan" value="<?= $id_tagihan ?>">
                <input type="hidden" name="total_tagihan" value="<?= $total_tagihan ?>">
                <input type="hidden" name="total_dibayar_lama" value="<?= $total_dibayar ?>">

                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Tanggal Bayar <span style="color:var(--danger)">*</span></label>
                      <input type="date" name="tanggal_bayar" class="form-control" value="<?= date('Y-m-d') ?>" required>
                      <div class="invalid-feedback">Tanggal bayar wajib diisi.</div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Jumlah Dibayar (Rp) <span style="color:var(--danger)">*</span></label>
                      <input type="number" name="jumlah" class="form-control" min="1" max="<?= $sisa_bayar ?>" step="1" placeholder="Contoh: 150000" required>
                      <div class="invalid-feedback">Jumlah bayar wajib diisi.</div>
                      <small class="text-muted">Sisa: Rp <?= number_format($sisa_bayar, 0, ',', '.') ?></small>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Metode Pembayaran <span style="color:var(--danger)">*</span></label>
                      <select name="metode" class="form-select" required>
                        <option value="">— Pilih Metode —</option>
                        <option value="Tunai">💵 Tunai</option>
                        <option value="Transfer">🏦 Transfer Bank</option>
                        <option value="QRIS">📱 QRIS</option>
                      </select>
                      <div class="invalid-feedback">Metode pembayaran wajib dipilih.</div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Keterangan (opsional)</label>
                      <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Pelunasan, DP pertama…">
                    </div>
                  </div>
                </div>

                <div class="d-flex gap-2 mt-3">
                  <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Pembayaran</button>
                  <a href="payments.php" class="btn btn-outline">Batal</a>
                </div>
              </form>
            </div>
          <?php endif; ?>

          <!-- Riwayat transaksi pembayaran (baris ke-2 dst) -->
          <div class="panel mt-3">
            <div class="panel-header">
              <h2 class="section-title h5 mb-0"><i class="bi bi-clock-history"></i> Riwayat Transaksi</h2>
            </div>
            <?php if (empty($transaksi)): ?>
              <p class="text-muted" style="font-size:13px;padding:12px 0;">Belum ada transaksi pembayaran yang masuk.</p>
            <?php else: ?>
              <?php
              $nomor = 1;
              foreach ($transaksi as $r):
                $metodeIcon = ['Tunai'=>'bi-cash-coin','Transfer'=>'bi-bank','QRIS'=>'bi-qr-code'];
                $icon = $metodeIcon[$r['metode']] ?? 'bi-credit-card';
              ?>
              <div class="history-item">
                <div>
                  <p class="fw-semibold mb-0">Transaksi #<?= $nomor++ ?></p>
                  <p class="text-muted mb-0 small">
                    <?= htmlspecialchars($r['tanggal_bayar']) ?>
                    &middot; <i class="bi <?= $icon ?>"></i> <?= htmlspecialchars($r['metode']) ?>
                  </p>
                  <?php if ($r['keterangan']): ?>
                    <p class="text-muted mb-0 small"><?= htmlspecialchars($r['keterangan']) ?></p>
                  <?php endif; ?>
                </div>
                <div class="text-end">
                  <p class="fw-semibold mb-0" style="color:#16a34a;">+Rp <?= number_format($r['jumlah'], 0, ',', '.') ?></p>
                </div>
              </div>
              <?php endforeach; ?>
              <div style="text-align:right;padding-top:10px;font-size:13px;font-weight:700;">
                Total masuk: Rp <?= number_format($total_dibayar, 0, ',', '.') ?>
              </div>
            <?php endif; ?>
          </div>

        </div>

        <!-- Kolom Kanan: Info Titipan -->
        <div class="col-xl-4">
          <div class="panel">
            <div class="panel-header">
              <h2 class="section-title h5 mb-0"><i class="bi bi-info-circle"></i> Info Titipan</h2>
            </div>

            <div class="summary-box mb-3">
              <div class="summary-row">
                <span class="text-muted">Hewan</span>
                <span class="fw-semibold"><?= htmlspecialchars($titipan['nama_hewan']) ?></span>
              </div>
              <div class="summary-row">
                <span class="text-muted">Jenis</span>
                <span><?= htmlspecialchars($titipan['jenis_hewan']) ?></span>
              </div>
              <div class="summary-row">
                <span class="text-muted">Ras</span>
                <span><?= htmlspecialchars($titipan['ras'] ?? '-') ?></span>
              </div>
              <div class="summary-row">
                <span class="text-muted">Masuk</span>
                <span><?= htmlspecialchars($titipan['tanggal_masuk']) ?></span>
              </div>
              <div class="summary-row">
                <span class="text-muted">Keluar</span>
                <span><?= $titipan['tanggal_keluar'] ? htmlspecialchars($titipan['tanggal_keluar']) : '-' ?></span>
              </div>
              <div class="summary-row">
                <span class="text-muted">Durasi</span>
                <span><?= $jumlah_hari ?> hari</span>
              </div>
              <div class="summary-row">
                <span class="text-muted">Kandang</span>
                <span><?= htmlspecialchars($titipan['kandang'] ?? '-') ?></span>
              </div>
              <div class="summary-row">
                <span class="text-muted">Status Titipan</span>
                <?php
                  $stTitipan = ['Aktif'=>'badge-success','Selesai'=>'badge-secondary','Batal'=>'badge-danger'];
                  $stClass   = $stTitipan[$titipan['status_titipan']] ?? '';
                ?>
                <span class="badge <?= $stClass ?>"><?= htmlspecialchars($titipan['status_titipan']) ?></span>
              </div>
            </div>

            <div class="summary-box">
              <div class="summary-row">
                <span class="text-muted">Tarif/hari</span>
                <span>Rp <?= number_format($tarif_per_hari, 0, ',', '.') ?></span>
              </div>
              <div class="summary-row">
                <span class="text-muted">Total tagihan</span>
                <span class="fw-semibold">Rp <?= number_format($total_tagihan, 0, ',', '.') ?></span>
              </div>
              <div class="summary-row">
                <span class="text-muted">Sudah dibayar</span>
                <span style="color:#16a34a;font-weight:600;">Rp <?= number_format($total_dibayar, 0, ',', '.') ?></span>
              </div>
              <div class="summary-row summary-total">
                <span>Sisa tagihan</span>
                <span style="color:<?= $sisa_bayar > 0 ? '#dc2626' : '#16a34a' ?>;font-size:16px;">
                  Rp <?= number_format($sisa_bayar, 0, ',', '.') ?>
                </span>
              </div>
              <div class="summary-row">
                <span class="text-muted">Status Bayar</span>
                <?php
                  $stBayar = ['Lunas'=>'badge-success','DP'=>'badge-warning','Belum Lunas'=>'badge-danger'];
                  $stBadge = $stBayar[$status_bayar] ?? 'badge-secondary';
                ?>
                <span class="badge <?= $stBadge ?>"><?= $status_bayar ?></span>
              </div>
            </div>
          </div>
        </div>

      </div>
    </main>
    <footer class="admin-footer">
      <span>© 2026 PawStay — Aplikasi Manajemen Penitipan Hewan</span>
      <span>Bayar Titipan</span>
    </footer>
  </div>
</div>
<script src="main.php"></script>
<script>
document.querySelector('form.needs-validation')?.addEventListener('submit', function(e) {
  if (!this.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
  this.classList.add('was-validated');
});
</script>
</body>
</html>
