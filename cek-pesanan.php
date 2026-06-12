<?php
require_once __DIR__ . '/config/config.php';

$invoice = trim($_GET['inv'] ?? '');
$order = null;

if ($invoice !== '') {
    $stmt = mysqli_prepare(db(), "SELECT * FROM orders WHERE invoice = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $invoice);
    mysqli_stmt_execute($stmt);
    $order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$pageTitle = 'Cek Pesanan';
include __DIR__ . '/includes/header.php';
?>
<div class="container narrow">
    <div class="page-head">
        <h1>Cek Status Pesanan</h1>
        <p>Masukkan nomor invoice untuk melihat status pesanan Anda.</p>
    </div>

    <div class="card">
        <form action="" method="GET" class="check-form">
            <div class="form-group">
                <label>Nomor Invoice</label>
                <input type="text" name="inv" value="<?= e($invoice) ?>" placeholder="INVxxxxxxxxxx" required>
            </div>
            <button type="submit" class="btn btn-primary">Cek Status</button>
        </form>
    </div>

    <?php if ($invoice !== '' && !$order): ?>
        <div class="alert alert-danger">Pesanan dengan invoice tersebut tidak ditemukan.</div>
    <?php elseif ($order): ?>
        <?php [$statusText, $statusColor] = status_label($order['status']); ?>
        <div class="card">
            <div class="step-head">
                Invoice <?= e($order['invoice']) ?>
                <span class="badge badge-<?= $statusColor ?>"><?= e($statusText) ?></span>
            </div>
            <div class="summary-row"><span>Produk</span><strong><?= e($order['product_name']) ?></strong></div>
            <div class="summary-row"><span>Kategori</span><strong><?= e($order['category_name']) ?></strong></div>
            <div class="summary-row"><span>User ID</span><strong><?= e($order['game_user_id']) ?><?= $order['game_server_id'] ? ' (' . e($order['game_server_id']) . ')' : '' ?></strong></div>
            <?php if ($order['game_username']): ?>
                <div class="summary-row"><span>Nickname</span><strong><?= e($order['game_username']) ?></strong></div>
            <?php endif; ?>
            <div class="summary-row"><span>Total</span><strong><?= (float)$order['price'] > 0 ? rupiah($order['price']) : '-' ?></strong></div>
            <div class="summary-row"><span>Tanggal</span><strong><?= e(date('d M Y H:i', strtotime($order['created_at']))) ?></strong></div>
            <?php if (!empty($order['admin_note'])): ?>
                <div class="alert alert-info mt"><strong>Catatan Admin:</strong><br><?= nl2br(e($order['admin_note'])) ?></div>
            <?php endif; ?>

            <?php if ($order['status'] === 'pending'): ?>
                <a href="<?= BASE_URL ?>/payment.php?inv=<?= urlencode($order['invoice']) ?>" class="btn btn-primary btn-block mt">Lanjutkan Pembayaran</a>
            <?php endif; ?>
        </div>

        <!-- Timeline status -->
        <div class="card">
            <div class="step-head">Lacak Pesanan</div>
            <?php
            $steps = [
                'pending'    => 'Menunggu Pembayaran',
                'paid'       => 'Pembayaran Diterima',
                'processing' => 'Sedang Diproses',
                'success'    => 'Pesanan Berhasil',
            ];
            $order_flow = ['pending','paid','processing','success'];
            $currentIdx = array_search($order['status'], $order_flow);
            if ($order['status'] === 'failed') $currentIdx = -1;
            ?>
            <ol class="timeline">
                <?php $i = 0; foreach ($steps as $key => $label): ?>
                    <li class="<?= ($currentIdx >= $i) ? 'done' : '' ?>">
                        <span class="dot"></span><?= e($label) ?>
                    </li>
                <?php $i++; endforeach; ?>
                <?php if ($order['status'] === 'failed'): ?>
                    <li class="failed"><span class="dot"></span>Pesanan Gagal / Dibatalkan</li>
                <?php endif; ?>
            </ol>
        </div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
