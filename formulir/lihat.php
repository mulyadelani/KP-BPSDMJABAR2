<?php
require_once '../config.php';
require_once '../auth.php'; 
//check_login();

// Validasi & Ambil data formulir (termasuk status)
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) die("Formulir tidak valid.");
$formulir_id = $_GET['id'];
$stmt_info = $conn->prepare("SELECT judul_pelatihan, deskripsi, status FROM formulir WHERE id = ?");
$stmt_info->bind_param("i", $formulir_id);
$stmt_info->execute();
$info_result = $stmt_info->get_result();
if ($info_result->num_rows == 0) die("Formulir tidak ditemukan.");
$formulir_info = $info_result->fetch_assoc();
$judul_pelatihan = $formulir_info['judul_pelatihan'];
$deskripsi_pelatihan = $formulir_info['deskripsi'];
$status_formulir = $formulir_info['status'];

// Ambil semua pertanyaan (hanya jika form aktif)
$pertanyaan_list = [];
if ($status_formulir == 1) {
    $result_pertanyaan = $conn->query("SELECT * FROM pertanyaan WHERE formulir_id = $formulir_id ORDER BY urutan ASC");
    while ($row = $result_pertanyaan->fetch_assoc()) {
        $pertanyaan_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presensi: <?php echo htmlspecialchars($judul_pelatihan); ?></title>
    <link rel="icon" type="image/png" href="/presensi-digital/assets/img/logo-presensi.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">

<header class="public-header">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="#"><i class="bi bi-fingerprint"></i> Presensi Digital</a>
    </div>
</header>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-8">
            <div class="card shadow-sm border-0 form-container">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-1 fw-bold">
                        <?php echo htmlspecialchars($judul_pelatihan); ?>
                    </h4>
                    <?php if (!empty($deskripsi_pelatihan)): ?>
                        <p class="mb-0 fw-light small">
                            <?php echo nl2br(htmlspecialchars($deskripsi_pelatihan)); ?>
                        </p>
                    <?php endif; ?>
                </div>

                    <?php if ($status_formulir == 1): // Jika status AKTIF, tampilkan form ?>
                    
                        <form action="submit.php" method="POST" enctype="multipart/form-data" id="presensiForm">
                            <input type="hidden" name="formulir_id" value="<?php echo $formulir_id; ?>">
                            
                            <?php 
                            foreach ($pertanyaan_list as $p): 
                                $isRequired = ($p['wajib_diisi'] == 1);
                                $requiredAttr = $isRequired ? 'required' : '';
                                $requiredStar = $isRequired ? ' <span class="text-danger">*</span>' : '';
                            ?>
                            <div class="question-block">
                                <label><?php echo htmlspecialchars($p['isi_pertanyaan']) . $requiredStar; ?></label>
                                <small class="form-text text-muted">Mohon isi jawaban Anda dengan benar dan sesuai.</small>
                                
                                <?php
                                switch ($p['tipe_pertanyaan']) {
                                    case 'text':
                                        echo "<input type='text' name='jawaban[{$p['id']}]' class='form-control' {$requiredAttr}>";
                                        break;
                                    case 'paragraf':
                                        echo "<textarea name='jawaban[{$p['id']}]' class='form-control' rows='4' {$requiredAttr}></textarea>";
                                        break;
                                    case 'pilihanganda':
                                        $options = explode(',', $p['pilihan']);
                                        foreach ($options as $key => $opt) {
                                            $trimmed_opt = trim($opt);
                                            echo "<div class='form-check'><input class='form-check-input' type='radio' name='jawaban[{$p['id']}]' value='{$trimmed_opt}' id='radio-{$p['id']}-{$key}' {$requiredAttr}><label class='form-check-label' for='radio-{$p['id']}-{$key}'>{$trimmed_opt}</label></div>";
                                        }
                                        break;
                                    case 'checkbox':
                                        $options = explode(',', $p['pilihan']);
                                        foreach ($options as $key => $opt) {
                                            $trimmed_opt = trim($opt);
                                            $checkboxRequired = ($key == 0) ? $requiredAttr : '';
                                            echo "<div class='form-check'><input class='form-check-input' type='checkbox' name='jawaban[{$p['id']}][]' value='{$trimmed_opt}' id='check-{$p['id']}-{$key}' {$checkboxRequired}><label class='form-check-label' for='check-{$p['id']}-{$key}'>{$trimmed_opt}</label></div>";
                                        }
                                        break;
                                    case 'dropdown':
                                        echo "<select name='jawaban[{$p['id']}]' class='form-select' {$requiredAttr}><option value='' selected disabled>-- Pilih Opsi --</option>";
                                        $options = explode(',', $p['pilihan']);
                                        foreach ($options as $opt) { echo "<option value='".trim($opt)."'>".trim($opt)."</option>"; }
                                        echo "</select>";
                                        break;
                                    case 'upload':
                                        echo "<input type='file' name='jawaban[{$p['id']}]' class='form-control' {$requiredAttr}>";
                                        break;
                                    case 'tandatangan':
                                        echo '<div><canvas id="signature-pad-canvas"></canvas></div>';
                                        echo "<input type='hidden' name='jawaban[{$p['id']}]' id='signature-data' {$requiredAttr}>";
                                        echo '<button type="button" id="clear-signature" class="btn btn-primary btn-sm mt-2">Ulangi Tanda Tangan</button>';
                                        break;
                                }
                                ?>
                            </div>
                            <?php endforeach; ?>
                            
                            <!-- Progress Bar & Tombol Submit -->
                            <div class="mt-4">
                                <label class="form-label">Progress Pengisian</label>
                                <div class="progress" style="height: 20px;"><div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div></div>
                            </div>
                            <div class="d-grid mt-4">
                                <button type="submit" id="submit-form" class="btn btn-primary btn-lg"><i class="bi bi-check-circle-fill"></i> Kirim Jawaban</button>
                            </div>
                        </form>
                    
                    <?php else: // Jika status TIDAK AKTIF, tampilkan pesan penutupan ?>
                    
                        <div class="text-center p-4">
                            <i class="bi bi-x-circle-fill text-danger display-3 mb-3"></i>
                            <h3 class="fw-bold">Presensi Ditutup</h3>
                            <p class="text-muted">Mohon maaf, presensi ini tidak lagi menerima jawaban.</p>
                        </div>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>


<script src="https://cdn.jsdelivr.net/npm/signature_pad@5.0.10/dist/signature_pad.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('presensiForm');
    const questionBlocks = form.querySelectorAll('.question-block');
    const totalQuestions = questionBlocks.length;
    const progressBar = document.getElementById('progressBar');
    
    const canvas = document.getElementById('signature-pad-canvas');
    let signaturePad;

    if (canvas) {
        function resizeCanvas() {
            const ratio =  Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            if (signaturePad) {
                signaturePad.clear(); 
                document.getElementById('signature-data').value = '';
            }
        }
        window.addEventListener("resize", resizeCanvas);
        signaturePad = new SignaturePad(canvas, { backgroundColor: 'rgb(255, 255, 255)' });
        resizeCanvas();
        document.getElementById('clear-signature').addEventListener('click', () => {
            signaturePad.clear();
            document.getElementById('signature-data').value = ''; 
            updateProgress();
        });
        signaturePad.addEventListener("endStroke", () => {
            document.getElementById('signature-data').value = signaturePad.isEmpty() ? '' : signaturePad.toDataURL('image/png');
            updateProgress();
        });
    }
    
    function updateProgress() {
        let answeredQuestions = 0;
        
        questionBlocks.forEach(block => {
            let isAnswered = false;
            const inputs = block.querySelectorAll('input, select, textarea');
            const firstInput = inputs[0];

            if (!firstInput) return; // Lewati jika blok tidak punya input
            
            if (firstInput.type === 'checkbox') {
                if (block.querySelector('input[type="checkbox"]:checked')) isAnswered = true;
            } else if (firstInput.type === 'radio') {
                if (block.querySelector('input[type="radio"]:checked')) isAnswered = true;
            } else if (firstInput.value && firstInput.value.trim() !== '') {
                isAnswered = true;
            }
            
            if (isAnswered) answeredQuestions++;
        });
        
        const percentage = totalQuestions > 0 ? Math.round((answeredQuestions / totalQuestions) * 100) : 0;
        progressBar.style.width = percentage + '%';
        progressBar.textContent = percentage + '%';
        progressBar.setAttribute('aria-valuenow', percentage);
    }

    form.addEventListener('input', updateProgress);
    form.addEventListener('change', updateProgress);

    form.addEventListener('submit', function(event) {
        // Validasi kustom untuk memastikan semua input 'required' terisi
        let allValid = true;
        questionBlocks.forEach(block => {
            const firstInput = block.querySelector('input[required], select[required], textarea[required]');
            if (firstInput) { // Hanya validasi jika ada input yang 'required'
                let blockIsValid = false;
                if (firstInput.type === 'checkbox' || firstInput.type === 'radio') {
                    if (block.querySelector('input:checked')) blockIsValid = true;
                } else if (firstInput.value.trim() !== '') {
                    blockIsValid = true;
                }

                if (!blockIsValid) allValid = false;
            }
        });

        if (!allValid) {
             alert('Harap isi semua kolom yang wajib diisi (bertanda *).');
             event.preventDefault();
             return;
        }
        
        if (signaturePad && document.getElementById('signature-data').hasAttribute('required') && signaturePad.isEmpty()) {
            alert('Tanda tangan wajib diisi.');
            event.preventDefault();
            return;
        }

        document.getElementById('submit-form').disabled = true;
        document.getElementById('submit-form').innerHTML = '<span class="spinner-border spinner-border-sm"></span> Mengirim...';
    });

    updateProgress();
});
</script>

</body>
</html>
