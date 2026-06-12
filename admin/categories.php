<?php
$adminPageTitle = 'Kategori';
require_once __DIR__ . '/includes/layout_head.php';

function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-') ?: 'kategori';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $cid       = (int)($_POST['id'] ?? 0);
        $name      = trim($_POST['name'] ?? '');
        $desc      = trim($_POST['description'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isActive  = isset($_POST['is_active']) ? 1 : 0;
        $slug      = slugify($name) . ($cid > 0 ? '' : '-' . substr(uniqid(), -4));

        if ($name === '') {
            set_flash('danger', 'Nama kategori wajib diisi.');
        } elseif ($cid > 0) {
            $stmt = mysqli_prepare(db(), "UPDATE categories SET name=?, description=?, sort_order=?, is_active=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, 'ssiii', $name, $desc, $sortOrder, $isActive, $cid);
            mysqli_stmt_execute($stmt);
            set_flash('success', 'Kategori diperbarui.');
        } else {
            $stmt = mysqli_prepare(db(), "INSERT INTO categories (name, slug, description, sort_order, is_active) VALUES (?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, 'sssii', $name, $slug, $desc, $sortOrder, $isActive);
            mysqli_stmt_execute($stmt);
            set_flash('success', 'Kategori ditambahkan.');
        }
    } elseif ($action === 'delete') {
        $cid = (int)($_POST['id'] ?? 0);
        $stmt = mysqli_prepare(db(), "DELETE FROM categories WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $cid);
        mysqli_stmt_execute($stmt);
        set_flash('success', 'Kategori dihapus (beserta produknya).');
    }
    redirect(BASE_URL . '/admin/categories.php');
}

$categories = [];
$res = mysqli_query(db(), "SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count FROM categories c ORDER BY sort_order ASC, id ASC");
while ($res && $row = mysqli_fetch_assoc($res)) { $categories[] = $row; }
?>
<div class="panel">
    <div class="panel-head">
        <h2>Daftar Kategori</h2>
        <button class="btn btn-sm btn-primary" onclick="openCatModal()">+ Tambah Kategori</button>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Nama</th><th>Slug</th><th>Deskripsi</th><th>Produk</th><th>Aktif</th><th>Urutan</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($categories as $c): ?>
                    <tr>
                        <td><strong><?= e($c['name']) ?></strong></td>
                        <td><code><?= e($c['slug']) ?></code></td>
                        <td><?= e(mb_strimwidth($c['description'] ?? '', 0, 50, '...')) ?></td>
                        <td><?= (int)$c['product_count'] ?></td>
                        <td><?= $c['is_active'] ? '✅' : '❌' ?></td>
                        <td><?= (int)$c['sort_order'] ?></td>
                        <td class="actions">
                            <button class="btn btn-xs btn-outline" onclick='editCat(<?= json_encode($c, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
                            <form method="POST" onsubmit="return confirm('Hapus kategori ini beserta semua produknya?')" style="display:inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                <button class="btn btn-xs btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal" id="catModal">
    <div class="modal-card">
        <div class="modal-head"><h3 id="catModalTitle">Tambah Kategori</h3><button class="modal-close" onclick="closeCatModal()">&times;</button></div>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" id="c_id" value="0">
            <div class="form-group"><label>Nama Kategori</label><input type="text" name="name" id="c_name" required></div>
            <div class="form-group"><label>Deskripsi</label><textarea name="description" id="c_desc" rows="2"></textarea></div>
            <div class="form-row">
                <div class="form-group"><label>Urutan</label><input type="number" name="sort_order" id="c_sort" value="0"></div>
                <div class="form-group checkbox-group"><label><input type="checkbox" name="is_active" id="c_active" checked> Aktif</label></div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Simpan Kategori</button>
        </form>
    </div>
</div>

<script>
function openCatModal() {
    document.getElementById('catModalTitle').textContent = 'Tambah Kategori';
    document.getElementById('c_id').value = 0;
    document.getElementById('c_name').value = '';
    document.getElementById('c_desc').value = '';
    document.getElementById('c_sort').value = 0;
    document.getElementById('c_active').checked = true;
    document.getElementById('catModal').classList.add('open');
}
function editCat(c) {
    document.getElementById('catModalTitle').textContent = 'Edit Kategori';
    document.getElementById('c_id').value = c.id;
    document.getElementById('c_name').value = c.name;
    document.getElementById('c_desc').value = c.description || '';
    document.getElementById('c_sort').value = c.sort_order;
    document.getElementById('c_active').checked = c.is_active == 1;
    document.getElementById('catModal').classList.add('open');
}
function closeCatModal() { document.getElementById('catModal').classList.remove('open'); }
</script>
<?php require_once __DIR__ . '/includes/layout_foot.php'; ?>
