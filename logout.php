<?php
/**
 * logout.php
 *
 * File ini bertanggung jawab untuk proses logout pengguna.
 * 1. Memulai session untuk mengakses data session yang ada.
 * 2. Mengosongkan semua variabel di dalam $_SESSION.
 * 3. Menghancurkan session di server.
 * 4. Mengalihkan pengguna kembali ke halaman login.
 */

// 1. Panggil file konfigurasi untuk memastikan session_start() dijalankan.
require_once 'config.php';

// 2. Kosongkan array $_SESSION.
// Ini adalah cara yang aman untuk menghapus semua data dari session saat ini.
$_SESSION = array();

// 3. Hancurkan session.
// Perintah ini akan menghapus file session dari server.
session_destroy();

// 4. Arahkan pengguna kembali ke halaman login.
// Pengguna tidak akan bisa kembali ke halaman yang terproteksi menggunakan tombol "Back" di browser.
header("Location: landing.php");

// 5. Hentikan eksekusi skrip.
// Ini adalah praktik yang baik setelah melakukan pengalihan header untuk memastikan tidak ada kode lain yang berjalan.
exit();
?>