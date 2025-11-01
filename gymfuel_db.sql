-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 26, 2025 at 06:50 PM
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
-- Database: `gymfuel_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reserved_until` datetime DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Συμπληρώματα'),
(2, 'Γεύματα'),
(3, 'Εξοπλισμός');

-- --------------------------------------------------------

--
-- Table structure for table `evaluation`
--

CREATE TABLE `evaluation` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `address` varchar(255) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','shipped','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `full_name`, `email`, `address`, `total`, `status`, `created_at`) VALUES
(1, 2, 'Spyros', 'spyros@gymfuel.local', 'kidshfd', 14.90, '', '2025-08-20 11:08:10'),
(2, 2, 'Spyros', 'spyros@gymfuel.local', 'κωηδ', 14.90, '', '2025-08-20 11:08:31'),
(3, 4, 'Maria', 'maria@gymfuel.local', 'tdfg', 39.90, '', '2025-08-20 11:09:21'),
(5, 2, 'Spyros', 'spyros@gymfuel.local', 'Spyros', 39.90, 'paid', '2025-08-21 14:30:48');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 12, 1, 14.90),
(2, 2, 12, 1, 14.90),
(3, 3, 10, 1, 39.90),
(5, 5, 10, 1, 39.90);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `image`, `category_id`, `created_at`) VALUES
(1, 'Whey Protein 2kg', 'Πρωτεΐνη υψηλής καθαρότητας.', 49.90, 'prod_01_whey_protein.jpg', 1, '2025-08-20 10:25:41'),
(2, 'Creatine Monohydrate 500g', 'Κρεατίνη μονοϋδρική.', 19.90, 'prod_02_creatine.jpg', 1, '2025-08-20 10:25:41'),
(3, 'Meal Prep Box 6 γεύματα', 'Έτοιμα ισορροπημένα γεύματα.', 29.90, 'prod_03_meal_prep.jpg', 2, '2025-08-20 10:25:41'),
(4, 'Kettlebell 12kg', 'Ιδανικό για προπόνηση στο σπίτι.', 34.90, 'prod_04_kettlebell.jpg', 3, '2025-08-20 10:25:41'),
(5, 'Resistance Bands', 'Λάστιχα αντίστασης.', 24.90, 'prod_05_bands.jpg', 3, '2025-08-20 10:25:41'),
(6, 'Shaker Bottle', 'Αναδευτήρας για ροφήματα.', 9.90, 'prod_06_shaker.jpg', 3, '2025-08-20 10:25:41'),
(7, 'BCAA Complex', 'Αμινοξέα διακλαδισμένης αλυσίδας.', 24.90, 'prod_07_bcaa.jpg', 1, '2025-08-20 10:25:41'),
(8, 'Vegan Protein', 'Φυτική πρωτεΐνη.', 34.90, 'prod_08_vegan_protein.jpg', 1, '2025-08-20 10:25:41'),
(9, 'Pre-Workout', 'Συμπλήρωμα προ-προπόνησης.', 29.90, 'prod_09_preworkout.jpg', 1, '2025-08-20 10:25:41'),
(10, 'Mass Gainer 3kg', 'Ιδανικό για αύξηση βάρους.', 39.90, 'prod_10_gainer.jpg', 1, '2025-08-20 10:25:41'),
(11, 'Yoga Mat', 'Στρώμα γυμναστικής υψηλής ποιότητας.', 19.90, 'prod_11_yoga_mat.jpg', 3, '2025-08-20 10:25:41'),
(12, 'Foam Roller', 'Κύλινδρος αποκατάστασης.', 14.90, 'prod_12_foam_roller.jpg', 3, '2025-08-20 10:25:41');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`id`, `user_id`, `product_id`, `rating`, `comment`, `created_at`) VALUES
(1, 2, 12, 2, NULL, '2025-08-21 14:14:22'),
(2, 2, 10, 2, NULL, '2025-08-21 14:31:50');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `is_hidden` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `review_likes`
--

CREATE TABLE `review_likes` (
  `id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `review_reports`
--

CREATE TABLE `review_reports` (
  `id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `full_name`, `email`, `address`, `password`, `is_admin`, `role`, `created_at`) VALUES
(1, 'Admin User', 'ADMIN', 'admin@gymfuel.local', 'ADMIN', '$2y$10$98/Lq.wAfXayDqBeGWH6KuHGkhfnwFW2loTJT4skaWt/b4FxLmB2S', 1, 'admin', '2025-08-20 10:25:41'),
(2, 'Spyros', 'Spyros GymFuel', 'spyros@gymfuel.local', 'GymFuel', '$2y$10$cRkLOogZoI.7I42xebZGeue6XJH1TK8xzg7aQCEnTBtxyn7HMpBWy', 0, 'user', '2025-08-20 10:25:41'),
(3, 'Giorgos', NULL, 'giorgos@gymfuel.local', NULL, 'user123', 0, 'user', '2025-08-20 10:25:41'),
(4, 'Maria', NULL, 'maria@gymfuel.local', NULL, '$2y$10$KtYrJgySyuU0gSjEXjnHze7fu7GCeCwUvxGHDvX8AMJ0N61GrFAvy', 0, 'user', '2025-08-20 10:25:41');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `ip` varchar(64) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `login_at` datetime NOT NULL DEFAULT current_timestamp(),
  `logout_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `session_token`, `ip`, `user_agent`, `login_at`, `logout_at`, `created_at`) VALUES
(1, 2, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-20 13:44:39', '2025-08-20 14:03:22', '2025-08-20 10:44:39'),
(2, 2, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-20 14:04:13', '2025-08-20 14:08:36', '2025-08-20 11:04:13'),
(3, 4, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-20 14:09:12', '2025-08-20 14:09:25', '2025-08-20 11:09:12'),
(4, 1, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-20 14:10:15', '2025-08-20 14:11:08', '2025-08-20 11:10:15'),
(5, 1, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-21 16:16:38', '2025-08-21 16:16:59', '2025-08-21 13:16:38'),
(6, 2, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-21 16:17:42', '2025-08-21 16:25:40', '2025-08-21 13:17:42'),
(7, 1, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-21 16:25:59', '2025-08-21 16:35:20', '2025-08-21 13:25:59'),
(8, 2, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-21 16:35:38', '2025-08-21 17:14:56', '2025-08-21 13:35:38'),
(9, 1, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-21 17:15:14', '2025-08-21 17:30:16', '2025-08-21 14:15:14'),
(10, 2, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-21 17:30:33', '2025-08-21 17:30:56', '2025-08-21 14:30:33'),
(11, 1, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-21 17:31:11', '2025-08-21 17:31:25', '2025-08-21 14:31:11'),
(12, 2, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-21 17:31:37', '2025-08-26 19:47:20', '2025-08-21 14:31:37'),
(0, 2, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-26 19:47:09', NULL, '2025-08-26 16:47:09'),
(0, 1, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-26 19:47:36', NULL, '2025-08-26 16:47:36'),
(0, 1, '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', '2025-08-26 19:48:05', NULL, '2025-08-26 16:48:05');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
