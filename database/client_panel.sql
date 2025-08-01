-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 01, 2025 at 08:34 AM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `client_panel`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `module` varchar(100) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `module`, `ip_address`, `user_agent`, `created_at`) VALUES
(10, NULL, 'failed_login', 'admin@example.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:22:56'),
(11, 3, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:26:49'),
(12, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:26:49'),
(13, 3, 'view_clients', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:27:14'),
(14, 3, 'view_client_add_form', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:27:23'),
(15, 3, 'view_client_add_form', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:28:29'),
(16, 3, 'view_clients', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:28:35'),
(17, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:28:37'),
(18, 3, 'view_clients', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:28:38'),
(19, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:28:40'),
(20, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:28:44'),
(21, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:28:46'),
(22, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:28:48'),
(23, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:28:51'),
(24, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:28:56'),
(25, 3, 'view_clients', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:28:57'),
(26, 3, 'view_client_add_form', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:28:59'),
(27, 3, 'create_client', 'michal.s@decor-styl.pl', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:29:22'),
(28, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:29:51'),
(29, 3, 'logout', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:29:59'),
(30, NULL, 'failed_login', 'michal.s@decor-styl.pl', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:30:28'),
(31, 3, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:30:42'),
(32, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:30:42'),
(33, 3, 'view_clients', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:30:44'),
(34, 3, 'view_clients', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:31:04'),
(35, 3, 'view_client_add_form', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:31:07'),
(36, 3, 'view_client_add_form', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:31:23'),
(37, 3, 'view_client_add_form', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:32:04'),
(38, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:32:14'),
(39, 3, 'logout', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:32:34'),
(40, NULL, 'failed_login', 'michal.s@decor-styl.pl', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:58:02'),
(41, 3, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:58:23'),
(42, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:58:23'),
(43, 3, 'view_clients', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:58:24'),
(44, 3, 'view_client_add_form', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:58:26'),
(45, NULL, 'failed_login', 'admin@example.pl', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:23:41'),
(46, NULL, 'failed_login', 'admin@system.pl', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:24:05'),
(47, 3, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:32:35'),
(48, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:32:35'),
(49, 3, 'view_orders_admin', 'admin_orders', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:32:57'),
(50, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:33:02'),
(51, 3, 'view_clients', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:33:04'),
(52, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:33:05'),
(53, 3, 'view_complaints_admin', 'admin_complaints', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:33:06'),
(54, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:33:07'),
(55, 3, 'view_files_admin', 'admin_files', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:33:09'),
(56, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:33:15'),
(57, 3, 'view_invoices_admin', 'admin_invoices', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:33:17'),
(58, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:33:23'),
(59, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:33:28'),
(60, 3, 'view_catalogs_admin', 'admin_catalogs', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:33:29'),
(61, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:33:50'),
(62, 3, 'view_orders_admin', 'admin_orders', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:33:51'),
(63, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:33:53'),
(64, 3, 'view_clients', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:33:54'),
(65, 3, 'view_client_add_form', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:33:55'),
(66, 3, 'view_clients', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:34:00'),
(67, 3, 'view_client_add_form', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:35:48'),
(68, 3, 'view_client_add_form', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:36:47'),
(69, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:36:58'),
(70, 3, 'logout', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:38:03'),
(71, 3, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:38:07'),
(72, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:38:07'),
(73, 3, 'view_orders_admin', 'admin_orders', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:44:10'),
(74, 3, 'view_orders_admin', 'admin_orders', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:45:34'),
(75, 3, 'view_orders_admin', 'admin_orders', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:46:59'),
(76, 3, 'view_orders_admin', 'admin_orders', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:47:02'),
(77, 3, 'view_orders_admin', 'admin_orders', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:47:05'),
(78, 3, 'view_orders_admin', 'admin_orders', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:47:07'),
(79, 3, 'view_orders_admin', 'admin_orders', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:48:56'),
(80, 3, 'view_orders_admin', 'admin_orders', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:49:00'),
(81, 3, 'view_orders_admin', 'admin_orders', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:49:03'),
(82, 3, 'view_orders_admin', 'admin_orders', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:50:45'),
(83, 3, 'view_orders_admin', 'admin_orders', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:50:49'),
(84, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:50:50'),
(85, 3, 'view_orders_admin', 'admin_orders', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:50:53'),
(86, 3, 'view_orders_admin', 'admin_orders', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:50:58'),
(87, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:51:12'),
(88, 3, 'view_orders_admin', 'admin_orders', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:51:13'),
(89, 3, 'view_orders_admin', 'admin_orders', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:51:16'),
(90, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:52:27'),
(91, 3, 'view_clients', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:52:31'),
(92, 3, 'view_client_add_form', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:53:01'),
(93, 3, 'view_client_add_form', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:54:50'),
(94, 3, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 07:48:20'),
(95, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 07:48:20'),
(96, 3, 'view_orders_admin', 'admin_orders', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 07:48:32'),
(97, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 07:48:43'),
(98, 3, 'view_clients', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 07:48:43'),
(99, 3, 'view_client_add_form', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 07:48:47'),
(100, 3, 'view_clients', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:00:56'),
(101, 3, 'view_client_add_form', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:01:01'),
(102, 3, 'view_clients', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:03:53'),
(103, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:04:01'),
(104, 3, 'view_complaints_admin', 'admin_complaints', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:04:08'),
(105, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:04:15'),
(106, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:04:24'),
(107, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:06:13'),
(108, 3, 'view_ads_admin', 'admin_ads', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:06:24'),
(109, 3, 'view_ads_admin', 'admin_ads', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:06:51'),
(110, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:06:55'),
(111, 3, 'view_catalogs_admin', 'admin_catalogs', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:07:03'),
(112, 3, 'view_catalogs_admin', 'admin_catalogs', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:07:39'),
(113, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:18:42'),
(114, 3, 'view_files_admin', 'admin_files', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:18:45'),
(115, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:19:25'),
(116, 3, 'view_invoices_admin', 'admin_invoices', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:19:28'),
(117, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:19:30'),
(118, 3, 'logout', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:20:50'),
(119, NULL, 'failed_login', 'michal.s@decor-styl.pl', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:22:47'),
(120, 4, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:23:48'),
(121, 4, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:23:48'),
(122, 4, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:23:57'),
(123, 4, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:24:00'),
(124, 4, 'view_catalogs', 'catalogs', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:24:02'),
(125, 4, 'view_catalogs', 'catalogs', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:24:04'),
(126, 4, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:24:07'),
(127, 4, 'view_disk', 'disk', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:24:07'),
(128, 4, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:24:18'),
(129, 4, 'view_subaccounts', 'subaccounts', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:24:20'),
(130, 4, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:24:59'),
(131, 4, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:25:35'),
(132, 4, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:25:54'),
(133, 4, 'view_catalogs', 'catalogs', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:26:14'),
(134, 4, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:27:52'),
(135, 4, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:29:23'),
(136, 4, 'view_complaints', 'complaints', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:29:24'),
(137, 4, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:29:28'),
(138, 4, 'view_subaccounts', 'subaccounts', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:30:09'),
(139, 4, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:34:01'),
(140, 4, 'view_catalogs', 'catalogs', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:34:06'),
(141, 4, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:34:08'),
(142, 4, 'view_catalogs', 'catalogs', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:34:09'),
(143, 4, 'view_catalogs', 'catalogs', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:34:10'),
(144, 4, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:34:11'),
(145, 4, 'view_disk', 'disk', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:34:11'),
(146, 4, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:34:17'),
(147, 4, 'logout', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:34:20'),
(148, 3, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:34:28'),
(149, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:34:29'),
(150, 3, 'view_clients', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:34:31'),
(151, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:34:40'),
(152, 3, 'view_complaints_admin', 'admin_complaints', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:34:41'),
(153, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:34:42'),
(154, 3, 'view_files_admin', 'admin_files', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:34:43'),
(155, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:36:23'),
(156, 3, 'view_invoices_admin', 'admin_invoices', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:36:24'),
(157, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:36:27'),
(158, 3, 'view_invoices_admin', 'admin_invoices', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:36:27'),
(159, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:36:30'),
(160, 3, 'view_catalogs_admin', 'admin_catalogs', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:36:31'),
(161, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:36:34'),
(162, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:36:36'),
(163, 3, 'view_orders_admin', 'admin_orders', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:36:39'),
(164, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:36:40'),
(165, 3, 'view_clients', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:36:41'),
(166, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:36:42'),
(167, 3, 'view_complaints_admin', 'admin_complaints', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:36:43'),
(168, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:36:44'),
(169, 3, 'view_clients', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:36:44'),
(170, 3, 'view_clients', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:36:51'),
(171, 3, 'view_clients', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:36:53'),
(172, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:38:06'),
(173, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:46:34'),
(174, 3, 'view_complaints_admin', 'admin_complaints', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:46:38'),
(175, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:46:39'),
(176, 3, 'view_files_admin', 'admin_files', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:46:49'),
(177, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:46:51'),
(178, 3, 'view_invoices_admin', 'admin_invoices', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:46:52'),
(179, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:46:52'),
(180, 3, 'view_catalogs_admin', 'admin_catalogs', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:46:53'),
(181, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:47:03'),
(182, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:47:05'),
(183, 3, 'view_complaints_admin', 'admin_complaints', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:47:06'),
(184, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:47:06'),
(185, 3, 'view_clients', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:47:07'),
(186, 3, 'view_client_add_form', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:47:10'),
(187, 3, 'view_clients', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:47:11'),
(188, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:47:12'),
(189, 3, 'view_orders_admin', 'admin_orders', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:47:13'),
(190, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:47:17'),
(191, 3, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:55:08'),
(192, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:55:08'),
(193, 3, 'view_orders_admin', 'admin_orders', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:55:20'),
(194, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:55:27'),
(195, 3, 'view_clients', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:55:28'),
(196, 3, 'view_client_add_form', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:55:30'),
(197, 3, 'view_clients', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:55:40'),
(198, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:55:42'),
(199, 3, 'view_complaints_admin', 'admin_complaints', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:55:43'),
(200, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:55:44'),
(201, 3, 'view_files_admin', 'admin_files', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:55:44'),
(202, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:55:45'),
(203, 3, 'view_catalogs_admin', 'admin_catalogs', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:55:46'),
(204, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:55:52'),
(205, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:55:55'),
(206, 3, 'view_orders_admin', 'admin_orders', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:57:04'),
(207, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:57:37'),
(208, 3, 'view_clients', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:57:39'),
(209, 3, 'view_client_add_form', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:57:48'),
(210, 3, 'view_clients', 'admin_clients', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:59:02'),
(211, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:59:05'),
(212, 3, 'view_files_admin', 'admin_files', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:59:07'),
(213, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:59:10'),
(214, 3, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:59:15'),
(215, 3, 'logout', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:59:21'),
(216, 4, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:59:44'),
(217, 4, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:59:44'),
(218, 4, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:59:53'),
(219, 4, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 11:59:54'),
(220, 4, 'view_catalogs', 'catalogs', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 12:00:02'),
(221, 4, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 12:00:07'),
(222, 4, 'view_disk', 'disk', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 12:00:09'),
(223, 4, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 12:00:35'),
(224, 4, 'view_complaints', 'complaints', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 12:00:40'),
(225, 4, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 12:00:52'),
(226, 4, 'view_subaccounts', 'subaccounts', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 12:00:57'),
(227, 4, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 12:19:49'),
(228, 4, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 12:19:59'),
(229, 4, 'view_catalogs', 'catalogs', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 12:20:01'),
(230, 4, 'view_dashboard', 'dashboard', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 12:20:09'),
(231, 4, 'logout', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 12:20:11'),
(232, 3, 'login', 'success', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 12:21:11'),
(233, 3, 'view_orders_admin', 'admin_orders', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 12:21:40');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `advertisements`
--

CREATE TABLE `advertisements` (
  `id` int(11) NOT NULL,
  `ad_number` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `image_path` text NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `ad_catalogs`
--

CREATE TABLE `ad_catalogs` (
  `id` int(11) NOT NULL,
  `ad_id` int(11) NOT NULL,
  `catalog_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `ad_clients`
--

CREATE TABLE `ad_clients` (
  `id` int(11) NOT NULL,
  `ad_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `ad_client_groups`
--

CREATE TABLE `ad_client_groups` (
  `id` int(11) NOT NULL,
  `ad_id` int(11) NOT NULL,
  `client_group` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `catalogs`
--

CREATE TABLE `catalogs` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `catalogs`
--

INSERT INTO `catalogs` (`id`, `parent_id`, `name`, `slug`, `is_active`, `created_at`) VALUES
(1, NULL, 'Rolety zewnętrzne', 'rolety-zewnetrzne', 1, '2025-07-29 11:19:12'),
(2, NULL, 'Rolety wewnętrzne', 'rolety-wewnetrzne', 1, '2025-07-29 11:19:12'),
(3, NULL, 'Moskitiery', 'moskitiery', 1, '2025-07-29 11:19:12'),
(4, NULL, 'Żaluzje', 'zaluzje', 1, '2025-07-29 11:19:12'),
(5, 1, 'Natynkowe', 'natynkowe', 1, '2025-07-29 11:19:12'),
(6, 1, 'Podtynkowe', 'podtynkowe', 1, '2025-07-29 11:19:12'),
(7, 1, 'Nadstawne', 'nadstawne', 1, '2025-07-29 11:19:12'),
(8, 1, 'Antywłamaniowe', 'antywlamaniowe', 1, '2025-07-29 11:19:12'),
(9, 1, 'Bramy garażowe', 'bramy-garazowe', 1, '2025-07-29 11:19:12'),
(10, 2, 'Dzień i noc', 'dzien-i-noc', 1, '2025-07-29 11:19:12'),
(11, 2, 'Klasyczne', 'klasyczne', 1, '2025-07-29 11:19:12'),
(12, 2, 'Wolnowiszące', 'wolnowiszace', 1, '2025-07-29 11:19:12'),
(13, 2, 'Rzymskie', 'rzymskie', 1, '2025-07-29 11:19:12'),
(14, 3, 'Ramkowe', 'ramkowe', 1, '2025-07-29 11:19:12'),
(15, 3, 'Drzwiowe', 'drzwiowe', 1, '2025-07-29 11:19:12'),
(16, 3, 'Przesuwne', 'przesuwne', 1, '2025-07-29 11:19:12'),
(17, 3, 'Plisowane', 'plisowane', 1, '2025-07-29 11:19:12'),
(18, 4, 'Poziome aluminiowe', 'poziome-aluminiowe', 1, '2025-07-29 11:19:12'),
(19, 4, 'Drewniane', 'drewniane', 1, '2025-07-29 11:19:12'),
(20, 4, 'Bambusowe', 'bambusowe', 1, '2025-07-29 11:19:12'),
(21, 4, 'Pionowe', 'pionowe', 1, '2025-07-29 11:19:12'),
(22, 4, 'Fasadowe', 'fasadowe', 1, '2025-07-29 11:19:12'),
(23, 4, 'Plisy', 'plisy', 1, '2025-07-29 11:19:12');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `catalog_files`
--

CREATE TABLE `catalog_files` (
  `id` int(11) NOT NULL,
  `catalog_id` int(11) NOT NULL,
  `file_type` enum('instrukcje','jak_zainstalowac','zdjecia','wideo') DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` text NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `catalog_permissions`
--

CREATE TABLE `catalog_permissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `catalog_id` int(11) NOT NULL,
  `can_view` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `client_files`
--

CREATE TABLE `client_files` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `folder_name` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` text NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `company_data`
--

CREATE TABLE `company_data` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nip` varchar(20) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `street` varchar(255) NOT NULL,
  `building_number` varchar(20) DEFAULT NULL,
  `postal_code` varchar(10) NOT NULL,
  `city` varchar(100) NOT NULL,
  `country` varchar(100) DEFAULT 'Polska',
  `client_group` enum('Klient Moskitiery','Klient Rolety','Klient Roletki','Klient Żaluzje','Klient Duży') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `complaints`
--

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL,
  `complaint_number` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `product_element` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('Złożona reklamacja','Reklamacja rozpatrywana','Reklamacja przyjęta','Reklamacja zakończona') DEFAULT 'Złożona reklamacja',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `debug_logs`
--

CREATE TABLE `debug_logs` (
  `id` int(11) NOT NULL,
  `error_level` enum('INFO','WARNING','ERROR','CRITICAL') DEFAULT NULL,
  `message` text NOT NULL,
  `file` varchar(255) DEFAULT NULL,
  `line` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('new_file','new_invoice','new_promotion','new_order','new_complaint') DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_status` enum('Nieopłacone','Opłacone','Częściowo opłacone') DEFAULT 'Nieopłacone',
  `order_status` enum('Zamówienie czeka na potwierdzenie','Zamówienie przyjęte','Zamówienie czeka na realizację','Zamówienie jest w trakcie realizacji','Zamówienie jest gotowe do odbioru') DEFAULT 'Zamówienie czeka na potwierdzenie',
  `total_net` decimal(10,2) DEFAULT 0.00,
  `invoice_path` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `position_number` int(11) NOT NULL,
  `product` varchar(255) NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `profile_system` varchar(100) NOT NULL,
  `profile_color` varchar(100) NOT NULL,
  `net_color` varchar(50) NOT NULL,
  `window_rabbet` int(11) NOT NULL,
  `hook_size` int(11) DEFAULT NULL,
  `riveting` varchar(50) DEFAULT NULL,
  `force_hook` varchar(50) DEFAULT NULL,
  `force_hook_count` int(11) DEFAULT NULL,
  `horizontal_bar` tinyint(1) DEFAULT 0,
  `bar_height` int(11) DEFAULT NULL,
  `supplier_note` text DEFAULT NULL,
  `total_width` int(11) DEFAULT NULL,
  `total_height` int(11) DEFAULT NULL,
  `customer_label` text DEFAULT NULL,
  `self_assembly` tinyint(1) DEFAULT 0,
  `corner_type` varchar(50) DEFAULT NULL,
  `powder_color` text DEFAULT NULL,
  `corner_color_ral` varchar(100) DEFAULT NULL,
  `price_net` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `order_permissions`
--

CREATE TABLE `order_permissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `can_quote` tinyint(1) DEFAULT 0,
  `can_order` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('admin','client','subaccount') DEFAULT 'client',
  `parent_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `user_type`, `parent_id`, `is_active`, `created_at`, `last_login`) VALUES
(3, 'admin@example.com', '$2y$10$quMlbGiXlwqo2Ph2E0q9tuMm/8TzBlWBq81C/JVGTsJNly2QUjRnW', 'admin', NULL, 1, '2025-07-29 12:02:38', '2025-07-31 12:21:11'),
(4, 'michal.s@decor-styl.pl', '$2y$10$quMlbGiXlwqo2Ph2E0q9tuMm/8TzBlWBq81C/JVGTsJNly2QUjRnW', 'client', NULL, 1, '2025-07-31 08:21:08', '2025-07-31 11:59:44');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_token` varchar(255) NOT NULL,
  `data` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `session_token`, `data`, `ip_address`, `user_agent`, `created_at`, `expires_at`) VALUES
(5, 3, '4k1mhhv7hre02b6f4m8fibn5ql', 'last_regeneration|i:1753792354;csrf_token|s:64:\"ccd2827b53976e081de61a7f5a868f4a673551830eab04f709805ca718788ce6\";user_id|i:3;user_type|s:5:\"admin\";user_email|s:17:\"admin@example.com\";last_activity|i:1753793906;', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-29 12:32:34', '2025-07-29 13:58:26'),
(7, 3, '1kh575m789ppcl963e8pk61dc9', 'last_regeneration|i:1753943883;csrf_token|s:64:\"dc9ee749306a2192f86ecf52d1bdebc5a336dd0827c9ec6743b7b8832b4f4569\";user_id|i:3;user_type|s:5:\"admin\";user_email|s:17:\"admin@example.com\";last_activity|i:1753944890;', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 06:38:03', '2025-07-31 07:54:50'),
(11, 3, '04uggk32no466l978k38393405', 'last_regeneration|i:1753950860;csrf_token|s:64:\"d1522e1a9acefcf2300bd259e042a2f5021d95393a7c5358e96fa5ef94685bed\";user_id|i:3;user_type|s:5:\"admin\";user_email|s:17:\"admin@example.com\";last_activity|i:1753951637;', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 08:34:20', '2025-07-31 09:47:17'),
(14, 3, 'rn95trjap2s52ca9aojp9rdd8c', 'last_regeneration|i:1753964411;csrf_token|s:64:\"8e3fdc2874d0f226c21bba616deacb49d361880cfd817a6ad67c28a6b517fbb6\";user_id|i:3;user_type|s:5:\"admin\";user_email|s:17:\"admin@example.com\";last_activity|i:1753964500;', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36', '2025-07-31 12:20:11', '2025-07-31 13:24:12');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activity_logs_user` (`user_id`);

--
-- Indeksy dla tabeli `advertisements`
--
ALTER TABLE `advertisements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ad_number` (`ad_number`),
  ADD KEY `idx_ads_dates` (`start_date`,`end_date`);

--
-- Indeksy dla tabeli `ad_catalogs`
--
ALTER TABLE `ad_catalogs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ad_id` (`ad_id`),
  ADD KEY `catalog_id` (`catalog_id`);

--
-- Indeksy dla tabeli `ad_clients`
--
ALTER TABLE `ad_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ad_id` (`ad_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeksy dla tabeli `ad_client_groups`
--
ALTER TABLE `ad_client_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ad_id` (`ad_id`);

--
-- Indeksy dla tabeli `catalogs`
--
ALTER TABLE `catalogs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indeksy dla tabeli `catalog_files`
--
ALTER TABLE `catalog_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `catalog_id` (`catalog_id`);

--
-- Indeksy dla tabeli `catalog_permissions`
--
ALTER TABLE `catalog_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_catalog` (`user_id`,`catalog_id`),
  ADD KEY `catalog_id` (`catalog_id`);

--
-- Indeksy dla tabeli `client_files`
--
ALTER TABLE `client_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indeksy dla tabeli `company_data`
--
ALTER TABLE `company_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeksy dla tabeli `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `complaint_number` (`complaint_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indeksy dla tabeli `debug_logs`
--
ALTER TABLE `debug_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user` (`user_id`,`is_read`);

--
-- Indeksy dla tabeli `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_orders_user` (`user_id`),
  ADD KEY `idx_orders_status` (`order_status`);

--
-- Indeksy dla tabeli `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indeksy dla tabeli `order_permissions`
--
ALTER TABLE `order_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indeksy dla tabeli `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_sessions_token` (`session_token`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=234;

--
-- AUTO_INCREMENT for table `advertisements`
--
ALTER TABLE `advertisements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ad_catalogs`
--
ALTER TABLE `ad_catalogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ad_clients`
--
ALTER TABLE `ad_clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ad_client_groups`
--
ALTER TABLE `ad_client_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `catalogs`
--
ALTER TABLE `catalogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `catalog_files`
--
ALTER TABLE `catalog_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `catalog_permissions`
--
ALTER TABLE `catalog_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_files`
--
ALTER TABLE `client_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_data`
--
ALTER TABLE `company_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `debug_logs`
--
ALTER TABLE `debug_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_permissions`
--
ALTER TABLE `order_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ad_catalogs`
--
ALTER TABLE `ad_catalogs`
  ADD CONSTRAINT `ad_catalogs_ibfk_1` FOREIGN KEY (`ad_id`) REFERENCES `advertisements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ad_catalogs_ibfk_2` FOREIGN KEY (`catalog_id`) REFERENCES `catalogs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ad_clients`
--
ALTER TABLE `ad_clients`
  ADD CONSTRAINT `ad_clients_ibfk_1` FOREIGN KEY (`ad_id`) REFERENCES `advertisements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ad_clients_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ad_client_groups`
--
ALTER TABLE `ad_client_groups`
  ADD CONSTRAINT `ad_client_groups_ibfk_1` FOREIGN KEY (`ad_id`) REFERENCES `advertisements` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `catalogs`
--
ALTER TABLE `catalogs`
  ADD CONSTRAINT `catalogs_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `catalogs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `catalog_files`
--
ALTER TABLE `catalog_files`
  ADD CONSTRAINT `catalog_files_ibfk_1` FOREIGN KEY (`catalog_id`) REFERENCES `catalogs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `catalog_permissions`
--
ALTER TABLE `catalog_permissions`
  ADD CONSTRAINT `catalog_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `catalog_permissions_ibfk_2` FOREIGN KEY (`catalog_id`) REFERENCES `catalogs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `client_files`
--
ALTER TABLE `client_files`
  ADD CONSTRAINT `client_files_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `client_files_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `company_data`
--
ALTER TABLE `company_data`
  ADD CONSTRAINT `company_data_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `complaints_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_permissions`
--
ALTER TABLE `order_permissions`
  ADD CONSTRAINT `order_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
