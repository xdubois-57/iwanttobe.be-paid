-- Database Initialization Script
-- This script creates the necessary tables for the QR Transfer application

-- Force foreign key checks off to avoid dependency issues when dropping tables
SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing tables if they exist
DROP TABLE IF EXISTS `WORD`;
DROP TABLE IF EXISTS `WORDCLOUD`;
DROP TABLE IF EXISTS `OVERLAY_PRESENCE`;
DROP TABLE IF EXISTS `OVERLAY_OBJECT`;
DROP TABLE IF EXISTS `EVENT`;

-- Turn foreign key checks back on
SET FOREIGN_KEY_CHECKS = 1;

-- Create EVENT table
CREATE TABLE IF NOT EXISTS `EVENT` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(6) NOT NULL UNIQUE,
    `description` TEXT,
    `password` VARCHAR(255),  -- Clear text password
    `active_url` VARCHAR(512) NULL,  -- URL to redirect users to
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create WORDCLOUD table
CREATE TABLE IF NOT EXISTS `WORDCLOUD` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `event_id` BIGINT NOT NULL,
    `question` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`event_id`) REFERENCES `EVENT`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create WORD table
CREATE TABLE IF NOT EXISTS `WORD` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `wordcloud_id` BIGINT NOT NULL,
    `word` VARCHAR(30) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`wordcloud_id`) REFERENCES `WORDCLOUD`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create OVERLAY_OBJECT table for tracking emoji reactions
CREATE TABLE IF NOT EXISTS `OVERLAY_OBJECT` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `url` VARCHAR(256) NOT NULL UNIQUE,
    `emoji_queue` TEXT NULL, -- JSON-encoded FIFO queue of pending emoji reactions
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create OVERLAY_PRESENCE table for tracking user presence
CREATE TABLE IF NOT EXISTS `OVERLAY_PRESENCE` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `overlay_object_id` BIGINT NOT NULL,
    `phpsessid` VARCHAR(128) NOT NULL,
    `last_seen` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`overlay_object_id`) REFERENCES `OVERLAY_OBJECT`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_presence` (`overlay_object_id`, `phpsessid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for better performance
CREATE INDEX idx_e_key ON EVENT(`key`);
CREATE INDEX idx_wc_event_id ON WORDCLOUD(`event_id`);
CREATE INDEX idx_w_wordcloud_id ON WORD(`wordcloud_id`);
CREATE INDEX idx_oo_url ON OVERLAY_OBJECT(`url`);
CREATE INDEX idx_op_overlay_object_id ON OVERLAY_PRESENCE(`overlay_object_id`);
