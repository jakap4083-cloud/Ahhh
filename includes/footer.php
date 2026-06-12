</main>
<footer class="site-footer">
    <div class="container footer-inner">
        <div>
            <div class="footer-brand">💎 <?= e(setting('site_name', 'TopUp ML')) ?></div>
            <p><?= e(setting('site_tagline', '')) ?></p>
        </div>
        <div class="footer-links">
            <a href="<?= BASE_URL ?>/">Beranda</a>
            <a href="<?= BASE_URL ?>/cek-pesanan.php">Cek Pesanan</a>
            <a href="https://wa.me/<?= e(setting('whatsapp_admin', '')) ?>" target="_blank" rel="noopener">WhatsApp Admin</a>
        </div>
    </div>
    <div class="footer-copy">
        &copy; <?= date('Y') ?> <?= e(setting('site_name', 'TopUp ML')) ?>. Bukan afiliasi resmi Moonton/Mobile Legends.
    </div>
</footer>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<?php if (!empty($pageScript)): ?><script><?= $pageScript ?></script><?php endif; ?>
</body>
</html>
