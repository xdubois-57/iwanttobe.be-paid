-- Add OVERLAY_PRESENCE table for user presence tracking
-- Run this script to update existing databases

-- Create OVERLAY_PRESENCE table if it doesn't exist
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

-- Add index for better performance
CREATE INDEX IF NOT EXISTS idx_op_overlay_object_id ON OVERLAY_PRESENCE(`overlay_object_id`);

-- Only applies when upgrading - ensure the OVERLAY_OBJECT table exists
CREATE TABLE IF NOT EXISTS `OVERLAY_OBJECT` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `url` VARCHAR(256) NOT NULL UNIQUE,
    `likes` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add index on OVERLAY_OBJECT if not exists
CREATE INDEX IF NOT EXISTS idx_oo_url ON OVERLAY_OBJECT(`url`);
