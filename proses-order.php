<?php
require_once __DIR__ . '/config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/');
}

if (!verify_csrf()) {
    set_flash('danger', 'Sesi tidak valid, silakan ulangi.');
    redirect(BASE_URL . '/');
}

$productId = (int)($_POST['product_id'] ?? 0);
$userId    = trim($_POST['game_user_id'] ?? '');
$serverId  = trim($_POST['game_server_id'] ?? '');
$username  = trim($_POST['game_username'] ?? '');
$contact   = trim($_POST['customer_contact'] ?? '');
$note      = trim($_POST['note'] ?? '');

// Validasi
$errors = [];
if ($productId <= 0)       $errors[] = 'Silakan pilih produk.';
if ($userId === '')        $errors[] = 'User ID wajib diisi.';
if ($contact === '')       $errors[] = 'Kontak wajib diisi.';

// Ambil produk + kategori
$product = null;
if ($productId > 0) {
    $stmt = mysqli_prepare(db(), "
        SELECT p.*, c.name AS category_name
        FROM products p
        JOIN categories c ON c.id = p.category_id
        WHERE p.id = ? AND p.is_active = 1
    ");
    mysqli_stmt_bind_param($stmt, 'i', $productId);
    mysqli_stmt_execute($stmt);
    $product = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if (!$product) {
        $errors[] = 'Produk tidak ditemukan.';
    } elseif ($product['stock_status'] === 'empty') {
        $errors[] = 'Produk sedang kosong.';
    }
}

if ($errors) {
    set_flash('danger', implode(' ', $errors));
    redirect(BASE_URL . '/');
}

$invoice = generate_invoice();
$price   = (float)$product['price'];

$stmt = mysqli_prepare(db(), "
    INSERT INTO orders
        (invoice, product_id, product_name, category_name, price,
         game_user_id, game_server_id, game_username, customer_contact, note, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
");
mysqli_stmt_bind_param(
    $stmt,
    'sissdsssss',
    $invoice,
    $productId,
    $product['name'],
    $product['category_name'],
    $price,
    $userId,
    $serverId,
    $username,
    $contact,
    $note
);

if (!mysqli_stmt_execute($stmt)) {
    set_flash('danger', 'Gagal membuat pesanan. Coba lagi.');
    redirect(BASE_URL . '/');
}

redirect(BASE_URL . '/payment.php?inv=' . urlencode($invoice));
