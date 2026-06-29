<?php require_once 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Petugas | PawStay</title>
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

        .role-badge-top {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #DBEAFE;
            color: #1E40AF;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 999px;
            margin-bottom: 10px;
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
                <span class="role-badge-top"><i class="bi bi-person-badge"></i> Portal Petugas</span>
                <div class="login-tagline">Masuk untuk mengelola operasional penitipan hewan.</div>
            </div>

            <?php if (!empty($_SESSION['login_petugas_error'])): ?>
                <div class="alert alert-danger mb-3" style="background:#FEE2E2;border:1px solid #FCA5A5;color:#991B1B;padding:12px 16px;border-radius:8px;font-size:13px;">
                    <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['login_petugas_error']) ?>
                </div>
                <?php unset($_SESSION['login_petugas_error']); ?>
            <?php endif; ?>

            <form action="proses_login_petugas.php" method="POST">

                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input
                        class="form-control"
                        type="text"
                        name="username"
                        autofocus
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
                    <i class="bi bi-box-arrow-in-right"></i> Masuk sebagai Petugas
                </button>

            </form>
            <div class="auth-footer">
                <a href="login.php"><i class="bi bi-arrow-left"></i> Kembali ke login Pengguna</a>
            </div>
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
