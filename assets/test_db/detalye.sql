-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 23, 2025 at 07:54 PM
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
-- Database: `detalye`
--

-- --------------------------------------------------------

--
-- Table structure for table `combined_orders_tbl`
--

CREATE TABLE `combined_orders_tbl` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(255) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `size` varchar(10) DEFAULT NULL,
  `custom_sizes` text DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('Pending','On Process','Delivered') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `combined_orders_tbl`
--

INSERT INTO `combined_orders_tbl` (`id`, `user_id`, `order_number`, `product_id`, `quantity`, `price`, `size`, `custom_sizes`, `total_amount`, `status`, `created_at`) VALUES
(4, 2, 'ORD-74C1CD68', 7, 2, 30000.00, 'L', '{\"custom_size\":\"23.5\",\"custom_collar\":\"11.65\",\"custom_shoulder\":\"345.3\",\"custom_chest\":\"32\",\"custom_waist\":\"234.5\",\"custom_hip\":\"11\",\"custom_cuff\":\"53\",\"sleeve_length\":\"35\",\"armhole\":\"422\",\"back_length\":\"342\"}', 60000.00, 'Delivered', '2025-04-23 16:19:52');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `date_sent` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `user_id`, `subject`, `message`, `date_sent`) VALUES
(1, 2, 'Detalye Barong Feedback', 'The UI is simple but super cool', '2025-04-24 01:07:09');

-- --------------------------------------------------------

--
-- Table structure for table `customer_add_cart_tbl`
--

CREATE TABLE `customer_add_cart_tbl` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `size` varchar(10) DEFAULT NULL,
  `custom_sizes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_sizes`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_add_cart_tbl`
--

INSERT INTO `customer_add_cart_tbl` (`id`, `user_id`, `product_id`, `quantity`, `size`, `custom_sizes`, `created_at`) VALUES
(22, 2, 7, 2, 'XL', NULL, '2025-04-23 16:46:07');

-- --------------------------------------------------------

--
-- Table structure for table `products_tbl`
--

CREATE TABLE `products_tbl` (
  `id` int(11) NOT NULL,
  `product_image` varchar(255) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_category` varchar(100) NOT NULL,
  `product_sub_category` varchar(100) DEFAULT NULL,
  `product_quantity` int(11) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `product_description` text DEFAULT NULL,
  `product_size` varchar(50) DEFAULT NULL,
  `custom_size_collar` varchar(50) DEFAULT NULL,
  `custom_size_shoulder` varchar(50) DEFAULT NULL,
  `custom_size_chest` varchar(50) DEFAULT NULL,
  `custom_size_waist` varchar(50) DEFAULT NULL,
  `custom_size_hips` varchar(50) DEFAULT NULL,
  `custom_size_cuff` varchar(50) DEFAULT NULL,
  `custom_size_sleeve_length` varchar(50) DEFAULT NULL,
  `custom_size_arm_hole` varchar(50) DEFAULT NULL,
  `custom_size_back_length` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products_tbl`
--

INSERT INTO `products_tbl` (`id`, `product_image`, `product_name`, `product_category`, `product_sub_category`, `product_quantity`, `product_price`, `product_description`, `product_size`, `custom_size_collar`, `custom_size_shoulder`, `custom_size_chest`, `custom_size_waist`, `custom_size_hips`, `custom_size_cuff`, `custom_size_sleeve_length`, `custom_size_arm_hole`, `custom_size_back_length`, `created_at`) VALUES
(7, 'uploads/IMG_680884c0baef17.68530848.jpg', 'Barong Tagalog ', 'Men', 'Detalye Barong Premium Collection', 14, 30000.00, 'qeqwewqe', 'XL', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-04-23 06:12:16'),
(8, 'uploads/IMG_680884dbb5d211.56464437.jpg', 'New Barong Tagalog ', 'Women', 'Filipiniana Gown & Dresses', 1, 30000.00, 'qeqwewq', 'S', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-04-23 06:12:43'),
(9, 'uploads/IMG_6808da3be87219.75025889.jpeg', 'Barong Pambata', 'Kids', 'For Kids', 0, 1500.00, 'Barong for kids for affordable price only.', 'S', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-04-23 12:16:59');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `middle_initial` char(1) DEFAULT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `complete_address` text DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Customer','Admin') DEFAULT 'Customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `first_name`, `last_name`, `middle_initial`, `contact_number`, `complete_address`, `password`, `role`) VALUES
(1, 'admin', 'admin@gmail.com', 'Juan', 'Dela', 'C', '09433189055', 'Poblacion, San Ramon', '1234', 'Admin'),
(2, 'test', 'test@gmail.com', 'Juan', 'Dela', 's', '0934324222', 'Poblacion, San Ramon', '1234', 'Customer');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `combined_orders_tbl`
--
ALTER TABLE `combined_orders_tbl`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `customer_add_cart_tbl`
--
ALTER TABLE `customer_add_cart_tbl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products_tbl`
--
ALTER TABLE `products_tbl`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `combined_orders_tbl`
--
ALTER TABLE `combined_orders_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customer_add_cart_tbl`
--
ALTER TABLE `customer_add_cart_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `products_tbl`
--
ALTER TABLE `products_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `combined_orders_tbl`
--
ALTER TABLE `combined_orders_tbl`
  ADD CONSTRAINT `combined_orders_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `combined_orders_tbl_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products_tbl` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD CONSTRAINT `contact_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_add_cart_tbl`
--
ALTER TABLE `customer_add_cart_tbl`
  ADD CONSTRAINT `customer_add_cart_tbl_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_add_cart_tbl_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products_tbl` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
