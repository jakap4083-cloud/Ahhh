-- =====================================================================
-- DATABASE: Website Top Up Diamond Mobile Legends
-- Stack: MySQL / MariaDB (MySQLi), PHP 8.2
-- Import melalui phpMyAdmin (aaPanel) ke database Anda.
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- Tabel: admins (akun pengelola)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Akun admin default:
--   username : admin
--   password : admin123  (WAJIB diganti setelah login pertama)
INSERT INTO `admins` (`name`, `username`, `password`) VALUES
('Administrator', 'admin', '$2y$10$zspWVTwdyZYWBW6sYBXkhOC6gBqG/acnbhtQEaKAg/PxjSDNlROzm');

-- ---------------------------------------------------------------------
-- Tabel: categories (jenis produk: Diamond, Starlight, WDP, Gift Skin, dll)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL,
  `description` TEXT NULL,
  `icon` VARCHAR(255) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categories` (`name`, `slug`, `description`, `sort_order`) VALUES
('Diamond',     'diamond',    'Top up Diamond Mobile Legends instan & aman.', 1),
('Starlight',   'starlight',  'Paket Starlight Member & Plus.', 2),
('Weekly Diamond Pass', 'wdp', 'Weekly Diamond Pass (WDP) hemat.', 3),
('Gift / Skin', 'gift-skin',  'Layanan gift skin & item spesial.', 4);

-- ---------------------------------------------------------------------
-- Tabel: products (item yang dijual di tiap kategori)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `price` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `stock_status` ENUM('available','empty') NOT NULL DEFAULT 'available',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category_id`),
  CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`)
    REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `products` (`category_id`, `name`, `price`, `sort_order`) VALUES
(1, '5 Diamonds',    1500,  1),
(1, '12 Diamonds',   3500,  2),
(1, '28 Diamonds',   7000,  3),
(1, '86 Diamonds',   19000, 4),
(1, '172 Diamonds',  38000, 5),
(1, '257 Diamonds',  56000, 6),
(2, 'Starlight Member',      149000, 1),
(2, 'Starlight Member Plus', 299000, 2),
(3, 'Weekly Diamond Pass',   28000, 1),
(4, 'Gift Skin (Normal)',    0, 1),
(4, 'Gift Skin (Elite)',     0, 2);

-- ---------------------------------------------------------------------
-- Tabel: orders (pesanan user)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice` VARCHAR(40) NOT NULL,
  `product_id` INT UNSIGNED NULL,
  `product_name` VARCHAR(150) NOT NULL,
  `category_name` VARCHAR(100) NOT NULL,
  `price` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `game_user_id` VARCHAR(60) NOT NULL,
  `game_server_id` VARCHAR(60) NULL,
  `game_username` VARCHAR(150) NULL,
  `customer_contact` VARCHAR(120) NULL,
  `note` TEXT NULL,
  `payment_proof` VARCHAR(255) NULL,
  `status` ENUM('pending','paid','processing','success','failed') NOT NULL DEFAULT 'pending',
  `admin_note` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_invoice` (`invoice`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Tabel: settings (key-value untuk pengaturan website)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `key` VARCHAR(80) NOT NULL,
  `value` TEXT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`key`, `value`) VALUES
('site_name',            'TopUp ML'),
('site_tagline',         'Top Up Diamond Mobile Legends Murah, Cepat & Aman'),
('whatsapp_admin',       '6281234567890'),
('qris_image',           ''),
('qris_owner_name',      'Nama Pemilik QRIS'),
('payment_instruction',  'Scan QRIS di atas, bayar sesuai nominal, lalu upload bukti pembayaran. Pesanan diproses manual oleh admin setelah pembayaran diverifikasi.'),
('idcheck_endpoint',     ''),
('idcheck_apikey',       ''),
('idcheck_method',       'GET'),
('idcheck_username_path','name')
ON DUPLICATE KEY UPDATE `value` = `value`;

SET FOREIGN_KEY_CHECKS = 1;
