<?php
$adminPageTitle = 'Produk';
require_once __DIR__ . '/includes/layout_head.php';

// Aksi: tambah / edit / hapus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $pid        = (int)($_POST['id'] ?? 0);
        $catId      = (int)($_POST['category_id'] ?? 0);
        $name       = trim($_POST['name'] ?? '');
        $price      = (float)($_POST['price'] ?? 0);
        $stock      = ($_POST['stock_status'] ?? 'available') === 'empty' ? 'empty' : 'available';
        $isActive   = isset($_POST['is_active']) ? 1 : 0;
        $sortOrder  = (int)($_POST['sort_order'] ?? 0);

        if ($name === '' || $catId <= 0) {
            set_flash('danger', 'Nama dan kategori wajib diisi.');
        } elseif ($pid > 0) {
            $stmt = mysqli_prepare(db(), "UPDATE products SET category_id=?, name=?, price=?, stock_status=?, is_active=?, sort_order=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, 'isdsiii', $catId, $name, $price, $stock, $isActive, $sortOrder, $pid);
            mysqli_stmt_execute($stmt);
            set_flash('success', 'Produk diperbarui.');
        } else {
            $stmt = mysqli_prepare(db(), "INSERT INTO products (category_id, name, price, stock_status, is_active, sort_order) VALUES (?,?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, 'isdsii', $catId, $name, $price, $stock, $isActive, $sortOrder);
            mysqli_stmt_execute($stmt);
            set_flash('success', 'Produk ditambahkan.');
        }
    } elseif ($action === 'delete') {
        $pid = (int)($_POST['id'] ?? 0);
        $stmt = mysqli_prepare(db(), "DELETE FROM products WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $pid);
        mysqli_stmt_execute($stmt);
        set_flash('success', 'Produk dihapus.');
    }
    redirect(BASE_URL . '/admin/products.php');
}

$categories = [];
$res = mysqli_query(db(), "SELECT * FROM categories ORDER BY sort_order ASC, id ASC");
while ($res && $row = mysqli_fetch_assoc($res)) { $categories[$row['id']] = $row; }

$products = [];
$res = mysqli_query(db(), "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.category_id ASC, p.sort_order ASC, p.id ASC");
while ($res && $row = mysqli_fetch_assoc($res)) { $products[] = $row; }
?>
<div class="panel">
    <div class="panel-head">
        <h2>Daftar Produk</h2>
        <button class="btn btn-sm btn-primary" onclick="openProductModal()">+ Tambah Produk</button>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Nama</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Aktif</th><th>Urutan</th><th></th></tr></thead>
            <tbody>
                <?php if (!$products): ?>
                    <tr><td colspan="7" class="center muted">Belum ada produk.</td></tr>
                <?php else: foreach ($products as $p): ?>
                    <tr>
                        <td><strong><?= e($p['name']) ?></strong></td>
                        <td><?= e($p['category_name'] ?? '-') ?></td>
                        <td><?= (float)$p['price'] > 0 ? rupiah($p['price']) : '<span class="muted">Hubungi Admin</span>' ?></td>
                        <td><?= $p['stock_status'] === 'empty' ? '<span class="badge badge-danger">Kosong</span>' : '<span class="badge badge-success">Tersedia</span>' ?></td>
                        <td><?= $p['is_active'] ? '✅' : '❌' ?></td>
                        <td><?= (int)$p['sort_order'] ?></td>
                        <td class="actions">
                            <button class="btn btn-xs btn-outline" onclick='editProduct(<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
                            <form method="POST" onsubmit="return confirm('Hapus produk ini?')" style="display:inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                <button class="btn btn-xs btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Produk -->
<div class="modal" id="productModal">
    <div class="modal-card">
        <div class="modal-head"><h3 id="modalTitle">Tambah Produk</h3><button class="modal-close" onclick="closeProductModal()">&times;</button></div>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" id="p_id" value="0">
            <div class="form-group">
                <label>Nama Produk</label>
                <input type="text" name="name" id="p_name" required>
            </div>
            <div class="form-group">
                <label>Kategori</label>
                <select name="category_id" id="p_category" class="select" required>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Harga (Rp) — isi 0 untuk "Hubungi Admin"</label>
                    <input type="number" name="price" id="p_price" min="0" step="100" value="0">
                </div>
                <div class="form-group">
                    <label>Urutan</label>
                    <input type="number" name="sort_order" id="p_sort" value="0">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Status Stok</label>
                    <select name="stock_status" id="p_stock" class="select">
                        <option value="available">Tersedia</option>
                        <option value="empty">Kosong</option>
                    </select>
                </div>
                <div class="form-group checkbox-group">
                    <label><input type="checkbox" name="is_active" id="p_active" checked> Aktif (tampil di website)</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Simpan Produk</button>
        </form>
    </div>
</div>

<script>
function openProductModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Produk';
    document.getElementById('p_id').value = 0;
    document.getElementById('p_name').value = '';
    document.getElementById('p_price').value = 0;
    document.getElementById('p_sort').value = 0;
    document.getElementById('p_stock').value = 'available';
    document.getElementById('p_active').checked = true;
    document.getElementById('productModal').classList.add('open');
}
function editProduct(p) {
    document.getElementById('modalTitle').textContent = 'Edit Produk';
    document.getElementById('p_id').value = p.id;
    document.getElementById('p_name').value = p.name;
    document.getElementById('p_category').value = p.category_id;
    document.getElementById('p_price').value = parseFloat(p.price);
    document.getElementById('p_sort').value = p.sort_order;
    document.getElementById('p_stock').value = p.stock_status;
    document.getElementById('p_active').checked = p.is_active == 1;
    document.getElementById('productModal').classList.add('open');
}
function closeProductModal() {
    document.getElementById('productModal').classList.remove('open');
}
</script>
<?php require_once __DIR__ . '/includes/layout_foot.php'; ?>
