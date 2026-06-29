<?php
require_once 'koneksi.php';
requireLoginPetugas();
$namaPetugas = namaPetugas();
$activePage  = 'petugas-dashboard.php';

// ── Statistik ringkas ──
$menungguVerifikasi = dbFetchOne("SELECT COUNT(*) AS c FROM penitipan WHERE status = 'Menunggu Verifikasi'");
$titipanAktif        = dbFetchOne("SELECT COUNT(*) AS c FROM penitipan WHERE status = 'Aktif'");
$jadwalJemputHariIni  = dbFetchOne("SELECT COUNT(*) AS c FROM penjemputan WHERE status = 'Dijadwalkan' AND tanggal_dijadwalkan = CURDATE()");
$kondisiPerluPerhatian = dbFetchOne("
  SELECT COUNT(*) AS c FROM kondisi_hewan k
  JOIN penitipan pt ON pt.id_penitipan = k.id_penitipan
  WHERE pt.status = 'Aktif'
    AND k.kondisi IN ('Sakit','Perlu Perhatian Khusus','Kurang Sehat')
    AND k.id_kondisi IN (SELECT MAX(id_kondisi) FROM kondisi_hewan GROUP BY id_penitipan)
");

// ── Titipan menunggu verifikasi (preview) ──
$daftarVerifikasi = dbFetchAll("
  SELECT pt.id_penitipan, pt.tanggal_masuk, pt.tanggal_keluar, h.nama_hewan, h.jenis_hewan, pm.nama_pemilik
  FROM penitipan pt
  JOIN hewan h ON h.id_hewan = pt.id_hewan
  JOIN pemilik pm ON pm.id_pemilik = h.id_pemilik
  WHERE pt.status = 'Menunggu Verifikasi'
  ORDER BY pt.tanggal_masuk ASC
  LIMIT 5
");

// ── Jadwal penjemputan terdekat ──
$jadwalJemput = dbFetchAll("
  SELECT pj.id_penjemputan, pj.tanggal_dijadwalkan, h.nama_hewan, h.jenis_hewan, pm.nama_pemilik, pt.kandang
  FROM penjemputan pj
  JOIN penitipan pt ON pt.id_penitipan = pj.id_penitipan
  JOIN hewan h ON h.id_hewan = pt.id_hewan
  JOIN pemilik pm ON pm.id_pemilik = h.id_pemilik
  WHERE pj.status = 'Dijadwalkan'
  ORDER BY pj.tanggal_dijadwalkan ASC
  LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Petugas | PawStay</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'partials/petugas-header.php'; ?>

      <div class="page-heading">
        <div class="page-heading-copy">
          <span class="page-icon"><i class="bi bi-speedometer2"></i></span>
          <div>
            <p class="eyebrow mb-1">Dashboard</p>
            <h1 style="font-size:22px;font-weight:700;margin-bottom:2px">Halo, <?= $namaPetugas ?> 👋</h1>
            <p class="text-muted mb-0" style="font-size:13px">Ringkasan operasional penitipan hewan hari ini.</p>
          </div>
        </div>
      </div>

      <!-- Kartu Statistik -->
      <div class="row g-3 mb-3">
        <div class="col-md-3 col-6">
          <div class="stat-card" style="border-left:4px solid #2D6A4F;">
            <div class="stat-label"><i class="bi bi-clipboard2-data text-success"></i> Titipan Aktif</div>
            <div class="stat-value"><?= $titipanAktif['c'] ?></div>
            <div class="stat-sub">Sedang dititipkan</div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="stat-card" style="border-left:4px solid #457B9D;">
            <div class="stat-label"><i class="bi bi-box-arrow-in-right text-info"></i> Jemput Hari Ini</div>
            <div class="stat-value"><?= $jadwalJemputHariIni['c'] ?></div>
            <div class="stat-sub">Dijadwalkan</div>
          </div>
        </div>
        <div class="col-md-3 col-6">
          <div class="stat-card" style="border-left:4px solid #E63946;">
            <div class="stat-label"><i class="bi bi-heart-pulse text-danger"></i> Perlu Perhatian</div>
            <div class="stat-value"><?= $kondisiPerluPerhatian['c'] ?></div>
            <div class="stat-sub">Kondisi hewan</div>
          </div>
        </div>
      </div>

      <div class="row g-3">
        <!-- Titipan menunggu verifikasi -->


        <!-- Jadwal penjemputan terdekat -->
        <div class="col-lg-6">
          <div class="panel h-100">
            <div class="panel-header">
              <h2 class="section-title h5 mb-0"><i class="bi bi-box-arrow-in-right"></i> Jadwal Penjemputan</h2>
              <a href="petugas-penjemputan.php" class="btn btn-light btn-sm">Lihat Semua</a>
            </div>
            <div class="table-responsive">
              <table>
                <thead>
                  <tr><th>Hewan</th><th>Pemilik</th><th>Kandang</th><th>Tanggal</th></tr>
                </thead>
                <tbody>
                  <?php if (count($jadwalJemput) === 0): ?>
                  <tr><td colspan="4" style="text-align:center;padding:24px;color:var(--text-muted);">Tidak ada jadwal penjemputan.</td></tr>
                  <?php else: foreach ($jadwalJemput as $j): ?>
                  <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($j['nama_hewan']) ?> <span class="text-muted small">(<?= htmlspecialchars($j['jenis_hewan']) ?>)</span></td>
                    <td><?= htmlspecialchars($j['nama_pemilik']) ?></td>
                    <td><?= htmlspecialchars($j['kandang'] ?? '-') ?></td>
                    <td><?= $j['tanggal_dijadwalkan'] ? date('d M Y', strtotime($j['tanggal_dijadwalkan'])) : '-' ?></td>
                  </tr>
                  <?php endforeach; endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

<?php include 'partials/petugas-footer.php'; ?>
