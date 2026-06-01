-- Nucoco ERP/Admin migration
-- Import this file into the production database with phpMyAdmin.
-- Safe intent: add new ERP tables/columns without dropping existing production data.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+07:00";
SET NAMES utf8mb4;

START TRANSACTION;

-- --------------------------------------------------------
-- Existing table upgrades
-- --------------------------------------------------------

ALTER TABLE `users`
    MODIFY COLUMN `role` varchar(50) NOT NULL DEFAULT 'admin',
    MODIFY COLUMN `status` enum('active','inactive') DEFAULT 'inactive',
    ADD COLUMN IF NOT EXISTS `activation_token_hash` varchar(64) DEFAULT NULL AFTER `status`,
    ADD COLUMN IF NOT EXISTS `activation_expires_at` datetime DEFAULT NULL AFTER `activation_token_hash`,
    ADD COLUMN IF NOT EXISTS `activated_at` datetime DEFAULT NULL AFTER `activation_expires_at`,
    ADD COLUMN IF NOT EXISTS `password_reset_token_hash` varchar(64) DEFAULT NULL AFTER `activated_at`,
    ADD COLUMN IF NOT EXISTS `password_reset_expires_at` datetime DEFAULT NULL AFTER `password_reset_token_hash`,
    ADD COLUMN IF NOT EXISTS `invited_at` datetime DEFAULT NULL AFTER `password_reset_expires_at`;

ALTER TABLE `products`
    ADD COLUMN IF NOT EXISTS `quotation_description` text DEFAULT NULL AFTER `description`;

ALTER TABLE `services`
    ADD COLUMN IF NOT EXISTS `quotation_description` text DEFAULT NULL AFTER `content`;

-- --------------------------------------------------------
-- Product categories
-- --------------------------------------------------------

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

-- --------------------------------------------------------
-- Leads
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `leads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inquiry_id` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `company` varchar(150) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `source` varchar(50) NOT NULL DEFAULT 'manual',
  `interest_type` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('new','contacted','qualified','proposal','won','lost') DEFAULT 'new',
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_inquiry_id` (`inquiry_id`),
  KEY `idx_email` (`email`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `leads`
    ADD COLUMN IF NOT EXISTS `address` text DEFAULT NULL AFTER `company`;

-- --------------------------------------------------------
-- Quotations
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `quotations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quote_number` varchar(50) NOT NULL,
  `inquiry_id` int(11) DEFAULT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `quote_type` enum('local','international') NOT NULL DEFAULT 'local',
  `customer_name` varchar(150) NOT NULL,
  `customer_email` varchar(150) DEFAULT NULL,
  `customer_phone` varchar(50) DEFAULT NULL,
  `customer_company` varchar(150) DEFAULT NULL,
  `customer_address` text DEFAULT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'IDR',
  `status` enum('draft','sent','accepted','rejected','cancelled') NOT NULL DEFAULT 'draft',
  `valid_until` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(15,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `quote_number` (`quote_number`),
  KEY `idx_inquiry_id` (`inquiry_id`),
  KEY `idx_lead_id` (`lead_id`),
  KEY `idx_status` (`status`),
  KEY `idx_quote_type` (`quote_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `quotations`
    ADD COLUMN IF NOT EXISTS `customer_address` text DEFAULT NULL AFTER `customer_company`;

CREATE TABLE IF NOT EXISTS `quotation_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quotation_id` int(11) NOT NULL,
  `item_type` enum('product','service') NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` decimal(12,2) NOT NULL DEFAULT 1.00,
  `unit` varchar(50) DEFAULT NULL,
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_quotation_id` (`quotation_id`),
  CONSTRAINT `fk_quotation_items_quote` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Invoices
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) NOT NULL,
  `quotation_id` int(11) DEFAULT NULL,
  `lead_id` int(11) DEFAULT NULL,
  `customer_name` varchar(150) NOT NULL,
  `customer_email` varchar(150) DEFAULT NULL,
  `customer_phone` varchar(50) DEFAULT NULL,
  `customer_company` varchar(150) DEFAULT NULL,
  `customer_address` text DEFAULT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'IDR',
  `status` enum('draft','sent','paid','overdue','cancelled') NOT NULL DEFAULT 'draft',
  `invoice_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `bank_account_number` varchar(100) DEFAULT NULL,
  `bank_account_name` varchar(150) DEFAULT NULL,
  `bank_branch` varchar(150) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(15,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `idx_invoices_quotation_id` (`quotation_id`),
  KEY `idx_invoices_lead_id` (`lead_id`),
  KEY `idx_invoices_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `invoices`
    ADD COLUMN IF NOT EXISTS `customer_address` text DEFAULT NULL AFTER `customer_company`;

CREATE TABLE IF NOT EXISTS `invoice_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `item_type` enum('product','service') NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` decimal(12,2) NOT NULL DEFAULT 1.00,
  `unit` varchar(50) DEFAULT NULL,
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_invoice_items_invoice_id` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- User roles and access matrix
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `user_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_key` varchar(50) NOT NULL,
  `role_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_key` (`role_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `user_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_key` varchar(50) NOT NULL,
  `permission_key` varchar(100) NOT NULL,
  `permission_name` varchar(150) NOT NULL,
  `allowed` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_role_permission` (`role_key`,`permission_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `user_roles` (`role_key`, `role_name`, `description`, `status`) VALUES
('admin', 'Administrator', 'Full system access', 'active'),
('editor', 'Editor', 'Web content access', 'active'),
('sales', 'Sales', 'Sales management access', 'active')
ON DUPLICATE KEY UPDATE
  `role_name` = VALUES(`role_name`),
  `description` = VALUES(`description`),
  `status` = VALUES(`status`);

INSERT INTO `user_access` (`role_key`, `permission_key`, `permission_name`, `allowed`) VALUES
('admin','sales.invoices.manage','Invoices',1),
('admin','sales.leads.manage','Leads',1),
('admin','sales.quotations.manage','Quotations',1),
('admin','users.access.manage','User Access',1),
('admin','users.roles.manage','User Roles',1),
('admin','users.users.manage','Users',1),
('admin','webadmin.choose_us.manage','Choose Us',1),
('admin','webadmin.faq.manage','FAQ',1),
('admin','webadmin.inbox.manage','Inbox',1),
('admin','webadmin.news.manage','News',1),
('admin','webadmin.pages.manage','Pages',1),
('admin','webadmin.page_content.manage','Page Content',1),
('admin','webadmin.products.manage','Products',1),
('admin','webadmin.services.manage','Services',1),
('admin','webadmin.settings.manage','Web Settings',1),
('admin','webadmin.team.manage','Team',1),
('admin','webadmin.testimonial.manage','Testimonial',1),
('editor','sales.invoices.manage','Invoices',0),
('editor','sales.leads.manage','Leads',0),
('editor','sales.quotations.manage','Quotations',0),
('editor','users.access.manage','User Access',0),
('editor','users.roles.manage','User Roles',0),
('editor','users.users.manage','Users',0),
('editor','webadmin.choose_us.manage','Choose Us',1),
('editor','webadmin.faq.manage','FAQ',1),
('editor','webadmin.inbox.manage','Inbox',1),
('editor','webadmin.news.manage','News',1),
('editor','webadmin.pages.manage','Pages',1),
('editor','webadmin.page_content.manage','Page Content',1),
('editor','webadmin.products.manage','Products',1),
('editor','webadmin.services.manage','Services',1),
('editor','webadmin.settings.manage','Web Settings',1),
('editor','webadmin.team.manage','Team',1),
('editor','webadmin.testimonial.manage','Testimonial',1),
('sales','sales.invoices.manage','Invoices',1),
('sales','sales.leads.manage','Leads',1),
('sales','sales.quotations.manage','Quotations',1),
('sales','users.access.manage','User Access',0),
('sales','users.roles.manage','User Roles',0),
('sales','users.users.manage','Users',0),
('sales','webadmin.choose_us.manage','Choose Us',0),
('sales','webadmin.faq.manage','FAQ',0),
('sales','webadmin.inbox.manage','Inbox',0),
('sales','webadmin.news.manage','News',0),
('sales','webadmin.pages.manage','Pages',0),
('sales','webadmin.page_content.manage','Page Content',0),
('sales','webadmin.products.manage','Products',0),
('sales','webadmin.services.manage','Services',0),
('sales','webadmin.settings.manage','Web Settings',0),
('sales','webadmin.team.manage','Team',0),
('sales','webadmin.testimonial.manage','Testimonial',0)
ON DUPLICATE KEY UPDATE
  `permission_name` = VALUES(`permission_name`),
  `allowed` = VALUES(`allowed`);

COMMIT;
