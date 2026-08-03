<?php
require_once 'config.php';
require_once 'auth.php';
check_login();

$user_id_session = $_SESSION['user_id'];
$user_role_session = $_SESSION['role'];
$pesan = '';

// --- LOGIKA SIMPAN / UPDATE KATEGORI ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_kategori'])) {
    $id = $_POST['id'];
    $nama_kategori = trim($_POST['nama_kategori']);
    $deskripsi = trim($_POST['deskripsi']);
    if (empty($id)) {
        $stmt = $conn->prepare("INSERT INTO kategori (user_id, nama_kategori, deskripsi) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id_session, $nama_kategori, $deskripsi);
        if ($stmt->execute()) $_SESSION['pesan_sukses'] = "Kategori berhasil ditambahkan.";
    } else {
        $stmt = $conn->prepare("UPDATE kategori SET nama_kategori=?, deskripsi=? WHERE id=?");
        $stmt->bind_param("ssi", $nama_kategori, $deskripsi, $id);
        if ($stmt->execute()) $_SESSION['pesan_sukses'] = "Kategori berhasil diperbarui.";
    }
    header("Location: kelola_kategori.php");
    exit();
}

// --- LOGIKA HAPUS KATEGORI ---
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM kategori WHERE id=?");
    $stmt->bind_param("i", $id_hapus);
    if ($stmt->execute()) $_SESSION['pesan_sukses'] = "Kategori berhasil dihapus.";
    header("Location: kelola_kategori.php");
    exit();
}

// Ambil data untuk mode edit
$mode = 'tambah';
$kategori_data = ['id' => '', 'nama_kategori' => '', 'deskripsi' => ''];
if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM kategori WHERE id = ?");
    $stmt->bind_param("i", $id_edit);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $kategori_data = $result->fetch_assoc();
        $mode = 'edit';
    }
}

// Ambil pesan notifikasi
if (isset($_SESSION['pesan_sukses'])) {
    $pesan = "<div class='alert alert-success alert-dismissible fade show' role='alert'>{$_SESSION['pesan_sukses']}<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    unset($_SESSION['pesan_sukses']);
}

include 'header.php';
?>

<!-- Header Halaman dan Breadcrumb -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h2 fw-bold">Manajemen Kategori</h1>
        <p class="text-muted">Tambah, edit, atau hapus semua kategori presensi.</p>
    </div>
    <div>
        <button type="button" class="btn btn-add-kategori float-end" data-bs-toggle="modal" data-bs-target="#kategoriModal">
            <i class="bi bi-plus-circle"></i> Tambah Kategori Baru
        </button>
    </div>
</div>
<?php echo $pesan; ?>

<!-- Tabel Daftar Kategori -->
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Daftar Kategori yang Ada</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-rapat align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                        <th class="text-center" style="width: 15%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    if ($user_role_session == 'admin') {
                        $sql = "SELECT * FROM kategori ORDER BY id DESC";
                        $stmt = $conn->prepare($sql);
                    } else {
                        $sql = "SELECT * FROM kategori WHERE user_id = ? ORDER BY id DESC";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $user_id_session);
                    }
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                        <td><?php echo htmlspecialchars($row['deskripsi']); ?></td>
                        <td class="text-center">
                            <a href="kelola_kategori.php?edit=<?php echo $row['id']; ?>" class="btn btn-info btn-sm text-white" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                            <a href="kelola_kategori.php?hapus=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus kategori ini?')" title="Hapus"><i class="bi bi-trash-fill"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; $stmt->close(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Kategori -->
<div class="modal fade" id="kategoriModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="kategoriModalLabel"><?php echo ($mode == 'edit') ? 'Edit Kategori' : 'Tambah Kategori Baru'; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="kelola_kategori.php">
                <input type="hidden" name="id" value="<?php echo $kategori_data['id']; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama_kategori" class="form-label">Nama Kategori</label>
                        <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" value="<?php echo htmlspecialchars($kategori_data['nama_kategori']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi (Opsional)</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?php echo htmlspecialchars($kategori_data['deskripsi']); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan_kategori" class="btn btn-primary"><?php echo ($mode == 'edit') ? 'Update Kategori' : 'Simpan Kategori'; ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<?php if ($mode == 'edit'): ?>
<!-- Script untuk otomatis membuka modal saat mode edit aktif -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const myModal = new bootstrap.Modal(document.getElementById('kategoriModal'));
        myModal.show();
    });
</script>
<?php endif; ?>