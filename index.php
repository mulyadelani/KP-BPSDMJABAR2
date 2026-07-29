<?php
require_once 'config.php';
require_once 'auth.php';
check_login();

$user_id_session = $_SESSION['user_id'];
$user_role_session = $_SESSION['role'];

// --- LOGIKA STATISTIK BERDASARKAN ROLE ---

if ($user_role_session == 'admin') {
    // Admin: Hitung semua data dari seluruh tabel
    $total_pelatihan = $conn->query("SELECT COUNT(id) as total FROM formulir")->fetch_assoc()['total'];
    $total_peserta = $conn->query("SELECT COUNT(id) as total FROM tanggapan")->fetch_assoc()['total'];
    $total_user = $conn->query("SELECT COUNT(id) as total FROM users")->fetch_assoc()['total'];
} else {
    // User Biasa: Hitung data yang relevan dengan user_id mereka
    
    // 1. Hitung total pelatihan yang dibuat oleh user ini
    $stmt_pelatihan = $conn->prepare("SELECT COUNT(id) as total FROM formulir WHERE user_id = ?");
    $stmt_pelatihan->bind_param("i", $user_id_session);
    $stmt_pelatihan->execute();
    $total_pelatihan = $stmt_pelatihan->get_result()->fetch_assoc()['total'];
    $stmt_pelatihan->close();

    // 2. Hitung total peserta yang mengisi formulir milik user ini
    $stmt_peserta = $conn->prepare(
        "SELECT COUNT(t.id) as total 
         FROM tanggapan t 
         JOIN formulir f ON t.formulir_id = f.id 
         WHERE f.user_id = ?"
    );
    $stmt_peserta->bind_param("i", $user_id_session);
    $stmt_peserta->execute();
    $total_peserta = $stmt_peserta->get_result()->fetch_assoc()['total'];
    $stmt_peserta->close();
}

include 'header.php';
?>

<div class="mb-4">
    <h1 class="h2 fw-bold">Dashboard</h1>
    <p class="text-muted">Selamat datang! Berikut adalah ringkasan data dari aplikasi presensi digital.</p>
</div>

<div class="row g-4">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                    <div class="bg-primary text-white p-3 rounded-3">
                        <i class="bi bi-journal-text fs-1"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <h5 class="card-title text-muted mb-1">Total Pelatihan</h5>
                    <p class="card-text fs-2 fw-bold mb-0"><?php echo $total_pelatihan; ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                    <div class="bg-success text-white p-3 rounded-3">
                        <i class="bi bi-people-fill fs-1"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <h5 class="card-title text-muted mb-1">Total Peserta Hadir</h5>
                    <p class="card-text fs-2 fw-bold mb-0"><?php echo $total_peserta; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($_SESSION['role'] == 'admin'): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                    <div class="bg-warning text-white p-3 rounded-3">
                        <i class="bi bi-person-plus-fill fs-1"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <h5 class="card-title text-muted mb-1">Jumlah Pengguna</h5>
                    <p class="card-text fs-2 fw-bold mb-0"><?php echo $total_user; ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>