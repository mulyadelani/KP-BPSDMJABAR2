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

    <!-- Judul Dashboard -->
    <div class="dashboard-title">
        <h1 class="h2">Dashboard</h1>
        <p class="mb-0">Ringkasan data dari aplikasi presensi digital.</p>
    </div>

    <!-- Kartu Statistik -->
    <div class="row g-4">
        <div class="col-md-6 col-lg-4">
            <div class="stat-card">
                <div class="stat-icon bg-blue">
                    <i class="bi bi-journal-text"></i>
                </div>
                <div class="stat-label">Total Pelatihan</div>
                <div class="stat-value"><?php echo $total_pelatihan; ?></div>
                <div class="stat-note">Formulir dibuat</div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="stat-card">
                <div class="stat-icon bg-green">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="stat-label">Total Peserta Hadir</div>
                <div class="stat-value"><?php echo $total_peserta; ?></div>
                <div class="stat-note">Tanggapan tercatat</div>
            </div>
        </div>

        <?php if ($_SESSION['role'] == 'admin'): ?>
        <div class="col-md-6 col-lg-4">
            <div class="stat-card">
                <div class="stat-icon bg-yellow">
                    <i class="bi bi-person-plus-fill"></i>
                </div>
                <div class="stat-label">Jumlah Pengguna</div>
                <div class="stat-value"><?php echo $total_user; ?></div>
                <div class="stat-note">Khusus admin</div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Banner CTA -->
    <div class="cta-banner">
        <div>
            <div class="cta-tag">Jangan Lupa</div>
            <h3>Kelola presensi pelatihan Anda hari ini</h3>
            <a href="/presensi/kelola_presensi.php" class="btn btn-cta">Buka Kelola Presensi</a>
        </div>
        <div class="cta-deco"></div>
    </div>

<?php include 'footer.php'; ?>