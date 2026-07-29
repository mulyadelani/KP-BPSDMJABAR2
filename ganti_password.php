<?php
require_once 'config.php';
check_login(); // Pastikan hanya user yang sudah login yang bisa mengakses

// --- LOGIKA PROSES FORM GANTI PASSWORD ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    $user_id = $_SESSION['user_id'];

    // Validasi 1: Pastikan password baru dan konfirmasi cocok
    if ($password_baru !== $konfirmasi_password) {
        $_SESSION['pesan_error'] = "Password baru dan konfirmasi tidak cocok.";
    } 
    // Validasi 2: Pastikan password baru tidak kosong
    elseif (empty($password_baru)) {
        $_SESSION['pesan_error'] = "Password baru tidak boleh kosong.";
    } 
    else {
        // Ambil hash password saat ini dari database
        $stmt_check = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt_check->bind_param("i", $user_id);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        $user = $result->fetch_assoc();

        // Validasi 3: Verifikasi apakah password lama yang dimasukkan benar
        if ($user && password_verify($password_lama, $user['password'])) {
            // Jika password lama benar, hash password baru dan update ke database
            $hashed_password_baru = password_hash($password_baru, PASSWORD_DEFAULT);
            
            $stmt_update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt_update->bind_param("si", $hashed_password_baru, $user_id);
            
            if ($stmt_update->execute()) {
                $_SESSION['pesan_sukses'] = "Password Anda telah berhasil diperbarui.";
            } else {
                $_SESSION['pesan_error'] = "Terjadi kesalahan saat memperbarui password.";
            }
        } else {
            $_SESSION['pesan_error'] = "Password lama yang Anda masukkan salah.";
        }
    }
    // Arahkan kembali ke halaman yang sama untuk menampilkan pesan
    header("Location: ganti_password.php");
    exit();
}

// Ambil pesan dari session untuk ditampilkan
$pesan = '';
if(isset($_SESSION['pesan_sukses'])) {
    $pesan = "<div class='alert alert-success alert-dismissible fade show' role='alert'>{$_SESSION['pesan_sukses']}<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    unset($_SESSION['pesan_sukses']);
} elseif(isset($_SESSION['pesan_error'])) {
    $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>{$_SESSION['pesan_error']}<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    unset($_SESSION['pesan_error']);
}

include 'header.php';
?>

<!-- Header Halaman dan Breadcrumb -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h2 fw-bold">Ganti Password</h1>
        <p class="text-muted mb-0">Perbarui password Anda secara berkala untuk menjaga keamanan akun.</p>
    </div>
    <nav style="--bs-breadcrumb-divider: '/';" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Ganti Password</li>
        </ol>
    </nav>
</div>

<?php echo $pesan; // Tampilkan pesan sukses atau error ?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST">
                    <div class="mb-3">
                        <label for="password_lama" class="form-label">Password Lama</label>
                        <input type="password" class="form-control" id="password_lama" name="password_lama" required>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label for="password_baru" class="form-label">Password Baru</label>
                        <input type="password" class="form-control" id="password_baru" name="password_baru" required>
                    </div>
                    <div class="mb-3">
                        <label for="konfirmasi_password" class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<?php include 'footer.php'; ?>