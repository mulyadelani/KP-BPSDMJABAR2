<?php
// Pastikan session sudah berjalan
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Deteksi halaman saat ini untuk menandai menu aktif
$currentPage = basename($_SERVER['PHP_SELF']); ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presensi Digital</title>
    
    <!-- ============================================= -->
    <!-- BARU: Tambahkan Favicon di sini -->
    <!-- ============================================= -->
    <link rel="icon" type="image/png" href="/presensi-digital/assets/img/logo-presensi.png">
    
    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS (Wajib setelah Bootstrap) -->
    <link rel="stylesheet" href="/presensi-digital/assets/css/style.css">
<!-- BARU: Link ke CSS Khusus Cetak -->
    <link rel="stylesheet" href="/presensi-digital/assets/css/print.css" media="print">
    
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container-fluid">
    <!-- Logo di Kiri -->
<a class="navbar-brand fw-bold text-primary d-flex align-items-center" href="/presensi-digital/index.php">
        <img src="/presensi-digital/assets/img/logo-presensi.png" alt="Logo Presensi" height="30" class="me-2">
        <span>Presensi Digital</span>
    </a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="main-nav">
      <!-- Menu di Kanan (menggunakan ms-auto) -->
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
        
        <li class="nav-item">
          <a class="nav-link <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>" href="/presensi-digital/index.php">
            <i class="bi bi-house-door-fill"></i> Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($currentPage == 'kelola_kategori.php') ? 'active' : ''; ?>" href="/presensi-digital/kelola_kategori.php">
            <i class="bi bi-tags-fill"></i> Kelola Kategori
          </a>
        </li>
        
        <li class="nav-item">
          <a class="nav-link <?php echo ($currentPage == 'kelola_presensi.php') ? 'active' : ''; ?>" href="/presensi-digital/kelola_presensi.php">
            <i class="bi bi-journal-text"></i> Kelola Presensi
          </a>
        </li>
        
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
        <li class="nav-item">
          <a class="nav-link <?php echo ($currentPage == 'kelola_user.php') ? 'active' : ''; ?>" href="/presensi-digital/admin/kelola_user.php">
            <i class="bi bi-people-fill"></i> Kelola User
          </a>
        </li>
        <?php endif; ?>
        
        <!-- Dropdown User digabung di sini -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><a class="dropdown-item" href="/presensi-digital/ganti_password.php"><i class="bi bi-key-fill"></i> Ganti Password</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="/presensi-digital/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
          </ul>
        </li>
        
      </ul>
    </div>
  </div>
</nav>

<main class="container-fluid mt-4">