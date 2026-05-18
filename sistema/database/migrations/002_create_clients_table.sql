-- Migration 002: Create clients table
CREATE TABLE IF NOT EXISTS `clients` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_type` VARCHAR(30) NOT NULL DEFAULT 'empresa',
  `business_name` VARCHAR(180) NOT NULL,
  `rut` VARCHAR(20) NULL,
  `giro` VARCHAR(180) NULL,
  `contact_name` VARCHAR(120) NULL,
  `email` VARCHAR(190) NULL,
  `phone` VARCHAR(40) NULL,
  `address` VARCHAR(255) NULL,
  `comuna` VARCHAR(120) NULL,
  `city` VARCHAR(120) NULL,
  `notes` TEXT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_clients_business_name` (`business_name`),
  KEY `idx_clients_rut` (`rut`),
  KEY `idx_clients_email` (`email`),
  KEY `idx_clients_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
