<?php
$adminPageTitle = 'Dashboard';
require_once __DIR__ . '/includes/layout_head.php';

// Statistik
function scalar_q(string $sql): int {
    $res = mysqli_query(db(), $sql);
    return $res ? (int)(mysqli_fetch_row($res)[0] ?? 0) : 0;
}
function scalar_money(string $sql): float {
    $res = mysqli_query(db(), $sql);
    return $res ? (float)(mysqli_fetch_row($res)[0] ?? 0) : 0;
}

$totalOrders   = scalar_q("SELECT COUNT(*) FROM orders");
$pendingOrders = scalar_q("SELECT COUNT(*) FROM orders WHERE status IN ('pending','paid','processing')");
$successOrders = scalar_q("SELECT COUNT(*) FROM orders WHERE status = 'success'");
$revenue       = scalar_money("SELECT COALESCE(SUM(price),0) FROM orders WHERE status = 'success'");

// Pesanan terbaru
$recent = [];
$res = mysqli_query(db(), "SELECT * FROM orders ORDER BY id DESC LIMIT 8");
while ($res && $row = mysqli_fetch_assoc($res)) { $recent[] = $row; }
?>
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-ic blue">🧾</div>
        <div><div class="stat-val"><?= $totalOrders ?></div><div class="stat-lbl">Total Pesanan</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-ic orange">⏳</div>
        <div><div class="stat-val"><?= $pendingOrders ?></div><div class="stat-lbl">Perlu Diproses</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-ic green">✅</div>
        <div><div class="stat-val"><?= $successOrders ?></div><div class="stat-lbl">Pesanan Berhasil</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-ic purple">💰</div>
        <div><div class="stat-val"><?= rupiah($revenue) ?></div><div class="stat-lbl">Pendapatan</div></div>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2>Pesanan Terbaru</h2>
        <a href="<?= BASE_URL ?>/admin/orders.php" class="btn btn-sm btn-outline">Lihat Semua</a>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Invoice</th><th>Produk</th><th>User ID</th><th>Total</th><th>Status</th><th>Tanggal</th><th></th></tr>
            </thead>
            <tbody>
                <?php if (!$recent): ?>
                    <tr><td colspan="7" class="center muted">Belum ada pesanan.</td></tr>
                <?php else: foreach ($recent as $o): [$st, $sc] = status_label($o['status']); ?>
                    <tr>
                        <td><strong><?= e($o['invoice']) ?></strong></td>
                        <td><?= e($o['product_name']) ?></td>
                        <td><?= e($o['game_user_id']) ?><?= $o['game_server_id'] ? ' (' . e($o['game_server_id']) . ')' : '' ?></td>
                        <td><?= rupiah($o['price']) ?></td>
                        <td><span class="badge badge-<?= $sc ?>"><?= e($st) ?></span></td>
                        <td><?= e(date('d/m/y H:i', strtotime($o['created_at']))) ?></td>
                        <td><a href="<?= BASE_URL ?>/admin/order-detail.php?id=<?= (int)$o['id'] ?>" class="btn btn-xs btn-primary">Detail</a></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/includes/layout_foot.php'; ?>
