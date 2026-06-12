<?php
/**
 * Kumpulan fungsi bantu (helper) untuk seluruh aplikasi.
 */

/** Escape output HTML untuk mencegah XSS */
function e(?string $str): string
{
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

/** Escape input untuk query (selain prepared statement) */
function esc(string $str): string
{
    return mysqli_real_escape_string(db(), $str);
}

/** Redirect ke URL lalu hentikan eksekusi */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/** Format angka ke Rupiah */
function rupiah($angka): string
{
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

/** Ambil nilai dari tabel settings */
function setting(string $key, $default = null)
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        $res = mysqli_query(db(), "SELECT `key`, `value` FROM settings");
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $cache[$row['key']] = $row['value'];
            }
        }
    }
    return $cache[$key] ?? $default;
}

/** Simpan / update setting */
function set_setting(string $key, string $value): bool
{
    $conn = db();
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO settings (`key`, `value`) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)"
    );
    mysqli_stmt_bind_param($stmt, 'ss', $key, $value);
    return mysqli_stmt_execute($stmt);
}

/** Buat invoice/kode order unik */
function generate_invoice(): string
{
    return 'INV' . date('ymd') . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
}

/** Token CSRF */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): bool
{
    return isset($_POST['csrf_token'])
        && hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token']);
}

/** Flash message sederhana */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/** Label status order -> teks & warna */
function status_label(string $status): array
{
    $map = [
        'pending'    => ['Menunggu Pembayaran', 'warning'],
        'paid'       => ['Sudah Dibayar / Diverifikasi', 'info'],
        'processing' => ['Sedang Diproses', 'primary'],
        'success'    => ['Berhasil', 'success'],
        'failed'     => ['Gagal / Dibatalkan', 'danger'],
    ];
    return $map[$status] ?? [ucfirst($status), 'secondary'];
}

/** Ambil kategori aktif */
function get_categories(): array
{
    $rows = [];
    $res = mysqli_query(db(), "SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
    while ($res && $row = mysqli_fetch_assoc($res)) {
        $rows[] = $row;
    }
    return $rows;
}

/**
 * Cek ID game (User ID + Zone/Server) ke API eksternal yang dikonfigurasi
 * di pengaturan admin. Mengembalikan ['success'=>bool, 'username'=>string, 'message'=>string].
 *
 * Placeholder yang didukung pada URL endpoint:
 *   {id}     -> User ID
 *   {server} -> Zone/Server ID
 *   {apikey} -> API key
 */
function check_game_id(string $userId, string $serverId): array
{
    $endpoint = trim((string)setting('idcheck_endpoint', ''));
    $apiKey   = trim((string)setting('idcheck_apikey', ''));
    $method   = strtoupper((string)setting('idcheck_method', 'GET'));
    $userPath = trim((string)setting('idcheck_username_path', 'name')); // path JSON ke nickname

    if ($endpoint === '') {
        return ['success' => false, 'username' => '', 'message' => 'API cek ID belum dikonfigurasi admin.'];
    }

    $url = str_replace(
        ['{id}', '{server}', '{zone}', '{apikey}'],
        [rawurlencode($userId), rawurlencode($serverId), rawurlencode($serverId), rawurlencode($apiKey)],
        $endpoint
    );

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => array_filter([
            'Accept: application/json',
            $apiKey !== '' ? 'Authorization: Bearer ' . $apiKey : null,
        ]),
    ]);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'id' => $userId, 'server' => $serverId, 'zone' => $serverId,
        ]));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return ['success' => false, 'username' => '', 'message' => 'Gagal menghubungi API: ' . $curlErr];
    }

    $data = json_decode($response, true);
    if (!is_array($data)) {
        return ['success' => false, 'username' => '', 'message' => 'Respons API tidak valid.'];
    }

    // Ambil nickname dari path JSON yang dikonfigurasi (mis. "data.username")
    $username = $data;
    foreach (explode('.', $userPath) as $segment) {
        if (is_array($username) && array_key_exists($segment, $username)) {
            $username = $username[$segment];
        } else {
            $username = null;
            break;
        }
    }

    if (is_string($username) && $username !== '') {
        return ['success' => true, 'username' => $username, 'message' => 'ID ditemukan.'];
    }

    return [
        'success'  => false,
        'username' => '',
        'message'  => $data['message'] ?? 'ID tidak ditemukan / tidak valid.',
    ];
}
