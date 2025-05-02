-- Database Initialization Script
-- This script creates the necessary tables for the QR Transfer application

-- Drop existing tables if they exist
DROP TABLE IF EXISTS `WORD`;
DROP TABLE IF EXISTS `WORDCLOUD`;
DROP TABLE IF EXISTS `EVENT`;

-- Create EVENT table
CREATE TABLE IF NOT EXISTS `EVENT` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(4) NOT NULL UNIQUE,
    `description` TEXT,
    `password` VARCHAR(255),  -- Clear text password
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

-- Add indexes for better performance
CREATE INDEX idx_e_key ON EVENT(`key`);
CREATE INDEX idx_e_created_at ON EVENT(`created_at`);
CREATE INDEX idx_w_event ON WORDCLOUD(`event_id`);
CREATE INDEX idx_w_created_at ON WORDCLOUD(`created_at`);
CREATE INDEX idx_word_wordcloud ON WORD(`wordcloud_id`);
CREATE INDEX idx_word_text ON WORD(`word`);
