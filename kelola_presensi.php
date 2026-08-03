<?php
require_once 'config.php';
require_once 'auth.php';
check_login();

$user_id_session = $_SESSION['user_id'];
$user_role_session = $_SESSION['role'];
$search = $_GET['search'] ?? '';

include 'header.php';
?>

<div class="mb-3">
    <h1 class="h2 fw-bold">Kelola Presensi</h1>
    <p class="text-muted mb-0">Pilih kategori untuk melihat atau menambah data presensi.</p>
</div>

<form class="d-flex mb-4" method="GET" action="kelola_presensi.php">
    <div class="input-group">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
        <input class="form-control border-start-0" type="search" name="search" placeholder="Cari nama kategori..." value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn btn-primary" type="submit">Cari</button>
    </div>
</form>

<h3 class="h4 mb-3">Kategori Presensi</h3>

<div class="row g-4">
    <?php
    $colors = ['primary', 'success', 'danger', 'warning', 'info', 'dark'];
    $colorIndex = 0;
    $searchTerm = "%{$search}%";
    if ($user_role_session == 'admin') {
        $sql = "SELECT * FROM kategori WHERE nama_kategori LIKE ? ORDER BY id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $searchTerm);
    } else {
        $sql = "SELECT * FROM kategori WHERE user_id = ? AND nama_kategori LIKE ? ORDER BY id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $user_id_session, $searchTerm);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0):
        while ($row = $result->fetch_assoc()):
            $currentColor = $colors[$colorIndex % count($colors)];
            $colorIndex++;
    ?>
    <div class="col-md-6 col-lg-4">
        <a href="daftar_presensi.php?kategori_id=<?php echo $row['id']; ?>" class="card category-card h-100 text-decoration-none text-dark">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-start mb-3">
                    <div class="bg-light p-3 rounded me-3"><i class="bi bi-folder2-open fs-2 text-<?php echo $currentColor; ?>"></i></div>
                    <div class="flex-grow-1"><h5 class="card-title mb-1 me-2"><?php echo htmlspecialchars($row['nama_kategori']); ?></h5></div>
                </div>
                <p class="card-text text-muted flex-grow-1"><?php echo htmlspecialchars($row['deskripsi'] ?: 'Kelola semua presensi yang termasuk dalam kategori ini.'); ?></p>
                <div class="btn btn-outline-primary mt-auto">Buka Kategori <i class="bi bi-arrow-right"></i></div>
            </div>
        </a>
    </div>
    <?php 
        endwhile;
    else: 
        echo "<p class='text-center text-muted'>Tidak ada kategori yang cocok. Silakan buat di halaman <a href='kelola_kategori.php'>Kelola Kategori</a>.</p>";
    endif;
    $stmt->close();
    ?>
</div>

<?php include 'footer.php'; ?>