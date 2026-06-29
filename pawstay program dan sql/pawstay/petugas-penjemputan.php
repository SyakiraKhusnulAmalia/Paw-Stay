<?php
require_once 'koneksi.php';
requireLoginPetugas();
$namaPetugas = namaPetugas();
$idPetugas   = (int) $_SESSION['id_petugas'];
$activePage  = 'petugas-penjemputan.php';

// ── Aksi: jadwalkan penjemputan baru ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'jadwalkan') {
    $id_penitipan = (int)($_POST['id_penitipan'] ?? 0);
    $tanggal      = trim($_POST['tanggal_dijadwalkan'] ?? '');

    if ($id_penitipan > 0 && $tanggal !== '') {
        $sudahAda = dbFetchOne("SELECT id_penjemputan FROM penjemputan WHERE id_penitipan = ? AND status = 'Dijadwalkan'", [$id_penitipan]);
        if (!$sudahAda) {
            dbExecute(
                "INSERT INTO penjemputan (id_penitipan, tanggal_dijadwalkan, status) VALUES (?, ?, 'Dijadwalkan')",
                [$id_penitipan, $tanggal]
            );
            $_SESSION['penjemputan_success'] = 'Jadwal penjemputan berhasil dibuat.';
        } else {
            $_SESSION['penjemputan_success'] = 'Titipan ini sudah memiliki jadwal penjemputan aktif.';
        }
    }
    header('Location: petugas-penjemputan.php');
    exit;
}

// ── Aksi: tandai sudah dijemput ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'selesaikan') {
    $id_penjemputan = (int)($_POST['id_penjemputan'] ?? 0);
    $dijemput_oleh  = trim($_POST['dijemput_oleh'] ?? '');
    $catatan        = trim($_POST['catatan'] ?? '');

    $pj = dbFetchOne("SELECT * FROM penjemputan WHERE id_penjemputan = ?", [$id_penjemputan]);
    if ($pj) {
        dbExecute(
            "UPDATE penjemputan SET status = 'Sudah Dijemput', tanggal_jemput = NOW(), dijemput_oleh = ?, catatan = ?, id_petugas = ? WHERE id_penjemputan = ?",
            [$dijemput_oleh ?: null, $catatan ?: null, $idPetugas, $id_penjemputan]
        );
        // otomatis tandai titipan sebagai selesai
        dbExecute("UPDATE penitipan SET status = 'Selesai', tanggal_keluar = CURDATE() WHERE id_penitipan = ?", [$pj['id_penitipan']]);
        $_SESSION['penjemputan_success'] = 'Penjemputan dicatat. Titipan otomatis ditandai Selesai.';
    }
    header('Location: petugas-penjemputan.php');
    exit;
}

// ── Titipan aktif tanpa jadwal penjemputan (untuk dijadwalkan) ──
$belumDijadwalkan = dbFetchAll("
  SELECT pt.id_penitipan, pt.tanggal_keluar, h.nama_hewan, h.jenis_hewan, pm.nama_pemilik
  FROM penitipan pt
  JOIN hewan h ON h.id_hewan = pt.id_hewan
  JOIN pemilik pm ON pm.id_pemilik = h.id_pemilik
  WHERE pt.status = 'Aktif'
    AND pt.id_penitipan NOT IN (SELECT id_penitipan FROM penjemputan WHERE status = 'Dijadwalkan')
  ORDER BY pt.tanggal_keluar ASC
");

// ── Jadwal penjemputan yang masih berjalan ──
$dijadwalkan = dbFetchAll("
  SELECT pj.*, h.nama_hewan, h.jenis_hewan, pm.nama_pemilik, pm.no_hp, pt.kandang
  FROM penjemputan pj
  JOIN penitipan pt ON pt.id_penitipan = pj.id_penitipan
  JOIN hewan h ON h.id_hewan = pt.id_hewan
  JOIN pemilik pm ON pm.id_pemilik = h.id_pemilik
  WHERE pj.status = 'Dijadwalkan'
  ORDER BY pj.tanggal_dijadwalkan ASC
");

// ── Riwayat penjemputan selesai ──
$riwayat = dbFetchAll("
  SELECT pj.*, h.nama_hewan, h.jenis_hewan, pm.nama_pemilik, pg.nama_petugas
  FROM penjemputan pj
  JOIN penitipan pt ON pt.id_penitipan = pj.id_penitipan
  JOIN hewan h ON h.id_hewan = pt.id_hewan
  JOIN pemilik pm ON pm.id_pemilik = h.id_pemilik
  LEFT JOIN petugas pg ON pg.id_petugas = pj.id_petugas
  WHERE pj.status = 'Sudah Dijemput'
  ORDER BY pj.tanggal_jemput DESC
  LIMIT 15
");
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Penjemputan Hewan | PawStay</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'partials/petugas-header.php'; ?>

      <div class="page-heading">
        <div class="page-heading-copy">
          <span class="page-icon"><i class="bi bi-box-arrow-in-right"></i></span>
          <div>
            <p class="eyebrow mb-1">Petugas</p>
            <h1 style="font-size:22px;font-weight:700;margin-bottom:2px">Mengelola Penjemputan Hewan</h1>
            <p class="text-muted mb-0" style="font-size:13px">Jadwalkan dan catat proses penjemputan hewan oleh pemilik.</p>
          </div>
        </div>
      </div>

      <?php if (!empty($_SESSION['penjemputan_success'])): ?>
        <div class="alert alert-success mb-3" style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:12px 16px;border-radius:8px;font-size:13px;">
          <i class="bi bi-check-circle"></i> <?= htmlspecialchars($_SESSION['penjemputan_success']) ?>
        </div>
        <?php unset($_SESSION['penjemputan_success']); ?>
      <?php endif; ?>

      <div class="row g-3 mb-3">
        <!-- Belum dijadwalkan -->
        <div class="col-lg-6">
          <div class="panel h-100">
            <div class="panel-header">
              <h2 class="section-title h6 mb-0"><i class="bi bi-calendar-plus"></i> Titipan Aktif — Belum Dijadwalkan</h2>
            </div>
            <div class="table-responsive">
              <table>
                <thead><tr><th>Hewan</th><th>Pemilik</th><th>Est. Keluar</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                  <?php if (count($belumDijadwalkan) === 0): ?>
                  <tr><td colspan="4" style="text-align:center;padding:24px;color:var(--text-muted);">Semua titipan aktif sudah punya jadwal.</td></tr>
                  <?php else: foreach ($belumDijadwalkan as $b): ?>
                  <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($b['nama_hewan']) ?> <span class="text-muted small">(<?= htmlspecialchars($b['jenis_hewan']) ?>)</span></td>
                    <td><?= htmlspecialchars($b['nama_pemilik']) ?></td>
                    <td><?= $b['tanggal_keluar'] ? date('d M Y', strtotime($b['tanggal_keluar'])) : '-' ?></td>
                    <td class="text-end">
                      <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalJadwal<?= $b['id_penitipan'] ?>">
                        <i class="bi bi-calendar-plus"></i> Jadwalkan
                      </button>
                    </td>
                  </tr>
                  <div class="modal-overlay" id="modalJadwal<?= $b['id_penitipan'] ?>">
                    <div class="modal-box">
                      <div class="modal-box-header">
                        <h3 class="h6 mb-0">Jadwalkan Penjemputan — <?= htmlspecialchars($b['nama_hewan']) ?></h3>
                        <button type="button" class="icon-button" data-modal-close="modalJadwal<?= $b['id_penitipan'] ?>"><i class="bi bi-x-lg"></i></button>
                      </div>
                      <form method="POST">
                        <input type="hidden" name="form" value="jadwalkan">
                        <input type="hidden" name="id_penitipan" value="<?= $b['id_penitipan'] ?>">
                        <div class="modal-box-body">
                          <div class="form-group mb-0">
                            <label class="form-label">Tanggal Penjemputan</label>
                            <input class="form-control" type="date" name="tanggal_dijadwalkan" value="<?= $b['tanggal_keluar'] ?? date('Y-m-d') ?>" required>
                          </div>
                        </div>
                        <div class="modal-box-footer">
                          <button type="button" class="btn btn-light btn-sm" data-modal-close="modalJadwal<?= $b['id_penitipan'] ?>">Batal</button>
                          <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save"></i> Simpan Jadwal</button>
                        </div>
                      </form>
                    </div>
                  </div>
                  <?php endforeach; endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Dijadwalkan -->
        <div class="col-lg-6">
          <div class="panel h-100">
            <div class="panel-header">
              <h2 class="section-title h6 mb-0"><i class="bi bi-calendar-event"></i> Jadwal Penjemputan Aktif</h2>
            </div>
            <div class="table-responsive">
              <table>
                <thead><tr><th>Hewan</th><th>Kandang</th><th>Tanggal</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                  <?php if (count($dijadwalkan) === 0): ?>
                  <tr><td colspan="4" style="text-align:center;padding:24px;color:var(--text-muted);">Tidak ada jadwal penjemputan.</td></tr>
                  <?php else: foreach ($dijadwalkan as $j): ?>
                  <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($j['nama_hewan']) ?> <span class="text-muted small d-block"><?= htmlspecialchars($j['nama_pemilik']) ?></span></td>
                    <td><?= htmlspecialchars($j['kandang'] ?? '-') ?></td>
                    <td><?= $j['tanggal_dijadwalkan'] ? date('d M Y', strtotime($j['tanggal_dijadwalkan'])) : '-' ?></td>
                    <td class="text-end">
                      <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalSelesai<?= $j['id_penjemputan'] ?>">
                        <i class="bi bi-check2-circle"></i> Sudah Dijemput
                      </button>
                    </td>
                  </tr>
                  <div class="modal-overlay" id="modalSelesai<?= $j['id_penjemputan'] ?>">
                    <div class="modal-box">
                      <div class="modal-box-header">
                        <h3 class="h6 mb-0">Konfirmasi Penjemputan — <?= htmlspecialchars($j['nama_hewan']) ?></h3>
                        <button type="button" class="icon-button" data-modal-close="modalSelesai<?= $j['id_penjemputan'] ?>"><i class="bi bi-x-lg"></i></button>
                      </div>
                      <form method="POST">
                        <input type="hidden" name="form" value="selesaikan">
                        <input type="hidden" name="id_penjemputan" value="<?= $j['id_penjemputan'] ?>">
                        <div class="modal-box-body">
                          <div class="form-group mb-2">
                            <label class="form-label">Dijemput Oleh</label>
                            <input class="form-control" type="text" name="dijemput_oleh" value="<?= htmlspecialchars($j['nama_pemilik']) ?>" placeholder="Nama yang menjemput">
                          </div>
                          <div class="form-group mb-0">
                            <label class="form-label">Catatan</label>
                            <textarea class="form-control" name="catatan" rows="2" placeholder="Kondisi hewan saat dijemput, dsb."></textarea>
                          </div>
                        </div>
                        <div class="modal-box-footer">
                          <button type="button" class="btn btn-light btn-sm" data-modal-close="modalSelesai<?= $j['id_penjemputan'] ?>">Batal</button>
                          <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check2-circle"></i> Konfirmasi Sudah Dijemput</button>
                        </div>
                      </form>
                    </div>
                  </div>
                  <?php endforeach; endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Riwayat -->
      <div class="panel">
        <div class="panel-header">
          <h2 class="section-title h5 mb-0"><i class="bi bi-clock-history"></i> Riwayat Penjemputan</h2>
        </div>
        <div class="table-responsive">
          <table>
            <thead><tr><th>Hewan</th><th>Pemilik</th><th>Dijemput Oleh</th><th>Waktu Jemput</th><th>Dicatat Petugas</th></tr></thead>
            <tbody>
              <?php if (count($riwayat) === 0): ?>
              <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text-muted);">Belum ada riwayat penjemputan.</td></tr>
              <?php else: foreach ($riwayat as $r): ?>
              <tr>
                <td class="fw-semibold"><?= htmlspecialchars($r['nama_hewan']) ?> <span class="text-muted small">(<?= htmlspecialchars($r['jenis_hewan']) ?>)</span></td>
                <td><?= htmlspecialchars($r['nama_pemilik']) ?></td>
                <td><?= htmlspecialchars($r['dijemput_oleh'] ?? '-') ?></td>
                <td><?= $r['tanggal_jemput'] ? date('d M Y H:i', strtotime($r['tanggal_jemput'])) : '-' ?></td>
                <td><?= htmlspecialchars($r['nama_petugas'] ?? '-') ?></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>

<?php include 'partials/petugas-footer.php'; ?>

<style>
.modal-overlay {
  display: none;
  position: fixed; inset: 0; z-index: 1000;
  background: rgba(0,0,0,.45);
  align-items: center; justify-content: center;
  padding: 16px;
}
.modal-overlay.is-open { display: flex; }
.modal-box {
  background: var(--bg-surface);
  border-radius: var(--radius-lg);
  width: 100%; max-width: 420px;
  box-shadow: var(--shadow-md);
  max-height: 90vh; overflow-y: auto;
}
.modal-box-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 18px; border-bottom: 1px solid var(--border-color);
}
.modal-box-body { padding: 18px; }
.modal-box-footer {
  display: flex; justify-content: flex-end; gap: 8px;
  padding: 14px 18px; border-top: 1px solid var(--border-color);
}
</style>
<script>
document.addEventListener('click', function (e) {
  const openBtn = e.target.closest('[data-bs-toggle="modal"]');
  if (openBtn) {
    const targetId = openBtn.getAttribute('data-bs-target').replace('#', '');
    document.getElementById(targetId)?.classList.add('is-open');
  }
  const closeBtn = e.target.closest('[data-modal-close]');
  if (closeBtn) {
    document.getElementById(closeBtn.getAttribute('data-modal-close'))?.classList.remove('is-open');
  }
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('is-open');
  }
});
</script>
