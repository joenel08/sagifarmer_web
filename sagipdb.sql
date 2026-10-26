-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for sagipfarmer
CREATE DATABASE IF NOT EXISTS `sagipfarmer` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `sagipfarmer`;

-- Dumping structure for table sagipfarmer.alerts
CREATE TABLE IF NOT EXISTS `alerts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `plot_id` int NOT NULL,
  `alert_type` varchar(40) COLLATE utf8mb4_general_ci NOT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `severity` enum('low','medium','high') COLLATE utf8mb4_general_ci DEFAULT 'low',
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `plot_id` (`plot_id`),
  CONSTRAINT `alerts_ibfk_1` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table sagipfarmer.alerts: ~5 rows (approximately)
INSERT IGNORE INTO `alerts` (`id`, `plot_id`, `alert_type`, `message`, `severity`, `is_read`, `created_at`) VALUES
	(8, 1, 'Low Water Level', 'Water level at RWaterLevel dropped to 30%. Irrigation needed.', 'high', 0, '2026-10-02 13:48:12'),
	(9, 2, 'Soil Too Dry', 'Soil moisture at Corn Field reads 3500 ADC. Consider watering.', 'medium', 0, '2026-10-25 13:19:34'),
	(10, 2, 'Soil Too Dry', 'Soil moisture at Corn Field reads 3500 ADC. Consider watering.', 'medium', 0, '2026-10-25 13:34:58'),
	(11, 1, 'Low Water Level', 'Water level at RWaterLevel is low (300 cm). Irrigation needed.', 'high', 0, '2026-10-25 13:34:58'),
	(12, 2, 'Soil Too Dry', 'Soil moisture at Corn Field reads 3500 ADC. Consider watering.', 'medium', 0, '2026-10-25 13:42:21'),
	(13, 1, 'Low Water Level', 'Water level at RWaterLevel is low (100 cm). Irrigation needed.', 'high', 0, '2026-10-25 13:42:23');

-- Dumping structure for table sagipfarmer.plots
CREATE TABLE IF NOT EXISTS `plots` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `sensor_name` varchar(120) COLLATE utf8mb4_general_ci NOT NULL,
  `sensor_type` enum('water_level','soil_moisture') COLLATE utf8mb4_general_ci NOT NULL,
  `sensor_id` varchar(60) COLLATE utf8mb4_general_ci NOT NULL,
  `full_address` text COLLATE utf8mb4_general_ci,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `plots_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table sagipfarmer.plots: ~2 rows (approximately)
INSERT IGNORE INTO `plots` (`id`, `user_id`, `sensor_name`, `sensor_type`, `sensor_id`, `full_address`, `latitude`, `longitude`, `created_at`) VALUES
	(1, 2, 'RWaterLevel', 'water_level', 'Esp123455', NULL, 14.59950000, 120.98450000, '2026-09-24 12:16:28'),
	(2, 2, 'Corn Field', 'soil_moisture', '1525818', NULL, 14.59898000, 120.98520000, '2026-09-26 11:25:28');

-- Dumping structure for table sagipfarmer.readings
CREATE TABLE IF NOT EXISTS `readings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `plot_id` int NOT NULL,
  `value` decimal(7,2) NOT NULL,
  `reading_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `plot_id` (`plot_id`),
  CONSTRAINT `readings_ibfk_1` FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table sagipfarmer.readings: ~16 rows (approximately)
INSERT IGNORE INTO `readings` (`id`, `plot_id`, `value`, `reading_time`) VALUES
	(1, 2, 45.00, '2026-09-30 12:46:24'),
	(2, 1, 30.00, '2026-10-02 13:08:16'),
	(3, 1, 30.00, '2026-10-02 13:09:07'),
	(4, 1, 30.00, '2026-10-02 13:09:23'),
	(5, 1, 30.00, '2026-10-02 13:09:54'),
	(6, 1, 30.00, '2026-10-02 13:24:53'),
	(7, 1, 30.00, '2026-10-02 13:29:26'),
	(8, 1, 30.00, '2026-10-02 13:39:46'),
	(9, 1, 30.00, '2026-10-02 13:41:21'),
	(10, 1, 30.00, '2026-10-02 13:48:12'),
	(11, 2, 3500.00, '2026-10-25 13:19:34'),
	(12, 1, 1200.00, '2026-10-25 13:19:36'),
	(13, 2, 3500.00, '2026-10-25 13:34:58'),
	(14, 1, 300.00, '2026-10-25 13:34:58'),
	(15, 2, 3500.00, '2026-10-25 13:35:32'),
	(16, 1, 100.00, '2026-10-25 13:35:32'),
	(17, 2, 3500.00, '2026-10-25 13:42:21'),
	(18, 1, 100.00, '2026-10-25 13:42:23');

-- Dumping structure for table sagipfarmer.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(120) COLLATE utf8mb4_general_ci NOT NULL,
  `contact_number` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `full_address` text COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(60) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('user','admin') COLLATE utf8mb4_general_ci DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table sagipfarmer.users: ~2 rows (approximately)
INSERT IGNORE INTO `users` (`id`, `full_name`, `contact_number`, `full_address`, `username`, `password`, `role`, `created_at`) VALUES
	(1, 'System Administrator', '09171234567', 'SAGIPfarmer HQ', 'admin', '$2y$10$PSiKOGjO6pbyDlRprrkZReFTyP0o5HmE2VTDH6JxA6hHqar43Hktm', 'admin', '2026-09-24 07:38:39'),
	(2, 'Sample Farmer', '09998682564', 'Cabagan isabela', 'sample123', '$2y$10$PSiKOGjO6pbyDlRprrkZReFTyP0o5HmE2VTDH6JxA6hHqar43Hktm', 'user', '2026-09-24 12:15:32');

-- Dumping structure for table sagipfarmer.user_devices
CREATE TABLE IF NOT EXISTS `user_devices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `fcm_token` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `platform` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'android',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_token` (`user_id`,`fcm_token`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_user_devices_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table sagipfarmer.user_devices: ~0 rows (approximately)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
