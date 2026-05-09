-- ============================================================
-- SSV Trucking System - Full Database Setup Script
-- Generated: 2026-05-07
-- Run this in phpMyAdmin or MySQL CLI to set up the database.
-- ============================================================

CREATE DATABASE IF NOT EXISTS `ssv_trucking` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `ssv_trucking`;

-- ============================================================
-- 1. USERS TABLE (core authentication)
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('Admin', 'Driver', 'Checker') NOT NULL DEFAULT 'Driver',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 2. TRUCKS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `trucks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `truck_code` VARCHAR(50) NOT NULL,
    `rfid_tag` VARCHAR(100) DEFAULT NULL,
    `rfid_active` TINYINT(1) DEFAULT 1,
    `status` VARCHAR(50) DEFAULT 'Idle',
    `current_location` VARCHAR(255) DEFAULT 'San Leonardo (Garage)',
    `latitude` DECIMAL(10, 6) DEFAULT 15.362100,
    `longitude` DECIMAL(10, 6) DEFAULT 120.963200,
    `speed` DECIMAL(5, 2) DEFAULT 0.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 3. DRIVERS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `drivers` (
    `id` INT PRIMARY KEY,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) DEFAULT '',
    `cdl_number` VARCHAR(50) DEFAULT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `status` VARCHAR(50) DEFAULT 'Off Duty',
    `rating` DECIMAL(3, 2) DEFAULT 5.00,
    `truck_id` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`truck_id`) REFERENCES `trucks`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 4. CHECKERS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `checkers` (
    `id` INT PRIMARY KEY,
    `first_name` VARCHAR(100) DEFAULT '',
    `last_name` VARCHAR(100) DEFAULT '',
    `phone` VARCHAR(20) DEFAULT '',
    FOREIGN KEY (`id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 5. DISPATCHES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `dispatches` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ticket_number` VARCHAR(50) NOT NULL,
    `truck_id` INT DEFAULT NULL,
    `driver_id` INT DEFAULT NULL,
    `status` VARCHAR(50) DEFAULT 'Pending',
    `origin` VARCHAR(255) DEFAULT NULL,
    `destination` VARCHAR(255) DEFAULT NULL,
    `pay_amount` DECIMAL(10, 2) DEFAULT 0.00,
    `dispatch_date` DATE DEFAULT NULL,
    `is_on_time` TINYINT(1) DEFAULT 1,
    `transit_start_time` DATETIME DEFAULT NULL,
    `transit_end_time` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`truck_id`) REFERENCES `trucks`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 6. DRIVER TRIPS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `driver_trips` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `driver_id` INT NOT NULL,
    `destination` VARCHAR(255) NOT NULL,
    `trip_date` DATE DEFAULT NULL,
    `status` VARCHAR(50) DEFAULT 'Pending',
    `order_id` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 7. DRIVER PAYROLL TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `driver_payroll` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `driver_id` INT NOT NULL UNIQUE,
    `total_amount` DECIMAL(12, 2) DEFAULT 0.00,
    `amount_claimed` DECIMAL(12, 2) DEFAULT 0.00,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 8. ORDERS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_number` VARCHAR(50) NOT NULL,
    `client_name` VARCHAR(255) NOT NULL,
    `gravel_type` VARCHAR(50) NOT NULL,
    `destination` VARCHAR(255) NOT NULL,
    `trucks_required` INT DEFAULT 1,
    `trucks_fulfilled` INT DEFAULT 0,
    `checker_id` INT DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `status` VARCHAR(50) DEFAULT 'Pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`checker_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- 9. ORDER SCANS TABLE (RFID checker scans)
-- ============================================================
CREATE TABLE IF NOT EXISTS `order_scans` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `truck_id` INT NOT NULL,
    `checker_id` INT NOT NULL,
    `scanned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`truck_id`) REFERENCES `trucks`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`checker_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- DEFAULT ADMIN ACCOUNT
-- Password: Admin123 (hashed with password_hash)
-- ============================================================
INSERT INTO `users` (`username`, `password`, `role`) VALUES
('admin', '$2y$10$YKxB0bBcKSOCVrGfmHJL0OX3pY1oRcAJVxjT5l3eO5MkxpGvyQwRi', 'Admin')
ON DUPLICATE KEY UPDATE `username` = `username`;

-- ============================================================
-- DONE! Your database is now ready.
-- ============================================================
