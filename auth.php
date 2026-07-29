<?php
/**
 * File ini berisi fungsi untuk proteksi halaman (autentikasi).
 */

// Panggil file ini HANYA di halaman yang perlu login.
if (!function_exists('check_login')) {
    function check_login() {
        // Mulai session jika belum dimulai
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "landing.php");
            exit();
        }
    }
}