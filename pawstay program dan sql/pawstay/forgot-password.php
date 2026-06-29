<?php
require_once 'koneksi.php';

$step    = 'verify'; // 'verify' | 'reset'
$errors  = [];
$success = '';
$id_pemilik = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── Step 1: Verifikasi username + email ──
    if ($action === 'verify') {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email']    ?? '');

        if ($username === '' || $email === '') {
            $errors[] = 'Username dan email wajib diisi.';
        } else {
            $user = dbFetchOne("
                SELECT id_pemilik FROM pemilik
                WHERE username = ? AND email = ?
            ", [$username, $email]);

            if (!$user) {
                $errors[] = 'Username dan email tidak cocok dengan data kami.';
            } else {
                $id_pemilik = $user['id_pemilik'];
                $step = 'reset';
            }
        }
    }

    // ── Step 2: Set password baru ──
    if ($action === 'reset') {
        $id_pemilik     = (int)($_POST['id_pemilik'] ?? 0);
        $username       = trim($_POST['username'] ?? '');
        $email          = trim($_POST['email'] ?? '');
        $password_baru  = $_POST['password_baru']  ?? '';
        $password_ulang = $_POST['password_ulang'] ?? '';

        // Verifikasi ulang agar tidak bisa langsung POST tahap 2 tanpa lewat tahap 1
        $user = dbFetchOne("
            SELECT id_pemilik FROM pemilik
            WHERE id_pemilik = ? AND username = ? AND email = ?
        ", [$id_pemilik, $username, $email]);

        if (!$user) {
            $errors[] = 'Sesi reset password tidak valid. Silakan ulangi dari awal.';
            $step = 'verify';
        } elseif (strlen($password_baru) < 6) {
            $errors[] = 'Password baru minimal 6 karakter.';
            $step = 'reset';
        } elseif ($password_baru !== $password_ulang) {
            $errors[] = 'Konfirmasi password tidak cocok.';
            $step = 'reset';
        } else {
            $hashed = password_hash($password_baru, PASSWORD_BCRYPT);
            dbExecute("UPDATE pemilik SET password = ? WHERE id_pemilik = ?", [$hashed, $id_pemilik]);
            $success = 'Password berhasil diubah. Silakan masuk dengan password baru kamu.';
            $step = 'done';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password | PawStay</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .auth-body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-body);
        }

        .paw-hero {
            font-size: 52px;
            margin-bottom: 8px;
        }

        .login-tagline {
            color: var(--text-muted);
            font-size: 13px;
            margin-bottom: 24px;
        }

        .input-password-wrap {
            position: relative;
        }

        .input-password-wrap .form-control {
            padding-right: 42px;
        }

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

        .toggle-password:hover {
            color: var(--brand-primary);
        }

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
    </style>
</head>

<body class="auth-body">
    <button class="icon-button auth-theme-toggle" data-theme-toggle aria-label="Ganti tema">
        <i class="bi bi-moon-stars" data-theme-icon></i>
    </button>

    <div class="auth-page">
        <div class="auth-card">
            <div class="text-center mb-4">
                <div class="paw-hero">🐾</div>
                <div style="font-size:24px; font-weight:800; color:var(--brand-primary)">PawStay</div>
                <div class="login-tagline">Reset Password Akun</div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert-error">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <span><?= htmlspecialchars(implode(' ', $errors)) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($step === 'verify'): ?>
                <!-- ── Step 1: Verifikasi identitas ── -->
                <p class="text-muted mb-3" style="font-size:13px">
                    Masukkan username dan email yang terdaftar pada akun kamu.
                </p>
                <form action="forgot-password.php" method="POST">
                    <input type="hidden" name="action" value="verify">

                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input class="form-control" type="text" name="username" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input class="form-control" type="email" name="email" required>
                    </div>

                    <button class="btn btn-primary w-100 btn-lg" type="submit">
                        <i class="bi bi-search"></i> Verifikasi Akun
                    </button>
                </form>

            <?php elseif ($step === 'reset'): ?>
                <!-- ── Step 2: Set password baru ── -->
                <p class="text-muted mb-3" style="font-size:13px">
                    Akun ditemukan. Masukkan password baru kamu.
                </p>
                <form action="forgot-password.php" method="POST">
                    <input type="hidden" name="action" value="reset">
                    <input type="hidden" name="id_pemilik" value="<?= htmlspecialchars($id_pemilik) ?>">
                    <input type="hidden" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

                    <div class="form-group">
                        <label class="form-label">Password Baru</label>
                        <div class="input-password-wrap">
                            <input class="form-control" type="password" name="password_baru" minlength="6" placeholder="Minimal 6 karakter" required>
                            <button type="button" class="toggle-password" data-toggle-target="password_baru" aria-label="Tampilkan password"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <div class="input-password-wrap">
                            <input class="form-control" type="password" name="password_ulang" minlength="6" placeholder="Ulangi password baru" required>
                            <button type="button" class="toggle-password" data-toggle-target="password_ulang" aria-label="Tampilkan password"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>

                    <button class="btn btn-primary w-100 btn-lg" type="submit">
                        <i class="bi bi-check2-circle"></i> Simpan Password Baru
                    </button>
                </form>

            <?php else: ?>
                <!-- ── Done ── -->
                <div class="alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
                <a class="btn btn-primary w-100 btn-lg" href="login.php">
                    <i class="bi bi-box-arrow-in-right"></i> Ke Halaman Masuk
                </a>
            <?php endif; ?>

            <div class="auth-footer"><a href="login.php">← Kembali ke halaman masuk</a></div>
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
