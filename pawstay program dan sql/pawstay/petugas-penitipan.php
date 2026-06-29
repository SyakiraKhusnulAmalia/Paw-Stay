<?php
require_once 'koneksi.php';
requireLoginPetugas();
$namaPetugas = namaPetugas();
$idPetugas   = (int) $_SESSION['id_petugas'];
$activePage  = 'petugas-penitipan.php';

// ── Aksi: update status / catatan / tanggal keluar ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_penitipan = (int)($_POST['id_penitipan'] ?? 0);
    $status        = trim($_POST['status'] ?? '');
    $kandang       = trim($_POST['kandang'] ?? '');
    $tanggal_keluar = trim($_POST['tanggal_keluar'] ?? '');
    $catatan       = trim($_POST['catatan'] ?? '');

    $allowedStatus = ['Menunggu Verifikasi', 'Aktif', 'Selesai', 'Batal'];
    if ($id_penitipan > 0 && in_array($status, $allowedStatus, true)) {
        dbExecute(
            "UPDATE penitipan SET status = ?, kandang = ?, tanggal_keluar = ?, catatan = ?, id_petugas = ? WHERE id_penitipan = ?",
            [$status, $kandang ?: null, $tanggal_keluar ?: null, $catatan ?: null, $idPetugas, $id_penitipan]
        );
        $_SESSION['penitipan_success'] = 'Data titipan #PS-' . str_pad($id_penitipan, 3, '0', STR_PAD_LEFT) . ' berhasil diperbarui.';
    }
    header('Location: petugas-penitipan.php');
    exit;
}

$daftar = dbFetchAll("
  SELECT pt.id_penitipan, pt.tanggal_masuk, pt.tanggal_keluar, pt.kandang, pt.status, pt.catatan,
         h.nama_hewan, h.jenis_hewan, h.ras,
         pm.nama_pemilik, pm.no_hp,
         pg.nama_petugas
  FROM penitipan pt
  JOIN hewan h ON h.id_hewan = pt.id_hewan
  JOIN pemilik pm ON pm.id_pemilik = h.id_pemilik
  LEFT JOIN petugas pg ON pg.id_petugas = pt.id_petugas
  ORDER BY pt.id_penitipan DESC
");
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Penitipan | PawStay</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'partials/petugas-header.php'; ?>

      <div class="page-heading">
        <div class="page-heading-copy">
          <span class="page-icon"><i class="bi bi-clipboard2-data"></i></span>
          <div>
            <p class="eyebrow mb-1">Petugas</p>
            <h1 style="font-size:22px;font-weight:700;margin-bottom:2px">Mengelola Data Penitipan</h1>
            <p class="text-muted mb-0" style="font-size:13px">Lihat dan kelola seluruh data titipan hewan di PawStay.</p>
          </div>
        </div>
      </div>

      <?php if (!empty($_SESSION['penitipan_success'])): ?>
        <div class="alert alert-success mb-3" style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:12px 16px;border-radius:8px;font-size:13px;">
          <i class="bi bi-check-circle"></i> <?= htmlspecialchars($_SESSION['penitipan_success']) ?>
        </div>
        <?php unset($_SESSION['penitipan_success']); ?>
      <?php endif; ?>

      <div class="panel">
        <div class="panel-header" style="flex-wrap:wrap;gap:12px">
          <div>
            <h2 class="section-title h5 mb-1"><i class="bi bi-table"></i> Semua Titipan</h2>
            <p class="text-muted mb-0" style="font-size:13px">Klik filter untuk menyaring berdasarkan status, atau Kelola untuk mengubah data.</p>
          </div>
          <input class="table-search" type="search" placeholder="Cari nama / hewan…" data-table-search="penitipanTable">
        </div>

        <div class="chip-tabs" data-table-target="penitipanTable">
          <button class="chip active" data-filter="all">Semua</button>
          <button class="chip" data-filter="menunggu verifikasi">Menunggu Verifikasi</button>
          <button class="chip" data-filter="aktif">Aktif</button>
          <button class="chip" data-filter="selesai">Selesai</button>
          <button class="chip" data-filter="batal">Dibatalkan</button>
        </div>

        <div class="table-responsive">
          <table id="penitipanTable">
            <thead>
              <tr>
                <th>ID</th><th>Hewan</th><th>Pemilik</th><th>Kandang</th><th>Masuk</th><th>Keluar</th><th>Petugas</th><th>Status</th><th class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($daftar) === 0): ?>
              <tr><td colspan="9" style="text-align:center;padding:40px;color:var(--text-muted);">Belum ada data titipan.</td></tr>
              <?php else: foreach ($daftar as $d):
                $statusMap = [
                  'menunggu verifikasi' => ['label'=>'Menunggu Verifikasi','badge'=>'badge-warning'],
                  'aktif'   => ['label'=>'Aktif',      'badge'=>'badge-success'],
                  'selesai' => ['label'=>'Selesai',     'badge'=>'badge-secondary'],
                  'batal'   => ['label'=>'Dibatalkan',  'badge'=>'badge-danger'],
                ];
                $st = $statusMap[strtolower($d['status'])] ?? ['label'=>$d['status'],'badge'=>''];
              ?>
              <tr data-status="<?= strtolower($d['status']) ?>">
                <td class="fw-semibold">#PS-<?= str_pad($d['id_penitipan'], 3, '0', STR_PAD_LEFT) ?></td>
                <td><?= htmlspecialchars($d['nama_hewan']) ?> <span class="text-muted small">(<?= htmlspecialchars($d['jenis_hewan']) ?>)</span></td>
                <td><?= htmlspecialchars($d['nama_pemilik']) ?></td>
                <td><?= htmlspecialchars($d['kandang'] ?? '-') ?></td>
                <td><?= date('d M Y', strtotime($d['tanggal_masuk'])) ?></td>
                <td><?= $d['tanggal_keluar'] ? date('d M Y', strtotime($d['tanggal_keluar'])) : '-' ?></td>
                <td><?= htmlspecialchars($d['nama_petugas'] ?? '-') ?></td>
                <td><span class="badge <?= $st['badge'] ?>"><?= $st['label'] ?></span></td>
                <td class="text-end">
                  <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalKelola<?= $d['id_penitipan'] ?>">
                    <i class="bi bi-pencil-square"></i> Kelola
                  </button>
                </td>
              </tr>

              <!-- Modal Kelola -->
              <div class="modal-overlay" id="modalKelola<?= $d['id_penitipan'] ?>">
                <div class="modal-box">
                  <div class="modal-box-header">
                    <h3 class="h6 mb-0">Kelola Titipan #PS-<?= str_pad($d['id_penitipan'], 3, '0', STR_PAD_LEFT) ?></h3>
                    <button type="button" class="icon-button" data-modal-close="modalKelola<?= $d['id_penitipan'] ?>"><i class="bi bi-x-lg"></i></button>
                  </div>
                  <form method="POST">
                    <input type="hidden" name="id_penitipan" value="<?= $d['id_penitipan'] ?>">
                    <div class="modal-box-body">
                      <div class="form-group mb-2">
                        <label class="form-label">Status</label>
                        <select class="form-control" name="status">
                          <?php foreach (['Menunggu Verifikasi','Aktif','Selesai','Batal'] as $opt): ?>
                          <option value="<?= $opt ?>" <?= $d['status'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="form-group mb-2">
                        <label class="form-label">Kandang</label>
                        <input class="form-control" type="text" name="kandang" value="<?= htmlspecialchars($d['kandang'] ?? '') ?>">
                      </div>
                      <div class="form-group mb-2">
                        <label class="form-label">Tanggal Keluar</label>
                        <input class="form-control" type="date" name="tanggal_keluar" value="<?= $d['tanggal_keluar'] ?? '' ?>">
                      </div>
                      <div class="form-group mb-0">
                        <label class="form-label">Catatan</label>
                        <textarea class="form-control" name="catatan" rows="3"><?= htmlspecialchars($d['catatan'] ?? '') ?></textarea>
                      </div>
                    </div>
                    <div class="modal-box-footer">
                      <button type="button" class="btn btn-light btn-sm" data-modal-close="modalKelola<?= $d['id_penitipan'] ?>">Batal</button>
                      <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save"></i> Simpan Perubahan</button>
                    </div>
                  </form>
                </div>
              </div>

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
