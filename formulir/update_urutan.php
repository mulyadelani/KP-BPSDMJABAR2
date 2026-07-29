<?php
// =======================================================
// KODE INI HANYA UNTUK DEBUGGING
// =======================================================
header('Content-Type: application/json');
require_once '../config.php';

// Path ke file log
$log_file = '../debug.log';
// Hapus log lama dan tulis header baru untuk setiap request
file_put_contents($log_file, "--- LOG BARU PADA " . date('Y-m-d H:i:s') . " ---\n");

function log_message($message) {
    global $log_file;
    file_put_contents($log_file, $message . "\n", FILE_APPEND);
}

log_message("Skrip update_urutan.php dimulai.");

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan tidak diketahui.'];
http_response_code(500);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    log_message("Metode POST dan session user ditemukan.");
    
    $json_input = file_get_contents('php://input');
    log_message("Data raw diterima: " . $json_input);
    
    $data = json_decode($json_input, true);

    if (json_last_error() === JSON_ERROR_NONE && isset($data['urutan']) && is_array($data['urutan'])) {
        $urutanIds = $data['urutan'];
        log_message("Data JSON berhasil di-decode. Urutan ID: " . implode(', ', $urutanIds));
        
        $conn->autocommit(FALSE);
        $conn->begin_transaction();
        log_message("Transaksi dimulai.");
        
        try {
            $stmt = $conn->prepare("UPDATE pertanyaan SET urutan = ? WHERE id = ?");
            if (!$stmt) {
                 throw new Exception("Gagal mempersiapkan statement: " . $conn->error);
            }

            log_message("Mulai loop UPDATE...");
            foreach ($urutanIds as $index => $pertanyaan_id) {
                $urutan_baru = $index;
                $clean_id = (int) $pertanyaan_id;
                
                log_message("Menjalankan: UPDATE pertanyaan SET urutan = {$urutan_baru} WHERE id = {$clean_id}");
                
                $stmt->bind_param("ii", $urutan_baru, $clean_id);
                $stmt->execute();
                
                // Cek apakah query benar-benar mengubah sesuatu
                if ($stmt->affected_rows > 0) {
                    log_message("  -> SUKSES: 1 baris terpengaruh.");
                } else {
                    log_message("  -> PERINGATAN: 0 baris terpengaruh untuk ID {$clean_id}. Mungkin ID tidak ada atau urutan sudah sama.");
                }
            }
            log_message("Loop UPDATE selesai.");
            
            $conn->commit();
            log_message("Transaksi di-commit.");
            
            http_response_code(200);
            $response = ['status' => 'success', 'message' => 'Urutan berhasil diperbarui.'];

        } catch (Exception $e) {
            log_message("!!! EXCEPTION TERTANGKAP !!!");
            if ($conn->ping()) $conn->rollback();
            log_message("Transaksi di-rollback. Error: " . $e->getMessage());
            $response = ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
        } finally {
            $conn->autocommit(TRUE);
            log_message("Autocommit diaktifkan kembali.");
        }
    } else {
        http_response_code(400);
        $response = ['status' => 'error', 'message' => 'Data urutan tidak valid atau tidak ditemukan.'];
        log_message("Error: Data tidak valid.");
    }
} else {
    http_response_code(403);
    $response = ['status' => 'error', 'message' => 'Akses ditolak.'];
    log_message("Error: Akses ditolak.");
}

log_message("Respons akhir yang dikirim: " . json_encode($response));
echo json_encode($response);
?>