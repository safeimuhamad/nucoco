-- Nucoco product category seed
-- Import this file if production_migration_erp_20260531.sql was already imported before product categories were added.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+07:00";
SET NAMES utf8mb4;

START TRANSACTION;

CREATE TABLE IF NOT EXISTS `product_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_key` varchar(100) NOT NULL,
  `label_en` varchar(150) NOT NULL,
  `label_id` varchar(150) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_key` (`category_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `product_categories` (`category_key`, `label_en`, `label_id`, `sort_order`, `status`) VALUES
('Fresh Coconut', 'Fresh Coconut', 'Kelapa Segar', 10, 'active'),
('Coconut Ingredients', 'Coconut Ingredients', 'Bahan Baku Kelapa', 20, 'active'),
('Coconut Derivatives', 'Coconut Derivatives', 'Produk Turunan Kelapa', 30, 'active'),
('Coconut Industrial Product', 'Coconut Industrial Product', 'Produk Industri Kelapa', 40, 'active')
ON DUPLICATE KEY UPDATE
  `label_en` = VALUES(`label_en`),
  `label_id` = VALUES(`label_id`),
  `sort_order` = VALUES(`sort_order`),
  `status` = VALUES(`status`);

COMMIT;
