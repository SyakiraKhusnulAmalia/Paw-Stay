<?php
require_once 'koneksi.php';
requireLogin();
$namaUser = namaUser();

$idPemilik = (int) $_SESSION['id_pemilik'];
$dataPemilik = dbFetchOne(
    "SELECT nama_pemilik, no_telepon, email, alamat FROM pemilik WHERE id_pemilik = ?",
    [$idPemilik]
);
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Titipan | PawStay</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="style.css">
  <style>
    .invalid-feedback { display:none; color:var(--danger); font-size:11.5px; margin-top:4px; }
    form.was-validated .form-control:invalid,
    form.was-validated .form-select:invalid { border-color: var(--danger); }
    form.was-validated .form-control:invalid + .invalid-feedback,
    form.was-validated .form-select:invalid + .invalid-feedback { display: block; }
    form.was-validated .form-control:valid { border-color: #52C41A; }
    .form-section { margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color); }
    .form-section:last-child { border-bottom: none; }
    .form-section-title { font-size: 13px; font-weight: 700; color: var(--text-secondary); margin-bottom: 14px; text-transform: uppercase; letter-spacing: .05em; }
    .fee-preview { background: var(--bg-body); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; margin-top: 16px; }
    .fee-row { display: flex; justify-content: space-between; font-size: 13px; padding: 5px 0; }
    .fee-total { border-top: 1px solid var(--border-color); margin-top: 8px; padding-top: 8px; font-weight: 700; font-size: 15px; }
    /* Badge warna untuk status kandang */
    .cage-badge-available { color: #15803d; background: #dcfce7; font-size: 11px; padding: 1px 7px; border-radius: 99px; font-weight: 600; }
    .cage-badge-taken     { color: #b91c1c; background: #fee2e2; font-size: 11px; padding: 1px 7px; border-radius: 99px; font-weight: 600; }
    #cageLoadingMsg { font-size: 12px; color: var(--text-secondary); margin-top: 5px; display: none; }
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
      <a class="nav-link active" href="add-booking.php"><span class="nav-icon"><i class="bi bi-plus-circle"></i></span><span class="nav-text">Tambah Titipan</span></a>
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
          <span class="page-icon"><i class="bi bi-plus-circle"></i></span>
          <div>
            <p class="eyebrow mb-1">Penitipan</p>
            <h1 style="font-size:22px;font-weight:700;margin-bottom:2px">Tambah Titipan</h1>
            <p class="text-muted mb-0" style="font-size:13px">Isi form berikut untuk mendaftarkan hewan baru.</p>
          </div>
        </div>
        <a class="btn btn-outline btn-sm" href="bookings.php"><i class="bi bi-arrow-left"></i> Kembali</a>
      </div>

      <div class="alert mb-3" style="background:#eff6ff;border:1px solid #93c5fd;color:#1e40af;padding:12px 16px;border-radius:8px;font-size:13px;">
        <i class="bi bi-info-circle"></i> Setelah mengisi formulir penitipan, harap melakukan pembayaran secara tunai di PawStay.
      </div>

      <div class="row g-3">
        <!-- Main form -->
        <div class="col-xl-8">
          <div class="panel">
            <form class="needs-validation" id="bookingForm" method="POST" action="proses_booking.php" novalidate>

              <!-- Data Pemilik -->
              <div class="form-section">
                <div class="form-section-title"><i class="bi bi-person"></i> Data Pemilik</div>
                <div style="background:var(--bg-body); border:1px solid var(--border-color); border-radius:var(--radius-sm); padding:14px 16px; display:flex; flex-direction:column; gap:6px; font-size:13px;">
                  <div><strong>Nama:</strong> <?= htmlspecialchars($dataPemilik['nama_pemilik']) ?></div>
                  <div><strong>No. HP:</strong> <?= htmlspecialchars($dataPemilik['no_telepon']) ?></div>
                  <?php if (!empty($dataPemilik['email'])): ?>
                    <div><strong>Email:</strong> <?= htmlspecialchars($dataPemilik['email']) ?></div>
                  <?php endif; ?>
                  <?php if (!empty($dataPemilik['alamat'])): ?>
                    <div><strong>Alamat:</strong> <?= htmlspecialchars($dataPemilik['alamat']) ?></div>
                  <?php endif; ?>
                  <p class="text-muted mb-0" style="font-size:12px;margin-top:4px;">
                    Titipan akan didaftarkan atas nama akun ini. Ingin mengubah data di atas? Buka <a href="profile.php">Profil Saya</a>.
                  </p>
                </div>
              </div>

              <!-- Data Hewan -->
              <div class="form-section">
                <div class="form-section-title"><i class="bi bi-heart"></i> Data Hewan</div>
                <div class="form-row">
                  <div class="form-group">
                    <label class="form-label" for="petName">Nama Hewan *</label>
                    <input class="form-control" id="petName" name="petName" type="text" placeholder="Contoh: Rocky" required>
                    <div class="invalid-feedback">Nama hewan wajib diisi.</div>
                  </div>
                  <div class="form-group">
                    <label class="form-label" for="petSpecies">Jenis Hewan *</label>
                    <select class="form-select" id="petSpecies" name="petSpecies" required onchange="onSpeciesChange()">
                      <option value="">Pilih jenis hewan</option>
                      <option>Anjing</option>
                      <option>Kucing</option>
                      <option>Burung</option>
                      <option>Kelinci</option>
                      <option>Hamster</option>
                      <option>Reptil</option>
                      <option>Lainnya</option>
                    </select>
                    <div class="invalid-feedback">Pilih jenis hewan.</div>
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label class="form-label" for="petBreed">Ras / Jenis</label>
                    <input class="form-control" id="petBreed" name="petBreed" type="text" placeholder="Contoh: Golden Retriever">
                  </div>
                  <div class="form-group">
                    <label class="form-label" for="petAge">Usia</label>
                    <input class="form-control" id="petAge" name="petAge" type="text" placeholder="Contoh: 2 tahun">
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label class="form-label" for="petGender">Jenis Kelamin</label>
                    <select class="form-select" id="petGender" name="petGender">
                      <option value="">Pilih</option>
                      <option>Jantan</option>
                      <option>Betina</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label class="form-label" for="petWeight">Berat Badan</label>
                    <input class="form-control" id="petWeight" name="petWeight" type="text" placeholder="Contoh: 5 kg">
                  </div>
                </div>
                <div class="form-group" style="margin-bottom:0">
                  <label class="form-label" for="petNotes">Catatan Khusus / Kondisi Kesehatan</label>
                  <textarea class="form-control" id="petNotes" name="petNotes" rows="3" placeholder="Alergi, obat rutin, kebiasaan, dll."></textarea>
                </div>
              </div>

              <!-- Jadwal Penitipan -->
              <div class="form-section">
                <div class="form-section-title"><i class="bi bi-calendar3"></i> Jadwal Penitipan</div>
                <div class="form-row">
                  <div class="form-group">
                    <label class="form-label" for="checkIn">Tanggal Masuk *</label>
                    <input class="form-control" id="checkIn" name="checkIn" type="date" required onchange="onDateChange()">
                    <div class="invalid-feedback">Tanggal masuk wajib diisi.</div>
                  </div>
                  <div class="form-group">
                    <label class="form-label" for="checkOut">Tanggal Pulang *</label>
                    <input class="form-control" id="checkOut" name="checkOut" type="date" required onchange="onDateChange()">
                    <div class="invalid-feedback">Tanggal pulang wajib diisi.</div>
                  </div>
                </div>
                <div class="form-row" style="margin-bottom:0">

                  <!-- Nomor Kandang: hanya tampil untuk Anjing, Kucing, Burung -->
                  <div class="form-group" id="cageGroup" style="display:none">
                    <label class="form-label" for="cageRoom">Nomor Kandang</label>
                    <select class="form-select" id="cageRoom" name="cageRoom">
                      <option value="">— Pilih tanggal dulu —</option>
                    </select>
                    <div id="cageLoadingMsg"><i class="bi bi-hourglass-split"></i> Mengecek ketersediaan kandang...</div>
                    <small class="text-muted" style="font-size:12px;">
                      <i class="bi bi-info-circle"></i> Kandang <span style="color:#15803d">✔ Tersedia</span> bisa dipilih; <span style="color:#b91c1c">✖ Terisi</span> tidak bisa dipilih.
                    </small>
                  </div>

                  <div class="form-group">
                    <label class="form-label" for="packageType">Paket Perawatan *</label>
                    <select class="form-select" id="packageType" name="packageType" required onchange="calcFee()">
                      <option value="">Pilih paket</option>
                      <option value="75000">Basic — Rp 75.000/malam</option>
                      <option value="120000">Standard — Rp 120.000/malam</option>
                      <option value="180000">Premium — Rp 180.000/malam</option>
                    </select>
                    <div class="invalid-feedback">Pilih paket perawatan.</div>
                  </div>
                </div>
              </div>

              <!-- Layanan Tambahan -->
              <div class="form-section">
                <div class="form-section-title"><i class="bi bi-stars"></i> Layanan Tambahan (Opsional)</div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px">
                  <label class="form-check" style="border:1px solid var(--border-color);border-radius:var(--radius-sm);padding:10px 12px;cursor:pointer">
                    <input class="form-check-input" type="checkbox" value="50000" onchange="calcFee()"> &nbsp;
                    <span style="font-size:13px"><strong>Grooming</strong><br><small class="text-muted">+Rp 50.000</small></span>
                  </label>
                  <label class="form-check" style="border:1px solid var(--border-color);border-radius:var(--radius-sm);padding:10px 12px;cursor:pointer">
                    <input class="form-check-input" type="checkbox" value="35000" onchange="calcFee()"> &nbsp;
                    <span style="font-size:13px"><strong>Vaksinasi Rutin</strong><br><small class="text-muted">+Rp 35.000</small></span>
                  </label>
                  <label class="form-check" style="border:1px solid var(--border-color);border-radius:var(--radius-sm);padding:10px 12px;cursor:pointer">
                    <input class="form-check-input" type="checkbox" value="25000" onchange="calcFee()"> &nbsp;
                    <span style="font-size:13px"><strong>Vitamin Harian</strong><br><small class="text-muted">+Rp 25.000</small></span>
                  </label>
                  <label class="form-check" style="border:1px solid var(--border-color);border-radius:var(--radius-sm);padding:10px 12px;cursor:pointer">
                    <input class="form-check-input" type="checkbox" value="40000" onchange="calcFee()"> &nbsp;
                    <span style="font-size:13px"><strong>Laporan Foto Harian</strong><br><small class="text-muted">+Rp 40.000</small></span>
                  </label>
                </div>
              </div>

              <div class="d-flex justify-content-end gap-2 mt-4">
                <a class="btn btn-outline" href="bookings.php">Batal</a>
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> Simpan Titipan</button>
              </div>

            </form>
          </div>
        </div>

        <!-- Summary panel -->
        <div class="col-xl-4">
          <div class="panel" style="position:sticky;top:76px">
            <h2 class="section-title h5 mb-3"><i class="bi bi-receipt"></i> Ringkasan Biaya</h2>

            <div class="fee-preview">
              <div class="fee-row"><span>Paket perawatan</span><span id="feePkg">—</span></div>
              <div class="fee-row"><span>Jumlah malam</span><span id="feeNights">—</span></div>
              <div class="fee-row"><span>Subtotal menginap</span><span id="feeSub">—</span></div>
              <div class="fee-row"><span>Layanan tambahan</span><span id="feeExtra">—</span></div>
              <div class="fee-row fee-total"><span>Total Estimasi</span><span id="feeTotal" style="color:var(--brand-primary)">—</span></div>
            </div>

    </main>
    <footer class="admin-footer">
      <span>© 2026 PawStay — Aplikasi Manajemen Penitipan Hewan</span>
      <span>Form Tambah Titipan</span>
    </footer>
  </div>
</div>
<script src="main.php"></script>
<script>
const CAGE_SPECIES = ['Anjing', 'Kucing', 'Burung'];

// Dipanggil saat jenis hewan berubah
function onSpeciesChange() {
  const species = document.getElementById('petSpecies').value;
  const cageGroup = document.getElementById('cageGroup');
  const cageSelect = document.getElementById('cageRoom');

  if (CAGE_SPECIES.includes(species)) {
    cageGroup.style.display = '';
    loadKandang(); // cek DB kalau tanggal sudah diisi
  } else {
    cageGroup.style.display = 'none';
    cageSelect.value = '';
  }
}

// Dipanggil saat tanggal masuk/pulang berubah
function onDateChange() {
  calcFee();
  const species = document.getElementById('petSpecies').value;
  if (CAGE_SPECIES.includes(species)) {
    loadKandang();
  }
}

// Fetch ketersediaan kandang dari server via AJAX
function loadKandang() {
  const species  = document.getElementById('petSpecies').value;
  const checkIn  = document.getElementById('checkIn').value;
  const checkOut = document.getElementById('checkOut').value;
  const select   = document.getElementById('cageRoom');
  const loading  = document.getElementById('cageLoadingMsg');

  // Butuh semua tiga nilai dulu
  if (!species || !checkIn || !checkOut) {
    select.innerHTML = '<option value="">— Pilih tanggal dulu —</option>';
    return;
  }
  if (checkOut <= checkIn) {
    select.innerHTML = '<option value="">— Tanggal pulang harus setelah tanggal masuk —</option>';
    return;
  }

  // Tampilkan indikator loading
  loading.style.display = 'block';
  select.innerHTML = '<option value="">Mengecek ketersediaan...</option>';
  select.disabled = true;

  fetch(`get_kandang.php?species=${encodeURIComponent(species)}&checkIn=${checkIn}&checkOut=${checkOut}`)
    .then(res => res.json())
    .then(data => {
      loading.style.display = 'none';
      select.disabled = false;

      if (data.error) {
        select.innerHTML = '<option value="">Gagal memuat data kandang</option>';
        return;
      }

      // Bangun opsi dropdown
      let html = '<option value="">Pilih kandang</option>';
      data.forEach(k => {
        if (k.tersedia) {
          html += `<option value="${k.kode}">${k.kode} &nbsp;✔ Tersedia</option>`;
        } else {
          html += `<option value="${k.kode}" disabled>${k.kode} &nbsp;✖ Terisi</option>`;
        }
      });
      select.innerHTML = html;
    })
    .catch(() => {
      loading.style.display = 'none';
      select.disabled = false;
      select.innerHTML = '<option value="">Gagal memuat data kandang</option>';
    });
}

function calcFee() {
  const pkg    = document.getElementById('packageType').value;
  const inVal  = document.getElementById('checkIn').value;
  const outVal = document.getElementById('checkOut').value;

  let nights = 0;
  if (inVal && outVal) {
    const diff = (new Date(outVal) - new Date(inVal)) / (1000*60*60*24);
    nights = diff > 0 ? diff : 0;
  }

  const pkgPrice = pkg ? parseInt(pkg) : 0;
  const sub = pkgPrice * nights;

  let extra = 0;
  document.querySelectorAll('input[type=checkbox]:checked').forEach(cb => {
    extra += parseInt(cb.value);
  });

  const total = sub + extra;
  const fmt = n => n > 0 ? 'Rp ' + n.toLocaleString('id-ID') : '—';
  const pkgLabel = pkg === '75000' ? 'Basic' : pkg === '120000' ? 'Standard' : pkg === '180000' ? 'Premium' : '—';

  document.getElementById('feePkg').textContent    = pkgLabel;
  document.getElementById('feeNights').textContent = nights > 0 ? nights + ' malam' : '—';
  document.getElementById('feeSub').textContent    = fmt(sub);
  document.getElementById('feeExtra').textContent  = fmt(extra);
  document.getElementById('feeTotal').textContent  = fmt(total);
}
</script>
</body>
</html>