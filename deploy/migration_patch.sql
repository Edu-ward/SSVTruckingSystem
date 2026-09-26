-- ============================================================
-- SSV Trucking System - Production Database Migration Patch
-- Safe to run on deployed database (InfinityFree / Live MySQL)
-- This script preserves existing users, trips, and live records.
-- Run this in phpMyAdmin > SQL tab on your live database.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Ensure `users` role supports all modern roles
ALTER TABLE `users` 
    MODIFY COLUMN `role` ENUM('Superadmin', 'Admin', 'Driver', 'Checker') NOT NULL DEFAULT 'Driver';

-- 2. Ensure `trucks` has all coordinates and RFID columns
ALTER TABLE `trucks`
    ADD COLUMN IF NOT EXISTS `rfid_tag` VARCHAR(100) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `rfid_active` TINYINT(1) DEFAULT 1,
    ADD COLUMN IF NOT EXISTS `current_location` VARCHAR(100) DEFAULT 'San Leonardo (Garage)',
    ADD COLUMN IF NOT EXISTS `latitude` DECIMAL(10, 8) DEFAULT 15.362100,
    ADD COLUMN IF NOT EXISTS `longitude` DECIMAL(11, 8) DEFAULT 120.963200,
    ADD COLUMN IF NOT EXISTS `speed` INT(11) DEFAULT 0;

-- 3. Ensure `drivers` has profile photo
ALTER TABLE `drivers`
    ADD COLUMN IF NOT EXISTS `profile_photo` VARCHAR(255) DEFAULT NULL;

-- 4. Ensure `checkers` table exists
CREATE TABLE IF NOT EXISTS `checkers` (
    `id` INT PRIMARY KEY,
    `first_name` VARCHAR(100) DEFAULT '',
    `last_name` VARCHAR(100) DEFAULT '',
    `phone` VARCHAR(20) DEFAULT '',
    `status` VARCHAR(50) DEFAULT 'Active',
    FOREIGN KEY (`id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Ensure `orders` has quantity types and dimension tracking
ALTER TABLE `orders`
    ADD COLUMN IF NOT EXISTS `quantity_type` ENUM('sqm','hectare','truck_count') NOT NULL DEFAULT 'truck_count',
    ADD COLUMN IF NOT EXISTS `quantity_value` DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    ADD COLUMN IF NOT EXISTS `cubic_meters_required` DECIMAL(10,2) DEFAULT 0.00,
    ADD COLUMN IF NOT EXISTS `cubic_meters_fulfilled` DECIMAL(10,2) DEFAULT 0.00,
    ADD COLUMN IF NOT EXISTS `checker_id` INT DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `contact_number` VARCHAR(50) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `landmark` VARCHAR(255) DEFAULT NULL;

-- 6. Ensure `dispatches` has distance and tracking columns
ALTER TABLE `dispatches`
    ADD COLUMN IF NOT EXISTS `distance_km` DECIMAL(10,2) DEFAULT 0.00,
    ADD COLUMN IF NOT EXISTS `cancellation_reason` VARCHAR(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `cancellation_photo` VARCHAR(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `landmark` VARCHAR(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `is_payroll_paid` TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS `payroll_settled_at` DATETIME DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `payroll_id` INT DEFAULT NULL;

-- 7. Ensure `driver_trips` has distance, payroll, and transit timing
ALTER TABLE `driver_trips`
    ADD COLUMN IF NOT EXISTS `distance_km` DECIMAL(8,2) DEFAULT 0.00,
    ADD COLUMN IF NOT EXISTS `pay_amount` DECIMAL(10,2) DEFAULT 0.00,
    ADD COLUMN IF NOT EXISTS `is_on_time` TINYINT(1) DEFAULT 1,
    ADD COLUMN IF NOT EXISTS `transit_start_time` DATETIME DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `estimated_arrival_time` DATETIME DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `transit_end_time` DATETIME DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `is_payroll_paid` TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS `payroll_settled_at` DATETIME DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `payroll_id` INT DEFAULT NULL;

-- 8. Ensure `driver_payroll` table exists
CREATE TABLE IF NOT EXISTS `driver_payroll` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `driver_id` INT NOT NULL UNIQUE,
    `total_amount` DECIMAL(12, 2) DEFAULT 0.00,
    `amount_claimed` DECIMAL(12, 2) DEFAULT 0.00,
    `remaining_balance` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Ensure `driver_payroll_settlements` table exists
CREATE TABLE IF NOT EXISTS `driver_payroll_settlements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `settlement_ticket` VARCHAR(50) NOT NULL UNIQUE,
    `driver_id` INT NOT NULL,
    `gross_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `previous_balance` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `cash_advance_deduction` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `net_pay` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `amount_claimed` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `remaining_balance` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `trips_count` INT NOT NULL DEFAULT 0,
    `settled_by` INT DEFAULT NULL,
    `settled_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `notes` TEXT DEFAULT NULL,
    FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Ensure `cash_advances` table exists
CREATE TABLE IF NOT EXISTS `cash_advances` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `driver_id` INT NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `reason` TEXT DEFAULT NULL,
    `status` ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
    `is_settled` TINYINT(1) NOT NULL DEFAULT 0,
    `settled_at` DATETIME DEFAULT NULL,
    `payroll_id` INT DEFAULT NULL,
    `requested_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `resolved_at` TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. Ensure `password_reset_requests` table exists
CREATE TABLE IF NOT EXISTS `password_reset_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `username` VARCHAR(100) DEFAULT NULL,
    `role` ENUM('Driver','Checker') NOT NULL,
    `status` ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
    `requested_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `resolved_at` TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. Ensure `system_settings` table and default settings exist
CREATE TABLE IF NOT EXISTS `system_settings` (
    `setting_key` VARCHAR(100) PRIMARY KEY,
    `setting_value` VARCHAR(255) NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `system_settings` (`setting_key`, `setting_value`, `description`) VALUES
('garage_name', 'San Leonardo (Garage)', 'Default garage/origin location name'),
('garage_lat',  '15.3621',              'Garage latitude coordinate'),
('garage_lng',  '120.9632',             'Garage longitude coordinate'),
('op_cost_pct', '0.40',                 'Estimated operational cost as a decimal fraction'),
('payday_day',  'Saturday',             'Day of the week when drivers are paid'),
('base_trip_rate', '300.00',            'Base flat rate for trips within San Leonardo (PHP)'),
('rate_per_km', '10.00',                'Rate per kilometer for distance outside San Leonardo boundary (PHP)')
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);

SET FOREIGN_KEY_CHECKS = 1;
