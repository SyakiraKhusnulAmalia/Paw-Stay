<?php require_once 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | PawStay</title>
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

        .invalid-feedback {
            display: none;
            color: var(--danger);
            font-size: 11.5px;
            margin-top: 4px;
        }

        form.was-validated .form-control:invalid {
            border-color: var(--danger);
        }

        form.was-validated .form-control:invalid+.invalid-feedback {
            display: block;
        }

        form.was-validated .form-control:valid {
            border-color: #52C41A;
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
                <div class="login-tagline">Aplikasi Manajemen Penitipan Hewan</div>
            </div>
            <form action="proses_login.php" method="POST">

                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input
                        class="form-control"
                        type="text"
                        name="username"
                        required>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-password-wrap">
                        <input
                            class="form-control"
                            type="password"
                            name="password"
                            required>
                        <button type="button" class="toggle-password" id="togglePassword" aria-label="Tampilkan password">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <button class="btn btn-primary w-100 btn-lg" type="submit">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk
                </button>

            </form>
            <div class="auth-footer">
                <a href="forgot-password.php">Lupa password?</a>
            </div>
            <div class="auth-footer">Belum punya akun? <a href="register.php">Daftar sekarang</a></div>
            <div class="auth-footer"><a href="login-petugas.php"><i class="bi bi-person-badge"></i> Masuk sebagai Petugas</a></div>
        </div>
    </div>

    <script src="main.php"></script>
    <script>
        const toggleBtn  = document.getElementById('togglePassword');
        const passInput  = document.querySelector('input[name="password"]');
        const toggleIcon = document.getElementById('toggleIcon');

        toggleBtn?.addEventListener('click', () => {
            const isHidden       = passInput.type === 'password';
            passInput.type       = isHidden ? 'text'            : 'password';
            toggleIcon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    </script>
</body>

</html>