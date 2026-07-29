<?php
// config.php
// Memulai atau melanjutkan session yang ada.
session_start();

// Pengaturan Database
define('DB_HOST', '172.21.0.2');
define('DB_USER', 'aplikasipelaithan');
define('DB_PASS', 'wadawaplikasipelaithan123');
define('DB_NAME', 'aplikasipelatihannative');

define('BASE_URL', 'https://integral-bpsdm.jabarprov.go.id/presensi-digital/');

// Pengaturan BASE URL STATIS
define('BASE_URL', 'https://integral-bpsdm.jabarprov.go.id/presensi-digital/');
define('BASE_PATH', dirname(__FILE__) . '/');

// Koneksi ke Database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Koneksi Database Gagal: " . $conn->connect_error);
}

// FUNGSI check_login() DIHAPUS DARI SINI
