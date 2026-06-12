<?php
require_once __DIR__ . '/../config/config.php';

/** Cek apakah admin sudah login */
function is_admin_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

/** Wajib login, jika belum redirect ke halaman login */
function require_admin(): void
{
    if (!is_admin_logged_in()) {
        redirect(BASE_URL . '/admin/login.php');
    }
}

/** Data admin yang sedang login */
function current_admin(): array
{
    return [
        'id'       => $_SESSION['admin_id'] ?? null,
        'name'     => $_SESSION['admin_name'] ?? '',
        'username' => $_SESSION['admin_username'] ?? '',
    ];
}
