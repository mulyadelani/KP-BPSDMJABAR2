<?php
header('Content-Type: application/json');
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['status' => 'error', 'message' => 'Terjadi kesalahan.'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['formulir_id']) && isset($data['status'])) {
        $formulir_id = (int)$data['formulir_id'];
        $status_baru = $data['status'] ? 1 : 0; // Pastikan nilainya 1 atau 0
        $user_id = $_SESSION['user_id'];
        $role = $_SESSION['role'];

        // Validasi kepemilikan sebelum update
        $stmt_check = $conn->prepare("SELECT k.user_id FROM formulir f JOIN kategori k ON f.kategori_id = k.id WHERE f.id = ?");
        $stmt_check->bind_param("i", $formulir_id);
        $stmt_check->execute();
        $owner_id = $stmt_check->get_result()->fetch_assoc()['user_id'];

        if ($owner_id && ($role == 'admin' || $owner_id == $user_id)) {
            $stmt_update = $conn->prepare("UPDATE formulir SET status = ? WHERE id = ?");
            $stmt_update->bind_param("ii", $status_baru, $formulir_id);

            if ($stmt_update->execute()) {
                $response = ['status' => 'success', 'message' => 'Status berhasil diperbarui.'];
            } else {
                http_response_code(500);
                $response = ['status' => 'error', 'message' => 'Gagal memperbarui status di database.'];
            }
        } else {
            http_response_code(403);
            $response = ['status' => 'error', 'message' => 'Anda tidak memiliki izin.'];
        }
    } else {
        http_response_code(400);
        $response = ['status' => 'error', 'message' => 'Data tidak lengkap.'];
    }
} else {
    http_response_code(403);
    $response = ['status' => 'error', 'message' => 'Akses ditolak.'];
}

echo json_encode($response);
?>