-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 23, 2025 at 10:46 AM
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
(7, 'uploads/IMG_680884c0baef17.68530848.jpg', 'Barong Tagalog ', 'Men', 'Detalye Barong Premium Collection', 23, 30000.00, 'qeqwewqe', 'XL', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-04-23 06:12:16'),
(8, 'uploads/IMG_680884dbb5d211.56464437.jpg', 'New Barong Tagalog ', 'Women', 'Filipiniana Gown & Dresses', 23, 30000.00, 'qeqwewq', 'S', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-04-23 06:12:43');

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
(1, 'admin', 'admin', 'Juan', 'Dela', 'C', '09433189055', 'Poblacion, San Ramon', '1234', 'Admin'),
(2, 'test', 'test', 'Juan', 'Dela', 'C', '0934324222', 'Poblacion, San Ramon', '1234', 'Customer');

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for table `products_tbl`
--
ALTER TABLE `products_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
