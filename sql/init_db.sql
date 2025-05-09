-- Database Initialization Script
-- This script creates the necessary tables for the QR Transfer application

-- Drop all tables in the database
SET FOREIGN_KEY_CHECKS = 0;

-- Get all table names
SET @tables = NULL;
SELECT GROUP_CONCAT(table_schema, '.', table_name) INTO @tables
  FROM information_schema.tables
  WHERE table_schema = (SELECT DATABASE());

-- Drop all tables
SET @tables = CONCAT('DROP TABLE IF EXISTS ', @tables);
PREPARE stmt FROM @tables;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Turn foreign key checks back on
SET FOREIGN_KEY_CHECKS = 1;

-- Create EVENT table

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

-- Create EVENT_ITEM table
CREATE TABLE IF NOT EXISTS `EVENT_ITEM` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `event_id` BIGINT NOT NULL,
    `question` TEXT NOT NULL,
    `type` VARCHAR(32) NOT NULL DEFAULT 'wordcloud',
    `position` INT DEFAULT 0,
    `emoji_queue` TEXT NULL COMMENT 'JSON-encoded FIFO queue of pending emoji reactions',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`event_id`) REFERENCES `EVENT`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Create EVENT_PRESENCE table for tracking user presence
CREATE TABLE IF NOT EXISTS `EVENT_PRESENCE` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `event_id` BIGINT NOT NULL,
    `phpsessid` VARCHAR(128) NOT NULL,
    `last_seen` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`event_id`) REFERENCES `EVENT`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_presence` (`event_id`, `phpsessid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create EVENT_ANSWERS table
CREATE TABLE IF NOT EXISTS `EVENT_ANSWERS` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `event_item_id` BIGINT NOT NULL,
    `value` TEXT NOT NULL,
    `votes` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`event_item_id`) REFERENCES `EVENT_ITEM`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for better performance
CREATE INDEX idx_e_key ON EVENT(`key`);
CREATE INDEX idx_ea_event_item_id ON EVENT_ANSWERS(`event_item_id`);
CREATE INDEX idx_ep_event_id ON EVENT_PRESENCE(`event_id`);
