<?php
/**
 * Endpoint AJAX: cek nickname dari User ID + Zone/Server ID.
 * Dipanggil dari halaman utama (JS).
 */
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan.']);
    exit;
}

// Validasi CSRF
if (!verify_csrf()) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'Sesi tidak valid, muat ulang halaman.']);
    exit;
}

$userId   = trim($_POST['game_user_id'] ?? '');
$serverId = trim($_POST['game_server_id'] ?? '');

if ($userId === '') {
    echo json_encode(['success' => false, 'message' => 'User ID wajib diisi.']);
    exit;
}

$result = check_game_id($userId, $serverId);
echo json_encode($result);
