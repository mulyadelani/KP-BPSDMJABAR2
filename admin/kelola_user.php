<?php
require_once '../config.php';
require_once '../auth.php'; 
check_login();

// Proteksi halaman, hanya untuk admin
if ($_SESSION['role'] !== 'admin') {
    die("Akses ditolak. Halaman ini hanya untuk admin.");
}

$mode = 'tambah';
$user_data = ['id' => '', 'nama_lengkap' => '', 'username' => '', 'role' => 'user', 'nama_bidang' => ''];

// --- LOGIKA PROSES FORM (TAMBAH & EDIT) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_user'])) {
    $id = $_POST['id'];
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $role = $_POST['role'];
    $password = $_POST['password'];

    // Cek duplikasi username
    $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt_check->bind_param("si", $username, $id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        $_SESSION['pesan_error'] = "Error: Username '$username' sudah digunakan!";
    } else {
        // Jika ID ada, berarti ini mode EDIT
        if (!empty($id)) {
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET nama_lengkap=?, username=?, role=?, password=? WHERE id=?");
                $stmt->bind_param("ssssi", $nama_lengkap, $username, $role, $hashed_password, $id);
            } else {
                // Update tanpa mengubah password
                $stmt = $conn->prepare("UPDATE users SET nama_lengkap=?, username=?, role=? WHERE id=?");
                $stmt->bind_param("sssi", $nama_lengkap, $username, $role, $id);
            }
            if(isset($stmt) && $stmt->execute()) $_SESSION['pesan_sukses'] = "User berhasil diperbarui.";

        } 
        // Jika ID kosong, berarti mode TAMBAH
        else {
            if (empty($password)) {
                $_SESSION['pesan_error'] = "Error: Password wajib diisi untuk user baru!";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (nama_lengkap, username, password, role) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $nama_lengkap, $username, $hashed_password, $role);
                if($stmt->execute()) $_SESSION['pesan_sukses'] = "User berhasil ditambahkan.";
            }
        }
    }
    header("Location: kelola_user.php");
    exit();
}

// --- LOGIKA HAPUS USER ---
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    if ($id_hapus != 1 && $id_hapus != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id_hapus);
        if ($stmt->execute()) $_SESSION['pesan_sukses'] = "User berhasil dihapus.";
    } else {
        $_SESSION['pesan_error'] = "Error: Anda tidak dapat menghapus akun ini.";
    }
    header("Location: kelola_user.php");
    exit();
}

// --- LOGIKA UNTUK MODE EDIT (MENGAMBIL DATA & MENGATUR MODE) ---
if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id_edit);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        $mode = 'edit';
    }
}

// Ambil pesan dari session untuk ditampilkan di alert
$pesan = '';
if(isset($_SESSION['pesan_sukses'])) {
    $pesan = "<div class='alert alert-success alert-dismissible fade show' role='alert'>{$_SESSION['pesan_sukses']}<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    unset($_SESSION['pesan_sukses']);
} elseif(isset($_SESSION['pesan_error'])) {
    $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>{$_SESSION['pesan_error']}<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    unset($_SESSION['pesan_error']);
}

include '../header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h2 fw-bold">Kelola User</h1>
        <p class="text-muted">Tambah atau hapus akun untuk Admin Bidang.</p>
    </div>
    <div>
        <nav style="--bs-breadcrumb-divider: '/';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/presensi-digital/index.php">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Kelola User</li>
            </ol>
        </nav>
        <button type="button" class="btn btn-success float-end" data-bs-toggle="modal" data-bs-target="#userModal">
            <i class="bi bi-plus-circle"></i> Tambah Admin Bidang
        </button>
    </div>
</div>

<?php echo $pesan; // Tampilkan pesan sukses/error ?>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Daftar User Admin</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nama Lengkap</th>
                        <th scope="col">Username</th>
                        <th scope="col">Peran</th>
                        <th scope="col" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM users ORDER BY id ASC");
                    $no = 1;
                    while ($user = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <th scope="row"><?php echo $no++; ?></th>
                        <td><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td>
                            <?php if ($user['role'] == 'admin'): ?>
                                <span class="badge bg-primary">Utama</span>
                            <?php else: ?>
                                <span class="badge bg-info text-dark">Bidang</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <a href="kelola_user.php?edit=<?php echo $user['id']; ?>" class="btn btn-warning btn-sm" title="Edit User">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            <?php if ($user['id'] != 1 && $user['id'] != $_SESSION['user_id']): ?>
                                <a href="kelola_user.php?hapus=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus user ini?');" title="Hapus User">
                                    <i class="bi bi-trash-fill"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah & Edit User -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel"><?php echo ($mode == 'edit') ? 'Edit User' : 'Tambah User Baru'; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $user_data['id']; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap / Nama Bidang</label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($user_data['nama_lengkap']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username (Email)</label>
                        <input type="email" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="<?php echo ($mode == 'edit') ? 'Kosongkan jika tidak ingin diubah' : 'Wajib diisi'; ?>" <?php echo ($mode == 'tambah') ? 'required' : ''; ?>>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Peran</label>
                        <select name="role" id="role" class="form-select" required>
                            <option value="user" <?php echo ($user_data['role'] == 'user') ? 'selected' : ''; ?>>Admin Bidang</option>
                            <option value="admin" <?php echo ($user_data['role'] == 'admin') ? 'selected' : ''; ?>>Admin Utama</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan_user" class="btn btn-primary"><?php echo ($mode == 'edit') ? 'Update User' : 'Simpan User'; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>

<?php if ($mode == 'edit'): ?>
<!-- Script untuk Otomatis Membuka Modal Saat Mode Edit -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const userModal = new bootstrap.Modal(document.getElementById('userModal'));
        userModal.show();
    });
</script>
<?php endif; ?>