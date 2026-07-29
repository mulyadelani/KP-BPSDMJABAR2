<?php
require_once '../config.php';
require_once '../auth.php'; 
check_login();

check_login();

// Validasi & Proteksi Akses (tidak berubah)
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header("Location: /presensi-digital/kelola_presensi.php"); exit(); }
$formulir_id = $_GET['id'];


// --- PERBAIKAN: Ambil judul, deskripsi, DAN kategori_id ---
$stmt_info = $conn->prepare("SELECT judul_pelatihan, deskripsi, kategori_id FROM formulir WHERE id = ?");
$stmt_info->bind_param("i", $formulir_id);
$stmt_info->execute();
$info_result = $stmt_info->get_result();
if ($info_result->num_rows == 0) die("Formulir tidak ditemukan.");
$formulir_info = $info_result->fetch_assoc();
$judul_pelatihan = $formulir_info['judul_pelatihan'];
$kategori_id_formulir = $formulir_info['kategori_id']; // Simpan ID kategori


// Validasi hak akses (tidak berubah)
if ($_SESSION['role'] != 'admin') {
    $stmt_owner = $conn->prepare("SELECT user_id FROM formulir WHERE id = ?");
    $stmt_owner->bind_param("i", $formulir_id);
    $stmt_owner->execute();
    $owner_id = $stmt_owner->get_result()->fetch_assoc()['user_id'];
    if ($owner_id != $_SESSION['user_id']) die("Akses ditolak.");
}

// LOGIKA UPDATE JUDUL/DESKRIPSI FORMULIR
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_header'])) {
    $judul = trim($_POST['judul_pelatihan']);
    $deskripsi = trim($_POST['deskripsi_formulir']);
    $stmt = $conn->prepare("UPDATE formulir SET judul_pelatihan = ?, deskripsi = ? WHERE id = ?");
    $stmt->bind_param("ssi", $judul, $deskripsi, $formulir_id);
    $stmt->execute();
    header("Location: buat.php?id=" . $formulir_id);
    exit();
}

// LOGIKA UPDATE PERTANYAAN (EDIT)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_pertanyaan'])) {
    $pertanyaan_id = $_POST['pertanyaan_id'];
    $tipe = $_POST['tipe_pertanyaan'];
    $isi = trim($_POST['isi_pertanyaan']);
    $pilihan = in_array($tipe, ['dropdown', 'checkbox', 'pilihanganda']) ? trim($_POST['pilihan']) : NULL;
    $wajib_diisi = isset($_POST['wajib_diisi']) ? 1 : 0;
    
    $stmt_update = $conn->prepare("UPDATE pertanyaan SET tipe_pertanyaan=?, isi_pertanyaan=?, pilihan=?, wajib_diisi=? WHERE id=?");
    $stmt_update->bind_param("sssii", $tipe, $isi, $pilihan, $wajib_diisi, $pertanyaan_id);
    if ($stmt_update->execute()) $_SESSION['pesan_sukses'] = "Pertanyaan berhasil diperbarui.";
    header("Location: buat.php?id=" . $formulir_id);
    exit();
}

// LOGIKA TAMBAH PERTANYAAN BARU
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_pertanyaan'])) {
    $tipe = $_POST['tipe_pertanyaan'];
    $isi = trim($_POST['isi_pertanyaan']);
    $pilihan = in_array($tipe, ['dropdown', 'checkbox', 'pilihanganda']) ? trim($_POST['pilihan']) : NULL;
    $wajib_diisi = isset($_POST['wajib_diisi']) ? 1 : 0;
    $result_urutan = $conn->query("SELECT MAX(urutan) as max_urutan FROM pertanyaan WHERE formulir_id = $formulir_id");
    $max_urutan = $result_urutan->fetch_assoc()['max_urutan'];
    $urutan = ($max_urutan !== null) ? $max_urutan + 1 : 0;
    $stmt_simpan = $conn->prepare("INSERT INTO pertanyaan (formulir_id, tipe_pertanyaan, isi_pertanyaan, pilihan, wajib_diisi, urutan) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_simpan->bind_param("isssii", $formulir_id, $tipe, $isi, $pilihan, $wajib_diisi, $urutan);
    if ($stmt_simpan->execute()) $_SESSION['pesan_sukses'] = "Pertanyaan berhasil ditambahkan.";
    header("Location: buat.php?id=" . $formulir_id);
    exit();
}

// LOGIKA HAPUS PERTANYAAN
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    $stmt_hapus = $conn->prepare("DELETE FROM pertanyaan WHERE id = ? AND formulir_id = ?");
    $stmt_hapus->bind_param("ii", $id_hapus, $formulir_id);
    if ($stmt_hapus->execute()) $_SESSION['pesan_sukses'] = "Pertanyaan berhasil dihapus.";
    header("Location: buat.php?id=" . $formulir_id);
    exit();
}

// =======================================================
// LOGIKA BARU UNTUK DUPLIKASI PERTANYAAN
// =======================================================
if (isset($_GET['duplikat'])) {
    $id_pertanyaan_sumber = $_GET['duplikat'];

    // 1. Ambil semua data dari pertanyaan sumber
    $stmt_sumber = $conn->prepare("SELECT * FROM pertanyaan WHERE id = ? AND formulir_id = ?");
    $stmt_sumber->bind_param("ii", $id_pertanyaan_sumber, $formulir_id);
    $stmt_sumber->execute();
    $result_sumber = $stmt_sumber->get_result();

    if ($result_sumber->num_rows > 0) {
        $pertanyaan_sumber = $result_sumber->fetch_assoc();
        
        // 2. Siapkan data untuk pertanyaan baru (duplikat)
        $tipe_baru = $pertanyaan_sumber['tipe_pertanyaan'];
        $isi_baru = $pertanyaan_sumber['isi_pertanyaan'] . " (Salinan)"; // Tambahkan "(Salinan)"
        $pilihan_baru = $pertanyaan_sumber['pilihan'];
        $wajib_baru = $pertanyaan_sumber['wajib_diisi'];

        // 3. Tentukan urutan baru (setelah pertanyaan sumber)
        $urutan_baru = $pertanyaan_sumber['urutan'] + 1;

        // 4. Geser urutan pertanyaan lain yang ada setelahnya
        $conn->query("UPDATE pertanyaan SET urutan = urutan + 1 WHERE formulir_id = $formulir_id AND urutan >= $urutan_baru");
        
        // 5. Masukkan pertanyaan baru ke database
        $stmt_duplikat = $conn->prepare("INSERT INTO pertanyaan (formulir_id, tipe_pertanyaan, isi_pertanyaan, pilihan, wajib_diisi, urutan) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_duplikat->bind_param("isssii", $formulir_id, $tipe_baru, $isi_baru, $pilihan_baru, $wajib_baru, $urutan_baru);
        if ($stmt_duplikat->execute()) {
            $_SESSION['pesan_sukses'] = "Pertanyaan berhasil diduplikasi.";
        }
    }
    header("Location: buat.php?id=" . $formulir_id);
    exit();
}

// Ambil data formulir (termasuk deskripsi)
$stmt_info = $conn->prepare("SELECT judul_pelatihan, deskripsi FROM formulir WHERE id = ?");
$stmt_info->bind_param("i", $formulir_id);
$stmt_info->execute();
$formulir_info = $stmt_info->get_result()->fetch_assoc();

// Ambil Daftar Pertanyaan
$pertanyaan_list = [];
$result_list = $conn->query("SELECT * FROM pertanyaan WHERE formulir_id = $formulir_id ORDER BY urutan ASC");
while ($row = $result_list->fetch_assoc()) $pertanyaan_list[] = $row;

$pesan = '';
if(isset($_SESSION['pesan_sukses'])) {
    $pesan = "<div class='alert alert-success'>{$_SESSION['pesan_sukses']}</div>";
    unset($_SESSION['pesan_sukses']);
}

$link_publik = BASE_URL . "formulir/lihat.php?id=" . $formulir_id;

include '../header.php';
?>

<!-- Breadcrumb dan Judul Halaman -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h2 fw-bold">Kelola Form Pertanyaan</h1>
        <p class="text-muted mb-0">Atur pertanyaan untuk presensi Anda.</p>
    </div>
    <nav style="--bs-breadcrumb-divider: '/';" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/presensi-digital/index.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/presensi-digital/kelola_presensi.php">Kelola Presensi</a></li>
            <li class="breadcrumb-item"><a href="/presensi-digital/daftar_presensi.php?kategori_id=<?php echo $kategori_id_formulir; ?>">Daftar Presensi</a></li>
            <li class="breadcrumb-item active" aria-current="page">Kelola Form</li>
        </ol>
    </nav>
</div>

<?php echo $pesan; ?>

<!-- Link Publik untuk Peserta -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <label class="form-label mb-2 fw-bold">Link Publik untuk Peserta:</label>

        <div class="input-group">
            <input type="text" class="form-control"
                   value="<?php echo $link_publik; ?>"
                   id="linkPublik" readonly>

           <button class="btn btn-outline-secondary" onclick="copyLink('linkPublik')">
                <i class="bi bi-clipboard me-1"></i> Salin
            </button>

            <a href="<?php echo $link_publik; ?>" target="_blank"
               class="btn btn-outline-primary">
                <i class="bi bi-box-arrow-up-right me-1"></i> Buka
            </a>
        </div>
    </div>
</div>

<style>
    /* Samakan tinggi input dan tombol */
    .input-group .form-control,
    .input-group .btn {
        height: 42px; /* bisa disesuaikan misal 40px atau 45px */
    }

    /* Biar icon + teks di tombol rata tengah */
    .input-group .btn {
        display: flex;
        align-items: center;
    }
</style>

<!-- ======================================================= -->
<!-- LAYOUT BARU SATU KOLOM -->
<!-- ======================================================= -->
<div class="row justify-content-center">
    <div class="col-lg-9">
        <!-- Header Form -->
        <div class="card shadow-sm mb-3 form-builder-header">
           <form method="POST" action="buat.php?id=<?php echo $formulir_id; ?>">
                <input type="text" class="form-control" name="judul_pelatihan" value="<?php echo htmlspecialchars($formulir_info['judul_pelatihan']); ?>" onblur="this.form.submit()">
                <!-- INI BARIS YANG DIPERBAIKI -->
                <textarea class="form-control mt-2" name="deskripsi_formulir" rows="1" placeholder="Deskripsi formulir (opsional)" onblur="this.form.submit()"><?php echo htmlspecialchars($formulir_info['deskripsi'] ?? ''); ?></textarea>
                <input type="hidden" name="update_header" value="1">
        </form>
        </div>

        <!-- Container untuk semua pertanyaan (yang sudah ada dan yang baru) -->
        <div id="questions-container">
            <?php foreach ($pertanyaan_list as $p): ?>
            <!-- ======================================================= -->
            <!-- BLOK PERTANYAAN BARU (VIEW & EDIT MODE) -->
            <!-- ======================================================= -->
           <div class="card shadow-sm mb-3 question-card" id="q-card-<?php echo $p['id']; ?>" data-id="<?php echo $p['id']; ?>">
                
                <!-- Tampilan Statis (View Mode) -->
                <div class="view-mode">
                    <div class="row g-2">
                        <div class="col-8">
                            <p class="form-control-plaintext px-2 mb-0"><strong><?php echo htmlspecialchars($p['isi_pertanyaan']); ?> <?php if($p['wajib_diisi']) echo '<span class="text-danger">*</span>'; ?></strong></p>
                        </div>
                        <div class="col-4">
                            <p class="form-control-plaintext px-2 mb-0 text-muted">Tipe: <?php echo ucfirst($p['tipe_pertanyaan']); ?></p>
                        </div>
                    </div>
                     <?php if($p['tipe_pertanyaan'] == 'dropdown' || $p['tipe_pertanyaan'] == 'checkbox'): ?>
                    <div class="mt-2 text-muted px-2"><small>Pilihan: <?php echo htmlspecialchars($p['pilihan']); ?></small></div>
                    <?php endif; ?>
                    <hr class="my-2">
                    <div class="text-end">
                        <a href="buat.php?id=<?php echo $formulir_id; ?>&duplikat=<?php echo $p['id']; ?>" class="btn btn-sm btn-link text-secondary" title="Duplikasi Pertanyaan">
                        <i class="bi bi-files"></i>
                        </a>
                        <button class="btn btn-sm btn-link text-primary" onclick="toggleEditMode(<?php echo $p['id']; ?>, true)"><i class="bi bi-pencil-square"></i> Edit</button>
                        <a href="buat.php?id=<?php echo $formulir_id; ?>&hapus=<?php echo $p['id']; ?>" class="btn btn-sm btn-link text-danger" onclick="return confirm('Yakin?')"><i class="bi bi-trash"></i></a>
                    </div>
                    <div class="drag-handle text-center text-muted py-1">
                    <i class="bi bi-grip-vertical"></i>
                    </div>
                </div>

                <!-- Form Edit (Edit Mode, disembunyikan) -->
                <div class="edit-mode" style="display: none;">
                    <form method="POST" action="buat.php?id=<?php echo $formulir_id; ?>">
                        <input type="hidden" name="pertanyaan_id" value="<?php echo $p['id']; ?>">
                        <div class="row g-3">
                            <div class="col-md-8"><input type="text" class="form-control" name="isi_pertanyaan" value="<?php echo htmlspecialchars($p['isi_pertanyaan']); ?>" required></div>
                            <div class="col-md-4">
                                <!-- =============================================== -->
                                <!-- DROPDOWN KUSTOM BARU (EDIT MODE) -->
                                <!-- =============================================== -->
                                <div class="dropdown custom-select-dropdown">
                                    <input type="hidden" name="tipe_pertanyaan" value="<?php echo $p['tipe_pertanyaan']; ?>">
                                    <button class="btn btn-dark btn-sm dropdown-toggle w-100 d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown">
                                        <!-- Teks tombol akan diisi oleh JS -->
                                    </button>
                                    <ul class="dropdown-menu w-100">
                                        <li><a class="dropdown-item" href="#" data-value="text"><i class="bi bi-fonts me-2"></i> Teks (Isian Bebas)</a></li>
                                        <li><a class="dropdown-item" href="#" data-value="paragraf"><i class="bi bi-text-paragraph me-2"></i> Paragraf</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="#" data-value="pilihanganda"><i class="bi bi-ui-radios me-2"></i> Pilihan Ganda</a></li>
                                        <li><a class="dropdown-item" href="#" data-value="dropdown"><i class="bi bi-caret-down-square me-2"></i> Dropdown (Pilihan)</a></li>
                                        <li><a class="dropdown-item" href="#" data-value="checkbox"><i class="bi bi-check2-square me-2"></i> Checkbox</a></li>
                                        <li><a class="dropdown-item" href="#" data-value="upload"><i class="bi bi-cloud-arrow-up me-2"></i> Upload File</a></li>
                                        <li><a class="dropdown-item" href="#" data-value="tandatangan"><i class="bi bi-pen me-2"></i> Tanda Tangan Digital</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 edit-pilihan-container" style="<?php echo ($p['tipe_pertanyaan'] == 'dropdown' || $p['tipe_pertanyaan'] == 'checkbox') ? '' : 'display: none;'; ?>">
                            <textarea class="form-control" name="pilihan" rows="3"><?php echo htmlspecialchars($p['pilihan']); ?></textarea>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-end align-items-center">
                            <div class="form-check form-switch me-auto">
                                <input class="form-check-input" type="checkbox" role="switch" name="wajib_diisi" <?php if($p['wajib_diisi']) echo 'checked'; ?>>
                                <label class="form-check-label">Wajib diisi</label>
                            </div>
                            <button type="button" class="btn btn-dark btn-sm me-2" onclick="toggleEditMode(<?php echo $p['id']; ?>, false)">Batal</button>
                            <button type="submit" name="update_pertanyaan" class="btn btn-sm btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Tombol untuk memunculkan form tambah -->
        <div class="text-center mt-3">
            <button id="add-question-btn" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Pertanyaan</button>
        </div>

        <!-- TEMPLATE UNTUK FORM TAMBAH PERTANYAAN BARU (Disembunyikan) -->
        <div class="card shadow-sm mt-3 question-card" id="new-question-template" style="display: none;">
             <form method="POST" action="buat.php?id=<?php echo $formulir_id; ?>">
                <div class="row g-3">
                    <div class="col-md-8">
                        <input type="text" class="form-control" name="isi_pertanyaan" placeholder="Tulis pertanyaan Anda di sini..." required>
                    </div>
                    <div class="col-md-4">
                        <!-- =============================================== -->
                        <!-- DROPDOWN KUSTOM BARU (TAMBAH MODE) -->
                        <!-- =============================================== -->
                        <div class="dropdown custom-select-dropdown">
                            <input type="hidden" name="tipe_pertanyaan" value="text">
                            <button class="btn btn-dark btn-sm dropdown-toggle w-100 d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-fonts me-2"></i> Teks (Isian Bebas)
                            </button>
                            <ul class="dropdown-menu w-100">
                                <li><a class="dropdown-item" href="#" data-value="text"><i class="bi bi-fonts me-2"></i> Teks (Isian Bebas)</a></li>
                                <li><a class="dropdown-item" href="#" data-value="paragraf"><i class="bi bi-text-paragraph me-2"></i> Paragraf</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" data-value="pilihanganda"><i class="bi bi-ui-radios me-2"></i> Pilihan Ganda</a></li>
                                <li><a class="dropdown-item" href="#" data-value="dropdown"><i class="bi bi-caret-down-square me-2"></i> Dropdown (Pilihan)</a></li>
                                <li><a class="dropdown-item" href="#" data-value="checkbox"><i class="bi bi-check2-square me-2"></i> Checkbox</a></li>
                                <li><a class="dropdown-item" href="#" data-value="upload"><i class="bi bi-cloud-arrow-up me-2"></i> Upload File</a></li>
                                <li><a class="dropdown-item" href="#" data-value="tandatangan"><i class="bi bi-pen me-2"></i> Tanda Tangan Digital</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="mt-3 pilihan-container-template" style="display: none;">
                    <textarea class="form-control" name="pilihan" rows="3" placeholder="Pisahkan setiap pilihan dengan koma..."></textarea>
                </div>
                <hr>
                <div class="d-flex justify-content-end">
                    <div class="form-check form-switch me-auto">
                        <input class="form-check-input" type="checkbox" role="switch" name="wajib_diisi" id="wajib_diisi_new" checked>
                        <label class="form-check-label" for="wajib_diisi_new">Wajib diisi</label>
                    </div>
                    <button type="button" class="btn btn-dark btn-sm me-2" onclick="removeNewQuestion(this)">Batal</button>
                    <button type="submit" name="simpan_pertanyaan" class="btn btn-sm btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@5.0.10/dist/signature_pad.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>


<script>
// Fungsi untuk menyalin link
function copyLink(elementId) {
    const input = document.getElementById(elementId);
    input.select();
    input.setSelectionRange(0, 99999);
    document.execCommand("copy");
    alert("Link berhasil disalin!");
}

// Fungsi untuk menghapus form pertanyaan baru yang batal dibuat
function removeNewQuestion(button) {
    button.closest('.question-card').remove();
    document.getElementById('add-question-btn').style.display = 'inline-block';
}

// Fungsi untuk beralih antara mode lihat dan mode edit
function toggleEditMode(questionId, isEditing) {
    const card = document.getElementById('q-card-' + questionId);
    const viewMode = card.querySelector('.view-mode');
    const editMode = card.querySelector('.edit-mode');
    viewMode.style.display = isEditing ? 'none' : 'block';
    editMode.style.display = isEditing ? 'block' : 'none';
}

// Fungsi utama untuk mengelola semua dropdown kustom (dengan ikon)
function initializeCustomDropdowns(context = document) {
    context.querySelectorAll('.custom-select-dropdown').forEach(dropdown => {
        const hiddenInput = dropdown.querySelector('input[type="hidden"]');
        const button = dropdown.querySelector('button');
        const items = dropdown.querySelectorAll('.dropdown-item');
        const pilihanContainer = dropdown.closest('form').querySelector('.edit-pilihan-container, .pilihan-container-template');

        function updateButton(value) {
            const selectedItem = dropdown.querySelector(`.dropdown-item[data-value="${value}"]`);
            if (selectedItem) {
                button.innerHTML = selectedItem.innerHTML;
                hiddenInput.value = value;
                if (pilihanContainer) {
                    const needsOptions = ['dropdown', 'checkbox', 'pilihanganda'].includes(value);
                    pilihanContainer.style.display = needsOptions ? 'block' : 'none';
                    pilihanContainer.querySelector('textarea').required = needsOptions;
                }
            }
        }

        if (hiddenInput.value) {
            updateButton(hiddenInput.value);
        }

        items.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const value = this.getAttribute('data-value');
                updateButton(value);
            });
        });
    });
}

// =======================================================
// EVENT LISTENER UTAMA (HANYA SATU)
// =======================================================
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Inisialisasi semua dropdown yang sudah ada (di mode edit)
    initializeCustomDropdowns();

    // 2. Event listener untuk tombol "Tambah Pertanyaan"
    const addQuestionBtn = document.getElementById('add-question-btn');
    if (addQuestionBtn) {
        addQuestionBtn.addEventListener('click', function() {
            this.style.display = 'none';
            const template = document.getElementById('new-question-template');
            const container = document.getElementById('questions-container');
            const newQuestion = template.cloneNode(true);
            newQuestion.style.display = 'block';
            newQuestion.id = '';
            container.appendChild(newQuestion);
            initializeCustomDropdowns(newQuestion); 
            newQuestion.querySelector('input[name="isi_pertanyaan"]').focus();
        });
    }

    // 3. Inisialisasi Logika Drag and Drop
    const container = document.getElementById('questions-container');
    if (container) {
        new Sortable(container, {
            animation: 150,
            handle: '.drag-handle',
            
            onEnd: function (evt) {
                // =======================================================
                // PERBAIKAN FINAL CARA MENGAMBIL ID
                // =======================================================
                const newOrderIds = [];
                // Ambil semua anak langsung (direct children) dari kontainer
                // Ini lebih andal daripada querySelectorAll
                Array.from(container.children).forEach(item => {
                    // Cek jika elemen memiliki data-id dan bukan template
                    if (item.hasAttribute('data-id') && item.id !== 'new-question-template') {
                        newOrderIds.push(item.getAttribute('data-id'));
                    }
                });

                // Tampilkan di console untuk debug SEBELUM mengirim
                console.log("Urutan ID Baru yang Akan Dikirim:", newOrderIds);

                // Hentikan jika array kosong untuk mencegah request yang tidak perlu
                if (newOrderIds.length === 0) {
                    console.error("Gagal mengumpulkan ID pertanyaan. Dibatalkan.");
                    return; 
                }
                
                const postUrl = '/presensi-digital/formulir/update_urutan.php';

                fetch(postUrl, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ urutan: newOrderIds })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => { throw new Error(text || `HTTP error! Status: ${response.status}`) });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.status === 'success') {
                        console.log('Urutan berhasil disimpan!');
                        const notif = document.createElement('div');
                        notif.className = 'alert alert-success position-fixed top-0 end-0 m-3 shadow-sm';
                        notif.style.zIndex = '9999';
                        notif.textContent = 'Urutan pertanyaan berhasil diperbarui!';
                        document.body.appendChild(notif);
                        setTimeout(() => notif.remove(), 2500);
                    } else {
                        const errorMessage = data ? data.message : 'Respons server tidak valid.';
                        alert('Gagal menyimpan urutan: ' + errorMessage);
                    }
                })
                .catch(error => {
                    console.error('Error AJAX:', error);
                    alert('Tidak dapat terhubung ke server. Detail: ' + error.message);
                });
            }
        });
    }
});
</script>