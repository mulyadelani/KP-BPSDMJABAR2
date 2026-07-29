<?php
// config.php
// Memulai atau melanjutkan session yang ada.
session_start();

// Pengaturan Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'aplikasipelatihannative');

define('BASE_URL', 'http://localhost/presensi/');

// Pengaturan BASE URL STATIS
define('BASE_PATH', dirname(__FILE__) . '/');

// Koneksi ke Database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Koneksi Database Gagal: " . $conn->connect_error);
}

// FUNGSI check_login() DIHAPUS DARI SINI
