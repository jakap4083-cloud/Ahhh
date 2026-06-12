<?php
require_once __DIR__ . '/config/config.php';

$invoice = trim($_GET['inv'] ?? '');
if ($invoice === '') {
    redirect(BASE_URL . '/');
}

// Ambil order
$stmt = mysqli_prepare(db(), "SELECT * FROM orders WHERE invoice = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $invoice);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$order) {
    set_flash('danger', 'Pesanan tidak ditemukan.');
    redirect(BASE_URL . '/');
}

// Proses upload bukti pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    if (!empty($_FILES['proof']['name']) && $_FILES['proof']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $_FILES['proof']['tmp_name']);
        finfo_close($finfo);

        if (!isset($allowed[$mime])) {
            set_flash('danger', 'Format file harus JPG, PNG, atau WEBP.');
        } elseif ($_FILES['proof']['size'] > 5 * 1024 * 1024) {
            set_flash('danger', 'Ukuran file maksimal 5 MB.');
        } else {
            if (!is_dir(UPLOAD_PATH . '/proofs')) {
                @mkdir(UPLOAD_PATH . '/proofs', 0755, true);
            }
            $filename = 'proof_' . $order['invoice'] . '_' . time() . '.' . $allowed[$mime];
            $dest = UPLOAD_PATH . '/proofs/' . $filename;
            if (move_uploaded_file($_FILES['proof']['tmp_name'], $dest)) {
                $relPath = 'proofs/' . $filename;
                $newStatus = 'paid';
                $stmt = mysqli_prepare(db(), "UPDATE orders SET payment_proof = ?, status = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'ssi', $relPath, $newStatus, $order['id']);
                mysqli_stmt_execute($stmt);
                set_flash('success', 'Bukti pembayaran berhasil dikirim! Admin akan memproses pesanan Anda.');
                redirect(BASE_URL . '/cek-pesanan.php?inv=' . urlencode($order['invoice']));
            } else {
                set_flash('danger', 'Gagal mengunggah file.');
            }
        }
    } else {
        set_flash('danger', 'Silakan pilih file bukti pembayaran.');
    }
    redirect(BASE_URL . '/payment.php?inv=' . urlencode($order['invoice']));
}

$pageTitle = 'Pembayaran - ' . $order['invoice'];
$qris = setting('qris_image', '');
[$statusText, $statusColor] = status_label($order['status']);
include __DIR__ . '/includes/header.php';
?>
<div class="container narrow">
    <div class="page-head">
        <h1>Pembayaran</h1>
        <p>Invoice: <strong><?= e($order['invoice']) ?></strong>
           <span class="badge badge-<?= $statusColor ?>"><?= e($statusText) ?></span></p>
    </div>

    <div class="card">
        <div class="step-head">Detail Pesanan</div>
        <div class="summary-row"><span>Produk</span><strong><?= e($order['product_name']) ?></strong></div>
        <div class="summary-row"><span>Kategori</span><strong><?= e($order['category_name']) ?></strong></div>
        <div class="summary-row"><span>User ID</span><strong><?= e($order['game_user_id']) ?><?= $order['game_server_id'] ? ' (' . e($order['game_server_id']) . ')' : '' ?></strong></div>
        <?php if ($order['game_username']): ?>
            <div class="summary-row"><span>Nickname</span><strong><?= e($order['game_username']) ?></strong></div>
        <?php endif; ?>
        <div class="summary-row total"><span>Total Bayar</span>
            <strong><?= (float)$order['price'] > 0 ? rupiah($order['price']) : 'Konfirmasi Admin' ?></strong>
        </div>
    </div>

    <?php if ($order['status'] === 'pending'): ?>
    <div class="card">
        <div class="step-head">Scan QRIS untuk Membayar</div>
        <div class="qris-box">
            <?php if ($qris): ?>
                <img src="<?= UPLOAD_URL . '/' . e($qris) ?>" alt="QRIS Pembayaran" class="qris-img">
            <?php else: ?>
                <div class="qris-placeholder">QRIS belum diunggah admin.<br>Hubungi admin via WhatsApp untuk pembayaran.</div>
            <?php endif; ?>
            <p class="qris-owner">a.n. <strong><?= e(setting('qris_owner_name', '-')) ?></strong></p>
        </div>
        <div class="alert alert-info"><?= nl2br(e(setting('payment_instruction', ''))) ?></div>

        <form action="" method="POST" enctype="multipart/form-data" class="upload-form">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Upload Bukti Pembayaran (JPG/PNG/WEBP, maks 5MB)</label>
                <input type="file" name="proof" accept="image/jpeg,image/png,image/webp" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Kirim Bukti Pembayaran</button>
        </form>
    </div>
    <?php else: ?>
        <div class="alert alert-success">
            Bukti pembayaran sudah diterima. Silakan pantau status di
            <a href="<?= BASE_URL ?>/cek-pesanan.php?inv=<?= urlencode($order['invoice']) ?>">Cek Pesanan</a>.
        </div>
    <?php endif; ?>

    <div class="center mt">
        <a href="https://wa.me/<?= e(setting('whatsapp_admin', '')) ?>?text=<?= rawurlencode('Halo admin, saya mau konfirmasi pesanan ' . $order['invoice']) ?>"
           target="_blank" rel="noopener" class="btn btn-outline">💬 Konfirmasi via WhatsApp</a>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
