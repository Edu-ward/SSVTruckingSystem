-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 23, 2026 at 02:49 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ssv_trucking`
--

-- --------------------------------------------------------

--
-- Table structure for table `delivery_performance`
--

CREATE TABLE `delivery_performance` (
  `id` int(11) NOT NULL,
  `week_name` varchar(10) DEFAULT NULL,
  `on_time` int(11) DEFAULT NULL,
  `delayed` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_performance`
--

INSERT INTO `delivery_performance` (`id`, `week_name`, `on_time`, `delayed`) VALUES
(1, 'Week 1', 45, 5),
(2, 'Week 2', 53, 3),
(3, 'Week 3', 48, 7),
(4, 'Week 4', 60, 4);

-- --------------------------------------------------------

--
-- Table structure for table `dispatches`
--

CREATE TABLE `dispatches` (
  `id` int(11) NOT NULL,
  `ticket_number` varchar(50) NOT NULL,
  `truck_id` int(11) DEFAULT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `status` enum('Pending','In Transit','Delivered') NOT NULL,
  `origin` varchar(100) DEFAULT 'San Leonardo, Nueva Ecija',
  `destination` varchar(100) NOT NULL,
  `weight` decimal(10,2) DEFAULT 0.00,
  `pay_amount` decimal(10,2) DEFAULT 0.00,
  `dispatch_date` date NOT NULL,
  `is_on_time` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dispatches`
--

INSERT INTO `dispatches` (`id`, `ticket_number`, `truck_id`, `driver_id`, `status`, `origin`, `destination`, `weight`, `pay_amount`, `dispatch_date`, `is_on_time`) VALUES
(1, 'TKT-2026-0001', 0, 0, 'In Transit', 'San Leonardo, Nueva Ecija', 'Cabanatuan City', 0.00, 0.00, '2026-03-10', 1),
(2, 'TKT-2026-0002', 0, 0, 'Pending', 'San Leonardo, Nueva Ecija', 'Gapan City', 0.00, 0.00, '2026-03-10', 1),
(3, 'TKT-2026-0003', 0, 0, 'In Transit', 'San Leonardo, Nueva Ecija', 'Tarlac City', 0.00, 0.00, '2026-03-10', 1),
(4, 'TKT-2026-0004', 0, 0, 'In Transit', 'San Leonardo, Nueva Ecija', 'Palayan City', 0.00, 0.00, '2026-03-10', 1),
(5, 'TKT-2025-0998', 0, 0, 'Delivered', 'San Leonardo, Nueva Ecija', 'Aliaga', 0.00, 0.00, '2026-03-10', 1),
(6, 'TKT-2026-7697', 0, NULL, 'Pending', 'San Leonardo, Nueva Ecija', 'Taguig City', 10000.00, 25000.00, '2026-03-26', 1),
(11, 'TKT-2026-9423', NULL, NULL, 'In Transit', 'Brgy. Burgos San Leonardo, Nueva Ecija', 'Brgy. Mallorca', 2000.00, 5000.00, '2026-04-15', 1),
(12, 'TKT-2026-1463', 7, 5, 'In Transit', 'Brgy. Burgos San Leonardo, Nueva Ecija', 'Brgy. Mallorca', 2000.00, 5000.00, '2026-04-17', 1),
(13, 'TKT-2026-0486', NULL, 7, 'In Transit', 'Brgy. Burgos San Leonardo, Nueva Ecija', 'Brgy. Mallorca', 2000.00, 5000.00, '2026-04-17', 1),
(14, 'TKT-2026-5313', 8, 7, 'In Transit', 'Brgy. Burgos San Leonardo, Nueva Ecija', 'Taguig City', 99999999.99, 99999999.99, '2026-04-17', 1);

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `cdl_number` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `status` enum('Active','Off Duty','Dispatched') DEFAULT 'Off Duty',
  `rating` decimal(2,1) DEFAULT 0.0,
  `total_deliveries` int(11) DEFAULT 0,
  `on_time_pct` int(11) DEFAULT 0,
  `hours_this_week` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drivers`
--

INSERT INTO `drivers` (`id`, `name`, `cdl_number`, `phone`, `status`, `rating`, `total_deliveries`, `on_time_pct`, `hours_this_week`) VALUES
(5, 'John Gabriel Valmonte', '2392312344', '09238347474', 'Dispatched', 0.0, 0, 0, 0),
(7, 'Edward Nelson Salvador', '21883723', '09109369096', 'Dispatched', 0.0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `driver_payroll`
--

CREATE TABLE `driver_payroll` (
  `id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `amount_claimed` decimal(10,2) DEFAULT 0.00,
  `available_balance` decimal(10,2) GENERATED ALWAYS AS (`total_amount` - `amount_claimed`) STORED,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `driver_payroll`
--

INSERT INTO `driver_payroll` (`id`, `driver_id`, `total_amount`, `amount_claimed`, `last_updated`) VALUES
(1, 5, 5000.00, 0.00, '2026-04-17 03:52:29'),
(3, 7, 99999999.99, 0.00, '2026-04-17 04:34:35');

-- --------------------------------------------------------

--
-- Table structure for table `driver_trips`
--

CREATE TABLE `driver_trips` (
  `id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `trip_date` date NOT NULL,
  `status` enum('Completed','In Transit','Cancelled') DEFAULT 'Completed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `driver_trips`
--

INSERT INTO `driver_trips` (`id`, `driver_id`, `destination`, `trip_date`, `status`) VALUES
(7, 5, 'Brgy. Mallorca', '2026-04-17', 'In Transit'),
(8, 7, 'Brgy. Mallorca', '2026-04-17', 'In Transit'),
(9, 7, 'Taguig City', '2026-04-17', 'In Transit');

-- --------------------------------------------------------

--
-- Table structure for table `efficiency_trend`
--

CREATE TABLE `efficiency_trend` (
  `id` int(11) NOT NULL,
  `month_name` varchar(10) DEFAULT NULL,
  `efficiency_pct` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `efficiency_trend`
--

INSERT INTO `efficiency_trend` (`id`, `month_name`, `efficiency_pct`) VALUES
(1, 'Jan', 85),
(2, 'Feb', 88),
(3, 'Mar', 92),
(4, 'Apr', 90),
(5, 'May', 94),
(6, 'Jun', 97);

-- --------------------------------------------------------

--
-- Table structure for table `finance_reports`
--

CREATE TABLE `finance_reports` (
  `id` int(11) NOT NULL,
  `month_name` varchar(10) DEFAULT NULL,
  `revenue` int(11) DEFAULT NULL,
  `expenses` int(11) DEFAULT NULL,
  `profit` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `finance_reports`
--

INSERT INTO `finance_reports` (`id`, `month_name`, `revenue`, `expenses`, `profit`) VALUES
(1, 'Jan', 12000, 18000, 13000),
(2, 'Feb', 15000, 18000, 17000),
(3, 'Mar', 14000, 17000, 15000),
(4, 'Apr', 22000, 16000, 24000),
(5, 'May', 18000, 19000, 20000),
(6, 'Jun', 28000, 15000, 26000),
(7, 'Jan', 12000, 18000, 13000),
(8, 'Feb', 15000, 18000, 17000),
(9, 'Mar', 14000, 17000, 15000),
(10, 'Apr', 22000, 16000, 24000),
(11, 'May', 18000, 19000, 20000),
(12, 'Jun', 28000, 15000, 26000);

-- --------------------------------------------------------

--
-- Table structure for table `fuel_consumption`
--

CREATE TABLE `fuel_consumption` (
  `id` int(11) NOT NULL,
  `day_name` varchar(10) DEFAULT NULL,
  `gallons` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fuel_consumption`
--

INSERT INTO `fuel_consumption` (`id`, `day_name`, `gallons`) VALUES
(1, 'Mon', 450),
(2, 'Tue', 520),
(3, 'Wed', 480),
(4, 'Thu', 610),
(5, 'Fri', 550),
(6, 'Sat', 320),
(7, 'Sun', 280);

-- --------------------------------------------------------

--
-- Table structure for table `performance_metrics`
--

CREATE TABLE `performance_metrics` (
  `id` int(11) NOT NULL,
  `metric` varchar(100) DEFAULT NULL,
  `this_month` varchar(50) DEFAULT NULL,
  `last_month` varchar(50) DEFAULT NULL,
  `change_str` varchar(20) DEFAULT NULL,
  `is_positive` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `performance_metrics`
--

INSERT INTO `performance_metrics` (`id`, `metric`, `this_month`, `last_month`, `change_str`, `is_positive`) VALUES
(1, 'Total Deliveries', '1,243', '1,156', '+7.5%', 1),
(2, 'On-Time Deliveries', '1,171', '1,064', '+10.1%', 1),
(3, 'Average Delivery Time', '2.3 days', '2.6 days', '-11.5%', 1),
(4, 'Fuel Efficiency (MPG)', '6.8', '6.5', '+4.6%', 1),
(5, 'Customer Satisfaction', '4.7/5', '4.6/5', '+2.2%', 1),
(6, 'Revenue per Mile', '$2.45', '$2.32', '+5.6%', 1);

-- --------------------------------------------------------

--
-- Table structure for table `report_kpis`
--

CREATE TABLE `report_kpis` (
  `id` int(11) NOT NULL,
  `title` varchar(50) DEFAULT NULL,
  `value` varchar(50) DEFAULT NULL,
  `subtext` varchar(100) DEFAULT NULL,
  `color_class` varchar(50) DEFAULT NULL,
  `icon_class` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report_kpis`
--

INSERT INTO `report_kpis` (`id`, `title`, `value`, `subtext`, `color_class`, `icon_class`) VALUES
(1, 'Total Revenue', '₱328K', '+12% from last period', 'bg-blue-500', 'fa-peso-sign'),
(2, 'Profit Margin', '28.5%', '+3.2% improvement', 'bg-green-500', 'fa-arrow-trend-up'),
(3, 'Deliveries', '1,243', 'This month', 'bg-orange-500', 'fa-truck-fast'),
(4, 'On-Time Rate', '94.2%', 'Industry avg: 89%', 'bg-purple-500', 'fa-calendar'),
(5, 'Total Revenue', '₱328K', '+12% from last period', 'bg-blue-500', 'fa-peso-sign'),
(6, 'Profit Margin', '28.5%', '+3.2% improvement', 'bg-green-500', 'fa-arrow-trend-up'),
(7, 'Deliveries', '1,243', 'This month', 'bg-orange-500', 'fa-truck-fast'),
(8, 'On-Time Rate', '94.2%', 'Industry avg: 89%', 'bg-purple-500', 'fa-calendar');

-- --------------------------------------------------------

--
-- Table structure for table `trucks`
--

CREATE TABLE `trucks` (
  `id` int(11) NOT NULL,
  `truck_code` varchar(20) NOT NULL,
  `status` enum('Idle','In Transit','Loading','Unloading') NOT NULL,
  `rfid_active` tinyint(1) DEFAULT 1,
  `current_location` varchar(100) DEFAULT 'Garage',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `speed` int(11) DEFAULT 0,
  `current_driver_id` int(11) DEFAULT NULL,
  `rfid_tag` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trucks`
--

INSERT INTO `trucks` (`id`, `truck_code`, `status`, `rfid_active`, `current_location`, `latitude`, `longitude`, `speed`, `current_driver_id`, `rfid_tag`) VALUES
(7, 'TRK-7788', 'In Transit', 1, 'San Leonardo (Garage)', 15.36210000, 120.96320000, 0, 5, '2838158891'),
(8, 'TRK-9238', 'In Transit', 1, 'San Leonardo (Garage)', 15.36210000, 120.96320000, 0, 7, '109238901');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(4, 'admin', '$2y$10$VwRzvFMGX28UsdAWaJoCFO9cxg9Y2IELNvEXr7ojfxBrg4h2GgFya', 'Admin'),
(5, 'johngabriel', '$2y$10$AOJDAX1PUX8zzNNjntwwaeRL2H3IqyGpWIM8Lhp01RrejTzI1FwJe', 'Driver'),
(7, 'edwardnelson', '$2y$10$626rpyr/Gf96r.3b9XxPwePDuGNol4hJ5mO8v8onIyPFHrR1HG45C', 'Driver');

-- --------------------------------------------------------

--
-- Table structure for table `weekly_activity`
--

CREATE TABLE `weekly_activity` (
  `id` int(11) NOT NULL,
  `day_name` varchar(10) DEFAULT NULL,
  `total_dispatches` int(11) DEFAULT NULL,
  `completed` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `weekly_activity`
--

INSERT INTO `weekly_activity` (`id`, `day_name`, `total_dispatches`, `completed`) VALUES
(1, 'Mon', 12, 10),
(2, 'Tue', 15, 13),
(3, 'Wed', 18, 16),
(4, 'Thu', 14, 12),
(5, 'Fri', 20, 18),
(6, 'Sat', 8, 7),
(7, 'Sun', 5, 5);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `delivery_performance`
--
ALTER TABLE `delivery_performance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dispatches`
--
ALTER TABLE `dispatches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `truck_id` (`truck_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `driver_payroll`
--
ALTER TABLE `driver_payroll`
  ADD PRIMARY KEY (`id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `driver_trips`
--
ALTER TABLE `driver_trips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `efficiency_trend`
--
ALTER TABLE `efficiency_trend`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `finance_reports`
--
ALTER TABLE `finance_reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fuel_consumption`
--
ALTER TABLE `fuel_consumption`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `performance_metrics`
--
ALTER TABLE `performance_metrics`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `report_kpis`
--
ALTER TABLE `report_kpis`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trucks`
--
ALTER TABLE `trucks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `current_driver_id` (`current_driver_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `weekly_activity`
--
ALTER TABLE `weekly_activity`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `delivery_performance`
--
ALTER TABLE `delivery_performance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `dispatches`
--
ALTER TABLE `dispatches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `driver_payroll`
--
ALTER TABLE `driver_payroll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `driver_trips`
--
ALTER TABLE `driver_trips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `efficiency_trend`
--
ALTER TABLE `efficiency_trend`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `finance_reports`
--
ALTER TABLE `finance_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `fuel_consumption`
--
ALTER TABLE `fuel_consumption`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `performance_metrics`
--
ALTER TABLE `performance_metrics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `report_kpis`
--
ALTER TABLE `report_kpis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `trucks`
--
ALTER TABLE `trucks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `weekly_activity`
--
ALTER TABLE `weekly_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dispatches`
--
ALTER TABLE `dispatches`
  ADD CONSTRAINT `dispatches_ibfk_1` FOREIGN KEY (`truck_id`) REFERENCES `trucks` (`id`),
  ADD CONSTRAINT `dispatches_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`);

--
-- Constraints for table `driver_payroll`
--
ALTER TABLE `driver_payroll`
  ADD CONSTRAINT `driver_payroll_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `driver_trips`
--
ALTER TABLE `driver_trips`
  ADD CONSTRAINT `driver_trips_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trucks`
--
ALTER TABLE `trucks`
  ADD CONSTRAINT `trucks_ibfk_1` FOREIGN KEY (`current_driver_id`) REFERENCES `drivers` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
