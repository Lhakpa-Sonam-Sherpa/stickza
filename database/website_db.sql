-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 09, 2026 at 06:00 AM
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
(1, 'lhakpa', 'sherpa', 'soamsrp.653@gmail.com', '$2y$10$NLOlV8RAwGecVOGi2pdxyuDSQlzheQrT9N9us6zBubSlMbuq30Ob.', 'Witz Academy, Tinchuli, Kathmandu', 'Kathmandu', '9803865676', '2025-11-10 05:48:18', 0),
(2, 'Test', 'One', 'test@gmail.com', '$2y$10$ZqdoZ//JjpMUEfcpyZdb0OLqozvas3dndrWRB3piZ0rZMkIGDSO/e', 'one_addr', 'one_city', '9888228833', '2025-11-11 04:30:20', 0),
(3, 'one', 'one', 'one@gmail.com', '$2y$10$Khq29hZOewzpwp7KcMYwduL5h.u58ekrRuAwHkSZ6ksSld2/7nORy', 'one_addr', 'one_city', '9834923983', '2025-11-11 16:45:26', 0),
(4, 'Test', 'Three', 'three@gmail.com', '$2y$10$OxYqPMa4k8/DnkZOEIUfFOJZQdDSYMNfZ9iFsxf9WOHU/bmu8s1sK', 'three_address', 'three_city', '9822774422', '2025-11-20 08:17:12', 0),
(5, 'example', 'example_lastn', 'example@gmail.com', '$2y$10$Y/4oUZFBG72acD3p/fdgxO/rQmd7jmpT8A.OCklYrVa9Y8emh4fQi', 'example', 'exam_cty', '9811223344', '2025-12-14 03:50:30', 0),
(6, 'example2', 'example_lastn', 'example2@gmail.com', '$2y$10$alqJ1pFG8ycUA2cYpoecVe/x.16W3LQKF48CizYkRz6L77./Ui5tK', 'example', 'exam_cty', '9811223344', '2025-12-14 03:56:34', 1),
(7, 'example3', 'example_lastn', 'example3@gmail.com', '$2y$10$RdT.2/5rGmVtp5f67YN78OVD9UScM62NrPJxq3/1EoBnABp3cgnEe', 'example', 'exam_cty', '9811223343', '2025-12-14 03:58:15', 0),
(8, 'example4', 'example_lastn', 'example4@gmail.com', '$2y$10$AsySWs/Zv.gzMwNnGyzlFOO64D6cDbuGGbO3TWOU4sXBJyms1B1Eu', 'example', 'exam_cty', '9811223345', '2025-12-14 04:00:02', 0),
(12, '1234', '5678', '1234@gmail.com', '$2y$10$f7NMVpIbQkchQnBB0uBDjuA/j0tkDAw3oT9uIL7NjdpJQvHVd58Ge', 'example', 'exam_cty', '9811223345', '2026-02-08 03:31:20', 0);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_status` varchar(255) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `total_amount`, `order_status`, `order_date`) VALUES
(1, 3, '425.50', 'pending', '2025-11-11 18:24:21'),
(2, 3, '640.00', 'pending', '2025-11-12 03:54:14'),
(3, 3, '270.00', 'pending', '2025-11-13 12:20:01'),
(4, 3, '4200.00', 'pending', '2025-11-13 17:20:21'),
(5, 3, '90.00', 'pending', '2025-11-13 17:34:05'),
(6, 2, '25.50', 'pending', '2025-11-14 01:23:49'),
(7, 2, '100.00', 'pending', '2025-11-14 01:25:24'),
(8, 3, '13200.00', 'delivered', '2025-11-14 13:48:13'),
(9, 3, '150.00', 'pending', '2025-11-14 13:52:20'),
(10, 3, '1650.00', 'pending', '2025-11-14 13:53:02'),
(11, 3, '450.00', 'pending', '2025-11-14 13:58:50'),
(12, 2, '4050.00', 'delivered', '2025-11-14 14:01:23'),
(13, 2, '1500.00', 'delivered', '2025-11-14 14:03:10'),
(14, 2, '1350.00', 'pending', '2025-11-14 14:04:54'),
(15, 3, '90.00', 'pending', '2025-11-16 02:12:13'),
(16, 3, '3750.00', 'pending', '2025-11-16 03:28:23'),
(17, 2, '300.00', 'pending', '2025-11-16 03:29:23'),
(18, 3, '240.00', 'pending', '2025-11-17 02:45:33'),
(19, 2, '1300.00', 'pending', '2025-11-17 03:51:52'),
(20, 3, '300.00', 'pending', '2025-11-18 11:24:49'),
(21, 2, '200.00', 'pending', '2025-11-20 04:05:26'),
(22, 3, '2.50', 'pending', '2025-12-13 15:55:42'),
(23, 3, '1.25', 'pending', '2025-12-13 16:39:07'),
(24, 3, '5.00', 'pending', '2025-12-13 18:02:10'),
(25, 3, '3.50', 'pending', '2025-12-14 03:20:55'),
(26, 3, '2.00', 'pending', '2025-12-14 03:29:16'),
(27, 7, '18.75', 'pending', '2025-12-14 04:12:09'),
(28, 3, '2.50', 'pending', '2025-12-14 04:57:40'),
(29, 7, '2.50', 'pending', '2025-12-16 18:33:01'),
(30, 7, '7.50', 'pending', '2025-12-17 01:34:00'),
(31, 7, '3.00', 'pending', '2026-01-10 12:17:19'),
(32, 7, '4.00', 'pending', '2026-02-07 10:44:24'),
(33, 8, '12.50', 'pending', '2026-02-07 15:57:34'),
(34, 12, '700.00', 'pending', '2026-02-08 03:33:51'),
(35, 12, '125.00', 'pending', '2026-02-08 03:35:06'),
(36, 7, '250.00', 'pending', '2026-02-08 03:40:20');

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
(44, 36, 11, 1, '250.00');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `image_url`, `stock_quantity`, `category_id`, `created_at`) VALUES
(3, 'Apple', 'Simple smiley Apple :)', '45.00', 'apple.jpeg', 21, 5, '2025-11-09 16:29:50'),
(4, 'Coffee Time', NULL, '100.00', 'coffeeTime.jpeg', 15, 5, '2025-11-09 16:29:50'),
(9, 'Galaxy Cat', 'A holographic sticker of a cosmic cat floating among stars - perfect for laptops and notebooks.', '200.00', 'galaxyCat.jpeg', 43, 1, '2025-11-11 17:50:52'),
(10, 'Retro Cassette', 'Vintage cassette tape design for nostalgic music lovers. Water-resistant and durable.', '125.00', 'retroCassette.jpeg', 16, 2, '2025-11-11 17:50:52'),
(11, 'Mountain Adventure', 'Minimalist mountain landscape sticker for travelers and adventurers. Matte finish.', '250.00', 'mountainAdventure.jpeg', 37, 3, '2025-11-11 17:50:52'),
(12, 'Cyber Panda', 'Futuristic panda design with neon highlights. Eye-catching and unique.', '125.00', 'cyberPanda.jpeg', 0, 1, '2025-11-11 17:50:52');

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
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
