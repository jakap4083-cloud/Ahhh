# 💎 TopUp ML — Website Top Up Diamond Mobile Legends

Website top up premium (PHP 8.2 + MySQLi) lengkap dengan **panel admin**, **pembayaran QRIS statis**,
**cek ID otomatis (konfigurable)**, dan **proses manual oleh admin**. Siap deploy di **VPS aaPanel + Nginx**.

---

## ✨ Fitur

**Sisi User**
- Pilih produk berdasarkan kategori: **Diamond, Starlight, Weekly Diamond Pass (WDP), Gift/Skin**, dll.
- Input **User ID + Zone/Server ID** dengan **cek nickname otomatis** (via API yang bisa diatur).
- Ringkasan pesanan real-time.
- Pembayaran via **QRIS statis** + **upload bukti transfer**.
- Halaman **Cek Pesanan** dengan timeline status.

**Sisi Admin**
- Login aman (password di-hash bcrypt).
- Dashboard statistik (total pesanan, perlu diproses, berhasil, pendapatan).
- Kelola **pesanan** (filter, cari, lihat bukti, ubah status, catatan admin).
- Kelola **produk** & **kategori** (tambah/edit/hapus).
- **Pengaturan**: info website, nomor WhatsApp, upload QRIS, instruksi pembayaran, dan **API cek ID**.
- Pengaturan akun (ganti username & password).

---

## 🗂️ Struktur Folder

```
/
├── admin/                  # Panel admin
│   ├── includes/           # Layout admin (sidebar, dll)
│   ├── auth.php            # Helper otentikasi
│   ├── login.php           # Halaman login admin
│   ├── index.php           # Dashboard
│   ├── orders.php          # Daftar pesanan
│   ├── order-detail.php    # Detail & proses pesanan
│   ├── products.php        # Kelola produk
│   ├── categories.php      # Kelola kategori
│   ├── settings.php        # Pengaturan (QRIS, API, dll)
│   └── account.php         # Pengaturan akun admin
├── api/
│   └── check_id.php        # Endpoint cek nickname (AJAX)
├── assets/
│   ├── css/style.css       # Tema user
│   ├── css/admin.css       # Tema admin
│   └── js/main.js          # Skrip user
├── config/
│   ├── config.php          # Konfigurasi utama (DB, dll) ← EDIT DI SINI
│   └── database.php        # Koneksi MySQLi
├── includes/               # Header, footer, fungsi bantu
├── uploads/                # QRIS & bukti pembayaran (perlu writable)
├── index.php               # Halaman utama (top up)
├── proses-order.php        # Membuat pesanan
├── payment.php             # Halaman pembayaran QRIS + upload bukti
├── cek-pesanan.php         # Cek status pesanan
└── database.sql            # Skema + data awal
```

---

## 🚀 Cara Deploy di VPS (aaPanel + Nginx + PHP 8.2)

### 1. Buat Website di aaPanel
- **Website → Add site**. Domain Anda, root otomatis `/www/wwwroot/namadomain`.
- Pilih **PHP 8.2**.

### 2. Upload Kode
Letakkan seluruh isi project ke folder root, contoh:
```
/www/wwwroot/namadomain/
```

### 3. Buat Database (phpMyAdmin)
- aaPanel → **Database → Add Database**. Catat **nama DB, user, password**.
- Buka **phpMyAdmin** → pilih database → tab **Import** → unggah `database.sql`.

### 4. Edit Konfigurasi
Edit `config/config.php`:
```php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'user_db_anda');
define('DB_PASS', 'password_db_anda');
define('DB_NAME', 'nama_db_anda');
```
Set `APP_DEBUG` ke `false` saat production (default sudah false).

### 5. Izin Folder Upload
Folder `uploads/` harus dapat ditulis web server:
```bash
chown -R www:www /www/wwwroot/namadomain/uploads
chmod -R 755 /www/wwwroot/namadomain/uploads
```
> Di aaPanel, user web server biasanya `www`.

### 6. Konfigurasi Nginx
Di aaPanel → Website → **Config (Konfigurasi)**, pastikan ada aturan keamanan berikut
(letakkan di dalam blok `server { ... }`):

```nginx
# Larang akses langsung ke folder sensitif
location ~ ^/(config|includes)/ { deny all; return 403; }
location ~ /\.(git|env) { deny all; return 404; }

# Larang eksekusi PHP di folder uploads (cegah upload script berbahaya)
location ^~ /uploads/ {
    location ~ \.php$ { deny all; return 403; }
}
```

Pengaturan PHP standar aaPanel (`try_files` & FastCGI ke `php-fpm`) sudah otomatis dibuat.

### 7. Login Admin
Buka: `https://namadomain/admin/login.php`
```
Username : admin
Password : admin123
```
> ⚠️ **WAJIB ganti password** di menu **Akun** setelah login pertama.

### 8. Atur QRIS & API
- Menu **Pengaturan**: upload **gambar QRIS statis**, isi nomor WhatsApp & instruksi.
- Bagian **API Cek ID**: isi endpoint penyedia Anda (lihat di bawah).

---

## 🔍 Pengaturan API Cek ID Otomatis

Sistem dibuat **fleksibel** agar bisa dipasang ke penyedia API mana pun.
Di menu **Pengaturan → API Cek ID**, gunakan placeholder berikut pada URL:

| Placeholder | Arti |
|-------------|------|
| `{id}` | User ID game |
| `{server}` / `{zone}` | Zone / Server ID |
| `{apikey}` | API key Anda |

**Contoh isi Endpoint URL:**
```
https://api-penyedia.com/ml?user_id={id}&zone_id={server}&key={apikey}
```

**Path JSON ke Nickname** = lokasi field nickname pada respons JSON API.
Contoh, jika API membalas:
```json
{ "success": true, "data": { "username": "PlayerOne" } }
```
maka isi **Path** dengan: `data.username`

> ℹ️ **Catatan:** API key & layanan cek ID harus Anda daftarkan/beli sendiri dari penyedia
> (banyak yang berbayar). Beberapa kata kunci untuk dicari: *"API cek ID Mobile Legends"*,
> *"API nickname ML"*. Selama belum diisi, user tetap bisa order — nickname hanya dilewati.

---

## 🔄 Alur Pemesanan

1. User input ID → (opsional) cek nickname → pilih produk → isi kontak.
2. Sistem membuat **invoice** & mengarahkan ke halaman **QRIS**.
3. User bayar via QRIS → **upload bukti** → status jadi **"Sudah Dibayar"**.
4. Admin verifikasi di panel → ubah status **Diproses** → kerjakan top up manual.
5. Admin set **Berhasil** + catatan → user lihat di **Cek Pesanan**.

---

## 🔐 Catatan Keamanan
- Ganti password admin default & kredensial database.
- Proteksi CSRF aktif pada semua form.
- Upload divalidasi tipe MIME (JPG/PNG/WEBP) & ukuran (maks 5MB).
- Pastikan aturan Nginx melarang eksekusi PHP di `uploads/`.
- Gunakan HTTPS (SSL gratis Let's Encrypt via aaPanel).

---

> Catatan: Website ini **bukan** produk resmi / afiliasi Moonton atau Mobile Legends.
> Pastikan layanan top up Anda mematuhi ketentuan yang berlaku.
