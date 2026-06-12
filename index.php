<?php
require_once __DIR__ . '/config/config.php';

$pageTitle = setting('site_name', 'TopUp ML') . ' - ' . setting('site_tagline', '');

// Ambil kategori + produknya
$categories = get_categories();
$productsByCat = [];
$res = mysqli_query(db(), "SELECT * FROM products WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
while ($res && $row = mysqli_fetch_assoc($res)) {
    $productsByCat[$row['category_id']][] = $row;
}

include __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="container">
        <h1 class="hero-title"><?= e(setting('site_tagline', 'Top Up Diamond Mobile Legends')) ?></h1>
        <p class="hero-sub">Proses cepat, harga bersahabat, pembayaran mudah via QRIS. Cukup masukkan ID, pilih produk, dan bayar.</p>
        <div class="hero-badges">
            <span class="badge-pill">⚡ Proses Cepat</span>
            <span class="badge-pill">🔒 Aman & Terpercaya</span>
            <span class="badge-pill">💬 CS Responsif</span>
        </div>
    </div>
</section>

<div class="container">
    <form id="orderForm" action="<?= BASE_URL ?>/proses-order.php" method="POST" class="order-layout">
        <?= csrf_field() ?>

        <!-- LANGKAH 1: Masukkan ID -->
        <section class="card step-card">
            <div class="step-head"><span class="step-num">1</span> Masukkan Data Akun</div>
            <div class="form-row">
                <div class="form-group">
                    <label>User ID</label>
                    <input type="text" name="game_user_id" id="game_user_id" placeholder="Contoh: 123456789" required>
                </div>
                <div class="form-group">
                    <label>Zone / Server ID</label>
                    <input type="text" name="game_server_id" id="game_server_id" placeholder="Contoh: 1234">
                </div>
            </div>
            <button type="button" id="btnCheckId" class="btn btn-outline">🔍 Cek Nickname</button>
            <div id="nicknameResult" class="nickname-result" hidden></div>
            <input type="hidden" name="game_username" id="game_username">
            <p class="hint">Cara melihat ID: buka menu profil di game, ID tertera di bawah nama. Angka dalam kurung adalah Zone/Server.</p>
        </section>

        <!-- LANGKAH 2: Pilih Produk -->
        <section class="card step-card">
            <div class="step-head"><span class="step-num">2</span> Pilih Produk</div>

            <div class="tabs" role="tablist">
                <?php foreach ($categories as $i => $cat): ?>
                    <button type="button" class="tab-btn <?= $i === 0 ? 'active' : '' ?>" data-tab="cat-<?= (int)$cat['id'] ?>">
                        <?= e($cat['name']) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <?php foreach ($categories as $i => $cat): ?>
                <div class="tab-panel <?= $i === 0 ? 'active' : '' ?>" id="cat-<?= (int)$cat['id'] ?>">
                    <?php if (!empty($cat['description'])): ?>
                        <p class="cat-desc"><?= e($cat['description']) ?></p>
                    <?php endif; ?>
                    <div class="product-grid">
                        <?php if (empty($productsByCat[$cat['id']])): ?>
                            <p class="empty-note">Belum ada produk pada kategori ini.</p>
                        <?php else: ?>
                            <?php foreach ($productsByCat[$cat['id']] as $p): ?>
                                <label class="product-item <?= $p['stock_status'] === 'empty' ? 'is-empty' : '' ?>">
                                    <input type="radio" name="product_id" value="<?= (int)$p['id'] ?>"
                                        <?= $p['stock_status'] === 'empty' ? 'disabled' : '' ?>
                                        data-price="<?= (float)$p['price'] ?>"
                                        data-name="<?= e($p['name']) ?>"
                                        data-cat="<?= e($cat['name']) ?>">
                                    <span class="product-name"><?= e($p['name']) ?></span>
                                    <span class="product-price">
                                        <?= (float)$p['price'] > 0 ? rupiah($p['price']) : 'Hubungi Admin' ?>
                                    </span>
                                    <?php if ($p['stock_status'] === 'empty'): ?>
                                        <span class="product-stock">Kosong</span>
                                    <?php endif; ?>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <!-- LANGKAH 3: Kontak & Catatan -->
        <section class="card step-card">
            <div class="step-head"><span class="step-num">3</span> Kontak & Catatan</div>
            <div class="form-group">
                <label>No. WhatsApp / Email (untuk notifikasi)</label>
                <input type="text" name="customer_contact" placeholder="08xxxxxxxxxx" required>
            </div>
            <div class="form-group">
                <label>Catatan (opsional)</label>
                <textarea name="note" rows="2" placeholder="Contoh: untuk gift skin sebutkan nama skin & hero."></textarea>
            </div>
        </section>

        <!-- RINGKASAN STICKY -->
        <aside class="card summary-card">
            <div class="step-head">Ringkasan Pesanan</div>
            <div class="summary-row"><span>Produk</span><strong id="sumProduct">-</strong></div>
            <div class="summary-row"><span>Kategori</span><strong id="sumCat">-</strong></div>
            <div class="summary-row"><span>Nickname</span><strong id="sumNick">-</strong></div>
            <div class="summary-row total"><span>Total</span><strong id="sumPrice">Rp 0</strong></div>
            <button type="submit" class="btn btn-primary btn-block" id="btnSubmit">Lanjut ke Pembayaran</button>
            <p class="hint center">Pembayaran via QRIS. Pesanan diproses manual oleh admin setelah pembayaran diverifikasi.</p>
        </aside>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
