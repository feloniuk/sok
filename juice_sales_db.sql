-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 14, 2025 at 06:20 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `juice_sales_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `image`, `created_at`, `updated_at`) VALUES
(1, 'Фруктовые соки', 'Натуральные соки из свежих фруктов', 'categories/681a8f03e646f_photo_271048.jpg', '2025-04-24 23:50:26', '2025-05-06 22:36:51'),
(2, 'Овощные соки', 'Полезные соки из свежих овощей', 'categories/681a8ebb2bf83_food-vegetable-juices-01.jpg', '2025-04-24 23:50:26', '2025-05-06 22:35:39'),
(3, 'Смузи', 'Густые напитки из смеси фруктов и овощей', 'categories/681a8ef009cd8_400_0_1627396684-9944.jpg', '2025-04-24 23:50:26', '2025-05-06 22:36:32'),
(4, 'Детокс напитки', 'Очищающие соки для детоксикации организма', 'categories/681a8ea5b57c0_detox-green-smoothie-get-inspired-everyday-4-1024x683.jpg', '2025-04-24 23:50:26', '2025-05-06 22:35:17'),
(5, 'Органические соки', 'Соки из органических фруктов и овощей', 'categories/681a8ed502e0e_fffad1eae17a266cb1baf9994f551f7b.jpg', '2025-04-24 23:50:26', '2025-05-06 22:36:05'),
(6, 'Набір &quot;Все для всього&quot;', '', 'categories/681a948c87f31_700-nw.jpg', '2025-05-06 22:56:24', '2025-05-06 23:00:28');

-- --------------------------------------------------------

--
-- Table structure for table `data`
--

CREATE TABLE `data` (
  `ID` int(10) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `Parameter` decimal(10,2) NOT NULL,
  `Dates` varchar(17) NOT NULL DEFAULT '0000-00-00',
  `Times` varchar(17) NOT NULL DEFAULT '00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `data`
--

INSERT INTO `data` (`ID`, `Name`, `Parameter`, `Dates`, `Times`) VALUES
(3223, 'Температура пастерізації соку', '71.99', '22.05.2025', '16:34:59'),
(3224, 'Температура пастерізації соку', '71.98', '22.05.2025', '16:35:05'),
(3225, 'Температура пастерізації соку', '71.97', '22.05.2025', '16:35:11'),
(3226, 'Температура пастерізації соку', '71.98', '22.05.2025', '16:35:16'),
(3227, 'Температура пастерізації соку', '71.98', '22.05.2025', '16:35:21'),
(3228, 'Температура пастерізації соку', '71.99', '22.05.2025', '16:35:26'),
(3229, 'Температура пастерізації соку', '71.99', '22.05.2025', '16:35:31'),
(3230, 'Температура пастерізації соку', '72.00', '22.05.2025', '16:35:35'),
(3231, 'Температура пастерізації соку', '72.00', '22.05.2025', '16:35:39'),
(3232, 'Температура пастерізації соку', '72.01', '22.05.2025', '16:35:43'),
(3233, 'Температура пастерізації соку', '72.01', '22.05.2025', '16:35:47'),
(3176, 'Температура пастерізації соку', '50.00', '22.05.2025', '16:14:02'),
(3177, 'Температура пастерізації соку', '50.00', '22.05.2025', '16:20:05'),
(3178, 'Температура пастерізації соку', '50.00', '22.05.2025', '16:29:39'),
(3179, 'Температура пастерізації соку', '51.26', '22.05.2025', '16:31:11'),
(3180, 'Температура пастерізації соку', '56.75', '22.05.2025', '16:31:16'),
(3181, 'Температура пастерізації соку', '63.42', '22.05.2025', '16:31:21'),
(3182, 'Температура пастерізації соку', '69.82', '22.05.2025', '16:31:26'),
(3183, 'Температура пастерізації соку', '74.78', '22.05.2025', '16:31:31'),
(3184, 'Температура пастерізації соку', '78.03', '22.05.2025', '16:31:36'),
(3185, 'Температура пастерізації соку', '79.57', '22.05.2025', '16:31:41'),
(3186, 'Температура пастерізації соку', '79.55', '22.05.2025', '16:31:46'),
(3187, 'Температура пастерізації соку', '78.42', '22.05.2025', '16:31:52'),
(3188, 'Температура пастерізації соку', '76.61', '22.05.2025', '16:31:57'),
(3189, 'Температура пастерізації соку', '74.52', '22.05.2025', '16:32:02'),
(3190, 'Температура пастерізації соку', '72.60', '22.05.2025', '16:32:07'),
(3191, 'Температура пастерізації соку', '71.07', '22.05.2025', '16:32:12'),
(3192, 'Температура пастерізації соку', '70.05', '22.05.2025', '16:32:17'),
(3193, 'Температура пастерізації соку', '69.58', '22.05.2025', '16:32:23'),
(3194, 'Температура пастерізації соку', '69.60', '22.05.2025', '16:32:28'),
(3195, 'Температура пастерізації соку', '69.98', '22.05.2025', '16:32:33'),
(3196, 'Температура пастерізації соку', '70.56', '22.05.2025', '16:32:38'),
(3197, 'Температура пастерізації соку', '71.22', '22.05.2025', '16:32:43'),
(3198, 'Температура пастерізації соку', '71.82', '22.05.2025', '16:32:49'),
(3199, 'Температура пастерізації соку', '72.32', '22.05.2025', '16:32:54'),
(3200, 'Температура пастерізації соку', '72.63', '22.05.2025', '16:32:59'),
(3201, 'Температура пастерізації соку', '72.77', '22.05.2025', '16:33:04'),
(3202, 'Температура пастерізації соку', '72.76', '22.05.2025', '16:33:09'),
(3203, 'Температура пастерізації соку', '72.64', '22.05.2025', '16:33:14'),
(3204, 'Температура пастерізації соку', '72.45', '22.05.2025', '16:33:18'),
(3205, 'Температура пастерізації соку', '72.24', '22.05.2025', '16:33:23'),
(3206, 'Температура пастерізації соку', '72.05', '22.05.2025', '16:33:28'),
(3207, 'Температура пастерізації соку', '71.90', '22.05.2025', '16:33:34'),
(3208, 'Температура пастерізації соку', '71.80', '22.05.2025', '16:33:39'),
(3209, 'Температура пастерізації соку', '71.75', '22.05.2025', '16:33:45'),
(3210, 'Температура пастерізації соку', '71.76', '22.05.2025', '16:33:51'),
(3211, 'Температура пастерізації соку', '71.80', '22.05.2025', '16:33:56'),
(3212, 'Температура пастерізації соку', '71.86', '22.05.2025', '16:34:02'),
(3213, 'Температура пастерізації соку', '71.93', '22.05.2025', '16:34:06'),
(3214, 'Температура пастерізації соку', '71.99', '22.05.2025', '16:34:11'),
(3215, 'Температура пастерізації соку', '72.03', '22.05.2025', '16:34:15'),
(3216, 'Температура пастерізації соку', '72.07', '22.05.2025', '16:34:20'),
(3217, 'Температура пастерізації соку', '72.08', '22.05.2025', '16:34:25'),
(3218, 'Температура пастерізації соку', '72.08', '22.05.2025', '16:34:30'),
(3219, 'Температура пастерізації соку', '72.06', '22.05.2025', '16:34:35'),
(3220, 'Температура пастерізації соку', '72.04', '22.05.2025', '16:34:41'),
(3221, 'Температура пастерізації соку', '72.02', '22.05.2025', '16:34:46'),
(3222, 'Температура пастерізації соку', '72.00', '22.05.2025', '16:34:52');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `product_id`, `warehouse_id`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 50, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(2, 1, 2, 30, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(3, 1, 3, 20, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(4, 2, 1, 60, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(5, 2, 2, 40, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(6, 2, 3, 20, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(7, 3, 1, 40, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(8, 3, 2, 25, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(9, 3, 3, 15, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(10, 4, 1, 45, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(11, 4, 2, 30, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(12, 4, 3, 15, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(13, 5, 1, 55, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(14, 5, 2, 35, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(15, 5, 3, 20, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(16, 6, 1, 30, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(17, 6, 2, 25, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(18, 6, 3, 15, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(19, 7, 1, 30, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(20, 7, 2, 20, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(21, 7, 3, 10, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(22, 8, 1, 25, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(23, 8, 2, 15, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(24, 8, 3, 10, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(25, 9, 1, 20, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(26, 9, 2, 10, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(27, 9, 3, 10, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(28, 10, 1, 25, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(29, 10, 2, 10, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(30, 10, 3, 10, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(31, 11, 1, 30, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(32, 11, 2, 15, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(33, 11, 3, 10, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(34, 12, 1, 20, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(35, 12, 2, 10, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(36, 12, 3, 5, '2025-04-24 23:50:26', '2025-04-24 23:50:26');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_movements`
--

CREATE TABLE `inventory_movements` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `movement_type` enum('incoming','outgoing','adjustment') NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_movements`
--

INSERT INTO `inventory_movements` (`id`, `product_id`, `warehouse_id`, `quantity`, `movement_type`, `reference_id`, `reference_type`, `notes`, `created_by`, `created_at`) VALUES
(1, 1, 1, 100, 'incoming', NULL, NULL, 'Начальное поступление', 3, '2025-04-24 23:50:26'),
(2, 2, 1, 120, 'incoming', NULL, NULL, 'Начальное поступление', 3, '2025-04-24 23:50:26'),
(3, 3, 1, 80, 'incoming', NULL, NULL, 'Начальное поступление', 3, '2025-04-24 23:50:26'),
(4, 4, 1, 90, 'incoming', NULL, NULL, 'Начальное поступление', 3, '2025-04-24 23:50:26'),
(5, 5, 1, 110, 'incoming', NULL, NULL, 'Начальное поступление', 3, '2025-04-24 23:50:26'),
(6, 1, 1, -3, 'outgoing', 1, 'order', 'Списание по заказу ORD-001', 2, '2025-04-24 23:50:26'),
(7, 4, 1, -2, 'outgoing', 1, 'order', 'Списание по заказу ORD-001', 2, '2025-04-24 23:50:26'),
(8, 7, 1, -1, 'outgoing', 1, 'order', 'Списание по заказу ORD-001', 2, '2025-04-24 23:50:26'),
(9, 2, 1, -2, 'outgoing', 2, 'order', 'Списание по заказу ORD-002', 2, '2025-04-24 23:50:26'),
(10, 5, 1, -2, 'outgoing', 2, 'order', 'Списание по заказу ORD-002', 2, '2025-04-24 23:50:26'),
(11, 10, 1, -1, 'outgoing', 2, 'order', 'Списание по заказу ORD-002', 2, '2025-04-24 23:50:26'),
(12, 3, 1, -3, 'outgoing', 3, 'order', 'Списание по заказу ORD-003', 2, '2025-04-24 23:50:26'),
(13, 8, 1, -2, 'outgoing', 3, 'order', 'Списание по заказу ORD-003', 2, '2025-04-24 23:50:26'),
(14, 12, 1, -2, 'outgoing', 3, 'order', 'Списание по заказу ORD-003', 2, '2025-04-24 23:50:26'),
(15, 1, 2, 50, 'adjustment', NULL, NULL, 'Коррекция инвентаря', 3, '2025-04-24 23:50:26'),
(16, 2, 2, 60, 'adjustment', NULL, NULL, 'Коррекция инвентаря', 3, '2025-04-24 23:50:26'),
(17, 3, 1, -1, 'outgoing', 18, 'order', 'Списание по заказу ORD-20250503001', 12, '2025-05-03 13:47:52'),
(18, 1, 1, -1, 'outgoing', 19, 'order', 'Списание по заказу ORD-20250503002', 12, '2025-05-03 13:57:43'),
(19, 11, 1, -1, 'outgoing', 19, 'order', 'Списание по заказу ORD-20250503002', 12, '2025-05-03 13:57:43'),
(20, 12, 1, -2, 'outgoing', 20, 'order', 'Списание по заказу ORD-20250507001', 12, '2025-05-07 19:03:44'),
(21, 7, 1, -1, 'outgoing', 20, 'order', 'Списание по заказу ORD-20250507001', 12, '2025-05-07 19:03:44');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('credit_card','bank_transfer','cash_on_delivery') NOT NULL,
  `shipping_address` text NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `order_number`, `status`, `total_amount`, `payment_method`, `shipping_address`, `notes`, `created_at`, `updated_at`) VALUES
(1, 4, 'ORD-001', 'delivered', '539.94', 'credit_card', 'ул. Шевченко, 10, кв. 5, Киев', 'Доставить до 18:00', '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(2, 5, 'ORD-002', 'shipped', '359.96', 'bank_transfer', 'ул. Франко, 15, кв. 12, Львов', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(3, 6, 'ORD-003', 'processing', '879.92', 'cash_on_delivery', 'ул. Леси Украинки, 22, кв. 7, Днепр', 'Позвонить за час до доставки', '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(4, 7, 'ORD-004', 'pending', '449.95', 'credit_card', 'ул. Сагайдачного, 5, кв. 3, Киев', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(5, 8, 'ORD-005', 'delivered', '629.93', 'bank_transfer', 'ул. Хмельницкого, 8, кв. 15, Харьков', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(6, 4, 'ORD-006', 'delivered', '319.96', 'credit_card', 'ул. Шевченко, 10, кв. 5, Киев', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(7, 5, 'ORD-007', 'cancelled', '259.97', 'cash_on_delivery', 'ул. Франко, 15, кв. 12, Львов', 'Клиент отменил заказ', '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(8, 6, 'ORD-008', 'delivered', '419.96', 'credit_card', 'ул. Леси Украинки, 22, кв. 7, Днепр', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(9, 7, 'ORD-009', 'processing', '579.95', 'bank_transfer', 'ул. Сагайдачного, 5, кв. 3, Киев', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(10, 8, 'ORD-010', 'pending', '499.96', 'cash_on_delivery', 'ул. Хмельницкого, 8, кв. 15, Харьков', 'Предварительно согласовать время', '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(18, 12, 'ORD-20250503001', 'shipped', '99.99', 'cash_on_delivery', '123', '', '2025-05-03 13:47:52', '2025-05-06 22:29:16'),
(19, 12, 'ORD-20250503002', 'processing', '219.98', 'bank_transfer', '321', '', '2025-05-03 13:57:43', '2025-05-06 22:23:17'),
(20, 12, 'ORD-20250507001', 'cancelled', '409.97', 'credit_card', 'Testova 12', '', '2025-05-07 19:03:44', '2025-05-07 19:10:33');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `warehouse_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 3, '89.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(2, 1, 4, 2, '69.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(3, 1, 7, 1, '109.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(4, 2, 2, 2, '79.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(5, 2, 5, 2, '59.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(6, 2, 10, 1, '139.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(7, 3, 3, 3, '99.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(8, 3, 8, 2, '119.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(9, 3, 12, 2, '149.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(10, 4, 1, 1, '89.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(11, 4, 2, 1, '79.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(12, 4, 3, 1, '99.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(13, 4, 4, 1, '69.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(14, 4, 5, 1, '59.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(15, 4, 6, 1, '69.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(16, 5, 7, 2, '109.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(17, 5, 8, 2, '119.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(18, 5, 9, 1, '129.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(19, 5, 10, 1, '139.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(20, 6, 1, 2, '89.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(21, 6, 4, 2, '69.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(22, 7, 2, 1, '79.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(23, 7, 5, 3, '59.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(24, 8, 3, 2, '99.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(25, 8, 11, 1, '129.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(26, 8, 6, 1, '69.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(27, 9, 7, 1, '109.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(28, 9, 8, 1, '119.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(29, 9, 9, 1, '129.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(30, 9, 10, 1, '139.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(31, 9, 11, 1, '129.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(32, 10, 12, 2, '149.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(33, 10, 3, 2, '99.99', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(38, 18, 3, 1, '99.99', 1, '2025-05-03 13:47:52', '2025-05-03 13:47:52'),
(39, 19, 1, 1, '89.99', 1, '2025-05-03 13:57:43', '2025-05-03 13:57:43'),
(40, 19, 11, 1, '129.99', 1, '2025-05-03 13:57:43', '2025-05-03 13:57:43'),
(41, 20, 12, 2, '149.99', 1, '2025-05-07 19:03:44', '2025-05-07 19:03:44'),
(42, 20, 7, 1, '109.99', 1, '2025-05-07 19:03:44', '2025-05-07 19:03:44');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock_quantity`, `image`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Апельсиновый сок', 'Свежевыжатый сок из спелых апельсинов', '89.99', 99, 'products/681a919fba517_apelsyn3.jpg', 1, 1, '2025-04-24 23:50:26', '2025-05-06 22:47:59'),
(2, 1, 'Яблочный сок', 'Натуральный сок из сочных яблок', '79.99', 120, 'products/681a918381af6_10a37be413e24b34ac6afb3866cd08a8.jpg', 0, 1, '2025-04-24 23:50:26', '2025-05-06 22:47:31'),
(3, 1, 'Ананасовый сок', 'Экзотический сок из свежих ананасов', '99.99', 79, 'pineapple_juice.jpg', 1, 1, '2025-04-24 23:50:26', '2025-05-03 13:47:52'),
(4, 2, 'Морковный сок', 'Полезный сок из свежей моркови', '69.99', 90, 'carrot_juice.jpg', 0, 1, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(5, 2, 'Томатный сок', 'Классический сок из спелых томатов', '59.99', 110, 'tomato_juice.jpg', 0, 1, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(6, 2, 'Свекольный сок', 'Полезный сок из свежей свеклы', '69.99', 70, 'beet_juice.jpg', 0, 1, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(7, 3, 'Банановый смузи', 'Густой смузи из бананов и молока', '109.99', 59, 'banana_smoothie.jpg', 1, 1, '2025-04-24 23:50:26', '2025-05-07 19:03:44'),
(8, 3, 'Ягодный смузи', 'Вкусный смузи из свежих ягод', '119.99', 50, 'berry_smoothie.jpg', 1, 1, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(9, 4, 'Зеленый детокс', 'Очищающий напиток из зелени и овощей', '129.99', 40, 'products/681bb0740591a_3.jpg', 0, 1, '2025-04-24 23:50:26', '2025-05-07 19:11:48'),
(10, 4, 'Цитрусовый детокс', 'Детокс-напиток из цитрусовых и имбиря', '139.99', 45, 'products/681a9177808a8_pngtree-detox-water-infused-with-citrus-fruits-and-herbs-cleanse-rejuvenate-image_16373839.jpg', 1, 1, '2025-04-24 23:50:26', '2025-05-06 22:47:19'),
(11, 5, 'Органический яблочный сок', 'Сок из органических яблок', '129.99', 54, 'products/681a915631c94_10a37be413e24b34ac6afb3866cd08a8.jpg', 0, 1, '2025-04-24 23:50:26', '2025-05-06 22:46:46'),
(12, 5, 'Органический гранатовый сок', 'Сок из органических гранатов', '149.99', 33, 'products/680f9a5f0d274_volga.jpg', 1, 1, '2025-04-24 23:50:26', '2025-05-07 19:03:44');

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('percentage','fixed_amount') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `promotions`
--

INSERT INTO `promotions` (`id`, `name`, `description`, `discount_type`, `discount_value`, `start_date`, `end_date`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Летняя распродажа', 'Скидка на все фруктовые соки', 'percentage', '15.00', '2025-06-01', '2025-08-31', 1, 1, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(2, 'Черная пятница', 'Специальные цены на премиум продукты', 'percentage', '25.00', '2025-11-29', '2025-11-30', 0, 1, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(3, 'Акция дня', 'Фиксированная скидка на овощные соки', 'fixed_amount', '20.00', '2025-04-01', '2025-04-30', 1, 1, '2025-04-24 23:50:26', '2025-04-24 23:50:26');

-- --------------------------------------------------------

--
-- Table structure for table `promotion_products`
--

CREATE TABLE `promotion_products` (
  `promotion_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `promotion_products`
--

INSERT INTO `promotion_products` (`promotion_id`, `product_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(2, 7),
(2, 8),
(2, 12),
(3, 4),
(3, 5),
(3, 6);

-- --------------------------------------------------------

--
-- Table structure for table `sales_analytics`
--

CREATE TABLE `sales_analytics` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity_sold` int(11) NOT NULL DEFAULT 0,
  `revenue` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `profit` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales_analytics`
--

INSERT INTO `sales_analytics` (`id`, `date`, `product_id`, `quantity_sold`, `revenue`, `cost`, `profit`) VALUES
(1, '2024-11-01', 1, 12, '1079.88', '647.93', '431.95'),
(2, '2024-11-01', 2, 10, '799.90', '399.95', '399.95'),
(3, '2024-11-01', 3, 8, '799.92', '439.96', '359.96'),
(4, '2024-11-01', 4, 7, '489.93', '244.97', '244.96'),
(5, '2024-11-01', 5, 9, '539.91', '269.96', '269.95'),
(6, '2024-11-15', 1, 14, '1259.86', '755.92', '503.94'),
(7, '2024-11-15', 2, 11, '879.89', '439.95', '439.94'),
(8, '2024-11-15', 3, 9, '899.91', '494.95', '404.96'),
(9, '2024-11-15', 4, 8, '559.92', '279.96', '279.96'),
(10, '2024-11-15', 5, 10, '599.90', '299.95', '299.95'),
(11, '2024-12-01', 1, 18, '1619.82', '971.89', '647.93'),
(12, '2024-12-01', 2, 15, '1199.85', '599.93', '599.92'),
(13, '2024-12-01', 3, 12, '1199.88', '659.93', '539.95'),
(14, '2024-12-01', 4, 10, '699.90', '349.95', '349.95'),
(15, '2024-12-01', 5, 14, '839.86', '419.93', '419.93'),
(16, '2024-12-15', 1, 22, '1979.78', '1187.87', '791.91'),
(17, '2024-12-15', 2, 18, '1439.82', '719.91', '719.91'),
(18, '2024-12-15', 3, 16, '1599.84', '879.91', '719.93'),
(19, '2024-12-15', 4, 12, '839.88', '419.94', '419.94'),
(20, '2024-12-15', 5, 15, '899.85', '449.93', '449.92'),
(21, '2025-01-01', 6, 8, '559.92', '279.96', '279.96'),
(22, '2025-01-01', 7, 10, '1099.90', '549.95', '549.95'),
(23, '2025-01-01', 8, 9, '1079.91', '539.96', '539.95'),
(24, '2025-01-01', 9, 6, '779.94', '389.97', '389.97'),
(25, '2025-01-01', 10, 7, '979.93', '489.97', '489.96'),
(26, '2025-01-15', 6, 9, '629.91', '314.96', '314.95'),
(27, '2025-01-15', 7, 12, '1319.88', '659.94', '659.94'),
(28, '2025-01-15', 8, 10, '1199.90', '599.95', '599.95'),
(29, '2025-01-15', 9, 7, '909.93', '454.97', '454.96'),
(30, '2025-01-15', 10, 8, '1119.92', '559.96', '559.96'),
(31, '2025-02-01', 1, 15, '1349.85', '809.91', '539.94'),
(32, '2025-02-01', 2, 13, '1039.87', '519.94', '519.93'),
(33, '2025-02-01', 3, 10, '999.90', '549.95', '449.95'),
(34, '2025-02-01', 11, 7, '909.93', '454.97', '454.96'),
(35, '2025-02-01', 12, 5, '749.95', '374.98', '374.97'),
(36, '2025-02-15', 1, 16, '1439.84', '863.90', '575.94'),
(37, '2025-02-15', 2, 14, '1119.86', '559.93', '559.93'),
(38, '2025-02-15', 3, 12, '1199.88', '659.93', '539.95'),
(39, '2025-02-15', 11, 9, '1169.91', '584.96', '584.95'),
(40, '2025-02-15', 12, 6, '899.94', '449.97', '449.97'),
(41, '2025-03-01', 4, 14, '979.86', '489.93', '489.93'),
(42, '2025-03-01', 5, 16, '959.84', '479.92', '479.92'),
(43, '2025-03-01', 6, 10, '699.90', '349.95', '349.95'),
(44, '2025-03-01', 7, 8, '879.92', '439.96', '439.96'),
(45, '2025-03-01', 8, 7, '839.93', '419.97', '419.96'),
(46, '2025-03-15', 4, 15, '1049.85', '524.93', '524.92'),
(47, '2025-03-15', 5, 17, '1019.83', '509.92', '509.91'),
(48, '2025-03-15', 6, 11, '769.89', '384.95', '384.94'),
(49, '2025-03-15', 7, 9, '989.91', '494.96', '494.95'),
(50, '2025-03-15', 8, 8, '959.92', '479.96', '479.96'),
(51, '2025-04-01', 9, 9, '1169.91', '584.96', '584.95'),
(52, '2025-04-01', 10, 10, '1399.90', '699.95', '699.95'),
(53, '2025-04-01', 11, 8, '1039.92', '519.96', '519.96'),
(54, '2025-04-01', 12, 7, '1049.93', '524.97', '524.96'),
(55, '2025-04-15', 9, 10, '1299.90', '649.95', '649.95'),
(56, '2025-04-15', 10, 11, '1539.89', '769.95', '769.94'),
(57, '2025-04-15', 11, 9, '1169.91', '584.96', '584.95'),
(58, '2025-04-15', 12, 8, '1199.92', '599.96', '599.96'),
(59, '2025-05-03', 3, 1, '99.99', '50.00', '50.00'),
(60, '2025-05-03', 1, 1, '89.99', '45.00', '45.00'),
(61, '2025-05-03', 11, 1, '129.99', '65.00', '65.00'),
(62, '2025-05-07', 12, 2, '299.98', '149.99', '149.99'),
(63, '2025-05-07', 7, 1, '109.99', '55.00', '55.00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','sales_manager','warehouse_manager','customer') NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `first_name`, `last_name`, `phone`, `created_at`, `updated_at`) VALUES
(9, 'admin', 'admin@juicesales.com', '$2y$10$ULgI4nd7pdQC.4ysViuHnuW7xIxCX/W3Uy6DtKB3Cd8lZJyhST5Fu', 'admin', 'Admin', 'User', '+380501234567', '2025-04-28 14:36:45', '2025-04-28 14:39:52'),
(10, 'sales1', 'sales1@juicesales.com', '$2y$10$ULgI4nd7pdQC.4ysViuHnuW7xIxCX/W3Uy6DtKB3Cd8lZJyhST5Fu', 'sales_manager', 'Sales', 'Manager', '+380502345678', '2025-04-28 14:36:45', '2025-04-28 15:12:14'),
(11, 'warehouse1', 'warehouse1@juicesales.com', '$2y$10$ULgI4nd7pdQC.4ysViuHnuW7xIxCX/W3Uy6DtKB3Cd8lZJyhST5Fu', 'warehouse_manager', 'Warehouse', 'Manager', '+380503456789', '2025-04-28 14:36:45', '2025-04-28 15:12:16'),
(12, 'customer1', 'customer1@example.com', '$2y$10$ULgI4nd7pdQC.4ysViuHnuW7xIxCX/W3Uy6DtKB3Cd8lZJyhST5Fu', 'customer', 'John', 'Doe', '+380504567890', '2025-04-28 14:36:45', '2025-04-28 15:12:18'),
(13, 'customer2', 'customer2@example.com', '$2y$10$ULgI4nd7pdQC.4ysViuHnuW7xIxCX/W3Uy6DtKB3Cd8lZJyhST5Fu', 'customer', 'Jane', 'Smith', '+380505678901', '2025-04-28 14:36:45', '2025-04-28 15:12:20'),
(14, 'customer3', 'customer3@example.com', '$2y$10$ULgI4nd7pdQC.4ysViuHnuW7xIxCX/W3Uy6DtKB3Cd8lZJyhST5Fu', 'customer', 'Robert', 'Johnson', '+380506789012', '2025-04-28 14:36:45', '2025-04-28 15:12:22'),
(15, 'customer4', 'customer4@example.com', '$2y$10$ULgI4nd7pdQC.4ysViuHnuW7xIxCX/W3Uy6DtKB3Cd8lZJyhST5Fu', 'customer', 'Emily', 'Williams', '+380507890123', '2025-04-28 14:36:45', '2025-04-28 15:12:24'),
(16, 'customer5', 'customer5@example.com', '$2y$10$ULgI4nd7pdQC.4ysViuHnuW7xIxCX/W3Uy6DtKB3Cd8lZJyhST5Fu', 'customer', 'Michael', 'Brown', '+380508901234', '2025-04-28 14:36:45', '2025-04-28 15:12:26');

-- --------------------------------------------------------

--
-- Table structure for table `warehouses`
--

CREATE TABLE `warehouses` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `warehouses`
--

INSERT INTO `warehouses` (`id`, `name`, `address`, `manager_id`, `created_at`, `updated_at`) VALUES
(1, 'Основной склад', 'ул. Складская, 1, Киев', 3, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(2, 'Северный склад', 'ул. Северная, 10, Харьков', 3, '2025-04-24 23:50:26', '2025-04-24 23:50:26'),
(3, 'Южный склад', 'ул. Приморская, 25, Одесса', NULL, '2025-04-24 23:50:26', '2025-04-24 23:50:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `data`
--
ALTER TABLE `data`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Indexes for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `warehouse_id` (`warehouse_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `promotion_products`
--
ALTER TABLE `promotion_products`
  ADD PRIMARY KEY (`promotion_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `sales_analytics`
--
ALTER TABLE `sales_analytics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `manager_id` (`manager_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `data`
--
ALTER TABLE `data`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3234;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sales_analytics`
--
ALTER TABLE `sales_analytics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD CONSTRAINT `inventory_movements_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_movements_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_movements_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `promotions`
--
ALTER TABLE `promotions`
  ADD CONSTRAINT `promotions_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `promotion_products`
--
ALTER TABLE `promotion_products`
  ADD CONSTRAINT `promotion_products_ibfk_1` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `promotion_products_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales_analytics`
--
ALTER TABLE `sales_analytics`
  ADD CONSTRAINT `sales_analytics_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD CONSTRAINT `warehouses_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
