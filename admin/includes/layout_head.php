<?php
require_once __DIR__ . '/../auth.php';
require_admin();
$admin = current_admin();
$currentPage = basename($_SERVER['PHP_SELF']);

// Hitung notifikasi pesanan yang butuh perhatian
$pendingCount = 0;
$res = mysqli_query(db(), "SELECT COUNT(*) AS c FROM orders WHERE status IN ('paid','pending')");
if ($res) { $pendingCount = (int)(mysqli_fetch_assoc($res)['c'] ?? 0); }

$nav = [
    'index.php'    => ['📊', 'Dashboard'],
    'orders.php'   => ['🧾', 'Pesanan'],
    'products.php' => ['📦', 'Produk'],
    'categories.php' => ['🗂️', 'Kategori'],
    'settings.php' => ['⚙️', 'Pengaturan'],
    'account.php'  => ['👤', 'Akun'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($adminPageTitle ?? 'Admin') ?> - <?= e(setting('site_name', 'TopUp ML')) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body>
<div class="admin-wrap">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">💎 <span><?= e(setting('site_name', 'TopUp ML')) ?></span></div>
        <nav class="sidebar-nav">
            <?php foreach ($nav as $file => $info): ?>
                <a href="<?= BASE_URL ?>/admin/<?= $file ?>" class="<?= $currentPage === $file ? 'active' : '' ?>">
                    <span class="nav-ic"><?= $info[0] ?></span> <?= $info[1] ?>
                    <?php if ($file === 'orders.php' && $pendingCount > 0): ?>
                        <span class="nav-badge"><?= $pendingCount ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
            <a href="<?= BASE_URL ?>/" target="_blank" class="nav-ext">🌐 Lihat Website</a>
            <a href="<?= BASE_URL ?>/admin/logout.php" class="nav-logout">🚪 Keluar</a>
        </nav>
    </aside>
    <div class="admin-main">
        <header class="admin-topbar">
            <button class="menu-toggle" id="menuToggle">☰</button>
            <h1 class="topbar-title"><?= e($adminPageTitle ?? 'Admin') ?></h1>
            <div class="topbar-user">👤 <?= e($admin['name']) ?></div>
        </header>
        <div class="admin-content">
        <?php if ($flash = get_flash()): ?>
            <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>
