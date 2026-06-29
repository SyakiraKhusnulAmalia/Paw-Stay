<?php
require_once 'koneksi.php';
requireLoginPetugas();
$namaPetugas = namaPetugas();
$idPetugas   = (int) $_SESSION['id_petugas'];
$activePage  = 'petugas-kondisi.php';

// ── Aksi: tambah catatan kondisi baru (sekaligus update status hewan terkini) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_penitipan = (int)($_POST['id_penitipan'] ?? 0);
    $kondisi      = trim($_POST['kondisi'] ?? 'Sehat');
    $nafsu_makan  = trim($_POST['nafsu_makan'] ?? 'Baik');
    $catatan      = trim($_POST['catatan'] ?? '');

    $allowedKondisi = ['Sehat', 'Kurang Sehat', 'Sakit', 'Perlu Perhatian Khusus'];
    $allowedNafsu   = ['Baik', 'Menurun', 'Tidak Mau Makan'];

    $titipan = dbFetchOne("SELECT id_penitipan FROM penitipan WHERE id_penitipan = ?", [$id_penitipan]);

    if ($titipan && in_array($kondisi, $allowedKondisi, true) && in_array($nafsu_makan, $allowedNafsu, true)) {
        dbExecute(
            "INSERT INTO kondisi_hewan (id_penitipan, id_petugas, tanggal_catat, kondisi, nafsu_makan, catatan)
             VALUES (?, ?, NOW(), ?, ?, ?)",
            [$id_penitipan, $idPetugas, $kondisi, $nafsu_makan, $catatan ?: null]
        );
        $_SESSION['kondisi_success'] = 'Catatan kondisi untuk titipan #PS-' . str_pad($id_penitipan, 3, '0', STR_PAD_LEFT) . ' berhasil disimpan.';
    }
    header('Location: petugas-kondisi.php');
    exit;
}

// ── Titipan aktif beserta kondisi terakhir ──
$titipanAktif = dbFetchAll("
  SELECT pt.id_penitipan, pt.kandang, pt.tanggal_masuk, pt.tanggal_keluar,
         h.nama_hewan, h.jenis_hewan, h.ras,
         pm.nama_pemilik,
         k.kondisi AS kondisi_terakhir, k.nafsu_makan AS nafsu_terakhir, k.tanggal_catat AS tanggal_kondisi_terakhir
  FROM penitipan pt
  JOIN hewan h ON h.id_hewan = pt.id_hewan
  JOIN pemilik pm ON pm.id_pemilik = h.id_pemilik
  LEFT JOIN kondisi_hewan k ON k.id_kondisi = (
    SELECT id_kondisi FROM kondisi_hewan WHERE id_penitipan = pt.id_penitipan ORDER BY tanggal_catat DESC LIMIT 1
  )
  WHERE pt.status = 'Aktif'
  ORDER BY pt.id_penitipan DESC
");

// ── Riwayat semua catatan kondisi (terbaru dulu) ──
$riwayatKondisi = dbFetchAll("
  SELECT k.*, h.nama_hewan, h.jenis_hewan, pg.nama_petugas
  FROM kondisi_hewan k
  JOIN penitipan pt ON pt.id_penitipan = k.id_penitipan
  JOIN hewan h ON h.id_hewan = pt.id_hewan
  JOIN petugas pg ON pg.id_petugas = k.id_petugas
  ORDER BY k.tanggal_catat DESC
  LIMIT 20
");

$kondisiBadge = [
  'Sehat'                   => 'badge-success',
  'Kurang Sehat'             => 'badge-warning',
  'Sakit'                    => 'badge-danger',
  'Perlu Perhatian Khusus'   => 'badge-danger',
];
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kondisi Hewan | PawStay</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'partials/petugas-header.php'; ?>

      <div class="page-heading">
        <div class="page-heading-copy">
          <span class="page-icon"><i class="bi bi-heart-pulse"></i></span>
          <div>
            <p class="eyebrow mb-1">Petugas</p>
            <h1 style="font-size:22px;font-weight:700;margin-bottom:2px">Mencatat &amp; Memperbarui Kondisi Hewan</h1>
            <p class="text-muted mb-0" style="font-size:13px">Catat kondisi kesehatan hewan secara berkala selama masa penitipan.</p>
          </div>
        </div>
      </div>

      <?php if (!empty($_SESSION['kondisi_success'])): ?>
        <div class="alert alert-success mb-3" style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:12px 16px;border-radius:8px;font-size:13px;">
          <i class="bi bi-check-circle"></i> <?= htmlspecialchars($_SESSION['kondisi_success']) ?>
        </div>
        <?php unset($_SESSION['kondisi_success']); ?>
      <?php endif; ?>

      <!-- Titipan aktif + status kondisi terakhir -->
      <div class="panel mb-3">
        <div class="panel-header">
          <h2 class="section-title h5 mb-0"><i class="bi bi-clipboard-pulse"></i> Hewan yang Sedang Dititipkan</h2>
        </div>
        <div class="table-responsive">
          <table>
            <thead>
              <tr><th>Hewan</th><th>Pemilik</th><th>Kandang</th><th>Status Kondisi Terkini</th><th>Update Terakhir</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody>
              <?php if (count($titipanAktif) === 0): ?>
              <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted);">Tidak ada titipan aktif saat ini.</td></tr>
              <?php else: foreach ($titipanAktif as $t):
                $badge = $kondisiBadge[$t['kondisi_terakhir']] ?? 'badge-secondary';
              ?>
              <tr>
                <td class="fw-semibold"><?= htmlspecialchars($t['nama_hewan']) ?> <span class="text-muted small">(<?= htmlspecialchars($t['jenis_hewan']) ?>)</span></td>
                <td><?= htmlspecialchars($t['nama_pemilik']) ?></td>
                <td><?= htmlspecialchars($t['kandang'] ?? '-') ?></td>
                <td>
                  <?php if ($t['kondisi_terakhir']): ?>
                    <span class="badge <?= $badge ?>"><?= htmlspecialchars($t['kondisi_terakhir']) ?></span>
                    <span class="text-muted small d-block">Nafsu makan: <?= htmlspecialchars($t['nafsu_terakhir']) ?></span>
                  <?php else: ?>
                    <span class="badge badge-secondary">Belum dicatat</span>
                  <?php endif; ?>
                </td>
                <td><?= $t['tanggal_kondisi_terakhir'] ? date('d M Y H:i', strtotime($t['tanggal_kondisi_terakhir'])) : '-' ?></td>
                <td class="text-end">
                  <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCatat<?= $t['id_penitipan'] ?>">
                    <i class="bi bi-plus-circle"></i> Catat Kondisi
                  </button>
                </td>
              </tr>

              <!-- Modal catat kondisi -->
              <div class="modal-overlay" id="modalCatat<?= $t['id_penitipan'] ?>">
                <div class="modal-box">
                  <div class="modal-box-header">
                    <h3 class="h6 mb-0">Catat Kondisi — <?= htmlspecialchars($t['nama_hewan']) ?></h3>
                    <button type="button" class="icon-button" data-modal-close="modalCatat<?= $t['id_penitipan'] ?>"><i class="bi bi-x-lg"></i></button>
                  </div>
                  <form method="POST">
                    <input type="hidden" name="id_penitipan" value="<?= $t['id_penitipan'] ?>">
                    <div class="modal-box-body">
                      <div class="form-group mb-2">
                        <label class="form-label">Kondisi Hewan</label>
                        <select class="form-control" name="kondisi" required>
                          <option value="Sehat">Sehat</option>
                          <option value="Kurang Sehat">Kurang Sehat</option>
                          <option value="Sakit">Sakit</option>
                          <option value="Perlu Perhatian Khusus">Perlu Perhatian Khusus</option>
                        </select>
                      </div>
                      <div class="form-group mb-2">
                        <label class="form-label">Nafsu Makan</label>
                        <select class="form-control" name="nafsu_makan" required>
                          <option value="Baik">Baik</option>
                          <option value="Menurun">Menurun</option>
                          <option value="Tidak Mau Makan">Tidak Mau Makan</option>
                        </select>
                      </div>
                      <div class="form-group mb-0">
                        <label class="form-label">Catatan Tambahan</label>
                        <textarea class="form-control" name="catatan" rows="3" placeholder="Contoh: hewan tampak lesu, ada luka kecil di kaki, dsb."></textarea>
                      </div>
                    </div>
                    <div class="modal-box-footer">
                      <button type="button" class="btn btn-light btn-sm" data-modal-close="modalCatat<?= $t['id_penitipan'] ?>">Batal</button>
                      <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save"></i> Simpan Catatan</button>
                    </div>
                  </form>
                </div>
              </div>

              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Riwayat seluruh catatan kondisi -->
      <div class="panel">
        <div class="panel-header">
          <h2 class="section-title h5 mb-0"><i class="bi bi-clock-history"></i> Riwayat Catatan Kondisi</h2>
        </div>
        <div class="table-responsive">
          <table>
            <thead>
              <tr><th>Waktu</th><th>Hewan</th><th>Kondisi</th><th>Nafsu Makan</th><th>Catatan</th><th>Dicatat Oleh</th></tr>
            </thead>
            <tbody>
              <?php if (count($riwayatKondisi) === 0): ?>
              <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted);">Belum ada riwayat catatan kondisi.</td></tr>
              <?php else: foreach ($riwayatKondisi as $r):
                $badge = $kondisiBadge[$r['kondisi']] ?? 'badge-secondary';
              ?>
              <tr>
                <td><?= date('d M Y H:i', strtotime($r['tanggal_catat'])) ?></td>
                <td class="fw-semibold"><?= htmlspecialchars($r['nama_hewan']) ?> <span class="text-muted small">(<?= htmlspecialchars($r['jenis_hewan']) ?>)</span></td>
                <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($r['kondisi']) ?></span></td>
                <td><?= htmlspecialchars($r['nafsu_makan']) ?></td>
                <td><?= htmlspecialchars($r['catatan'] ?? '-') ?></td>
                <td><?= htmlspecialchars($r['nama_petugas']) ?></td>
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
