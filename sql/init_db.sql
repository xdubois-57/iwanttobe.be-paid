-- Database Initialization Script
-- This script creates the necessary tables for the QR Transfer application

-- Create INVOLVED_EVENT table
CREATE TABLE IF NOT EXISTS `INVOLVED_EVENT` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(4) NOT NULL,
    `description` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for better performance
CREATE INDEX idx_ie_key ON INVOLVED_EVENT(`key`);
CREATE INDEX idx_ie_created_at ON INVOLVED_EVENT(`created_at`);

-- Create USER_FAVORITE table for storing user favorites
CREATE TABLE IF NOT EXISTS `USER_FAVORITE` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `user_id` VARCHAR(128) NOT NULL,
    `name` VARCHAR(255),
    `iban` VARCHAR(34) NOT NULL,
    `amount` DECIMAL(10,2),
    `communication` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for user_favorite table
CREATE INDEX idx_uf_user_id ON USER_FAVORITE(`user_id`);
