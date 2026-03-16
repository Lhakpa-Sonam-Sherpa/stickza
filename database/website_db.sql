-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 16, 2026 at 11:26 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.0.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `website_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Cute & Animals'),
(2, 'Retro'),
(3, 'Nature & Travel'),
(4, 'Meme'),
(5, 'Cartoon');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `phone_no` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `first_name`, `last_name`, `email`, `password`, `address`, `city`, `phone_no`, `created_at`, `is_admin`) VALUES
(1, 'lhakpa', 'sherpa', 'soamsrp.653@gmail.com', '$2y$10$fndIm7jfHvm9EUEs2UGBWup2Wv52ZsjkcD41u18FKTuTNIyRJ1dTG', 'Witz Academy, Tinchuli, Kathmandu', 'Kathmandu', '9803865676', '2025-11-10 05:48:18', 0),
(2, 'Test', 'One', 'test@gmail.com', '$2y$10$ZqdoZ//JjpMUEfcpyZdb0OLqozvas3dndrWRB3piZ0rZMkIGDSO/e', 'one_addr', 'one_city', '9888228833', '2025-11-11 04:30:20', 0),
(3, 'one', 'one', 'one@gmail.com', '$2y$10$Khq29hZOewzpwp7KcMYwduL5h.u58ekrRuAwHkSZ6ksSld2/7nORy', 'one_addr', 'one_city', '9834923983', '2025-11-11 16:45:26', 0),
(4, 'Test', 'Three', 'three@gmail.com', '$2y$10$OxYqPMa4k8/DnkZOEIUfFOJZQdDSYMNfZ9iFsxf9WOHU/bmu8s1sK', 'three_address', 'three_city', '9822774422', '2025-11-20 08:17:12', 0),
(5, 'example', 'example_lastn', 'example@gmail.com', '$2y$10$Y/4oUZFBG72acD3p/fdgxO/rQmd7jmpT8A.OCklYrVa9Y8emh4fQi', 'example', 'exam_cty', '9811223344', '2025-12-14 03:50:30', 0),
(6, 'example2', 'example_lastn', 'example2@gmail.com', '$2y$10$alqJ1pFG8ycUA2cYpoecVe/x.16W3LQKF48CizYkRz6L77./Ui5tK', 'example', 'exam_cty', '9811223344', '2025-12-14 03:56:34', 1),
(7, 'example3', 'example_lastn', 'example3@gmail.com', '$2y$10$RdT.2/5rGmVtp5f67YN78OVD9UScM62NrPJxq3/1EoBnABp3cgnEe', 'example3_addr', 'exam_cty', '9811223343', '2025-12-14 03:58:15', 0),
(8, 'example4', 'example_lastn', 'example4@gmail.com', '$2y$10$AsySWs/Zv.gzMwNnGyzlFOO64D6cDbuGGbO3TWOU4sXBJyms1B1Eu', 'example', 'exam_cty', '9811223345', '2025-12-14 04:00:02', 0),
(12, '1234', '5678', '1234@gmail.com', '$2y$10$f7NMVpIbQkchQnBB0uBDjuA/j0tkDAw3oT9uIL7NjdpJQvHVd58Ge', 'example', 'exam_cty', '9811223345', '2026-02-08 03:31:20', 0),
(13, 'example5', 'example_lastn', 'example5@gmail.com', '$2y$10$xXWMsspR9HStyBcCfmMgDelbzx0z79pTFerRt8.oql9LvTLTWFoYu', 'example', 'exam_cty', '9811223347', '2026-03-10 21:42:35', 0),
(14, 'Mano', 'Man', 's09.mano@gmail.com', '$2y$10$8fwbTI.XqLf7eG648KsJ4uC0zQDretaW9JbEWJl1j0XvhYH.4ePre', 'Faika, Kapan', 'Kathmandu', '9823764567', '2026-03-11 04:06:14', 0),
(15, 'example6', 'example_lastn', 'example6@gmail.com', '$2y$10$QR7wiXx670iyfbX3qCmFue71l8eXQCPInEcITi4rg/tgRhJThSp0S', 'example', 'exam_cty', '9811223366', '2026-03-12 19:01:48', 0);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('new','read','replied') NOT NULL DEFAULT 'new'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `name`, `email`, `message`, `submitted_at`, `status`) VALUES
(1, 'example3 example_lastn', 'example3@gmail.com', 'This feedback is from Example-3', '2026-03-10 21:29:23', 'read'),
(2, 'example5 example_lastn', 'example5@gmail.com', 'Cyber Panda sticker is out of stock !', '2026-03-10 21:45:43', 'read');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_status` varchar(255) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `total_amount`, `order_status`, `order_date`, `updated_at`) VALUES
(1, 3, '425.50', 'pending', '2025-11-11 18:24:21', '2026-03-10 23:07:52'),
(2, 3, '640.00', 'pending', '2025-11-12 03:54:14', '2026-03-10 23:07:52'),
(3, 3, '270.00', 'pending', '2025-11-13 12:20:01', '2026-03-10 23:07:52'),
(4, 3, '4200.00', 'pending', '2025-11-13 17:20:21', '2026-03-10 23:07:52'),
(5, 3, '90.00', 'pending', '2025-11-13 17:34:05', '2026-03-10 23:07:52'),
(6, 2, '25.50', 'pending', '2025-11-14 01:23:49', '2026-03-10 23:07:52'),
(7, 2, '100.00', 'pending', '2025-11-14 01:25:24', '2026-03-10 23:07:52'),
(8, 3, '13200.00', 'shipped', '2025-11-14 13:48:13', '2026-03-12 11:46:36'),
(9, 3, '150.00', 'pending', '2025-11-14 13:52:20', '2026-03-10 23:07:52'),
(10, 3, '1650.00', 'pending', '2025-11-14 13:53:02', '2026-03-10 23:07:52'),
(11, 3, '450.00', 'pending', '2025-11-14 13:58:50', '2026-03-10 23:07:52'),
(12, 2, '4050.00', 'shipped', '2025-11-14 14:01:23', '2026-03-12 11:45:56'),
(13, 2, '1500.00', 'shipped', '2025-11-14 14:03:10', '2026-03-12 11:44:18'),
(14, 2, '1350.00', 'pending', '2025-11-14 14:04:54', '2026-03-10 23:07:52'),
(15, 3, '90.00', 'pending', '2025-11-16 02:12:13', '2026-03-10 23:07:52'),
(16, 3, '3750.00', 'pending', '2025-11-16 03:28:23', '2026-03-10 23:07:52'),
(17, 2, '300.00', 'pending', '2025-11-16 03:29:23', '2026-03-10 23:07:52'),
(18, 3, '240.00', 'pending', '2025-11-17 02:45:33', '2026-03-10 23:07:52'),
(19, 2, '1300.00', 'shipped', '2025-11-17 03:51:52', '2026-03-10 23:07:52'),
(20, 3, '300.00', 'pending', '2025-11-18 11:24:49', '2026-03-10 23:07:52'),
(21, 2, '200.00', 'pending', '2025-11-20 04:05:26', '2026-03-10 23:07:52'),
(22, 3, '2.50', 'pending', '2025-12-13 15:55:42', '2026-03-10 23:07:52'),
(23, 3, '1.25', 'pending', '2025-12-13 16:39:07', '2026-03-10 23:07:52'),
(24, 3, '5.00', 'pending', '2025-12-13 18:02:10', '2026-03-10 23:07:52'),
(25, 3, '3.50', 'pending', '2025-12-14 03:20:55', '2026-03-10 23:07:52'),
(26, 3, '2.00', 'pending', '2025-12-14 03:29:16', '2026-03-10 23:07:52'),
(27, 7, '18.75', 'pending', '2025-12-14 04:12:09', '2026-03-10 23:07:52'),
(28, 3, '2.50', 'pending', '2025-12-14 04:57:40', '2026-03-10 23:07:52'),
(29, 7, '2.50', 'pending', '2025-12-16 18:33:01', '2026-03-10 23:07:52'),
(30, 7, '7.50', 'pending', '2025-12-17 01:34:00', '2026-03-10 23:07:52'),
(31, 7, '3.00', 'pending', '2026-01-10 12:17:19', '2026-03-10 23:07:52'),
(32, 7, '4.00', 'pending', '2026-02-07 10:44:24', '2026-03-10 23:07:52'),
(33, 8, '12.50', 'pending', '2026-02-07 15:57:34', '2026-03-10 23:07:52'),
(34, 12, '700.00', 'pending', '2026-02-08 03:33:51', '2026-03-10 23:07:52'),
(35, 12, '125.00', 'pending', '2026-02-08 03:35:06', '2026-03-10 23:07:52'),
(36, 7, '250.00', 'shipped', '2026-02-08 03:40:20', '2026-03-12 11:45:40'),
(37, 8, '845.00', 'processing', '2026-02-09 08:28:02', '2026-03-10 23:07:52'),
(38, 6, '550.00', 'processing', '2026-02-16 03:56:28', '2026-03-10 23:07:52'),
(39, 7, '250.00', 'pending', '2026-03-10 21:35:50', '2026-03-10 23:07:52'),
(40, 7, '90.00', 'pending', '2026-03-10 21:37:10', '2026-03-10 23:07:52'),
(41, 13, '45.00', 'pending', '2026-03-10 23:22:53', '2026-03-10 23:23:58'),
(42, 14, '45.00', 'cancelled', '2026-03-11 04:07:29', '2026-03-11 04:08:18'),
(43, 7, '250.00', 'pending', '2026-03-12 10:48:06', '2026-03-12 10:48:06'),
(44, 7, '1450.00', 'pending', '2026-03-12 11:17:53', '2026-03-12 11:17:53'),
(45, 13, '125.00', 'processing', '2026-03-12 22:02:40', '2026-03-14 18:04:05'),
(46, 1, '125.00', 'pending', '2026-03-14 18:05:09', '2026-03-14 18:05:09'),
(47, 1, '375.00', 'pending', '2026-03-14 19:35:16', '2026-03-14 19:35:16'),
(48, 1, '40.00', 'pending', '2026-03-14 19:41:24', '2026-03-14 19:41:24'),
(49, 8, '237.50', 'pending', '2026-03-14 22:44:42', '2026-03-14 22:44:42'),
(50, 7, '40.00', 'pending', '2026-03-14 22:49:01', '2026-03-14 22:49:01');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 4, 1, '25.50'),
(2, 1, 3, 1, '100.00'),
(3, 1, 12, 2, '150.00'),
(4, 2, 11, 6, '90.00'),
(5, 2, 3, 1, '100.00'),
(6, 3, 11, 3, '90.00'),
(7, 4, 12, 28, '150.00'),
(8, 5, 11, 1, '90.00'),
(9, 6, 4, 1, '25.50'),
(10, 7, 10, 1, '100.00'),
(11, 8, 12, 88, '150.00'),
(12, 9, 12, 1, '150.00'),
(13, 10, 12, 11, '150.00'),
(14, 11, 12, 3, '150.00'),
(15, 12, 12, 27, '150.00'),
(16, 13, 12, 10, '150.00'),
(17, 14, 12, 9, '150.00'),
(18, 15, 11, 1, '90.00'),
(19, 16, 12, 25, '150.00'),
(20, 17, 12, 2, '150.00'),
(21, 18, 11, 1, '90.00'),
(22, 18, 12, 1, '150.00'),
(23, 19, 3, 12, '100.00'),
(24, 19, 10, 1, '100.00'),
(25, 20, 12, 2, '150.00'),
(26, 21, 10, 2, '100.00'),
(27, 22, 11, 1, '2.50'),
(28, 23, 12, 1, '1.25'),
(29, 24, 11, 2, '2.50'),
(30, 25, 11, 1, '2.50'),
(31, 25, 3, 1, '1.00'),
(32, 26, 9, 1, '2.00'),
(33, 27, 10, 15, '1.25'),
(34, 28, 11, 1, '2.50'),
(35, 29, 11, 1, '2.50'),
(36, 30, 11, 3, '2.50'),
(37, 31, 4, 3, '1.00'),
(38, 32, 3, 4, '1.00'),
(39, 33, 11, 1, '2.50'),
(40, 33, 9, 5, '2.00'),
(41, 34, 10, 4, '125.00'),
(42, 34, 9, 1, '200.00'),
(43, 35, 10, 1, '125.00'),
(44, 36, 11, 1, '250.00'),
(45, 37, 11, 2, '250.00'),
(46, 37, 3, 1, '45.00'),
(47, 37, 4, 1, '100.00'),
(48, 37, 9, 1, '200.00'),
(49, 38, 9, 1, '200.00'),
(50, 38, 4, 1, '100.00'),
(51, 38, 11, 1, '250.00'),
(52, 39, 11, 1, '250.00'),
(53, 40, 3, 2, '45.00'),
(54, 41, 3, 1, '45.00'),
(55, 42, 3, 1, '45.00'),
(56, 43, 11, 1, '250.00'),
(57, 44, 11, 1, '250.00'),
(58, 44, 9, 6, '200.00'),
(59, 45, 10, 1, '125.00'),
(60, 46, 10, 1, '125.00'),
(61, 47, 10, 1, '125.00'),
(62, 47, 11, 1, '250.00'),
(63, 48, 3, 1, '40.00'),
(64, 49, 11, 1, '237.50'),
(65, 50, 3, 1, '40.00');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `category_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `discount_percent` decimal(5,2) DEFAULT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `image_url`, `stock_quantity`, `category_id`, `created_at`, `discount_percent`, `discount_price`) VALUES
(3, 'Apple', 'Simple smiley Apple :)', '45.00', 'apple.jpeg', 15, 5, '2025-11-09 16:29:50', NULL, '40.00'),
(4, 'Coffee Time', 'A cup of coffee :)', '100.00', 'coffeeTime.jpeg', 13, 5, '2025-11-09 16:29:50', '4.00', NULL),
(9, 'Galaxy Cat', 'A holographic sticker of a cosmic cat floating among stars - perfect for laptops and notebooks.', '200.00', 'galaxyCat.jpeg', 35, 1, '2025-11-11 17:50:52', NULL, NULL),
(10, 'Retro Cassette', 'Vintage cassette tape design for nostalgic music lovers. Water-resistant and durable.', '125.00', 'retroCassette.jpeg', 13, 2, '2025-11-11 17:50:52', NULL, NULL),
(11, 'Mountain Adventure', 'Minimalist mountain landscape sticker for travelers and adventurers. Matte finish.', '250.00', 'mountainAdventure.jpeg', 29, 3, '2025-11-11 17:50:52', '5.00', NULL),
(12, 'Cyber Panda', 'Futuristic panda design with neon highlights. Eye-catching and unique. Sticker', '125.00', 'cyberPanda.jpeg', 10, 1, '2025-11-11 17:50:52', NULL, '120.00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_customers_email` (`email`),
  ADD KEY `idx_customers_created` (`created_at`),
  ADD KEY `idx_customers_admin` (`is_admin`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `submitted_at` (`submitted_at`),
  ADD KEY `idx_feedback_status` (`status`),
  ADD KEY `idx_feedback_email` (`email`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `idx_orders_date` (`order_date`),
  ADD KEY `idx_orders_status` (`order_status`),
  ADD KEY `idx_orders_customer` (`customer_id`),
  ADD KEY `idx_orders_amount` (`total_amount`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_oi_order` (`order_id`),
  ADD KEY `idx_oi_product` (`product_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pr_token` (`token`),
  ADD KEY `idx_pr_email` (`email`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_products_stock` (`stock_quantity`),
  ADD KEY `idx_products_category` (`category_id`),
  ADD KEY `idx_products_price` (`price`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
