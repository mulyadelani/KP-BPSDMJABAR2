<?php
require_once 'config.php';
require_once 'auth.php';
check_login();

// Validasi Kategori ID dari URL
if (!isset($_GET['kategori_id']) || !is_numeric($_GET['kategori_id'])) {
    header("Location: kelola_presensi.php");
    exit();
}
$kategori_id = $_GET['kategori_id'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$pesan = '';

// --- PERBAIKAN: Ambil info kategori dan validasi kepemilikan ---
$stmt_kat = $conn->prepare("SELECT nama_kategori, user_id FROM kategori WHERE id = ?");
$stmt_kat->bind_param("i", $kategori_id);
$stmt_kat->execute();
$result_kat = $stmt_kat->get_result();

if ($result_kat->num_rows == 0) {
    die("Kategori tidak ditemukan.");
}
$kategori_info = $result_kat->fetch_assoc();
$nama_kategori = $kategori_info['nama_kategori']; // Variabel sekarang dijamin terisi

// Validasi hak akses ke kategori ini
if ($role != 'admin' && $kategori_info['user_id'] != $user_id) {
    die("Akses ditolak. Anda tidak memiliki izin untuk mengakses kategori ini.");
}
// --- AKHIR PERBAIKAN ---

// Logika Tambah Presensi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_pelatihan'])) {
    $judul = trim($_POST['judul_pelatihan']);
    $deskripsi = trim($_POST['deskripsi']);
    $stmt = $conn->prepare("INSERT INTO formulir (user_id, kategori_id, judul_pelatihan, deskripsi) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $user_id, $kategori_id, $judul, $deskripsi);
    if ($stmt->execute()) $_SESSION['pesan_sukses'] = "Presensi baru berhasil ditambahkan.";
    header("Location: daftar_presensi.php?kategori_id=" . $kategori_id);
    exit();
}

// Logika Hapus Presensi
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM formulir WHERE id=?");
    $stmt->bind_param("i", $id_hapus);
    if ($stmt->execute()) $_SESSION['pesan_sukses'] = "Presensi berhasil dihapus.";
    header("Location: daftar_presensi.php?kategori_id=" . $kategori_id);
    exit();
}

if (isset($_GET['duplikat'])) {
    $id_sumber = $_GET['duplikat'];

    $conn->begin_transaction();
    try {
        // 1. Ambil data dari formulir sumber
        $stmt_sumber = $conn->prepare("SELECT * FROM formulir WHERE id = ?");
        $stmt_sumber->bind_param("i", $id_sumber);
        $stmt_sumber->execute();
        $formulir_sumber = $stmt_sumber->get_result()->fetch_assoc();

        if ($formulir_sumber) {
            // 2. Buat formulir baru (salinan)
            $judul_baru = $formulir_sumber['judul_pelatihan'] . " (Salinan)";
            $stmt_baru = $conn->prepare("INSERT INTO formulir (user_id, kategori_id, judul_pelatihan, deskripsi, status) VALUES (?, ?, ?, ?, ?)");
            $stmt_baru->bind_param("iissi", 
                $formulir_sumber['user_id'], 
                $formulir_sumber['kategori_id'], 
                $judul_baru, 
                $formulir_sumber['deskripsi'], 
                $formulir_sumber['status']
            );
            $stmt_baru->execute();
            $id_baru = $stmt_baru->insert_id; // Dapatkan ID formulir yang baru dibuat

            // 3. Ambil semua pertanyaan dari formulir sumber
            $stmt_pertanyaan_sumber = $conn->prepare("SELECT * FROM pertanyaan WHERE formulir_id = ? ORDER BY urutan ASC");
            $stmt_pertanyaan_sumber->bind_param("i", $id_sumber);
            $stmt_pertanyaan_sumber->execute();
            $pertanyaan_sumber_list = $stmt_pertanyaan_sumber->get_result();

            // 4. Masukkan salinan dari setiap pertanyaan ke formulir baru
            $stmt_pertanyaan_baru = $conn->prepare("INSERT INTO pertanyaan (formulir_id, tipe_pertanyaan, isi_pertanyaan, pilihan, wajib_diisi, urutan) VALUES (?, ?, ?, ?, ?, ?)");
            while ($p_sumber = $pertanyaan_sumber_list->fetch_assoc()) {
                $stmt_pertanyaan_baru->bind_param("isssii",
                    $id_baru,
                    $p_sumber['tipe_pertanyaan'],
                    $p_sumber['isi_pertanyaan'],
                    $p_sumber['pilihan'],
                    $p_sumber['wajib_diisi'],
                    $p_sumber['urutan']
                );
                $stmt_pertanyaan_baru->execute();
            }
            
            $conn->commit();
            $_SESSION['pesan_sukses'] = "Presensi berhasil diduplikasi.";
        } else {
            throw new Exception("Presensi sumber tidak ditemukan.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        // Opsional: catat error $e->getMessage() ke log
        $_SESSION['pesan_error'] = "Gagal menduplikasi presensi.";
    }

    header("Location: daftar_presensi.php?kategori_id=" . $kategori_id);
    exit();
}

// Ambil pesan notifikasi
if (isset($_SESSION['pesan_sukses'])) {
    $pesan = "<div class='alert alert-success alert-dismissible fade show' role='alert'>{$_SESSION['pesan_sukses']}<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    unset($_SESSION['pesan_sukses']);
}

include 'header.php';
?>

<!-- Header Halaman -->
<div class="mb-3">
    <h1 class="h2 fw-bold"><?php echo htmlspecialchars($nama_kategori); ?></h1>
    <p class="text-muted mb-0">Kelola semua presensi yang ada di dalam kategori ini.</p>
</div>

<?php echo $pesan; ?>

<!-- Form Tambah Presensi Baru -->
<div class="accordion mb-4" id="accordionTambah">
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" 
                    data-bs-toggle="collapse" data-bs-target="#collapseOne"
                    style="background-color: #198754; color: white;">
              <i class="bi bi-plus-circle-fill me-2"></i> Klik untuk Menambah Presensi Baru
            </button>
        </h2>
        <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionTambah">
            <div class="accordion-body">
                <form action="daftar_presensi.php?kategori_id=<?php echo $kategori_id; ?>" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nama Presensi/Pelatihan</label>
                        <input type="text" class="form-control" name="judul_pelatihan" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi (Opsional)</label>
                        <textarea class="form-control" name="deskripsi" rows="3"></textarea>
                    </div>
                    <button type="submit" name="tambah_pelatihan" class="btn btn-primary">Simpan Presensi</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Daftar Presensi -->
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Daftar Presensi yang Ada</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-rapat">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nama Pelatihan</th>
                        <th>Deskripsi</th>
                        <th>Link Publik</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Tampilkan formulir HANYA dari kategori ini
                     $stmt = $conn->prepare("SELECT id, judul_pelatihan, deskripsi, status FROM formulir WHERE kategori_id = ? ORDER BY id DESC");
                    $stmt->bind_param("i", $kategori_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $no = 1;
                    if ($result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                            $link = BASE_URL . "formulir/lihat.php?id=" . $row['id'];
                            $safe_title = preg_replace('/[^a-zA-Z0-9_-]/', '_', $row['judul_pelatihan']);
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($row['judul_pelatihan']); ?></td>
                        <td><?php echo htmlspecialchars($row['deskripsi']); ?></td>
                        <td class="align-middle">
                            <div class="input-group input-group-sm" style="max-width: 500px;">
                                <input type="text" 
                                       class="form-control form-control-sm" 
                                       value="<?php echo $link; ?>" 
                                       id="link-<?php echo $row['id']; ?>" 
                                       readonly>
                                <button class="btn btn-copy-link"
                                        type="button" 
                                        onclick="copyLink('link-<?php echo $row['id']; ?>')" 
                                        title="Salin Link"
                                        style="height: 100%; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-clipboard-fill"></i>
                                </button>
                                <a></a>
                                <button class="btn btn-dark btn-sm"
                                        type="button"
                                        data-bs-toggle="modal"
                                        data-bs-target="#qrCodeModal"
                                        data-link="<?php echo $link; ?>"
                                        data-filename="QRCode_<?php echo $safe_title; ?>.png"
                                        title="Tampilkan QR Code"
                                        style="height: 100%; display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-qr-code"></i>
                                </button>
                            </div>
                        </td>


                        <td class="text-center align-middle">
                            <a href="formulir/buat.php?id=<?php echo $row['id']; ?>" 
                               class="btn btn-primary btn-sm me-1" 
                               title="Kelola Form">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                             <a href="daftar_presensi.php?kategori_id=<?php echo $kategori_id; ?>&duplikat=<?php echo $row['id']; ?>" class="btn btn-info btn-sm text-white" title="Duplikasi Presensi">
                                    <i class="bi bi-files"></i>
                                </a>
                            <a href="formulir/tanggapan.php?id=<?php echo $row['id']; ?>" 
                               class="btn btn-success btn-sm me-1" 
                               title="Lihat Tanggapan">
                                <i class="bi bi-eye-fill"></i>
                            </a>
                            <a href="daftar_presensi.php?kategori_id=<?php echo $kategori_id; ?>&hapus=<?php echo $row['id']; ?>" 
                               class="btn btn-danger btn-sm me-1" 
                               onclick="return confirm('Yakin ingin menghapus presensi ini?')" 
                               title="Hapus">
                                <i class="bi bi-trash-fill"></i>
                            </a>

                            <div class="form-check form-switch d-inline-block ms-2">
                                <input class="form-check-input status-toggle" type="checkbox" role="switch" 
                                       id="status-<?php echo $row['id']; ?>" 
                                       data-id="<?php echo $row['id']; ?>"
                                       <?php echo ($row['status'] == 1) ? 'checked' : ''; ?>>
                                <label class="form-check-label small" for="status-<?php echo $row['id']; ?>">
                                    <?php echo ($row['status'] == 1) ? 'Aktif' : 'Nonaktif'; ?>
                                </label>
                            </div>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                        echo "<tr><td colspan='5' class='text-center'>Belum ada presensi di dalam kategori ini.</td></tr>";
                    endif;
                    $stmt->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ======================================================= -->
<!-- BARU: Modal untuk Menampilkan QR Code -->
<!-- ======================================================= -->
<div class="modal fade" id="qrCodeModal" tabindex="-1" aria-labelledby="qrCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrCodeModalLabel">QR Code Presensi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <!-- QR Code akan digenerate di sini oleh JavaScript -->
                <div id="qrcode-container" class="d-flex justify-content-center"></div>
                <p class="mt-3 text-muted" id="qr-code-link"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btn-download-qr">Download Gambar</button>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>


<!-- Library untuk membuat QR Code -->
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
// Fungsi copy link (tidak berubah)
function copyLink(elementId) {
    const input = document.getElementById(elementId);
    input.select();
    input.setSelectionRange(0, 99999);
    document.execCommand("copy");
    alert("Link berhasil disalin!");
}

// --- LOGIKA BARU UNTUK QR CODE (tidak berubah) ---
document.addEventListener('DOMContentLoaded', function() {
    const qrCodeModal = document.getElementById('qrCodeModal');
    // ... (sisa logika QR Code tetap sama) ...

    // --- LOGIKA UNTUK TOGGLE STATUS (DENGAN PERBAIKAN PATH) ---
    const statusToggles = document.querySelectorAll('.status-toggle');

    statusToggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const formulirId = this.getAttribute('data-id');
            const newStatus = this.checked;
            const label = this.nextElementSibling;

            // =======================================================
            // PERBAIKAN: Gunakan path absolut untuk endpoint AJAX
            // =======================================================
            const postUrl = '/presensi-digital/update_status.php';

            fetch(postUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    formulir_id: formulirId,
                    status: newStatus
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    label.textContent = newStatus ? 'Aktif' : 'Nonaktif';
                } else {
                    alert('Gagal mengubah status: ' + data.message);
                    this.checked = !newStatus;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Tidak dapat terhubung ke server untuk mengubah status. Cek console (F12) untuk detail.');
                this.checked = !newStatus;
            });
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const exportBtn = document.getElementById('export-excel-btn');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            // Dapatkan elemen tabel berdasarkan ID
            const table = document.getElementById('tabel-tanggapan');
            
            // Buat nama file dinamis
            const fileName = "Rekap_Tanggapan_<?php echo preg_replace('/[^a-zA-Z0-9-]/', '_', $judul_pelatihan); ?>.xlsx";

            // Buat Workbook baru
            const wb = XLSX.utils.book_new();
            
            // Konversi tabel HTML menjadi worksheet, dengan beberapa opsi
            const ws = XLSX.utils.table_to_sheet(table, {
                // Opsi ini akan mencoba mempertahankan format tanggal jika memungkinkan
                cellDates: true 
            });

            // Tambahkan worksheet ke workbook
            XLSX.utils.book_append_sheet(wb, ws, "Tanggapan");

            // Unduh file Excel
            XLSX.writeFile(wb, fileName);
        });
    }
});
</script>

<script>
// Fungsi copy link
function copyLink(elementId) {
    const input = document.getElementById(elementId);
    input.select();
    input.setSelectionRange(0, 99999);
    document.execCommand("copy");
    alert("Link berhasil disalin!");
}

// Event listener utama
document.addEventListener('DOMContentLoaded', function() {
    
    // Inisialisasi Logika Modal QR Code
    const qrCodeModal = document.getElementById('qrCodeModal');
    if (qrCodeModal) {
        const qrContainer = document.getElementById('qrcode-container');
        const btnDownloadQr = document.getElementById('btn-download-qr');
        const qrCodeLinkP = document.getElementById('qr-code-link');
        let currentQrImageSrc = '';
let currentQrFilename = 'qrcode.png';

        qrCodeModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const linkToEncode = button.getAttribute('data-link');
            const downloadFilename = button.getAttribute('data-filename');
            
            qrCodeLinkP.textContent = linkToEncode;
            qrContainer.innerHTML = ''; // Wajib dikosongkan sebelum membuat yang baru

            // Buat instance QR Code baru
            new QRCode(qrContainer, {
                text: linkToEncode,
                width: 256,
                height: 256,
                correctLevel: QRCode.CorrectLevel.H
            });
            
            // Perbarui link download setelah QR Code dibuat
            // Butuh sedikit delay agar gambar selesai dirender oleh library
            setTimeout(() => {
    const qrImage = qrContainer.querySelector('img');
    if (qrImage) {
        currentQrImageSrc = qrImage.src;
        currentQrFilename = downloadFilename;
    }
}, 100); // delay 100ms
        });

        btnDownloadQr.addEventListener('click', function() {
            if (!currentQrImageSrc) {
                alert('QR Code belum siap, coba tunggu sebentar lalu klik lagi.');
                return;
            }
            const tempLink = document.createElement('a');
            tempLink.href = currentQrImageSrc;
            tempLink.download = currentQrFilename;
            document.body.appendChild(tempLink);
            tempLink.click();
            document.body.removeChild(tempLink);
        });
    }

    // Inisialisasi Logika Toggle Status
    const statusToggles = document.querySelectorAll('.status-toggle');
    statusToggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const formulirId = this.getAttribute('data-id');
            const newStatus = this.checked;
            const label = this.nextElementSibling;
            const postUrl = '/presensi-digital/update_status.php';

            fetch(postUrl, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json' 
                },
                body: JSON.stringify({
                    formulir_id: formulirId,
                    status: newStatus
                })
            })
            .then(response => {
                if (!response.ok) {
                    // Jika ada error, coba baca pesan teks dari server
                    return response.text().then(text => { throw new Error(text || `HTTP error! Status: ${response.status}`) });
                }
                return response.json();
            })
            .then(data => {
                if (data && data.status === 'success') {
                    // Update teks label jika berhasil
                    label.textContent = newStatus ? 'Aktif' : 'Nonaktif';
                } else {
                    // Jika gagal, tampilkan pesan error dari server dan kembalikan toggle
                    const errorMessage = data ? data.message : 'Respons server tidak valid.';
                    alert('Gagal mengubah status: ' + errorMessage);
                    this.checked = !newStatus;
                }
            })
            .catch(error => {
                console.error('Error AJAX:', error);
                alert('Tidak dapat terhubung ke server untuk mengubah status.');
                this.checked = !newStatus; // Kembalikan posisi toggle jika koneksi gagal
            });
        });
    });
});

</script>
