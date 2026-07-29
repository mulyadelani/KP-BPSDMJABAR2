<?php
require_once 'config.php';

// Jika user sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// Logika proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $sql = "SELECT id, nama_lengkap, role, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['role'] = $user['role'];
        header("Location: " . BASE_URL . "index.php");
        exit();
    }
    $error = "Username atau password yang Anda masukkan salah.";
}
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Presensi Digital</title>
    <link rel="icon" type="image/png" href="/presensi-digital/assets/img/logo-presensi.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* CSS Kustom untuk Halaman Login */
        html, body {
            height: 100%;
        }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }
        .login-container {
            display: flex;
            width: 100%;
            max-width: 900px;
            min-height: 550px;
            background: #fff;
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
            border-radius: 1rem;
            overflow: hidden;
        }
        .login-banner {
            background-color: #0d6efd; /* Warna biru primer Bootstrap */
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            width: 45%;
        }
        .login-banner .icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
        }
        .login-banner h1 {
            font-size: 2.5rem;
            font-weight: bold;
        }
        .login-form-container {
            padding: 3rem;
            width: 55%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-form-container .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .input-group-text {
            background-color: #e9ecef;
            border-right: 0;
        }
        .form-control {
            border-left: 0;
        }
        .form-control:focus {
            box-shadow: none;
        }
        @media (max-width: 768px) {
            .login-banner {
                display: none; /* Sembunyikan banner di layar kecil */
            }
            .login-form-container {
                width: 100%;
            }
            .login-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Kolom Kiri: Banner Biru -->
        <div class="login-banner">
            <div class="icon">
                <i class="bi bi-clipboard-check"></i>
            </div>
            <h1>Presensi Digital</h1>
            <p class="lead mt-3">Digitalisasasi Pengelolaan presensi yang cepat, akurat, dan terintegrasi untuk berbagai kegiatan.</p>
        </div>

        <!-- Kolom Kanan: Form Login -->
        <div class="login-form-container">
            <div class="logo">
                <img src="assets/img/logo-presensi.png" alt="Logo Presensi" height="60"> <!-- Ganti dengan path logo Anda -->
            </div>
            <div class="text-center mb-4">
                <h2 class="fw-bold">Login Panel Admin</h2>
                <p class="text-muted">Selamat datang kembali!</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                    </div>
                </div>
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-lg">Login</button>
                </div>
                <div class="text-center">
                    <a href="landing.php" class="text-decoration-none"><i class="bi bi-arrow-left-circle"></i> Kembali ke Beranda</a>
                </div>
            </form>
            <div class="text-center text-muted mt-auto">
                <p class="mb-0">&copy;Sem <?php echo date('Y'); ?> Presensi Digital. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>