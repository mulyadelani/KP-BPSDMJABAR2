<?php
require_once 'config.php';
require_once 'auth.php';
check_login();

header('Content-Type: application/json');

$user_id_session = $_SESSION['user_id'];
$user_role_session = $_SESSION['role'];
$search = $_GET['search'] ?? '';
$searchTerm = "%{$search}%";

if ($user_role_session == 'admin') {
    $sql = "SELECT id, nama_kategori, deskripsi FROM kategori WHERE nama_kategori LIKE ? ORDER BY id DESC LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $searchTerm);
} else {
    $sql = "SELECT id, nama_kategori, deskripsi FROM kategori WHERE user_id = ? AND nama_kategori LIKE ? ORDER BY id DESC LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id_session, $searchTerm);
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
$stmt->close();