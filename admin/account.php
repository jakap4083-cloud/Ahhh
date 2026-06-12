<?php
$adminPageTitle = 'Akun';
require_once __DIR__ . '/includes/layout_head.php';

$admin = current_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $name        = trim($_POST['name'] ?? '');
    $username    = trim($_POST['username'] ?? '');
    $curPass     = $_POST['current_password'] ?? '';
    $newPass     = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    // Ambil data admin saat ini
    $stmt = mysqli_prepare(db(), "SELECT * FROM admins WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $admin['id']);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    $errors = [];
    if ($name === '' || $username === '') $errors[] = 'Nama dan username wajib diisi.';

    // Jika ingin ganti password
    $updatePassword = false;
    if ($newPass !== '') {
        if (!password_verify($curPass, $row['password'])) {
            $errors[] = 'Password lama salah.';
        } elseif (strlen($newPass) < 6) {
            $errors[] = 'Password baru minimal 6 karakter.';
        } elseif ($newPass !== $confirmPass) {
            $errors[] = 'Konfirmasi password tidak cocok.';
        } else {
            $updatePassword = true;
        }
    }

    // Cek username unik
    if (!$errors) {
        $stmt = mysqli_prepare(db(), "SELECT id FROM admins WHERE username = ? AND id != ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'si', $username, $admin['id']);
        mysqli_stmt_execute($stmt);
        if (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))) {
            $errors[] = 'Username sudah dipakai.';
        }
    }

    if ($errors) {
        set_flash('danger', implode(' ', $errors));
    } else {
        if ($updatePassword) {
            $hash = password_hash($newPass, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare(db(), "UPDATE admins SET name=?, username=?, password=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, 'sssi', $name, $username, $hash, $admin['id']);
        } else {
            $stmt = mysqli_prepare(db(), "UPDATE admins SET name=?, username=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, 'ssi', $name, $username, $admin['id']);
        }
        mysqli_stmt_execute($stmt);
        $_SESSION['admin_name'] = $name;
        $_SESSION['admin_username'] = $username;
        set_flash('success', 'Akun berhasil diperbarui.');
    }
    redirect(BASE_URL . '/admin/account.php');
}

// Ambil data terbaru
$stmt = mysqli_prepare(db(), "SELECT * FROM admins WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $admin['id']);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
?>
<div class="panel" style="max-width:560px">
    <div class="panel-head"><h2>Pengaturan Akun</h2></div>
    <form method="POST">
        <?= csrf_field() ?>
        <div class="form-group"><label>Nama</label><input type="text" name="name" value="<?= e($data['name']) ?>" required></div>
        <div class="form-group"><label>Username</label><input type="text" name="username" value="<?= e($data['username']) ?>" required></div>
        <hr class="divider">
        <p class="help-text">Isi bagian di bawah hanya jika ingin mengganti password.</p>
        <div class="form-group"><label>Password Lama</label><input type="password" name="current_password"></div>
        <div class="form-group"><label>Password Baru</label><input type="password" name="new_password"></div>
        <div class="form-group"><label>Konfirmasi Password Baru</label><input type="password" name="confirm_password"></div>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </form>
</div>
<?php require_once __DIR__ . '/includes/layout_foot.php'; ?>
