<?php
// Pastikan session sudah berjalan
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Deteksi halaman saat ini untuk menandai menu aktif
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presensi Digital</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/presensi/assets/img/logo-presensi.png">

    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Redesign (Sidebar layout) — WAJIB setelah Bootstrap -->
    <link rel="stylesheet" href="/presensi/assets/css/dash.css">

    <!-- Custom CSS lama (kalau masih dipakai untuk halaman lain) -->
    <link rel="stylesheet" href="/presensi/assets/css/style.css">
    <!-- Link ke CSS Khusus Cetak -->
    <link rel="stylesheet" href="/presensi/assets/css/print.css" media="print">
</head>
<body class="d-flex flex-column min-vh-100">

<div class="app-shell">

    <!-- ============================= SIDEBAR ============================= -->
    <aside class="app-sidebar">
        <a class="sidebar-logo" href="/presensi/index.php">
            <img src="/presensi/assets/img/logo-presensi.png" alt="Logo Presensi">
            <span>Presensi Digital</span>
        </a>

        <!-- Tombol menu khusus mobile (buka offcanvas) -->
        <button class="btn btn-sm d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav" aria-label="Buka menu">
            <i class="bi bi-list fs-4"></i>
        </button>

        <ul class="sidebar-nav">
            <li>
                <a class="nav-link <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>" href="/presensi/index.php">
                    <i class="bi bi-house-door-fill"></i> Dashboard
                </a>
            </li>
            <li>
                <a class="nav-link <?php echo ($currentPage == 'kelola_kategori.php') ? 'active' : ''; ?>" href="/presensi/kelola_kategori.php">
                    <i class="bi bi-tags-fill"></i> Kelola Kategori
                </a>
            </li>
            <li>
                <a class="nav-link <?php echo ($currentPage == 'kelola_presensi.php') ? 'active' : ''; ?>" href="/presensi/kelola_presensi.php">
                    <i class="bi bi-journal-text"></i> Kelola Presensi
                </a>
            </li>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
            <li>
                <a class="nav-link <?php echo ($currentPage == 'kelola_user.php') ? 'active' : ''; ?>" href="/presensi/admin/kelola_user.php">
                    <i class="bi bi-people-fill"></i> Kelola User
                </a>
            </li>
            <?php endif; ?>
        </ul>

        <div class="sidebar-bottom">
            <a class="nav-link" href="/presensi/ganti_password.php">
                <i class="bi bi-key-fill"></i> Ganti Password
            </a>
            <a class="nav-link text-danger" href="/presensi/logout.php">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </aside>

    <!-- ===================== OFFCANVAS MENU (mobile) ===================== -->
    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="mobileNav">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">Menu</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="sidebar-nav mb-4">
                <li><a class="nav-link <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>" href="/presensi/index.php"><i class="bi bi-house-door-fill"></i> Dashboard</a></li>
                <li><a class="nav-link <?php echo ($currentPage == 'kelola_kategori.php') ? 'active' : ''; ?>" href="/presensi/kelola_kategori.php"><i class="bi bi-tags-fill"></i> Kelola Kategori</a></li>
                <li><a class="nav-link <?php echo ($currentPage == 'kelola_presensi.php') ? 'active' : ''; ?>" href="/presensi/kelola_presensi.php"><i class="bi bi-journal-text"></i> Kelola Presensi</a></li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                <li><a class="nav-link <?php echo ($currentPage == 'kelola_user.php') ? 'active' : ''; ?>" href="/presensi/admin/kelola_user.php"><i class="bi bi-people-fill"></i> Kelola User</a></li>
                <?php endif; ?>
            </ul>
            <div class="sidebar-bottom">
                <a class="nav-link" href="/presensi/ganti_password.php"><i class="bi bi-key-fill"></i> Ganti Password</a>
                <a class="nav-link text-danger" href="/presensi/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
        </div>
    </div>

    <!-- ============================ MAIN CONTENT ============================ -->
    <main class="app-main">

        <!-- Top bar -->
        <div class="app-topbar">
            <div>
                <p class="welcome-text">Selamat datang kembali,</p>
                <p class="welcome-sub"><?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User'); ?></p>
            </div>
            <div class="topbar-actions">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center gap-2 text-decoration-none user-chip" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="/presensi/assets/img/logo-presensi.png" alt="Avatar">
                        <span class="fw-semibold text-dark d-none d-sm-inline"><?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? 'User'); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="/presensi/ganti_password.php"><i class="bi bi-key-fill"></i> Ganti Password</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/presensi/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- Konten halaman (index.php, kelola_presensi.php, dll) menyusul di bawah ini -->