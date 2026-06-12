<?php
/**
 * Koneksi Database menggunakan MySQLi
 */

function db(): mysqli
{
    static $conn = null;

    if ($conn instanceof mysqli) {
        return $conn;
    }

    mysqli_report(MYSQLI_REPORT_OFF); // tangani error manual

    $conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    if (!$conn) {
        if (APP_DEBUG) {
            die('Koneksi database gagal: ' . mysqli_connect_error());
        }
        die('Maaf, terjadi gangguan pada server. Silakan coba lagi nanti.');
    }

    mysqli_set_charset($conn, 'utf8mb4');
    return $conn;
}
