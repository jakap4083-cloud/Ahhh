<?php
require_once __DIR__ . '/auth.php';

if (is_admin_logged_in()) {
    redirect(BASE_URL . '/admin/index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Sesi tidak valid, coba lagi.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = mysqli_prepare(db(), "SELECT * FROM admins WHERE username = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']       = $admin['id'];
            $_SESSION['admin_name']     = $admin['name'];
            $_SESSION['admin_username'] = $admin['username'];
            redirect(BASE_URL . '/admin/index.php');
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - <?= e(setting('site_name', 'TopUp ML')) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body class="login-body">
    <div class="login-card">
        <div class="login-logo">💎</div>
        <h1>Panel Admin</h1>
        <p class="login-sub"><?= e(setting('site_name', 'TopUp ML')) ?></p>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
        </form>
    </div>
</body>
</html>
