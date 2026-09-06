-- ============================================================
-- SSV Trucking System - Full Database Setup Script
-- Generated: 2026-05-07
--
-- SHARED HOSTING NOTE (InfinityFree, etc.):
-- Select your pre-created database in phpMyAdmin FIRST,
-- then import this file. Do NOT run CREATE DATABASE manually.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. USERS TABLE (core authentication)
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('Admin', 'Driver', 'Checker') NOT NULL DEFAULT 'Driver',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 4. CHECKERS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `checkers` (
    `id` INT PRIMARY KEY,
    `first_name` VARCHAR(100) DEFAULT '',
    `last_name` VARCHAR(100) DEFAULT '',
    `phone` VARCHAR(20) DEFAULT '',
    FOREIGN KEY (`id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 5. ORDERS TABLE (must exist before dispatches)
-- ============================================================
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_number` VARCHAR(50) NOT NULL,
    `client_name` VARCHAR(255) NOT NULL,
    `gravel_type` VARCHAR(50) NOT NULL,
    `destination` VARCHAR(255) NOT NULL,
    `trucks_required` INT DEFAULT 1,
    `trucks_fulfilled` INT DEFAULT 0,
    `cubic_meters_required` DECIMAL(10, 2) DEFAULT 0.00,
    `cubic_meters_fulfilled` DECIMAL(10, 2) DEFAULT 0.00,
    `checker_id` INT DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `status` VARCHAR(50) DEFAULT 'Pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`checker_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 6. DISPATCHES TABLE
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
    `cubic_meters` DECIMAL(10, 2) DEFAULT 0.00,
    `order_id` INT DEFAULT NULL,
    `dispatch_date` DATE DEFAULT NULL,
    `is_on_time` TINYINT(1) DEFAULT 1,
    `transit_start_time` DATETIME DEFAULT NULL,
    `transit_end_time` DATETIME DEFAULT NULL,
    `is_payroll_paid` TINYINT(1) NOT NULL DEFAULT 0,
    `payroll_settled_at` DATETIME DEFAULT NULL,
    `payroll_id` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`truck_id`) REFERENCES `trucks`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 7. DRIVER TRIPS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `driver_trips` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `driver_id` INT NOT NULL,
    `destination` VARCHAR(255) NOT NULL,
    `trip_date` DATE DEFAULT NULL,
    `status` VARCHAR(50) DEFAULT 'Pending',
    `order_id` INT DEFAULT NULL,
    `distance_km` DECIMAL(8, 2) DEFAULT 0.00,
    `pay_amount` DECIMAL(10, 2) DEFAULT 0.00,
    `transit_start_time` DATETIME DEFAULT NULL,
    `transit_end_time` DATETIME DEFAULT NULL,
    `is_payroll_paid` TINYINT(1) NOT NULL DEFAULT 0,
    `payroll_settled_at` DATETIME DEFAULT NULL,
    `payroll_id` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 8. DRIVER PAYROLL TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `driver_payroll` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `driver_id` INT NOT NULL UNIQUE,
    `total_amount` DECIMAL(12, 2) DEFAULT 0.00,
    `amount_claimed` DECIMAL(12, 2) DEFAULT 0.00,
    `remaining_balance` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 9. DRIVER PAYROLL SETTLEMENTS TABLE
-- ============================================================
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

-- ============================================================
-- 10. ORDER SCANS TABLE (RFID checker scans)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 11. DESTINATIONS TABLE (delivery locations + driver pay rates)
-- ============================================================
CREATE TABLE IF NOT EXISTS `destinations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL UNIQUE,
    `driver_rate` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `distance_km` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 12. GRAVEL TYPES TABLE (display labels only)
-- ============================================================
CREATE TABLE IF NOT EXISTS `gravel_types` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `type_key` VARCHAR(50) NOT NULL UNIQUE,
    `label` VARCHAR(100) NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 13. SYSTEM SETTINGS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `system_settings` (
    `setting_key` VARCHAR(100) PRIMARY KEY,
    `setting_value` VARCHAR(255) NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 14. ACTIVITY LOGS TABLE (audit trail)
-- ============================================================
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `username` VARCHAR(100) DEFAULT NULL,
    `role` VARCHAR(50) DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `details` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 15. CASH ADVANCES TABLE
-- ============================================================
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

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- DEFAULT DATA
-- ============================================================

-- Default admin account (Password: Admin123)
INSERT INTO `users` (`username`, `password`, `role`) VALUES
('admin', '$2y$10$YKxB0bBcKSOCVrGfmHJL0OX3pY1oRcAJVxjT5l3eO5MkxpGvyQwRi', 'Admin')
ON DUPLICATE KEY UPDATE `username` = `username`;

-- Default destinations
INSERT INTO `destinations` (`name`, `driver_rate`, `distance_km`) VALUES
('San Leonardo', 300.00, 0.00),
('Tarlac', 800.00, 0.00),
('Laur', 900.00, 0.00),
('Gabaldon', 1000.00, 0.00)
ON DUPLICATE KEY UPDATE `driver_rate` = VALUES(`driver_rate`);

-- Default gravel types
INSERT INTO `gravel_types` (`type_key`, `label`) VALUES
('S1_regular',  'S1 Regular'),
('S1_crushed',  'S1 Crushed'),
('3_4_regular', '3/4 Regular'),
('3_4_crushed', '3/4 Crushed'),
('G1_regular',  'G1 Regular'),
('G1_crushed',  'G1 Crushed'),
('38_regular',  '3/8 Regular'),
('38_crushed',  '3/8 Crushed'),
('base_course', 'Base Course'),
('river_mix',   'River Mix'),
('garden_soil', 'Garden Soil')
ON DUPLICATE KEY UPDATE `label` = VALUES(`label`);

-- Default system settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `description`) VALUES
('garage_name', 'San Leonardo (Garage)', 'Default garage/origin location name'),
('garage_lat',  '15.3621',              'Garage latitude coordinate'),
('garage_lng',  '120.9632',             'Garage longitude coordinate'),
('op_cost_pct', '0.40',                 'Estimated operational cost as a decimal fraction'),
('payday_day',  'Saturday',             'Day of the week when drivers are paid'),
('base_trip_rate', '300.00',            'Base flat rate for trips within San Leonardo (PHP)'),
('rate_per_km', '10.00',                'Rate per kilometer for distance outside San Leonardo boundary (PHP)')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- ============================================================
-- DONE! Your database is now ready.
-- ============================================================
