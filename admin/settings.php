<?php
$adminPageTitle = 'Pengaturan';
require_once __DIR__ . '/includes/layout_head.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $fields = [
        'site_name', 'site_tagline', 'whatsapp_admin', 'qris_owner_name',
        'payment_instruction', 'idcheck_endpoint', 'idcheck_apikey',
        'idcheck_method', 'idcheck_username_path',
    ];
    foreach ($fields as $f) {
        if (isset($_POST[$f])) {
            set_setting($f, trim($_POST[$f]));
        }
    }

    // Upload QRIS
    if (!empty($_FILES['qris_image']['name']) && $_FILES['qris_image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $_FILES['qris_image']['tmp_name']);
        finfo_close($finfo);
        if (isset($allowed[$mime]) && $_FILES['qris_image']['size'] <= 5 * 1024 * 1024) {
            if (!is_dir(UPLOAD_PATH)) @mkdir(UPLOAD_PATH, 0755, true);
            $filename = 'qris.' . $allowed[$mime];
            if (move_uploaded_file($_FILES['qris_image']['tmp_name'], UPLOAD_PATH . '/' . $filename)) {
                set_setting('qris_image', $filename);
            }
        } else {
            set_flash('danger', 'QRIS harus JPG/PNG/WEBP maks 5MB.');
            redirect(BASE_URL . '/admin/settings.php');
        }
    }

    set_flash('success', 'Pengaturan disimpan.');
    redirect(BASE_URL . '/admin/settings.php');
}

$qris = setting('qris_image', '');
?>
<form method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="panel">
        <div class="panel-head"><h2>Informasi Website</h2></div>
        <div class="form-group"><label>Nama Website</label><input type="text" name="site_name" value="<?= e(setting('site_name')) ?>"></div>
        <div class="form-group"><label>Tagline</label><input type="text" name="site_tagline" value="<?= e(setting('site_tagline')) ?>"></div>
        <div class="form-group"><label>Nomor WhatsApp Admin (format: 62xxx)</label><input type="text" name="whatsapp_admin" value="<?= e(setting('whatsapp_admin')) ?>"></div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>Pembayaran QRIS</h2></div>
        <div class="settings-2col">
            <div>
                <div class="form-group"><label>Nama Pemilik QRIS</label><input type="text" name="qris_owner_name" value="<?= e(setting('qris_owner_name')) ?>"></div>
                <div class="form-group"><label>Upload Gambar QRIS Statis (JPG/PNG/WEBP)</label><input type="file" name="qris_image" accept="image/*"></div>
                <div class="form-group"><label>Instruksi Pembayaran</label><textarea name="payment_instruction" rows="4"><?= e(setting('payment_instruction')) ?></textarea></div>
            </div>
            <div class="qris-preview">
                <label>Preview QRIS</label>
                <?php if ($qris): ?>
                    <img src="<?= UPLOAD_URL . '/' . e($qris) ?>?v=<?= time() ?>" alt="QRIS">
                <?php else: ?>
                    <div class="qris-placeholder">Belum ada QRIS</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h2>API Cek ID Otomatis</h2></div>
        <p class="help-text">
            Masukkan endpoint API penyedia cek ID Anda. Gunakan placeholder:
            <code>{id}</code> (User ID), <code>{server}</code> atau <code>{zone}</code> (Zone/Server ID),
            <code>{apikey}</code> (API key). Kosongkan jika tidak ingin pakai cek otomatis.
        </p>
        <div class="form-group">
            <label>Endpoint URL</label>
            <input type="text" name="idcheck_endpoint" value="<?= e(setting('idcheck_endpoint')) ?>"
                   placeholder="https://api.contoh.com/ml?id={id}&zone={server}&key={apikey}">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Metode</label>
                <select name="idcheck_method" class="select">
                    <option value="GET" <?= setting('idcheck_method') === 'GET' ? 'selected' : '' ?>>GET</option>
                    <option value="POST" <?= setting('idcheck_method') === 'POST' ? 'selected' : '' ?>>POST</option>
                </select>
            </div>
            <div class="form-group">
                <label>Path JSON ke Nickname</label>
                <input type="text" name="idcheck_username_path" value="<?= e(setting('idcheck_username_path')) ?>" placeholder="contoh: data.username">
            </div>
        </div>
        <div class="form-group">
            <label>API Key (opsional)</label>
            <input type="text" name="idcheck_apikey" value="<?= e(setting('idcheck_apikey')) ?>" placeholder="API key dari penyedia">
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-lg">💾 Simpan Semua Pengaturan</button>
</form>
<?php require_once __DIR__ . '/includes/layout_foot.php'; ?>
