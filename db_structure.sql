-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 22, 2026 at 12:32 PM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ocean_pearl_villa`
--
CREATE DATABASE IF NOT EXISTS `ocean_pearl_villa` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ocean_pearl_villa`;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE IF NOT EXISTS `bookings` (
  `booking_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `room_id` int NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `guests` int NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`booking_id`),
  KEY `user_id` (`user_id`),
  KEY `room_id` (`room_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE IF NOT EXISTS `rooms` (
  `room_id` int NOT NULL AUTO_INCREMENT,
  `room_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `room_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price_per_night` decimal(10,2) NOT NULL,
  `capacity` int NOT NULL,
  `facilities` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('available','unavailable') COLLATE utf8mb4_unicode_ci DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`room_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('guest','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'guest',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
COMMIT;



-- ==========================================
-- OCEAN PEARL VILLA - SAMPLE RECORDS
-- ==========================================

-- Sample Users
INSERT INTO `users`
(`user_id`, `full_name`, `email`, `phone`, `password`, `role`)
VALUES
(1, 'Demo Guest', 'guest.demo@oceanpearlvilla.com',
 '0771234567',
 '$2y$10$Ne8UNI1rs1GzAzOlZ7UpUuoP6Hh3/jawPs27siKDzjDco.31EWQEG',
 'guest'),

(2, 'Sample Guest Two', 'guest2@oceanpearlvilla.com',
 '0712345678',
 '$2y$10$Ne8UNI1rs1GzAzOlZ7UpUuoP6Hh3/jawPs27siKDzjDco.31EWQEG',
 'guest'),

(3, 'Villa Administrator', 'admin@oceanpearlvilla.com',
 '0751234567',
 '$2y$10$ghq8axMHR8PTkl2DC59OFejVs5mXFCnkZJ4KGpqvhOFLNFsTeIDPe',
 'admin');


-- Sample Rooms
INSERT INTO `rooms`
(`room_id`, `room_name`, `room_type`, `description`,
 `price_per_night`, `capacity`, `facilities`, `image`, `status`)
VALUES
(1, 'Ocean View Deluxe', 'Deluxe Room',
 'Comfortable room with beautiful ocean views.',
 15000.00, 2,
 'Air Conditioning, Wi-Fi, Sea View',
 'ocean-view.jpg', 'available'),

(2, 'Luxury Family Suite', 'Family Suite',
 'Spacious suite suitable for family stays.',
 25000.00, 4,
 'Air Conditioning, Wi-Fi, TV, Family Space',
 'family-suite.jpg', 'available'),

(3, 'Premium Beach Villa', 'Premium Villa',
 'Premium villa offering a relaxing coastal experience.',
 35000.00, 6,
 'Air Conditioning, Wi-Fi, Private Balcony',
 'beach-villa.jpg', 'available');


-- Sample Bookings
INSERT INTO `bookings`
(`booking_id`, `user_id`, `room_id`, `check_in`,
 `check_out`, `guests`, `total_price`, `status`)
VALUES
(1, 1, 1, '2026-12-10', '2026-12-12',
 2, 30000.00, 'pending'),

(2, 2, 2, '2026-12-15', '2026-12-18',
 3, 75000.00, 'confirmed'),

(3, 1, 3, '2026-12-20', '2026-12-22',
 4, 70000.00, 'cancelled');


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
