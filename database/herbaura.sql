-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 06, 2025 at 02:04 PM
-- Server version: 8.0.39
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `herbaura`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
CREATE TABLE IF NOT EXISTS `addresses` (
  `address_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `address_type` enum('home','work','billing','shipping','other') DEFAULT 'home',
  `street` varchar(255) NOT NULL,
  `apartment` varchar(255) DEFAULT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(50) DEFAULT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(50) NOT NULL DEFAULT 'United States',
  `is_default` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`address_id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`address_id`, `user_id`, `address_type`, `street`, `apartment`, `city`, `state`, `postal_code`, `country`, `is_default`, `created_at`, `updated_at`) VALUES
(4, 5, 'home', 'senevirathna niwasa', NULL, 'badulla', 'uva', '10234', 'Sri Lanka', 1, '2025-03-29 06:59:34', '2025-03-29 06:59:34');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `parent_id` int DEFAULT NULL,
  PRIMARY KEY (`category_id`),
  KEY `fk_parent_category` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=114 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `description`, `created_at`, `updated_at`, `parent_id`) VALUES
(66, 'Herbal Supplements', 'Organic herbal supplements for health and wellness', '2025-04-03 17:02:01', '2025-04-03 17:02:01', NULL),
(67, 'Skincare', 'Natural Ayurvedic skincare solutions', '2025-04-03 17:02:01', '2025-04-03 17:02:01', NULL),
(68, 'Essential Oils', 'Pure and therapeutic-grade essential oils', '2025-04-03 17:02:01', '2025-04-03 17:02:01', NULL),
(69, 'Herbal Teas', 'Traditional Ayurvedic herbal teas for wellness', '2025-04-03 17:02:01', '2025-04-03 17:02:01', NULL),
(70, 'Hair & Scalp Care', 'Nourishing herbal solutions for hair health', '2025-04-03 17:02:01', '2025-04-03 17:02:01', NULL),
(71, 'Pain Relief & Therapy', 'Ayurvedic solutions for pain relief and muscle care', '2025-04-03 17:02:01', '2025-04-03 17:02:01', NULL),
(72, 'Oral Care', 'Herbal oral hygiene products', '2025-04-03 17:02:01', '2025-04-03 17:02:01', NULL),
(73, 'Detox & Cleansing', 'Herbal detox solutions for a healthy body', '2025-04-03 17:02:01', '2025-04-03 17:02:01', NULL),
(74, 'Sleep & Relaxation', 'Natural remedies for better sleep and relaxation', '2025-04-03 17:02:01', '2025-04-03 17:02:01', NULL),
(75, 'Women\'s Wellness', 'Ayurvedic care products for women?s health', '2025-04-03 17:02:01', '2025-04-03 17:07:51', NULL),
(76, 'Men\'s Wellness', 'Ayurvedic products for men?s health and grooming', '2025-04-03 17:02:01', '2025-04-03 17:08:26', NULL),
(77, 'Baby & Kids Wellness', 'Gentle and safe herbal products for babies and kids', '2025-04-03 17:02:01', '2025-04-03 17:02:01', NULL),
(78, 'Immunity Boosters', 'Enhance your immune system naturally', '2025-04-03 17:07:24', '2025-04-03 17:07:24', 66),
(79, 'Digestive Health', 'Improve digestion with natural herbs', '2025-04-03 17:07:24', '2025-04-03 17:07:24', 66),
(80, 'Stress & Anxiety Relief', 'Relax with Ayurvedic herbs', '2025-04-03 17:07:24', '2025-04-03 17:07:24', 66),
(81, 'Face Care', 'Natural skincare for radiant skin', '2025-04-03 17:07:24', '2025-04-03 17:07:24', 67),
(82, 'Body Care', 'Ayurvedic solutions for smooth and healthy skin', '2025-04-03 17:07:24', '2025-04-03 17:07:24', 67),
(83, 'Hair & Scalp Treatments', 'Herbal solutions for scalp health', '2025-04-03 17:07:24', '2025-04-03 17:07:24', 67),
(84, 'Aromatherapy', 'Essential oils for relaxation and stress relief', '2025-04-03 17:07:24', '2025-04-03 17:07:24', 68),
(85, 'Massage Oils', 'Herbal oils for therapeutic massages', '2025-04-03 17:07:24', '2025-04-03 17:07:24', 68),
(86, 'Medicinal Oils', 'Traditional Ayurvedic medicinal oils', '2025-04-03 17:07:24', '2025-04-03 17:07:24', 68),
(87, 'Detox & Cleansing Teas', 'Tea blends for natural detoxification', '2025-04-03 17:07:24', '2025-04-03 17:07:24', 69),
(88, 'Relaxation & Sleep Teas', 'Herbal teas to improve sleep quality', '2025-04-03 17:07:24', '2025-04-03 17:07:24', 69),
(89, 'Immunity Boosting Teas', 'Support your immune system with herbal blends', '2025-04-03 17:07:24', '2025-04-03 17:07:24', 69),
(90, 'Herbal Shampoos', 'Ayurvedic shampoos for strong hair', '2025-04-03 17:07:24', '2025-04-03 17:07:24', 70),
(91, 'Hair Oils', 'Nourishing oils for scalp and hair health', '2025-04-03 17:07:24', '2025-04-03 17:07:24', 70),
(92, 'Hair Masks', 'Deep conditioning herbal treatments for hair', '2025-04-03 17:07:24', '2025-04-03 17:07:24', 70),
(93, 'Muscle & Joint Pain Relief', 'Herbal solutions for pain management', '2025-04-03 17:07:24', '2025-04-03 17:07:24', 71),
(94, 'Ayurvedic Balms', 'Traditional balms for pain relief', '2025-04-03 17:07:24', '2025-04-03 17:07:24', 71),
(95, 'Therapeutic Massage Oils', 'Pain-relieving oils for massages', '2025-04-03 17:07:24', '2025-04-03 17:07:24', 71),
(96, 'Herbal Toothpaste', 'Fluoride-free natural toothpaste', '2025-04-03 17:07:24', '2025-04-03 17:07:24', 72),
(97, 'Mouthwashes', 'Herbal rinses for fresh breath and oral health', '2025-04-03 17:07:24', '2025-04-03 17:07:24', 72),
(98, 'Gum Care', 'Natural solutions for healthy gums', '2025-04-03 17:07:24', '2025-04-03 17:07:24', 72),
(99, 'Liver Detox', 'Herbal supplements for liver cleansing', '2025-04-03 17:07:24', '2025-04-03 17:07:24', 73),
(100, 'Full Body Detox', 'Comprehensive Ayurvedic detox solutions', '2025-04-03 17:07:24', '2025-04-03 17:07:24', 73),
(101, 'Skin Detox', 'Cleansing herbs for clear and healthy skin', '2025-04-03 17:07:24', '2025-04-03 17:07:24', 73),
(102, 'Sleep Aid Teas', 'Herbal teas to promote restful sleep', '2025-04-03 17:07:25', '2025-04-03 17:07:25', 74),
(103, 'Relaxing Essential Oils', 'Aromatherapy oils for better sleep', '2025-04-03 17:07:25', '2025-04-03 17:07:25', 74),
(104, 'Stress Relief Supplements', 'Herbal solutions to ease stress', '2025-04-03 17:07:25', '2025-04-03 17:07:25', 74),
(105, 'Menstrual Health', 'Herbal remedies for menstrual support', '2025-04-03 17:07:25', '2025-04-03 17:07:25', 75),
(106, 'Prenatal & Postnatal Care', 'Ayurvedic wellness for pregnancy and motherhood', '2025-04-03 17:07:25', '2025-04-03 17:07:25', 75),
(107, 'Hormonal Balance', 'Natural solutions for hormonal health', '2025-04-03 17:07:25', '2025-04-03 17:07:25', 75),
(108, 'Beard & Grooming', 'Herbal care for men?s grooming', '2025-04-03 17:07:25', '2025-04-03 17:07:25', 76),
(109, 'Vitality & Energy', 'Ayurvedic supplements for energy and stamina', '2025-04-03 17:07:25', '2025-04-03 17:07:25', 76),
(110, 'Stress & Focus', 'Natural remedies for mental clarity and stress relief', '2025-04-03 17:07:25', '2025-04-03 17:07:25', 76),
(111, 'Baby Massage Oils', 'Gentle Ayurvedic oils for baby massage', '2025-04-03 17:07:25', '2025-04-03 17:07:25', 77),
(112, 'Herbal Bath Products', 'Natural and safe bath products for babies', '2025-04-03 17:07:25', '2025-04-03 17:07:25', 77),
(113, 'Immune Support', 'Ayurvedic immune boosters for children', '2025-04-03 17:07:25', '2025-04-03 17:07:25', 77);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `status_description` text COMMENT 'Detailed description of the current order status',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','completed','failed') DEFAULT 'pending',
  `shipping_address` varchar(255) DEFAULT NULL,
  `billing_address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `productimages`
--

DROP TABLE IF EXISTS `productimages`;
CREATE TABLE IF NOT EXISTS `productimages` (
  `image_id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`image_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `product_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int NOT NULL,
  `category_id` int DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `images` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`product_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `description`, `price`, `stock_quantity`, `category_id`, `image_url`, `images`, `created_at`, `updated_at`) VALUES
(1, 'Estroven® Complete Multi-Symptom Menopause Relief', 'Contains clinically proven rhapontic rhubarb, providing faster menopause relief*. It offers up to a 90% reduction in hot flashes and night sweats*▲, making it an effective solution for all stages of menopause. Available in both 4-week and 2-month supplies, this product helps ease the discomforts of menopause with proven results.', 25.00, 50, 83, NULL, '[\"946f35c62e067a338aaadbbe1287048e.jpg\",\"2ccbb90187d6daf24e4d39f23f452c12.jpg\",\"354859c593084c09cf8bb58680566b26.jpg\",\"b5f1ae229455b1f2a647297b4e915909.jpg\"]', '2025-03-29 09:58:32', '2025-04-03 17:09:19'),
(2, 'California Gold Nutrition', 'This product is 100% authentic, with a best-by date of 03/2028. It was first available in August 2015 and has a shipping weight of 0.25 kg. The product code is CGN-01033, and its UPC is 898220010332. The package contains 205 g, with dimensions of 16.1 x 15.2 x 5.2 cm and a weight of 0.22 kg. It is iTested Verified, ensuring quality and authenticity.', 20.00, 50, 78, NULL, '[\"06a9e90e9356a1d73887cbb2e52edc7f.jpg\",\"a4b2d57452a1785ac4be01592f65960c.jpg\",\"c91546ce5964e03e574695f27ef0ff0f.jpg\",\"081c133fc55d5167ce44cdefdb3fda9c.jpg\",\"4929421a4a7ac070aae9420997b5fbc4.jpg\"]', '2025-03-29 10:06:40', '2025-04-03 17:09:06'),
(3, 'Hela Medicinal Yakinaran Drink', 'A traditional Ayurvedic beverage formulated to enhance immunity and overall health.', 5.00, 50, 78, NULL, '[\"a33ac267abf5ee1820665db839b5ffc8.png\",\"9f90af286c4f04e2386740c5cb5ae36a.jpg\"]', '2025-04-03 17:35:22', '2025-04-03 17:35:22');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `zip_code` varchar(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `role`, `first_name`, `last_name`, `phone_number`, `address`, `city`, `state`, `country`, `zip_code`, `created_at`, `updated_at`) VALUES
(5, 'admin', 'admin@gmail.com', '$2y$10$CE5RMXh4INDxjk2arzBrNurLBz87OD3ZjtAsYqJXj7FDbpZ6iQXse', 'admin', 'Navodya', 'Hewage', '0701382326', NULL, NULL, NULL, NULL, NULL, '2025-03-29 06:59:34', '2025-03-29 10:28:19');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `fk_user_address` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_parent_category` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `productimages`
--
ALTER TABLE `productimages`
  ADD CONSTRAINT `productimages_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
