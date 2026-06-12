<?php
/**
 * Konfigurasi Utama Website Top Up
 * -------------------------------------------------
 * Ubah nilai di bawah sesuai server VPS Anda (aaPanel).
 */

// Tampilkan error saat development. Set ke 0 di production.
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', false);
}

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// ====== KONFIGURASI DATABASE (MySQLi) ======
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'topup_user');       // ganti dengan user database Anda
define('DB_PASS', 'ubah_password_ini'); // ganti dengan password database Anda
define('DB_NAME', 'topup_db');         // ganti dengan nama database Anda
define('DB_PORT', 3306);

// ====== KONFIGURASI SITE ======
// Base URL otomatis (jangan ada trailing slash). Ubah manual bila perlu.
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
if (!defined('BASE_URL')) {
    define('BASE_URL', $scheme . '://' . $host);
}

// Zona waktu
date_default_timezone_set('Asia/Jakarta');

// Path absolut root project
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('UPLOAD_URL', BASE_URL . '/uploads');

// Mulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/database.php';
require_once ROOT_PATH . '/includes/functions.php';
