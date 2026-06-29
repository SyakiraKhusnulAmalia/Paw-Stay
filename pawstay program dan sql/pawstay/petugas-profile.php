<?php
require_once 'koneksi.php';
requireLoginPetugas();
$activePage = 'petugas-profile.php';

$errors  = [];
$success = '';
$idPetugas = (int) $_SESSION['id_petugas'];

$petugas = dbFetchOne("SELECT * FROM petugas WHERE id_petugas = ?", [$idPetugas]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $nama  = trim($_POST['nama_petugas'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $no_hp = trim($_POST['no_hp'] ?? '');

        if ($nama === '') $errors[] = 'Nama wajib diisi.';
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Masukkan email yang valid.';

        if (empty($errors)) {
            dbExecute("UPDATE petugas SET nama_petugas = ?, email = ?, no_hp = ? WHERE id_petugas = ?", [$nama, $email ?: null, $no_hp ?: null, $idPetugas]);
            $_SESSION['nama_petugas'] = $nama;
            $success = 'Profil berhasil diperbarui.';
            $petugas = dbFetchOne("SELECT * FROM petugas WHERE id_petugas = ?", [$idPetugas]);
        }
    }

    if ($action === 'update_password') {
        $passwordLama = $_POST['password_lama'] ?? '';
        $passwordBaru = $_POST['password_baru'] ?? '';
        $passwordKonfirmasi = $_POST['password_konfirmasi'] ?? '';

        if (!password_verify($passwordLama, $petugas['password'])) {
            $errors[] = 'Password lama salah.';
        } elseif (strlen($passwordBaru) < 6) {
            $errors[] = 'Password baru minimal 6 karakter.';
        } elseif ($passwordBaru !== $passwordKonfirmasi) {
            $errors[] = 'Konfirmasi password tidak cocok.';
        }

        if (empty($errors)) {
            $hashed = password_hash($passwordBaru, PASSWORD_BCRYPT);
            dbExecute("UPDATE petugas SET password = ? WHERE id_petugas = ?", [$hashed, $idPetugas]);
            $success = 'Password berhasil diubah.';
        }
    }
}

$namaPetugas = namaPetugas();
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil Petugas | PawStay</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'partials/petugas-header.php'; ?>

      <div class="page-heading">
        <div class="page-heading-copy">
          <span class="page-icon"><i class="bi bi-person"></i></span>
          <div>
            <p class="eyebrow mb-1">Petugas</p>
            <h1 style="font-size:22px;font-weight:700;margin-bottom:2px">Profil Saya</h1>
            <p class="text-muted mb-0" style="font-size:13px">Kelola informasi akun petugas Anda.</p>
          </div>
        </div>
      </div>

      <?php if ($success): ?>
        <div class="alert alert-success mb-3" style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:12px 16px;border-radius:8px;font-size:13px;">
          <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>
      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger mb-3" style="background:#FEE2E2;border:1px solid #FCA5A5;color:#991B1B;padding:12px 16px;border-radius:8px;font-size:13px;">
          <?php foreach ($errors as $e): ?><div><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="row g-3">
        <div class="col-lg-6">
          <div class="panel">
            <div class="panel-header"><h2 class="section-title h6 mb-0"><i class="bi bi-person-lines-fill"></i> Informasi Akun</h2></div>
            <form method="POST" style="padding:18px;">
              <input type="hidden" name="action" value="update_profile">
              <div class="form-group mb-2">
                <label class="form-label">Nama Lengkap</label>
                <input class="form-control" type="text" name="nama_petugas" value="<?= htmlspecialchars($petugas['nama_petugas']) ?>" required>
              </div>
              <div class="form-group mb-2">
                <label class="form-label">Username</label>
                <input class="form-control" type="text" value="<?= htmlspecialchars($petugas['username']) ?>" disabled>
              </div>
              <div class="form-group mb-2">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" name="email" value="<?= htmlspecialchars($petugas['email'] ?? '') ?>">
              </div>
              <div class="form-group mb-2">
                <label class="form-label">No. HP</label>
                <input class="form-control" type="text" name="no_hp" value="<?= htmlspecialchars($petugas['no_hp'] ?? '') ?>">
              </div>
              <div class="form-group mb-3">
                <label class="form-label">Jabatan</label>
                <input class="form-control" type="text" value="<?= htmlspecialchars($petugas['jabatan'] ?? '-') ?>" disabled>
              </div>
              <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Simpan Perubahan</button>
            </form>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="panel">
            <div class="panel-header"><h2 class="section-title h6 mb-0"><i class="bi bi-shield-lock"></i> Ganti Password</h2></div>
            <form method="POST" style="padding:18px;">
              <input type="hidden" name="action" value="update_password">
              <div class="form-group mb-2">
                <label class="form-label">Password Lama</label>
                <input class="form-control" type="password" name="password_lama" required>
              </div>
              <div class="form-group mb-2">
                <label class="form-label">Password Baru</label>
                <input class="form-control" type="password" name="password_baru" required minlength="6">
              </div>
              <div class="form-group mb-3">
                <label class="form-label">Konfirmasi Password Baru</label>
                <input class="form-control" type="password" name="password_konfirmasi" required minlength="6">
              </div>
              <button class="btn btn-primary" type="submit"><i class="bi bi-shield-check"></i> Ubah Password</button>
            </form>
          </div>
        </div>
      </div>

<?php include 'partials/petugas-footer.php'; ?>
