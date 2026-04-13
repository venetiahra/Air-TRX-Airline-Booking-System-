-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 13, 2026 at 05:11 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `air_trx_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `full_name`, `username`, `password_hash`, `created_at`) VALUES
(1, 'Air-TRX Administrator', 'ferreradmin', '$2y$10$a9eTd0BxKL0cpQfOGMnHXu5DotMWi5P.yNs19T.WtUasyl5IcOAg2', '2026-04-11 00:41:48');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `flight_id` int(11) NOT NULL,
  `booking_reference` varchar(30) NOT NULL,
  `seat_no` varchar(10) NOT NULL,
  `seat_class` varchar(30) NOT NULL DEFAULT 'Economy',
  `booking_status` varchar(50) NOT NULL DEFAULT 'Confirmed',
  `fare_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) NOT NULL DEFAULT 'GCash',
  `payment_status` varchar(50) NOT NULL DEFAULT 'Paid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `flight_id`, `booking_reference`, `seat_no`, `seat_class`, `booking_status`, `fare_amount`, `payment_method`, `payment_status`, `created_at`) VALUES
(1, 1, 1, 'ATX-A11BC2', '3A', 'Economy', 'Confirmed', 3680.00, 'GCash', 'Paid', '2026-04-11 00:41:48'),
(2, 2, 2, 'ATX-B77D91', '1C', 'Business', 'Confirmed', 8290.00, 'Card', 'Paid', '2026-04-11 00:41:48'),
(3, 3, 2, 'ATX-6A2F09', '2A', 'Business', 'Confirmed', 8290.00, 'Cash (24 hours)', '', '2026-04-11 14:57:21'),
(4, 3, 3, 'ATX-40840F', '3A', 'Business', 'Confirmed', 5990.00, 'Cash (24 hours)', '', '2026-04-11 14:57:46'),
(5, 3, 6, 'ATX-2941F0', '9B', 'Economy', 'Confirmed', 5999.00, 'Cash (24 hours)', '', '2026-04-13 02:46:30');

-- --------------------------------------------------------

--
-- Table structure for table `flights`
--

CREATE TABLE `flights` (
  `id` int(11) NOT NULL,
  `flight_code` varchar(20) NOT NULL,
  `origin` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `departure_date` date NOT NULL,
  `departure_time` time NOT NULL DEFAULT '08:00:00',
  `economy_fare` decimal(10,2) NOT NULL DEFAULT 0.00,
  `premium_fare` decimal(10,2) NOT NULL DEFAULT 0.00,
  `business_fare` decimal(10,2) NOT NULL DEFAULT 0.00,
  `first_class_fare` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `flights`
--

INSERT INTO `flights` (`id`, `flight_code`, `origin`, `destination`, `departure_date`, `departure_time`, `economy_fare`, `premium_fare`, `business_fare`, `first_class_fare`, `created_at`) VALUES
(1, 'ATX101', 'Manila', 'Cebu', '2026-04-15', '08:15:00', 3680.00, 5250.00, 6900.00, 9800.00, '2026-04-11 00:41:48'),
(2, 'ATX202', 'Manila', 'Davao', '2026-04-18', '13:30:00', 4590.00, 6290.00, 8290.00, 11490.00, '2026-04-11 00:41:48'),
(3, 'ATX305', 'Clark', 'Iloilo', '2026-04-20', '10:45:00', 3125.00, 4490.00, 5990.00, 8450.00, '2026-04-11 00:41:48'),
(4, 'ATX411', 'Cebu', 'Siargao', '2026-04-22', '07:25:00', 4290.00, 5690.00, 7990.00, 10890.00, '2026-04-11 00:41:48'),
(5, 'ATX550', 'Manila', 'Boracay', '2026-04-24', '16:00:00', 3950.00, 5390.00, 7450.00, 10190.00, '2026-04-11 00:41:48'),
(6, 'ATX109', 'Manila', 'Thailand', '2026-08-12', '00:36:00', 5999.00, 7999.00, 9999.00, 12999.00, '2026-04-13 02:42:25');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `passport_no` varchar(50) NOT NULL,
  `birthday` date DEFAULT NULL,
  `contact_no` varchar(30) DEFAULT '',
  `address` varchar(255) DEFAULT '',
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `passport_no`, `birthday`, `contact_no`, `address`, `password_hash`, `created_at`) VALUES
(1, 'Josefa Jose', 'prsrlphs@gmail.com', 'P8801221', '2003-06-21', '09171234567', 'Imus, Cavite', '123456789', '2026-04-07 00:41:48'),
(2, 'Barbie Sus', 'trxmcln@gmail.com', 'P5542018', '1999-11-12', '09182345678', 'Cebu City', '123456789\r\n', '2026-04-07 00:41:48'),
(3, 'Zacahriah Feliz', 'beatriciesr@gmail.com', 'PA12345688', '1999-05-26', '091234567898', 'Imus, Cavite', '$2y$10$FzyK41VqJOlEvA9lzWcIZ.hacAtk9Yhdnx1RlKcgcr76VzLXHywZS', '2026-04-07 14:47:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_reference` (`booking_reference`),
  ADD KEY `fk_booking_user` (`user_id`),
  ADD KEY `fk_booking_flight` (`flight_id`);

--
-- Indexes for table `flights`
--
ALTER TABLE `flights`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `flights`
--
ALTER TABLE `flights`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_booking_flight` FOREIGN KEY (`flight_id`) REFERENCES `flights` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_booking_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
