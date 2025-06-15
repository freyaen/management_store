-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 15, 2025 at 11:59 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `management_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(225) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Roti Tawar'),
(2, 'Sandwich'),
(3, 'Sandwich Zuperr Creamy'),
(4, 'Sobek'),
(5, 'Sobek Duo'),
(6, 'Dorayaki'),
(7, 'Cheese Cake');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `code` varchar(225) NOT NULL,
  `image` varchar(225) NOT NULL,
  `name` varchar(225) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 0,
  `buy_price` int(11) NOT NULL DEFAULT 0,
  `sale_price` int(11) NOT NULL DEFAULT 0,
  `expired_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `input_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `code`, `image`, `name`, `qty`, `buy_price`, `sale_price`, `expired_date`, `input_date`) VALUES
(1, 1, 'RT/1/RTS', '1749886979_WhatsAppImage20250614at14.22.01_f9512853.jpg', 'Roti Tawar Special', 0, 13500, 15000, '2025-06-15 09:34:47', '2025-06-14 07:42:59'),
(2, 7, 'CC/1/CRV', '1749887416_WhatsAppImage20250614at14.48.57_80eb73e9.jpg', 'Cheese Cake Red Velvet', 6, 8500, 9500, '2025-06-15 09:50:29', '2025-06-14 07:50:16'),
(3, 6, 'DR/1/DCH', '1749887568_WhatsAppImage20250614at14.51.35_f9e536c3.jpg', 'Dorayaki Cheese Cake Hokkaido', 3, 5400, 6000, '2025-06-15 09:36:31', '2025-06-14 07:52:48'),
(5, 3, 'SZC/01/SZCC', '1749978936_684b611e58cd6.jpeg', 'Sandwich Zuperr Creamy Choco', 0, 4500, 5500, '2025-06-22 09:15:00', '2025-06-15 09:15:36'),
(6, 5, 'SB/01/', '1749979963_684b6230409de.jpg', 'Roti Sobek Duo Coklat Keju', 0, 9900, 11000, '2025-07-17 09:32:00', '2025-06-15 09:32:43');

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `code` varchar(225) NOT NULL DEFAULT '0',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `request_status` enum('menunggu','disetujui','ditolak','dikirim','selesai') NOT NULL DEFAULT 'menunggu',
  `payment_status` enum('belum dibayar','sudah dibayar') NOT NULL DEFAULT 'belum dibayar',
  `reject_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `code`, `request_date`, `request_status`, `payment_status`, `reject_reason`) VALUES
(1, 'REQ/14/06/2025/001', '2025-06-14 19:48:49', 'selesai', 'sudah dibayar', NULL),
(2, 'REQ/15/06/2025/002', '2025-06-15 08:51:58', 'selesai', 'sudah dibayar', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `request_details`
--

CREATE TABLE `request_details` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL DEFAULT 0,
  `product_id` int(11) NOT NULL DEFAULT 0,
  `qty` int(11) NOT NULL DEFAULT 0,
  `price` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `request_details`
--

INSERT INTO `request_details` (`id`, `request_id`, `product_id`, `qty`, `price`) VALUES
(1, 1, 2, 5, 8500),
(2, 1, 1, 2, 13500),
(3, 2, 3, 1, 5400),
(4, 2, 1, 2, 13500),
(5, 2, 2, 3, 8500);

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

CREATE TABLE `returns` (
  `id` int(11) NOT NULL,
  `code` varchar(225) NOT NULL DEFAULT '0',
  `return_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu',
  `reject_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `returns`
--

INSERT INTO `returns` (`id`, `code`, `return_date`, `status`, `reject_reason`) VALUES
(1, 'RET/15/06/2025/001', '2025-06-15 09:34:23', 'disetujui', NULL),
(2, 'RET/15/06/2025/002', '2025-06-15 09:50:13', 'disetujui', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `return_details`
--

CREATE TABLE `return_details` (
  `id` int(11) NOT NULL,
  `return_id` int(11) NOT NULL DEFAULT 0,
  `product_id` int(11) NOT NULL DEFAULT 0,
  `image` varchar(225) NOT NULL DEFAULT '0',
  `qty` int(11) NOT NULL DEFAULT 0,
  `return_reason` enum('rusak','salah kirim','kedaluarsa','lainnya') NOT NULL,
  `return_reason_other` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `return_details`
--

INSERT INTO `return_details` (`id`, `return_id`, `product_id`, `image`, `qty`, `return_reason`, `return_reason_other`) VALUES
(1, 1, 1, '1749980063_0.jpg', 12, 'kedaluarsa', ''),
(2, 2, 2, '1749981013_0.jpeg', 2, 'kedaluarsa', '');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(225) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(1, 'Manager Gudang'),
(2, 'Karyawan Toko');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `code` varchar(225) NOT NULL DEFAULT '0',
  `sales_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `code`, `sales_date`) VALUES
(1, 'SAL/14/06/2025/001', '2025-06-14 19:53:04'),
(2, 'SAL/14/06/2025/002', '2025-06-14 19:53:54'),
(3, 'SAL/15/06/2025/003', '2025-06-15 09:35:34'),
(4, 'SAL/15/06/2025/004', '2025-06-15 09:36:31');

-- --------------------------------------------------------

--
-- Table structure for table `sales_details`
--

CREATE TABLE `sales_details` (
  `id` int(11) NOT NULL,
  `sales_id` int(11) NOT NULL DEFAULT 0,
  `product_id` int(11) NOT NULL DEFAULT 0,
  `qty` int(11) NOT NULL DEFAULT 0,
  `price` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `sales_details`
--

INSERT INTO `sales_details` (`id`, `sales_id`, `product_id`, `qty`, `price`) VALUES
(1, 1, 1, 1, 15000),
(2, 2, 1, 1, 15000),
(3, 3, 3, 1, 6000),
(4, 4, 2, 1, 9500),
(5, 4, 3, 1, 6000);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `username` varchar(225) NOT NULL,
  `name` varchar(225) NOT NULL,
  `password` varchar(225) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `username`, `name`, `password`) VALUES
(3, 1, 'gudang', 'Hello Manager Gudang', '$2y$10$NXznN2DqFD7T.H0A3mjCn.gcfX0O/o2A17YPEUsUgUMrWn1nEjk7a'),
(4, 2, 'toko', 'Hello Karyawan Toko', '$2y$10$1tSbdR1etr7XCFu8W7WVf.4sS0ubG.inM2ZHa7d9XW6.EHN2T5Tju');

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
  ADD KEY `fk_products_to_categories` (`category_id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `request_details`
--
ALTER TABLE `request_details`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `fk_request_details_to_request` (`request_id`),
  ADD KEY `fk_request_details_to_products` (`product_id`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `return_details`
--
ALTER TABLE `return_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`return_id`) USING BTREE,
  ADD KEY `FK_return_details_products` (`product_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales_details`
--
ALTER TABLE `sales_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sales_details_to_sales` (`sales_id`),
  ADD KEY `FK_sales_details_products` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_users_to_roles` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `request_details`
--
ALTER TABLE `request_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `return_details`
--
ALTER TABLE `return_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sales_details`
--
ALTER TABLE `sales_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_to_categories` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `request_details`
--
ALTER TABLE `request_details`
  ADD CONSTRAINT `fk_request_details_to_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_request_details_to_request` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `return_details`
--
ALTER TABLE `return_details`
  ADD CONSTRAINT `FK_return_details_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_return_details_to_returns` FOREIGN KEY (`return_id`) REFERENCES `returns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sales_details`
--
ALTER TABLE `sales_details`
  ADD CONSTRAINT `FK_sales_details_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sales_details_to_sales` FOREIGN KEY (`sales_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_to_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
