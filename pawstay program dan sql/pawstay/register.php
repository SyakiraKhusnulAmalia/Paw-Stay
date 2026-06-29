<?php
require_once 'koneksi.php';

$errors  = [];
$success = false;
$old     = ['nama_pemilik' => '', 'email' => '', 'username' => '', 'no_telepon' => '', 'no_hp' => '', 'alamat' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pemilik = trim($_POST['nama_pemilik'] ?? '');
    $email        = trim($_POST['email']        ?? '');
    $username     = trim($_POST['username']     ?? '');
    $no_telepon   = trim($_POST['no_telepon']   ?? '');
    $no_hp        = trim($_POST['no_hp']        ?? '');
    $alamat       = trim($_POST['alamat']       ?? '');
    $password     = $_POST['password']          ?? '';
    $terms        = isset($_POST['terms']);

    // --- Validasi input ---
    if ($nama_pemilik === '') {
        $errors['nama_pemilik'] = 'Nama lengkap wajib diisi.';
    }
    if ($username === '') {
        $errors['username'] = 'Username wajib diisi.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
        $errors['username'] = 'Username hanya boleh huruf, angka, dan underscore (3–30 karakter).';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Masukkan email yang valid.';
    }
    if ($no_telepon === '') {
        $errors['no_telepon'] = 'Nomor telepon wajib diisi.';
    }
    if ($no_hp === '') {
        $errors['no_hp'] = 'Nomor HP wajib diisi.';
    }
    if ($alamat === '') {
        $errors['alamat'] = 'Alamat wajib diisi.';
    }
    if (strlen($password) < 6) {
        $errors['password'] = 'Password minimal 6 karakter.';
    }
    if (!$terms) {
        $errors['terms'] = 'Kamu harus menyetujui syarat & ketentuan.';
    }

    // --- Cek duplikat email & username ---
    if (empty($errors)) {
        if (dbFetchOne("SELECT id_pemilik FROM pemilik WHERE email = ?", [$email])) {
            $errors['email'] = 'Email sudah terdaftar.';
        }
        if (dbFetchOne("SELECT id_pemilik FROM pemilik WHERE username = ?", [$username])) {
            $errors['username'] = 'Username sudah dipakai.';
        }
    }

    // --- Simpan ke database ---
    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);

        dbExecute(
            "INSERT INTO pemilik (nama_pemilik, no_telepon, email, alamat, username, no_hp, password, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
            [$nama_pemilik, $no_telepon, $email, $alamat, $username, $no_hp, $hashed]
        );

        $success = true;
    } else {
        $old = compact('nama_pemilik', 'email', 'username', 'no_telepon', 'no_hp', 'alamat');
        array_walk($old, fn(&$v) => $v = htmlspecialchars($v));
    }
}

function err(array $errors, string $key): string {
    return isset($errors[$key])
        ? '<div class="invalid-feedback-custom">' . htmlspecialchars($errors[$key]) . '</div>'
        : '';
}
function cls(array $errors, string $key): string {
    return isset($errors[$key]) ? ' is-invalid' : '';
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar | PawStay</title>
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

        .register-tagline {
            color: var(--text-muted);
            font-size: 13px;
            margin-bottom: 24px;
        }

        /* ── Error / Success messages ── */
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

        /* ── Field error text ── */
        .invalid-feedback-custom {
            color: var(--danger, #cf1322);
            font-size: 11.5px;
            margin-top: 4px;
        }

        .form-control.is-invalid {
            border-color: var(--danger, #cf1322);
        }

        /* ── Section label ── */
        .section-divider {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border-color, #dee2e6);
            padding-bottom: 4px;
            margin: 20px 0 14px;
        }

        /* ── Password toggle ── */
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

        /* ── Two-column row ── */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        @media (max-width: 400px) {
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body class="auth-body">
    <button class="icon-button auth-theme-toggle" data-theme-toggle aria-label="Ganti tema">
        <i class="bi bi-moon-stars" data-theme-icon></i>
    </button>

    <div class="auth-page">
        <div class="auth-card">

            <!-- Brand -->
            <div class="text-center mb-4">
                <div class="paw-hero">🐾</div>
                <div style="font-size:24px; font-weight:800; color:var(--brand-primary)">PawStay</div>
                <div class="register-tagline">Aplikasi Manajemen Penitipan Hewan</div>
            </div>

            <?php if ($success): ?>
                <!-- Sukses -->
                <div class="alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Akun berhasil dibuat! <a href="login.php">Masuk sekarang →</a></span>
                </div>

            <?php else: ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert-error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <span>Terdapat kesalahan. Periksa isian di bawah.</span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="register.php" novalidate>

                    <!-- ── Data Akun ── -->
                    <div class="section-divider">Data Akun</div>

                    <div class="form-group">
                        <label class="form-label" for="username">Username</label>
                        <input class="form-control<?= cls($errors,'username') ?>"
                               id="username" name="username" type="text"
                               value="<?= $old['username'] ?>"
                               placeholder="contoh: johndoe123"
                               autocomplete="username" required>
                        <?= err($errors, 'username') ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-password-wrap">
                            <input class="form-control<?= cls($errors,'password') ?>"
                                   id="password" name="password"
                                   type="password" minlength="6"
                                   placeholder="Minimal 6 karakter"
                                   autocomplete="new-password" required>
                            <button type="button" class="toggle-password" id="togglePassword" aria-label="Tampilkan password">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        <?= err($errors, 'password') ?>
                    </div>

                    <!-- ── Data Pribadi ── -->
                    <div class="section-divider">Data Pribadi</div>

                    <div class="form-group">
                        <label class="form-label" for="nama_pemilik">Nama Lengkap</label>
                        <input class="form-control<?= cls($errors,'nama_pemilik') ?>"
                               id="nama_pemilik" name="nama_pemilik" type="text"
                               value="<?= $old['nama_pemilik'] ?>"
                               placeholder="Nama sesuai KTP" required>
                        <?= err($errors, 'nama_pemilik') ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input class="form-control<?= cls($errors,'email') ?>"
                               id="email" name="email" type="email"
                               value="<?= $old['email'] ?>"
                               placeholder="contoh@email.com"
                               autocomplete="email" required>
                        <?= err($errors, 'email') ?>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="no_telepon">No. Telepon</label>
                            <input class="form-control<?= cls($errors,'no_telepon') ?>"
                                   id="no_telepon" name="no_telepon" type="tel"
                                   value="<?= $old['no_telepon'] ?>"
                                   placeholder="021xxxxxxx" required>
                            <?= err($errors, 'no_telepon') ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="no_hp">No. HP / WA</label>
                            <input class="form-control<?= cls($errors,'no_hp') ?>"
                                   id="no_hp" name="no_hp" type="tel"
                                   value="<?= $old['no_hp'] ?>"
                                   placeholder="08xxxxxxxxxx" required>
                            <?= err($errors, 'no_hp') ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="alamat">Alamat Lengkap</label>
                        <textarea class="form-control<?= cls($errors,'alamat') ?>"
                                  id="alamat" name="alamat" rows="2"
                                  placeholder="Jl. Contoh No. 1, Kota" required><?= $old['alamat'] ?></textarea>
                        <?= err($errors, 'alamat') ?>
                    </div>

                    <!-- Terms -->
                    <div class="form-group" style="display:flex; align-items:flex-start; gap:8px; margin-bottom:20px;">
                        <input type="checkbox" id="terms" name="terms"
                               style="margin-top:3px; flex-shrink:0; accent-color:var(--brand-primary)"
                               <?= isset($_POST['terms']) ? 'checked' : '' ?> required>
                        <label for="terms" style="font-size:13px; color:var(--text-muted); cursor:pointer;">
                            Saya menyetujui <a href="terms.php" target="_blank">Syarat &amp; Ketentuan</a> PawStay
                        </label>
                    </div>
                    <?= err($errors, 'terms') ?>

                    <button class="btn btn-primary w-100 btn-lg" type="submit">
                        <i class="bi bi-person-plus"></i> Buat Akun
                    </button>
                </form>

            <?php endif; ?>

            <div class="auth-footer">Sudah punya akun? <a href="login.php">Masuk di sini</a></div>
        </div>
    </div>

    <script src="main.php"></script>
    <script>
        const toggleBtn  = document.getElementById('togglePassword');
        const passInput  = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        toggleBtn?.addEventListener('click', () => {
            const isHidden       = passInput.type === 'password';
            passInput.type       = isHidden ? 'text'            : 'password';
            toggleIcon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    </script>
</body>
</html>