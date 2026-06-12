<?php
$adminPageTitle = 'Detail Pesanan';
require_once __DIR__ . '/includes/layout_head.php';

$id = (int)($_GET['id'] ?? 0);

// Update status / catatan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $newStatus = $_POST['status'] ?? '';
    $adminNote = trim($_POST['admin_note'] ?? '');
    $validStatus = ['pending','paid','processing','success','failed'];
    if (in_array($newStatus, $validStatus, true)) {
        $stmt = mysqli_prepare(db(), "UPDATE orders SET status = ?, admin_note = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'ssi', $newStatus, $adminNote, $id);
        mysqli_stmt_execute($stmt);
        set_flash('success', 'Pesanan berhasil diperbarui.');
    } else {
        set_flash('danger', 'Status tidak valid.');
    }
    redirect(BASE_URL . '/admin/order-detail.php?id=' . $id);
}

$stmt = mysqli_prepare(db(), "SELECT * FROM orders WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$order) {
    echo '<div class="alert alert-danger">Pesanan tidak ditemukan.</div>';
    require_once __DIR__ . '/includes/layout_foot.php';
    exit;
}
[$st, $sc] = status_label($order['status']);
?>
<a href="<?= BASE_URL ?>/admin/orders.php" class="back-link">← Kembali ke daftar</a>

<div class="detail-grid">
    <div class="panel">
        <div class="panel-head"><h2>Pesanan <?= e($order['invoice']) ?></h2>
            <span class="badge badge-<?= $sc ?>"><?= e($st) ?></span></div>
        <table class="detail-table">
            <tr><th>Produk</th><td><?= e($order['product_name']) ?></td></tr>
            <tr><th>Kategori</th><td><?= e($order['category_name']) ?></td></tr>
            <tr><th>Harga</th><td><?= rupiah($order['price']) ?></td></tr>
            <tr><th>User ID</th><td><strong><?= e($order['game_user_id']) ?></strong></td></tr>
            <tr><th>Zone / Server</th><td><?= e($order['game_server_id'] ?: '-') ?></td></tr>
            <tr><th>Nickname</th><td><?= e($order['game_username'] ?: '-') ?></td></tr>
            <tr><th>Kontak</th><td><?= e($order['customer_contact'] ?: '-') ?></td></tr>
            <tr><th>Catatan User</th><td><?= nl2br(e($order['note'] ?: '-')) ?></td></tr>
            <tr><th>Tanggal</th><td><?= e(date('d M Y H:i', strtotime($order['created_at']))) ?></td></tr>
        </table>
        <?php if ($order['customer_contact']): ?>
            <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $order['customer_contact'])) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline">💬 Hubungi via WhatsApp</a>
        <?php endif; ?>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>Bukti Pembayaran</h2></div>
        <?php if ($order['payment_proof']): ?>
            <a href="<?= UPLOAD_URL . '/' . e($order['payment_proof']) ?>" target="_blank">
                <img src="<?= UPLOAD_URL . '/' . e($order['payment_proof']) ?>" alt="Bukti Pembayaran" class="proof-img">
            </a>
            <p class="muted center">Klik gambar untuk memperbesar.</p>
        <?php else: ?>
            <p class="muted center">Belum ada bukti pembayaran.</p>
        <?php endif; ?>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h2>Proses Pesanan</h2></div>
    <form method="POST" class="process-form">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Ubah Status</label>
            <select name="status" class="select">
                <?php
                $opts = [
                    'pending' => 'Menunggu Pembayaran',
                    'paid' => 'Sudah Dibayar / Diverifikasi',
                    'processing' => 'Sedang Diproses',
                    'success' => 'Berhasil',
                    'failed' => 'Gagal / Dibatalkan',
                ];
                foreach ($opts as $val => $label):
                ?>
                    <option value="<?= $val ?>" <?= $order['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Catatan Admin (tampil ke user di halaman cek pesanan)</label>
            <textarea name="admin_note" rows="3" placeholder="Contoh: Diamond sudah masuk ke akun. Terima kasih!"><?= e($order['admin_note'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </form>
</div>
<?php require_once __DIR__ . '/includes/layout_foot.php'; ?>
