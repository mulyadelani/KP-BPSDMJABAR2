<?php
require_once '../config.php';
check_login();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul_pelatihan = $_POST['judul_pelatihan'];
    $user_id = $_SESSION['user_id'];

    // 1. Simpan data formulir utama
    $stmt = $conn->prepare("INSERT INTO formulir (user_id, judul_pelatihan) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $judul_pelatihan);
    $stmt->execute();
    $formulir_id = $stmt->insert_id; // Dapatkan ID formulir yang baru dibuat

    // 2. Simpan setiap pertanyaan
    if (isset($_POST['pertanyaan'])) {
        $stmt_pertanyaan = $conn->prepare("INSERT INTO pertanyaan (formulir_id, tipe_pertanyaan, isi_pertanyaan, pilihan, urutan) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($_POST['pertanyaan'] as $urutan => $p) {
            $tipe = $p['tipe'];
            $isi = $p['isi'];
            $pilihan = ($tipe == 'dropdown' || $tipe == 'checkbox') ? $p['pilihan'] : NULL;
            $stmt_pertanyaan->bind_param("isssi", $formulir_id, $tipe, $isi, $pilihan, $urutan);
            $stmt_pertanyaan->execute();
        }
    }

    echo "Formulir berhasil disimpan! <a href='../index.php'>Kembali ke Dashboard</a>";
}
?>