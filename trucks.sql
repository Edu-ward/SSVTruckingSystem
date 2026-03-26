-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 26, 2026 at 09:01 AM
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
(1, 'TRK-001', 'Loading', 1, 'San Leonardo (Garage)', 15.36210000, 120.96320000, 0, 6, 'RFID-A1B2C3'),
(2, 'TRK-002', 'Idle', 1, 'San Leonardo (Garage)', 15.36210000, 120.96320000, 0, NULL, 'RFID-D4E5F6'),
(3, 'TRK-003', 'Idle', 1, 'San Leonardo (Garage)', 15.36210000, 120.96320000, 0, NULL, 'RFID-G7H8I9'),
(4, 'TRK-004', 'Idle', 1, 'San Leonardo (Garage)', 15.36210000, 120.96320000, 0, NULL, 'RFID-J1K2L3'),
(5, 'TRK-005', 'Idle', 1, 'San Leonardo (Garage)', 15.36210000, 120.96320000, 0, NULL, 'RFID-M4N5O6');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `trucks`
--
ALTER TABLE `trucks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `current_driver_id` (`current_driver_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `trucks`
--
ALTER TABLE `trucks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `trucks`
--
ALTER TABLE `trucks`
  ADD CONSTRAINT `trucks_ibfk_1` FOREIGN KEY (`current_driver_id`) REFERENCES `drivers` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
