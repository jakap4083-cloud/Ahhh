<?php
$adminPageTitle = 'Pesanan';
require_once __DIR__ . '/includes/layout_head.php';

$filter = $_GET['status'] ?? 'all';
$search = trim($_GET['q'] ?? '');

$where = [];
$params = [];
$types = '';

$validStatus = ['pending','paid','processing','success','failed'];
if (in_array($filter, $validStatus, true)) {
    $where[] = "status = ?";
    $params[] = $filter;
    $types .= 's';
}
if ($search !== '') {
    $where[] = "(invoice LIKE ? OR game_user_id LIKE ? OR product_name LIKE ?)";
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like);
    $types .= 'sss';
}

$sql = "SELECT * FROM orders";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY id DESC LIMIT 200";

$stmt = mysqli_prepare(db(), $sql);
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$orders = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

$tabs = [
    'all'        => 'Semua',
    'pending'    => 'Pending',
    'paid'       => 'Dibayar',
    'processing' => 'Proses',
    'success'    => 'Berhasil',
    'failed'     => 'Gagal',
];
?>
<div class="panel">
    <div class="panel-head">
        <h2>Daftar Pesanan</h2>
        <form method="GET" class="inline-search">
            <input type="hidden" name="status" value="<?= e($filter) ?>">
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari invoice / ID / produk...">
            <button class="btn btn-sm btn-primary">Cari</button>
        </form>
    </div>

    <div class="filter-tabs">
        <?php foreach ($tabs as $key => $label): ?>
            <a href="?status=<?= $key ?><?= $search ? '&q=' . urlencode($search) : '' ?>"
               class="filter-tab <?= $filter === $key ? 'active' : '' ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Invoice</th><th>Produk</th><th>ID Game</th><th>Total</th><th>Bukti</th><th>Status</th><th>Tanggal</th><th></th></tr>
            </thead>
            <tbody>
                <?php if (!$orders): ?>
                    <tr><td colspan="8" class="center muted">Tidak ada pesanan.</td></tr>
                <?php else: foreach ($orders as $o): [$st, $sc] = status_label($o['status']); ?>
                    <tr>
                        <td><strong><?= e($o['invoice']) ?></strong></td>
                        <td><?= e($o['product_name']) ?><br><small class="muted"><?= e($o['category_name']) ?></small></td>
                        <td><?= e($o['game_user_id']) ?><?= $o['game_server_id'] ? ' (' . e($o['game_server_id']) . ')' : '' ?>
                            <?php if ($o['game_username']): ?><br><small class="muted"><?= e($o['game_username']) ?></small><?php endif; ?>
                        </td>
                        <td><?= rupiah($o['price']) ?></td>
                        <td><?= $o['payment_proof'] ? '<span class="badge badge-info">Ada</span>' : '<span class="muted">-</span>' ?></td>
                        <td><span class="badge badge-<?= $sc ?>"><?= e($st) ?></span></td>
                        <td><?= e(date('d/m/y H:i', strtotime($o['created_at']))) ?></td>
                        <td><a href="<?= BASE_URL ?>/admin/order-detail.php?id=<?= (int)$o['id'] ?>" class="btn btn-xs btn-primary">Kelola</a></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/includes/layout_foot.php'; ?>
