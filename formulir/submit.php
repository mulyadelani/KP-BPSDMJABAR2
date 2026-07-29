<?php
require_once '../config.php';

// Pastikan ini adalah request POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Validasi dasar: pastikan formulir_id ada
    if (!isset($_POST['formulir_id'])) {
        die("Error: ID Formulir tidak ditemukan.");
    }
    $formulir_id = $_POST['formulir_id'];

    // Mulai transaksi database untuk memastikan integritas data
    $conn->begin_transaction();

    try {
        // ====================================================================
        // LANGKAH 1: Buat record tanggapan utama untuk mendapatkan ID
        // ====================================================================
        $stmt_tanggapan = $conn->prepare("INSERT INTO tanggapan (formulir_id) VALUES (?)");
        $stmt_tanggapan->bind_param("i", $formulir_id);
        $stmt_tanggapan->execute();
        $tanggapan_id = $stmt_tanggapan->insert_id;

        // Siapkan statement untuk menyimpan jawaban individual (akan digunakan berulang kali)
        $stmt_jawaban = $conn->prepare("INSERT INTO jawaban (tanggapan_id, pertanyaan_id, isi_jawaban) VALUES (?, ?, ?)");

        // ====================================================================
        // LANGKAH 2: Proses semua jawaban dari $_POST (Teks, Dropdown, Checkbox, TTD)
        // ====================================================================
        if (isset($_POST['jawaban']) && is_array($_POST['jawaban'])) {
            foreach ($_POST['jawaban'] as $pertanyaan_id => $jawaban) {
                $isi_jawaban = '';
                
                // Kasus 1: Jawaban adalah array (dari checkbox)
                if (is_array($jawaban)) {
                    $isi_jawaban = implode(', ', $jawaban);
                } 
                // Kasus 2: Jawaban adalah Tanda Tangan (data base64)
                elseif (strpos($jawaban, 'data:image/png;base64,') === 0) {
                    list($type, $data) = explode(';', $jawaban);
                    list(, $data)      = explode(',', $data);
                    $data = base64_decode($data);
                    
                    $folderPath = BASE_PATH . 'uploads/signatures/';
                    $file_name = 'ttd_' . time() . '_' . $tanggapan_id . '.png';
                    $filePath = $folderPath . $file_name;

                    if (!is_dir($folderPath)) {
                        mkdir($folderPath, 0777, true);
                    }

                    if (file_put_contents($filePath, $data)) {
                        $isi_jawaban = $file_name;
                    }
                } 
                // Kasus 3: Jawaban adalah teks biasa (teks, dropdown)
                else {
                    $isi_jawaban = trim($jawaban);
                }

                // Simpan jawaban dari $_POST ke database jika tidak kosong
                if (!empty($isi_jawaban)) {
                    $stmt_jawaban->bind_param("iis", $tanggapan_id, $pertanyaan_id, $isi_jawaban);
                    $stmt_jawaban->execute();
                }
            }
        }

        // ====================================================================
        // LANGKAH 3: Proses semua jawaban dari $_FILES (Upload File)
        // ====================================================================
        if (isset($_FILES['jawaban']) && is_array($_FILES['jawaban']['name'])) {
            foreach ($_FILES['jawaban']['name'] as $pertanyaan_id => $filename) {
                // Proses hanya jika tidak ada error upload
                if ($_FILES['jawaban']['error'][$pertanyaan_id] === UPLOAD_ERR_OK) {
                    $folderPath = BASE_PATH . 'uploads/files/';
                    $original_filename = basename($filename);
                    
                    // Bersihkan nama file dari karakter yang tidak aman
                    $safe_filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $original_filename);
                    $file_name = time() . '_' . $safe_filename;
                    $target_file = $folderPath . $file_name;

                    // Buat folder jika belum ada
                    if (!is_dir($folderPath)) {
                        mkdir($folderPath, 0777, true);
                    }

                    // Pindahkan file yang di-upload ke folder tujuan
                    if (move_uploaded_file($_FILES['jawaban']['tmp_name'][$pertanyaan_id], $target_file)) {
                        $isi_jawaban = $file_name;
                        // Simpan nama file ke database
                        $stmt_jawaban->bind_param("iis", $tanggapan_id, $pertanyaan_id, $isi_jawaban);
                        $stmt_jawaban->execute();
                    }
                }
            }
        }

        // ====================================================================
        // LANGKAH 4: Finalisasi
        // ====================================================================
        // Jika semua proses di atas berhasil tanpa error, simpan perubahan ke database
        $conn->commit();
         // Jika semua proses di atas berhasil tanpa error, simpan perubahan ke database
        $conn->commit();
        
        // Alihkan ke halaman sukses yang baru
        header("Location: sukses.php");
        exit(); // Hentikan eksekusi skrip setelah redirect

    } catch (Exception $e) {
        // Jika terjadi error di salah satu langkah, batalkan semua query yang sudah dijalankan
        $conn->rollback();
        // Tampilkan pesan error yang informatif
        die("Terjadi kesalahan saat menyimpan data: " . $e->getMessage());
    }

} else {
    // Jika halaman diakses bukan dengan metode POST
    echo "Metode tidak diizinkan.";
}
?>