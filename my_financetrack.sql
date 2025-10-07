-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 07, 2025 at 10:29 AM
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
-- Database: `myfinance_track`
--

-- --------------------------------------------------------

--
-- Table structure for table `budget`
--

CREATE TABLE `budget` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budget`
--

INSERT INTO `budget` (`id`, `user_id`, `category`, `amount`, `start_date`, `end_date`, `status`, `created_at`) VALUES
(1, 1, 'Food', 5000.00, '2024-08-01', '2024-08-31', 'active', '2025-08-01 03:45:53'),
(2, 1, 'Food', 5000.00, '2024-08-01', '2024-08-31', 'active', '2025-08-01 03:47:18');

-- --------------------------------------------------------

--
-- Table structure for table `budgets`
--

CREATE TABLE `budgets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `month_year` varchar(7) DEFAULT NULL,
  `budget` decimal(10,2) DEFAULT NULL,
  `primary_salary` decimal(10,2) DEFAULT NULL,
  `additional_income` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budgets`
--

INSERT INTO `budgets` (`id`, `user_id`, `amount`, `month_year`, `budget`, `primary_salary`, `additional_income`) VALUES
(1, 1, 5000.00, '2025-01', NULL, NULL, NULL),
(2, 1, 4500.00, '2025-02', NULL, NULL, NULL),
(3, 2, 6000.00, '2025-01', NULL, NULL, NULL),
(4, 2, 5800.00, '2025-02', NULL, NULL, NULL),
(5, 3, 7000.00, '2025-01', NULL, NULL, NULL),
(6, 3, 6800.00, '2025-03', NULL, NULL, NULL),
(7, 4, 4000.00, '2025-01', NULL, NULL, NULL),
(8, 4, 4200.00, '2025-04', NULL, NULL, NULL),
(9, 5, 5500.00, '2025-02', NULL, NULL, NULL),
(10, 5, 5300.00, '2025-05', NULL, NULL, NULL),
(11, 6, 3000.00, '2025-01', NULL, NULL, NULL),
(12, 6, 3500.00, '2025-02', NULL, NULL, NULL),
(13, 2, 4500.00, '2025-09', NULL, NULL, NULL),
(16, 1, 1500.00, '2025-09', 2000.00, NULL, NULL),
(17, 1, 30000.00, '2025-09', 15000.00, 25000.00, 5000.00),
(18, 1, 30000.00, '2025-09', 15000.00, 25000.00, 5000.00);

-- --------------------------------------------------------

--
-- Table structure for table `cash_flow`
--

CREATE TABLE `cash_flow` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('income','expense') NOT NULL,
  `category` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date` date NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cash_flow`
--

INSERT INTO `cash_flow` (`id`, `user_id`, `type`, `category`, `amount`, `date`, `description`, `created_at`) VALUES
(1, 1, 'income', 'Salary', 50000.00, '2024-07-31', 'Monthly salary', '2025-08-01 03:32:00');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `user_id`, `name`, `description`) VALUES
(1, 1, 'Food', NULL),
(2, 2, 'travel', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `financial_planning`
--

CREATE TABLE `financial_planning` (
  `id` int(11) NOT NULL,
  `primary_salary` decimal(15,2) NOT NULL,
  `additional_income` decimal(15,2) DEFAULT 0.00,
  `total_income` decimal(15,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financial_planning`
--

INSERT INTO `financial_planning` (`id`, `primary_salary`, `additional_income`, `total_income`, `created_at`) VALUES
(1, 50000.00, 800.00, 50800.00, '2025-09-03 09:05:52'),
(2, 5000.00, 50.00, 5050.00, '2025-09-08 04:13:08'),
(3, 55.00, 50.00, 105.00, '2025-09-08 04:21:07'),
(4, 5000.00, 0.00, 5000.00, '2025-09-08 04:24:22'),
(5, 5000.00, 0.00, 5000.00, '2025-09-08 04:26:12'),
(6, 5.00, 5.00, 10.00, '2025-09-08 04:30:40'),
(7, 55.00, 0.00, 55.00, '2025-09-08 04:31:57'),
(8, 55.00, 55.00, 110.00, '2025-09-08 04:32:46'),
(9, 5.00, 5.00, 10.00, '2025-09-08 04:35:15'),
(10, 5000.00, 0.00, 5000.00, '2025-09-08 04:41:20'),
(11, 20.00, 0.00, 20.00, '2025-09-08 04:41:33'),
(12, 2000.00, 0.00, 2000.00, '2025-09-08 04:41:47'),
(13, 60.00, 60.00, 120.00, '2025-09-09 03:00:50'),
(14, 50.00, 50.00, 100.00, '2025-09-11 06:32:23'),
(15, 50.00, 25.00, 75.00, '2025-09-11 06:36:31');

-- --------------------------------------------------------

--
-- Table structure for table `goals`
--

CREATE TABLE `goals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `target_amount` decimal(10,2) DEFAULT NULL,
  `saved_amount` decimal(10,2) DEFAULT 0.00,
  `deadline` date DEFAULT NULL,
  `status` enum('active','completed','cancelled') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `goals`
--

INSERT INTO `goals` (`id`, `user_id`, `name`, `target_amount`, `saved_amount`, `deadline`, `status`) VALUES
(1, 1, 'Buy a Laptop', 40000.00, 2000.00, '2024-12-31', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `incomes`
--

CREATE TABLE `incomes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `income_date` date NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `token` varchar(100) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `receipts`
--

CREATE TABLE `receipts` (
  `id` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `receipts`
--

INSERT INTO `receipts` (`id`, `category`, `date`, `amount`, `description`, `tags`) VALUES
(1, 'Food and Dining\" (or \"Travel\", \"Future', '2025-09-03', 28.50, 'Lunch at cafe', 'food, cafe'),
(2, 'Food and Dining', '2025-09-03', 250.00, 'food', 'biriyani'),
(3, 'Travel', '2025-09-02', 600.00, 'cab', 'Chennai'),
(4, 'Future', '2025-09-01', 320.00, 'home', 'site'),
(5, 'Food and Dining', '2025-09-08', 2.00, '', ''),
(6, 'Health', '2025-09-10', 200.00, 'fever', 'saveetha hospital');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `type` enum('income','expense') DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `txn_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `failed_login_attempts` int(11) NOT NULL DEFAULT 0,
  `last_failed_login` datetime DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `failed_login_attempts`, `last_failed_login`, `reset_token`, `reset_token_expiry`) VALUES
(1, 'John Doe', 'newemail@example.com', '$2y$10$dqdbdVSJVHH/7HT8io2VEeMZLqVBSOOvEng1ZHwY3.cdKRyqQL.Ta', 0, NULL, NULL, NULL),
(2, 'GOAT', 'pavithra1@gmail.com', '$2y$10$bAb8Pu6XI40cMErgPRSmSePn.AkgPRGcBbKsRPqFwbr9oLmULD4Ee', 0, NULL, NULL, NULL),
(3, 'Pavithra', 'pavi@example.com', '$2y$10$v5dgyr/Zn/dvGfCg3JHrRuqWvo7C7.3mYJ03GZMvmvynk/w4nR6ji', 0, NULL, NULL, NULL),
(4, 'Pavithra', 'pavi23@example.com', '$2y$10$OWpmoIYbbSOuwWsNAiIcm.SH8WIfi/S5QN/4SgVhsBHqKOqlDvpxS', 0, NULL, NULL, NULL),
(5, 'Pavithra', 'pavithragoturi@gmail.com', '$2y$10$BLVEUxvMfaaTd3gapEDiM.rUB6.cgAfnfWn7tZ1Tjji3ao7ctgGrC', 0, NULL, 'b372c65a6b41f503a789d3f8458b10ef3622392fc2e7795ea247a48cbe61f410', '2025-08-05 08:03:12'),
(6, 'vijay', 'vijays@gmail.com', '$2y$10$8GzcF2jD2CkM50ENU/Wr5e..oiBC6LO6xOlL3dznGJrkay750JCG.', 0, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `budget`
--
ALTER TABLE `budget`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `budgets`
--
ALTER TABLE `budgets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cash_flow`
--
ALTER TABLE `cash_flow`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `financial_planning`
--
ALTER TABLE `financial_planning`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `goals`
--
ALTER TABLE `goals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `incomes`
--
ALTER TABLE `incomes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `receipts`
--
ALTER TABLE `receipts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

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
-- AUTO_INCREMENT for table `budget`
--
ALTER TABLE `budget`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `budgets`
--
ALTER TABLE `budgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `cash_flow`
--
ALTER TABLE `cash_flow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `financial_planning`
--
ALTER TABLE `financial_planning`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `goals`
--
ALTER TABLE `goals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `incomes`
--
ALTER TABLE `incomes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `budgets`
--
ALTER TABLE `budgets`
  ADD CONSTRAINT `budgets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `goals`
--
ALTER TABLE `goals`
  ADD CONSTRAINT `goals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `incomes`
--
ALTER TABLE `incomes`
  ADD CONSTRAINT `incomes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
