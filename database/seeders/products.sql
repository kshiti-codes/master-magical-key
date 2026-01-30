-- ============================================================================
-- Product System Database Migration SQL
-- For: The Master Magical Keys Website
-- Date: January 18, 2025
-- Description: Creates products table and updates cart_items and purchase_items
-- ============================================================================

-- Disable foreign key checks temporarily for smooth import
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- 1. CREATE PRODUCTS TABLE
-- ============================================================================

CREATE TABLE IF NOT EXISTS `products` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10, 2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'AUD',
  `type` enum('digital_download','course','session','subscription','video','other') NOT NULL DEFAULT 'digital_download',
  `pdf_file_path` varchar(255) DEFAULT NULL,
  `audio_file_path` varchar(255) DEFAULT NULL,
  `popup_text` longtext DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sku` varchar(255) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  KEY `products_is_active_index` (`is_active`),
  KEY `products_type_index` (`type`),
  KEY `products_slug_index` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. ADD PRODUCT_ID TO CART_ITEMS TABLE
-- ============================================================================

-- Check if column doesn't exist before adding
SET @column_exists = (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'cart_items'
    AND COLUMN_NAME = 'product_id'
);

-- Add product_id column if it doesn't exist
SET @query = IF(
  @column_exists = 0,
  'ALTER TABLE `cart_items` ADD COLUMN `product_id` bigint(20) UNSIGNED DEFAULT NULL AFTER `cart_id`',
  'SELECT "Column product_id already exists in cart_items" AS message'
);

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key constraint if it doesn't exist
SET @fk_exists = (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'cart_items'
    AND CONSTRAINT_NAME = 'cart_items_product_id_foreign'
);

SET @query = IF(
  @fk_exists = 0,
  'ALTER TABLE `cart_items` ADD CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE',
  'SELECT "Foreign key cart_items_product_id_foreign already exists" AS message'
);

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- 3. ADD PRODUCT_ID TO PURCHASE_ITEMS TABLE
-- ============================================================================

-- Check if column doesn't exist before adding
SET @column_exists = (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'purchase_items'
    AND COLUMN_NAME = 'product_id'
);

-- Add product_id column if it doesn't exist
SET @query = IF(
  @column_exists = 0,
  'ALTER TABLE `purchase_items` ADD COLUMN `product_id` bigint(20) UNSIGNED DEFAULT NULL AFTER `purchase_id`',
  'SELECT "Column product_id already exists in purchase_items" AS message'
);

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key constraint if it doesn't exist
SET @fk_exists = (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'purchase_items'
    AND CONSTRAINT_NAME = 'purchase_items_product_id_foreign'
);

SET @query = IF(
  @fk_exists = 0,
  'ALTER TABLE `purchase_items` ADD CONSTRAINT `purchase_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE',
  'SELECT "Foreign key purchase_items_product_id_foreign already exists" AS message'
);

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- 4. CREATE PRODUCT_USER TABLE
-- ============================================================================

CREATE TABLE `product_user` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `price` DECIMAL(10, 2) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  KEY `product_user_user_id_foreign` (`user_id`),
  KEY `product_user_product_id_foreign` (`product_id`),
  CONSTRAINT `product_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_user_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  `title` = VALUES(`title`),
  `updated_at` = NOW();

-- ============================================================================
-- Re-enable foreign key checks
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 1;

-- END OF SQL FILE