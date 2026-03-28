-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 24, 2026 at 12:42 PM
-- Server version: 10.4.25-MariaDB
-- PHP Version: 7.4.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `supermarket_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `status`, `created_at`) VALUES
(1, 'Beverages', 'Soft drinks, juices, water', 'active', '2026-03-21 14:00:09'),
(2, 'Snacks', 'Chips, cookies, candies', 'active', '2026-03-21 14:00:09'),
(3, 'Dairy', 'Milk, cheese, yogurt', 'active', '2026-03-21 14:00:09'),
(4, 'Produce', 'Fresh fruits and vegetables', 'active', '2026-03-21 14:00:09'),
(5, 'Meat', 'Fresh and frozen meat', 'active', '2026-03-21 14:00:09'),
(6, 'food', 'beans,mateke,patatoes', 'active', '2026-03-21 14:42:59');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost_price` decimal(10,2) DEFAULT 0.00,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `min_stock_level` int(11) DEFAULT 5,
  `unit` varchar(20) DEFAULT 'piece',
  `image_url` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `barcode`, `name`, `description`, `category_id`, `supplier_id`, `price`, `cost_price`, `stock_quantity`, `min_stock_level`, `unit`, `image_url`, `status`, `created_at`, `updated_at`) VALUES
(1, '14000000', 'BYERI', 'IRARYOSHYE BYASANA', 1, NULL, '2000.00', '2001.00', 70, 59, 'PIEC', 'uploads/products/69c1225a204bc_1774264922.webp', 'inactive', '2026-03-21 14:04:34', '2026-03-23 11:22:02'),
(3, '123456789', 'umuceri', 'umuceri wu muhinde', 6, NULL, '2000.00', '1900.00', 100, 5, 'kg', 'uploads/products/69c1178be003a_1774262155.jpeg', 'active', '2026-03-23 10:35:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(10,2) DEFAULT 0.00,
  `discount` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('cash','card','mobile_money') DEFAULT 'cash',
  `payment_status` enum('paid','pending','cancelled') DEFAULT 'paid',
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `invoice_number`, `user_id`, `customer_name`, `customer_phone`, `subtotal`, `tax`, `discount`, `total_amount`, `payment_method`, `payment_status`, `sale_date`) VALUES
(1, 'INV-20260323-7797', 1, 'Misigaro', NULL, '10000.00', '1000.00', '0.00', '11000.00', 'cash', 'paid', '2026-03-23 10:22:06');

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 1, 5, '2000.00', '10000.00');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `contact_person`, `phone`, `email`, `address`, `status`, `created_at`) VALUES
(1, 'Fresh Foods Ltd', 'John Doe', '0788888888', 'john@freshfoods.com', NULL, 'active', '2026-03-21 14:00:10'),
(2, 'Beverage Distributors', 'Jane Smith', '0788888889', 'jane@beverages.com', NULL, 'active', '2026-03-21 14:00:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','manager','cashier') DEFAULT 'cashier',
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'Impano Irerwe', 'impanoirerwe@supermarket.com', '$2y$10$KEaV.ejfYhjskptRfltTdOyyUmwpQADRfMUO0mKmX8qNw0Rn0Ztca', 'admin', 'active', '2026-03-23 16:12:32', '2026-03-21 14:00:09', '2026-03-23 16:12:32'),
(2, 'Manager User', 'manager@supermarket.com', '$2y$10$LckLy7x0geoJz4EpXVEU6uG7DFN9qFuB4ESWHvpeKDPl5LgIWJWdq', 'manager', 'active', '2026-03-23 11:21:02', '2026-03-21 14:00:09', '2026-03-23 11:21:02'),
(3, 'Cashier User', 'cashier@supermarket.com', '$2y$10$LckLy7x0geoJz4EpXVEU6uG7DFN9qFuB4ESWHvpeKDPl5LgIWJWdq', 'cashier', 'active', '2026-03-22 18:30:02', '2026-03-21 14:00:09', '2026-03-22 18:30:02'),
(4, 'Misigaro Jean DE DIEU', 'misigaro@supermarket.com', '$2y$10$ZF0P.o6ycFjbAv0Ed.bEs.cVKMbMCRyGmSJxJXqtxm0giX0WYE0xu', 'cashier', 'active', '2026-03-23 10:14:28', '2026-03-22 19:18:50', '2026-03-23 10:14:28'),
(5, 'anick Mugeni funny', 'mugeni@example.com', '$2y$10$Wx8HfjITxBONAAZjJgNNEu0BWb.CfRh9y86iunYebahh.wDfEGQ.6', 'cashier', 'active', '2026-03-23 15:55:00', '2026-03-23 15:53:59', '2026-03-23 15:56:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
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
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
