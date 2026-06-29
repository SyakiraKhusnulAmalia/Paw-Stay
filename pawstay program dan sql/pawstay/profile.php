<?php
require_once 'koneksi.php';
requireLogin();

$errors  = [];
$success = '';

$user = dbFetchOne("
    SELECT *
    FROM pemilik
    WHERE id_pemilik = ?
", [$_SESSION['id_pemilik']]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $nama_pemilik = trim($_POST['nama_pemilik'] ?? '');
        $email        = trim($_POST['email']        ?? '');
        $no_telepon   = trim($_POST['no_telepon']   ?? '');
        $no_hp        = trim($_POST['no_hp']        ?? '');
        $alamat       = trim($_POST['alamat']       ?? '');

        if ($nama_pemilik === '') {
            $errors[] = 'Nama lengkap wajib diisi.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Masukkan email yang valid.';
        }

        if (empty($errors)) {
            $existing = dbFetchOne("SELECT id_pemilik FROM pemilik WHERE email = ? AND id_pemilik != ?", [$email, $_SESSION['id_pemilik']]);
            if ($existing) {
                $errors[] = 'Email sudah digunakan akun lain.';
            }
        }

        if (empty($errors)) {
            dbExecute("
                UPDATE pemilik
                SET nama_pemilik = ?, email = ?, no_telepon = ?, no_hp = ?, alamat = ?
                WHERE id_pemilik = ?
            ", [$nama_pemilik, $email, $no_telepon, $no_hp, $alamat, $_SESSION['id_pemilik']]);

            $_SESSION['nama_pemilik'] = $nama_pemilik;
            $success = 'Profil berhasil diperbarui.';

            $user = dbFetchOne("SELECT * FROM pemilik WHERE id_pemilik = ?", [$_SESSION['id_pemilik']]);
        }
    }

    if ($action === 'update_password') {
        $password_lama  = $_POST['password_lama']  ?? '';
        $password_baru  = $_POST['password_baru']  ?? '';
        $password_ulang = $_POST['password_ulang'] ?? '';

        if (!password_verify($password_lama, $user['password'])) {
            $errors[] = 'Password lama tidak sesuai.';
        }
        if (strlen($password_baru) < 6) {
            $errors[] = 'Password baru minimal 6 karakter.';
        }
        if ($password_baru !== $password_ulang) {
            $errors[] = 'Konfirmasi password baru tidak cocok.';
        }

        if (empty($errors)) {
            $hashed = password_hash($password_baru, PASSWORD_BCRYPT);
            dbExecute("UPDATE pemilik SET password = ? WHERE id_pemilik = ?", [$hashed, $_SESSION['id_pemilik']]);
            $success = 'Password berhasil diperbarui.';
        }
    }
}

$namaUser = htmlspecialchars($user['nama_pemilik'] ?? 'Pengguna', ENT_QUOTES, 'UTF-8');
$inisial  = strtoupper(mb_substr($namaUser, 0, 1));
$email    = htmlspecialchars($user['email']      ?? '');
$noTelp   = htmlspecialchars($user['no_telepon'] ?? '');
$noHp     = htmlspecialchars($user['no_hp']      ?? '');
$alamat   = htmlspecialchars($user['alamat']     ?? '');
$username = htmlspecialchars($user['username']   ?? '');
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil | PawStay</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="style.css">
  <style>
    /* ── Profile card ── */
    .profile-card { overflow:hidden; padding:0 0 24px; }
    .profile-cover {
      height: 110px;
      background: linear-gradient(135deg, var(--brand-primary) 0%, #6366f1 100%);
      position: relative;
    }
    .profile-photo {
      width: 80px; height: 80px;
      border-radius: 50%;
      border: 3px solid var(--bg-surface);
      background: var(--brand-accent-light);
      display: flex; align-items: center; justify-content: center;
      font-size: 32px; font-weight: 700;
      color: var(--brand-primary);
      position: absolute;
      left: 50%; transform: translateX(-50%);
      bottom: -40px;
      line-height: 1;
    }
    .profile-card-body { padding: 52px 24px 0; text-align: center; }
    .profile-card-name { font-size:18px; font-weight:700; margin-bottom:2px; }
    .profile-card-role { font-size:13px; color:var(--text-muted); margin-bottom:14px; }
    .info-list { margin-top:16px; text-align:left; display:flex; flex-direction:column; gap:10px; }
    .info-list > div { display:flex; justify-content:space-between; font-size:13px; padding:8px 0; border-bottom:1px solid var(--border-color); }
    .info-list > div:last-child { border-bottom:none; }
    .info-list span { color:var(--text-muted); }

    /* ── Alerts ── */
    .alert-error,
    .alert-success {
      border-radius: 8px;
      padding: 10px 14px;
      font-size: 13px;
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .alert-error {
      background: #fff1f0;
      border: 1px solid #ffa39e;
      color: #cf1322;
    }
    .alert-success {
      background: #f6ffed;
      border: 1px solid #b7eb8f;
      color: #389e0d;
    }
    [data-theme="dark"] .alert-error {
      background: #2a1215;
      border-color: #58181c;
      color: #ff7875;
    }
    [data-theme="dark"] .alert-success {
      background: #162312;
      border-color: #274916;
      color: #73d13d;
    }

    /* ── Password toggle ── */
    .input-password-wrap { position: relative; }
    .input-password-wrap .form-control { padding-right: 42px; }
    .toggle-password {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      color: var(--text-muted);
      padding: 0;
      font-size: 16px;
      line-height: 1;
    }
    .toggle-password:hover { color: var(--brand-primary); }
  </style>
</head>
<body>
<div class="admin-shell">
  <div class="sidebar-backdrop"></div>

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
      <a class="nav-link" href="index.php"><span class="nav-icon"><i class="bi bi-speedometer2"></i></span><span class="nav-text">Dashboard</span></a>
      <a class="nav-link" href="bookings.php"><span class="nav-icon"><i class="bi bi-calendar-check"></i></span><span class="nav-text">Titipan Saya</span></a>
      <a class="nav-link" href="add-booking.php"><span class="nav-icon"><i class="bi bi-plus-circle"></i></span><span class="nav-text">Tambah Titipan</span></a>
      <a class="nav-link" href="payments.php"><span class="nav-icon"><i class="bi bi-credit-card"></i></span><span class="nav-text">Pembayaran</span></a>
      <a class="nav-link active" href="profile.php"><span class="nav-icon"><i class="bi bi-person"></i></span><span class="nav-text">Profil</span></a>
    </nav>
    <div class="sidebar-user">
      <div class="avatar-placeholder avatar-md"><?= $inisial ?></div>
      <div><strong><?= $namaUser ?></strong><small>Pengguna Aktif</small></div>
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
            <div class="avatar-placeholder avatar-sm"><?= $inisial ?></div>
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
          <span class="page-icon"><i class="bi bi-person"></i></span>
          <div>
            <p class="eyebrow mb-1">Akun</p>
            <h1 style="font-size:22px;font-weight:700;margin-bottom:2px">Profil</h1>
            <p class="text-muted mb-0" style="font-size:13px">Kelola informasi akun dan keamanan kamu.</p>
          </div>
        </div>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="alert-error">
          <i class="bi bi-exclamation-circle-fill"></i>
          <span><?= htmlspecialchars(implode(' ', $errors)) ?></span>
        </div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert-success">
          <i class="bi bi-check-circle-fill"></i>
          <span><?= htmlspecialchars($success) ?></span>
        </div>
      <?php endif; ?>

      <div class="row g-3">

        <!-- Kartu profil -->
        <div class="col-xl-4">
          <div class="panel profile-card">
            <div class="profile-cover">
              <div class="profile-photo"><?= $inisial ?></div>
            </div>
            <div class="profile-card-body">
              <div class="profile-card-name"><?= $namaUser ?></div>
              <div class="profile-card-role">@<?= $username ?: '—' ?></div>
              <div class="d-flex justify-content-center gap-2">
                <span class="badge badge-success">Aktif</span>
                <span class="badge badge-primary">Terverifikasi</span>
              </div>
              <div class="info-list">
                <div>
                  <span><i class="bi bi-envelope"></i> Email</span>
                  <strong><?= $email ?: '—' ?></strong>
                </div>
                <div>
                  <span><i class="bi bi-telephone"></i> No. Telepon</span>
                  <strong><?= $noTelp ?: '—' ?></strong>
                </div>
                <div>
                  <span><i class="bi bi-phone"></i> No. HP</span>
                  <strong><?= $noHp ?: '—' ?></strong>
                </div>
                <div>
                  <span><i class="bi bi-geo-alt"></i> Alamat</span>
                  <strong style="max-width:160px;text-align:right"><?= $alamat ?: '—' ?></strong>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Form edit profil -->
        <div class="col-xl-8">
          <div class="panel">
            <div class="panel-header">
              <div>
                <h2 class="section-title h5 mb-1"><i class="bi bi-person-gear"></i> Ubah Data Akun</h2>
                <p class="text-muted mb-0" style="font-size:13px">Perbarui nama, email, dan kontak kamu.</p>
              </div>
            </div>
            <form method="POST" action="profile.php">
              <input type="hidden" name="action" value="update_profile">
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Nama Lengkap</label>
                  <input class="form-control" type="text" name="nama_pemilik" value="<?= $namaUser ?>" required>
                </div>
                <div class="form-group">
                  <label class="form-label">No. Telepon</label>
                  <input class="form-control" type="tel" name="no_telepon" value="<?= $noTelp ?>">
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Email</label>
                  <input class="form-control" type="email" name="email" value="<?= $email ?>" required>
                </div>
                <div class="form-group">
                  <label class="form-label">No. HP / WhatsApp</label>
                  <input class="form-control" type="tel" name="no_hp" value="<?= $noHp ?>">
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Alamat</label>
                <textarea class="form-control" name="alamat" rows="2"><?= $alamat ?></textarea>
              </div>
              <div class="d-flex justify-content-end mt-4">
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> Simpan Profil</button>
              </div>
            </form>

            <hr style="border-color:var(--border-color);margin:20px 0">

            <h2 class="section-title h5 mb-3" style="font-size:14px"><i class="bi bi-lock"></i> Ubah Password</h2>
            <form method="POST" action="profile.php">
              <input type="hidden" name="action" value="update_password">
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Password Lama</label>
                  <div class="input-password-wrap">
                    <input class="form-control" type="password" name="password_lama" placeholder="••••••••" required>
                    <button type="button" class="toggle-password" data-toggle-target="password_lama" aria-label="Tampilkan password"><i class="bi bi-eye"></i></button>
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label">Password Baru</label>
                  <div class="input-password-wrap">
                    <input class="form-control" type="password" name="password_baru" placeholder="Minimal 6 karakter" minlength="6" required>
                    <button type="button" class="toggle-password" data-toggle-target="password_baru" aria-label="Tampilkan password"><i class="bi bi-eye"></i></button>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Konfirmasi Password Baru</label>
                <div class="input-password-wrap">
                  <input class="form-control" type="password" name="password_ulang" placeholder="Ulangi password baru" minlength="6" required>
                  <button type="button" class="toggle-password" data-toggle-target="password_ulang" aria-label="Tampilkan password"><i class="bi bi-eye"></i></button>
                </div>
              </div>
              <div class="d-flex justify-content-end mt-4">
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> Simpan Password</button>
              </div>
            </form>
          </div>
        </div>

      </div>

    </main>
    <footer class="admin-footer">
      <span>© 2026 PawStay — Aplikasi Manajemen Penitipan Hewan</span>
      <span>Profil</span>
    </footer>
  </div>
</div>

<script src="main.php"></script>
<script>
document.querySelectorAll('.toggle-password').forEach(btn => {
  btn.addEventListener('click', () => {
    const input = btn.closest('.input-password-wrap').querySelector('input');
    const icon  = btn.querySelector('i');
    const isHidden = input.type === 'password';
    input.type   = isHidden ? 'text' : 'password';
    icon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
  });
});
</script>
</body>
</html>
