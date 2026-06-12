<?php require_once __DIR__ . '/../config/config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? setting('site_name', 'TopUp ML')) ?></title>
    <meta name="description" content="<?= e(setting('site_tagline', '')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <script>window.BASE_URL = '<?= BASE_URL ?>/';</script>
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a href="<?= BASE_URL ?>/" class="brand">
            <span class="brand-logo">💎</span>
            <span class="brand-name"><?= e(setting('site_name', 'TopUp ML')) ?></span>
        </a>
        <nav class="main-nav">
            <a href="<?= BASE_URL ?>/">Beranda</a>
            <a href="<?= BASE_URL ?>/cek-pesanan.php">Cek Pesanan</a>
            <a href="https://wa.me/<?= e(setting('whatsapp_admin', '')) ?>" target="_blank" rel="noopener">Bantuan</a>
        </nav>
    </div>
</header>
<main class="site-main">
<?php if ($flash = get_flash()): ?>
    <div class="container">
        <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    </div>
<?php endif; ?>
