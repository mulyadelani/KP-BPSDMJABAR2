<?php
require_once '../config.php';
require_once '../auth.php'; 
check_login();

// Validasi ID formulir dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: " . BASE_URL . "kelola_presensi.php");
    exit();
}
$formulir_id = $_GET['id'];

// Ambil info formulir (judul, user_id pemilik, dan kategori_id)
$stmt_info = $conn->prepare("SELECT f.judul_pelatihan, f.kategori_id, k.user_id FROM formulir f JOIN kategori k ON f.kategori_id = k.id WHERE f.id = ?");
$stmt_info->bind_param("i", $formulir_id);
$stmt_info->execute();
$info_result = $stmt_info->get_result();
if ($info_result->num_rows == 0) die("Formulir tidak ditemukan.");
$formulir_info = $info_result->fetch_assoc();
$judul_pelatihan = $formulir_info['judul_pelatihan'];
$kategori_id_formulir = $formulir_info['kategori_id'];

// Proteksi Akses
if ($_SESSION['role'] != 'admin' && $formulir_info['user_id'] != $_SESSION['user_id']) die("Akses ditolak.");

// Logika Hapus Tanggapan
if (isset($_GET['hapus'])) {
    $id_tanggapan_hapus = $_GET['hapus'];
    $stmt_hapus = $conn->prepare("DELETE FROM tanggapan WHERE id = ?");
    $stmt_hapus->bind_param("i", $id_tanggapan_hapus);
    if ($stmt_hapus->execute()) $_SESSION['pesan_sukses'] = "Tanggapan berhasil dihapus.";
    header("Location: tanggapan.php?id=" . $formulir_id);
    exit();
}

// Ambil semua data pertanyaan, termasuk tipe_pertanyaan
$stmt_p = $conn->prepare("SELECT id, isi_pertanyaan, tipe_pertanyaan FROM pertanyaan WHERE formulir_id = ? ORDER BY urutan ASC");
$stmt_p->bind_param("i", $formulir_id);
$stmt_p->execute();
$pertanyaan_result = $stmt_p->get_result();
$questions = [];
while ($row = $pertanyaan_result->fetch_assoc()) $questions[] = $row;

// Ambil semua tanggapan dan jawabannya
$stmt_t = $conn->prepare("SELECT t.id as tanggapan_id, t.created_at, j.pertanyaan_id, j.isi_jawaban, p.tipe_pertanyaan FROM tanggapan t LEFT JOIN jawaban j ON t.id = j.tanggapan_id LEFT JOIN pertanyaan p ON j.pertanyaan_id = p.id WHERE t.formulir_id = ? ORDER BY t.id ASC, p.urutan ASC");
$stmt_t->bind_param("i", $formulir_id);
$stmt_t->execute();
$result = $stmt_t->get_result();
$responses = [];
while ($row = $result->fetch_assoc()) {
    $responses[$row['tanggapan_id']]['timestamp'] = $row['created_at'];
    $responses[$row['tanggapan_id']]['jawaban'][$row['pertanyaan_id']] = ['isi' => $row['isi_jawaban'], 'tipe' => $row['tipe_pertanyaan']];
}

$pesan = '';
if(isset($_SESSION['pesan_sukses'])) {
    $pesan = "<div class='alert alert-success alert-dismissible fade show no-print' role='alert'>{$_SESSION['pesan_sukses']}<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    unset($_SESSION['pesan_sukses']);
}

include '../header.php';
?>

<!-- KONTEN HANYA UNTUK DICETAK -->
<div class="print-only">
    <div class="print-header">
        <img src="<?php echo BASE_URL; ?>assets/img/logo-jabar.png" alt="Logo" class="logo">
        <div class="kop-text">
            <h1>PEMERINTAH DAERAH PROVINSI JAWA BARAT</h1>
            <h2>BADAN PENGEMBANGAN SUMBER DAYA MANUSIA</h2>
            <p>Jalan Kolonel Masturi KM 3,5 No. 11 Kota Cimahi Telepon (022) 6649471<br>Faksimil (022) 6649463 Website: bpsdm.jabarprov.go.id E-mail: bpsdm@jabarprov.go.id<br>KOTA CIMAHI 40511</p>
        </div>
        <div style="clear: both;"></div>
    </div>
    <div class="print-title-container">
        <h3>DAFTAR HADIR</h3>
        <h4><?php echo htmlspecialchars($judul_pelatihan); ?></h4>
    </div>
    <!-- Kontainer untuk tabel versi cetak yang akan digenerate oleh JavaScript -->
    <div id="print-table-container"></div>
</div>

<!-- KONTEN HANYA UNTUK TAMPILAN LAYAR -->
<div class="no-print">
    <!-- Header Halaman dan Breadcrumb -->
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h1 class="h2 fw-bold">Rekap Tanggapan</h1>
            <p class="text-muted mb-0">Tanggapan untuk: <strong><?php echo htmlspecialchars($judul_pelatihan); ?></strong></p>
        </div>
        <div class="d-flex flex-column align-items-end">
            <nav style="--bs-breadcrumb-divider: '/';" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>kelola_presensi.php">Kelola Presensi</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>daftar_presensi.php?kategori_id=<?php echo $kategori_id_formulir ?? ''; ?>">Daftar Presensi</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Lihat Tanggapan</li>
                </ol>
            </nav>
            <div class="d-flex gap-2 mt-2">
                <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#kolomModal" title="Pilih Kolom"><i class="bi bi-layout-three-columns"></i></button>
                <button class="btn btn-success" id="export-excel-btn"><i class="bi bi-file-earmark-excel-fill"></i> Ekspor Excel</button>
                <div class="btn-group">
                    <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-printer-fill"></i> Cetak Laporan</button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" onclick="handlePrint('portrait')"><i class="bi bi-file-earmark-text me-2"></i> Cetak Portrait</a></li>
                        <li><a class="dropdown-item" href="#" onclick="handlePrint('landscape')"><i class="bi bi-file-earmark-text-fill me-2"></i> Cetak Landscape</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php echo $pesan; ?>

    <!-- Tabel Rekap dalam Card (untuk tampilan layar) -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tabel-tanggapan">
                    <thead class="table-light">
                        <tr>
                            <th data-column-index="0" style="width: 5%;">No.</th>
                            <th data-column-index="1">Waktu Submit</th>
                            <?php 
                            $columnIndex = 2;
                            foreach ($questions as $q): 
                            ?>
                                <th data-column-index="<?php echo $columnIndex++; ?>">
                                    <?php echo htmlspecialchars($q['isi_pertanyaan']); ?>
                                </th>
                            <?php endforeach; ?>
                            <th class="text-center no-print" data-column-index="<?php echo $columnIndex; ?>">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (!empty($responses)):
                            $no = 1;
                            foreach ($responses as $tanggapan_id => $data): 
                        ?>
                            <tr>
                                <td data-column-index="0"><?php echo $no++; ?></td>
                                <td data-column-index="1"><?php echo $data['timestamp']; ?></td>
                                <?php 
                                $columnIndex = 2;
                                foreach ($questions as $q): 
                                ?>
                                    <td data-column-index="<?php echo $columnIndex++; ?>">
                                        <?php
                                        $jawaban = $data['jawaban'][$q['id']] ?? ['isi' => '', 'tipe' => ''];
                                        if ($jawaban['tipe'] == 'upload') {
                                            if (!empty($jawaban['isi']) && file_exists(BASE_PATH . "uploads/files/{$jawaban['isi']}")) {
                                                $webPath = BASE_URL . "uploads/files/{$jawaban['isi']}";
                                                echo "<a href='{$webPath}' target='_blank' class='btn btn-outline-secondary btn-sm'>Lihat File</a>";
                                            } else { echo "<small class='text-muted'>-</small>"; }
                                        } 
                                        elseif ($jawaban['tipe'] == 'tandatangan') {
                                            if (!empty($jawaban['isi']) && file_exists(BASE_PATH . "uploads/signatures/{$jawaban['isi']}")) {
                                                $webPath = BASE_URL . "uploads/signatures/{$jawaban['isi']}";
                                                echo "<a href='{$webPath}' target='_blank'><img src='{$webPath}' alt='Tanda Tangan' height='40' style='border: 1px solid #ddd;'></a>";
                                            } else { echo "<small class='text-muted'>-</small>"; }
                                        } 
                                        else { echo htmlspecialchars($jawaban['isi']); }
                                        ?>
                                    </td>
                                <?php endforeach; ?>
                                <td class="text-center no-print" data-column-index="<?php echo $columnIndex; ?>">
                                    <a href="tanggapan.php?id=<?php echo $formulir_id; ?>&hapus=<?php echo $tanggapan_id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin?');" title="Hapus"><i class="bi bi-trash-fill"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pilihan Kolom -->
<div class="modal fade" id="kolomModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Pilih Kolom</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="kolom-pilihan-container"></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button></div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>

<!-- Muat library SheetJS -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
// Fungsi Cetak dengan Orientasi (Versi Paling Kompatibel)
function handlePrint(orientation) {
    const printContent = document.querySelector('.print-only');
    const table = document.getElementById('tabel-tanggapan');
    if (!printContent || !table) return;

    // 1. Buat salinan tabel layar yang sudah difilter
    const tableClone = table.cloneNode(true);
    tableClone.removeAttribute('id'); // Hapus ID agar tidak duplikat
    tableClone.classList.remove('table-hover', 'align-middle');
    tableClone.classList.add('table-bordered'); // Beri border yang jelas untuk cetak

    // Hapus kolom yang tidak terlihat dan kolom aksi dari salinan
    tableClone.querySelectorAll('th, td').forEach(cell => {
        if (cell.style.display === 'none' || cell.classList.contains('no-print')) {
            cell.remove();
        }
    });

    // 2. Buat iframe baru yang tersembunyi
    const iframe = document.createElement('iframe');
    iframe.style.position = 'absolute';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = '0';
    document.body.appendChild(iframe);
    
    // 3. Tulis konten ke dalam iframe
    const doc = iframe.contentWindow.document;
    doc.open();
    doc.write('<html><head><title>Cetak Laporan</title>');
    // Link ke stylesheet Bootstrap & print.css
    doc.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">');
    doc.write('<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/print.css" media="print">');
    
    // 4. SUNTIKKAN STYLE ORIENTASI DI SINI
    doc.write('<style>');
    if (orientation === 'landscape') {
        doc.write('@page { size: 330mm 210mm landscape; margin: 15mm; }');
    } else {
        doc.write('@page { size: 210mm 330mm portrait; margin: 15mm; }');
    }
    doc.write('</style>');

    doc.write('</head><body>');
    // Salin HTML dari kop surat & judul
    doc.write(document.querySelector('.print-only').innerHTML);
    // Ganti placeholder tabel di kop surat dengan tabel yang sudah difilter
    const printTableContainerInIframe = doc.getElementById('print-table-container');
    if (printTableContainerInIframe) {
        printTableContainerInIframe.appendChild(tableClone);
    }
    doc.write('</body></html>');
    doc.close();

    // 5. Cetak dari iframe setelah konten dimuat
    iframe.contentWindow.focus();
    setTimeout(() => {
        iframe.contentWindow.print();
        // Hapus iframe setelah selesai (opsional, untuk kebersihan)
        document.body.removeChild(iframe);
    }, 250); // Beri waktu 250ms untuk rendering
}

document.addEventListener('DOMContentLoaded', function() {
    
    // Hapus kelas orientasi setelah proses cetak selesai
    window.addEventListener('afterprint', () => {
        document.body.classList.remove('print-landscape');
    });

    const table = document.getElementById('tabel-tanggapan');
    const kolomModalContainer = document.getElementById('kolom-pilihan-container');
    const exportBtn = document.getElementById('export-excel-btn');
    if (!table || !kolomModalContainer || !exportBtn) return;
    
    // Inisialisasi Modal Pemilihan Kolom
    const headers = table.querySelectorAll('thead th');
    let kolomOptionsHTML = '';
    headers.forEach((th, index) => {
        if (!th.classList.contains('no-print')) {
            const columnName = th.textContent.trim();
            kolomOptionsHTML += `<div class="form-check form-switch"><input class="form-check-input kolom-toggle" type="checkbox" role="switch" id="kolom-${index}" data-index="${index}" checked><label class="form-check-label" for="kolom-${index}">${columnName}</label></div>`;
        }
    });
    kolomModalContainer.innerHTML = kolomOptionsHTML;

    // Fungsi untuk menyembunyikan/menampilkan kolom di layar
    function updateKolomVisibility() {
        kolomModalContainer.querySelectorAll('.kolom-toggle').forEach(toggle => {
            const index = toggle.getAttribute('data-index');
            const isVisible = toggle.checked;
            table.querySelectorAll(`[data-column-index="${index}"]`).forEach(cell => {
                cell.style.display = isVisible ? '' : 'none';
            });
        });
    }
    kolomModalContainer.addEventListener('change', updateKolomVisibility);

    // Logika Ekspor Excel
    exportBtn.addEventListener('click', function() {
        const data_to_export = [];
        const visibleColumnIndexes = new Set();
        kolomModalContainer.querySelectorAll('.kolom-toggle:checked').forEach(toggle => {
            visibleColumnIndexes.add(toggle.getAttribute('data-index'));
        });

        const visibleHeaders = [];
        headers.forEach((th, index) => {
            if (visibleColumnIndexes.has(String(index))) {
                visibleHeaders.push(th.textContent.trim());
            }
        });
        data_to_export.push(visibleHeaders);

        table.querySelectorAll('tbody tr').forEach(row => {
            const rowData = [];
            row.querySelectorAll('td').forEach((cell, index) => {
                if (visibleColumnIndexes.has(String(index))) {
                    let cellValue = cell.textContent.trim();
                    const ttdLink = cell.querySelector('a[href*="/signatures/"]');
                    const uploadLink = cell.querySelector('a[href*="/files/"]');
                    if (ttdLink) cellValue = ttdLink.href;
                    else if (uploadLink) cellValue = uploadLink.href;
                    rowData.push(cellValue);
                }
            });
            data_to_export.push(rowData);
        });

        const fileName = "Rekap_Tanggapan_<?php echo preg_replace('/[^a-zA-Z0-9-]/', '_', $judul_pelatihan); ?>.xlsx";
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(data_to_export);

        const range = XLSX.utils.decode_range(ws['!ref']);
        for (let R = range.s.r; R <= range.e.r; ++R) {
            for (let C = range.s.c; C <= range.e.c; ++C) {
                const cell_ref = XLSX.utils.encode_cell({c:C, r:R});
                if (!ws[cell_ref]) continue;
                const cell = ws[cell_ref];
                const cell_text = cell.v || '';
                if (typeof cell_text === 'string' && cell_text.startsWith('http')) {
                    cell.l = { Target: cell_text, Tooltip: "Klik untuk membuka" };
                }
            }
        }
        
        XLSX.utils.book_append_sheet(wb, ws, "Tanggapan");
        XLSX.writeFile(wb, fileName);
    });
});
</script>