-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 07, 2024 at 03:40 PM
-- Server version: 11.2.2-MariaDB
-- PHP Version: 8.2.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `eshop_pps`
--

-- --------------------------------------------------------

--
-- Table structure for table `pps_addresses_per_user`
--

DROP TABLE IF EXISTS `pps_addresses_per_user`;
CREATE TABLE IF NOT EXISTS `pps_addresses_per_user` (
  `adr_id` int(11) NOT NULL AUTO_INCREMENT,
  `adr_user` int(6) NOT NULL,
  `adr_line1` varchar(200) NOT NULL,
  `adr_line2` varchar(200) DEFAULT NULL,
  `adr_city` varchar(100) NOT NULL,
  `adr_state` varchar(100) DEFAULT NULL,
  `adr_postal_code` varchar(20) NOT NULL,
  `adr_country` varchar(100) NOT NULL,
  `adr_is_main` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`adr_id`),
  KEY `adr_user` (`adr_user`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Addresses per User' ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `pps_addresses_per_user`
--

INSERT INTO `pps_addresses_per_user` (`adr_id`, `adr_user`, `adr_line1`, `adr_line2`, `adr_city`, `adr_state`, `adr_postal_code`, `adr_country`, `adr_is_main`) VALUES
(3, 10, 'calle 1111', 'calle 2', 'vlc', 'vlc', '46035', 'España', 1),
(4, 13, 'calle 1', 'calle 2', 'VLC', 'VLC', '46035', 'España', 1),
(5, 11, 'calle cita', 'calle zota', 'vlc', 'vlc', '45215', 'España', 1);

-- --------------------------------------------------------

--
-- Table structure for table `pps_categories`
--

DROP TABLE IF EXISTS `pps_categories`;
CREATE TABLE IF NOT EXISTS `pps_categories` (
  `cat_id` int(3) NOT NULL AUTO_INCREMENT,
  `cat_description` varchar(100) NOT NULL,
  PRIMARY KEY (`cat_id`),
  UNIQUE KEY `cat_id` (`cat_id`,`cat_description`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `pps_categories`
--

INSERT INTO `pps_categories` (`cat_id`, `cat_description`) VALUES
(1, 'Frutas cítricas'),
(2, 'Frutas dulces'),
(3, 'Verduras'),
(4, 'Bayas'),
(5, 'Melones'),
(6, 'Frutas tropicales'),
(7, 'Frutos secos');

-- --------------------------------------------------------

--
-- Table structure for table `pps_coupons`
--

DROP TABLE IF EXISTS `pps_coupons`;
CREATE TABLE IF NOT EXISTS `pps_coupons` (
  `cou_id` int(11) NOT NULL AUTO_INCREMENT,
  `cou_code` varchar(12) NOT NULL,
  `cou_discount` int(11) NOT NULL,
  `cou_is_used` varchar(1) NOT NULL,
  PRIMARY KEY (`cou_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `pps_coupons`
--

INSERT INTO `pps_coupons` (`cou_id`, `cou_code`, `cou_discount`, `cou_is_used`) VALUES
(1, '5RH8K7', 10, 'N'),
(2, 'LPRYHV', 20, 'N'),
(3, 'KJBNP1', 15, 'N'),
(4, 'VCZ2G9', 25, 'N'),
(5, 'DDK97X', 30, 'N'),
(6, '1NTJ2B', 35, 'N'),
(7, '34YRYP', 40, 'N'),
(8, '8EAX8C', 45, 'N'),
(9, 'JVZKFW', 50, 'N');

-- --------------------------------------------------------

--
-- Table structure for table `pps_logs_2fa`
--

DROP TABLE IF EXISTS `pps_logs_2fa`;
CREATE TABLE IF NOT EXISTS `pps_logs_2fa` (
  `lfa_id` int(11) NOT NULL AUTO_INCREMENT,
  `lfa_user` int(6) NOT NULL,
  `lfa_ip` varchar(40) NOT NULL,
  `lfa_was_successful` tinyint(1) NOT NULL COMMENT 'True si la verificación fue exitosa, False si fue fallida',
  `lfa_datetime` datetime NOT NULL,
  PRIMARY KEY (`lfa_id`),
  KEY `lfa_user` (`lfa_user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pps_logs_login`
--

DROP TABLE IF EXISTS `pps_logs_login`;
CREATE TABLE IF NOT EXISTS `pps_logs_login` (
  `lol_id` int(11) NOT NULL AUTO_INCREMENT,
  `lol_user` int(6) NOT NULL,
  `lol_ip` varchar(40) NOT NULL,
  `lol_was_correct_login` tinyint(1) NOT NULL COMMENT 'True si el login fue exitoso, False si fue fallido',
  `lol_datetime` datetime NOT NULL,
  PRIMARY KEY (`lol_id`),
  UNIQUE KEY `rlo_id` (`lol_id`,`lol_user`),
  KEY `lol_user` (`lol_user`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro de intentos de login' ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `pps_logs_login`
--

INSERT INTO `pps_logs_login` (`lol_id`, `lol_user`, `lol_ip`, `lol_was_correct_login`, `lol_datetime`) VALUES
(8, 8, '192.168.56.1', 1, '2024-05-20 20:26:08'),
(9, 8, '192.168.56.1', 1, '2024-05-20 20:27:12'),
(10, 9, '192.168.56.1', 1, '2024-05-21 19:21:08'),
(11, 10, '192.168.56.1', 1, '2024-05-21 19:40:55'),
(12, 13, '192.168.56.1', 1, '2024-05-23 19:08:25'),
(13, 13, '192.168.56.1', 1, '2024-05-23 19:28:06'),
(14, 13, '192.168.56.1', 1, '2024-05-23 19:30:36'),
(15, 13, '192.168.56.1', 1, '2024-05-23 19:30:49'),
(16, 13, '192.168.56.1', 1, '2024-05-23 19:31:15'),
(17, 13, '192.168.56.1', 1, '2024-05-23 19:31:29'),
(18, 13, '192.168.56.1', 1, '2024-05-23 20:13:35'),
(19, 10, '192.168.56.1', 1, '2024-05-24 16:58:59'),
(20, 10, '192.168.56.1', 1, '2024-05-24 17:00:59'),
(21, 10, '192.168.56.1', 1, '2024-05-24 17:33:51'),
(22, 13, '192.168.56.1', 1, '2024-05-24 17:41:55'),
(23, 10, '192.168.56.1', 1, '2024-05-24 18:50:15'),
(24, 10, '192.168.56.1', 1, '2024-05-24 18:51:43'),
(25, 10, '192.168.56.1', 1, '2024-05-24 19:38:50'),
(26, 10, '192.168.56.1', 1, '2024-05-25 20:07:56'),
(27, 10, '192.168.56.1', 1, '2024-05-28 16:44:12'),
(28, 10, '192.168.56.1', 1, '2024-05-28 18:23:28'),
(29, 10, '192.168.56.1', 1, '2024-05-28 20:11:37'),
(30, 10, '192.168.56.1', 1, '2024-05-28 20:41:53'),
(31, 10, '192.168.56.1', 1, '2024-05-29 16:30:17'),
(32, 10, '192.168.56.1', 1, '2024-05-29 16:35:30'),
(34, 10, '192.168.56.1', 1, '2024-05-29 16:41:48'),
(35, 10, '192.168.56.1', 1, '2024-05-29 16:58:03'),
(36, 13, '192.168.56.1', 1, '2024-05-29 18:31:16'),
(37, 13, '192.168.56.1', 1, '2024-05-29 19:08:17'),
(38, 13, '192.168.56.1', 1, '2024-05-29 19:35:31'),
(39, 13, '192.168.56.1', 1, '2024-05-29 20:07:20'),
(40, 13, '192.168.56.1', 1, '2024-05-30 20:17:25'),
(41, 10, '::1', 0, '2024-05-31 20:15:34'),
(42, 10, '::1', 1, '2024-05-31 20:15:45'),
(43, 12, '::1', 1, '2024-05-31 20:16:06'),
(44, 12, '::1', 1, '2024-06-04 19:17:03'),
(45, 12, '::1', 1, '2024-06-04 20:06:38'),
(46, 12, '::1', 1, '2024-06-04 20:32:36'),
(47, 10, '::1', 1, '2024-06-04 20:35:42'),
(48, 10, '::1', 1, '2024-06-04 22:39:58'),
(49, 10, '::1', 1, '2024-06-04 22:55:01'),
(50, 13, '192.168.56.1', 1, '2024-06-05 18:17:40'),
(51, 13, '192.168.56.1', 1, '2024-06-05 18:22:01'),
(52, 10, '192.168.56.1', 1, '2024-06-05 18:49:38'),
(53, 13, '192.168.56.1', 1, '2024-06-05 18:51:02'),
(54, 13, '192.168.56.1', 1, '2024-06-05 19:29:01'),
(55, 11, '192.168.56.1', 1, '2024-06-05 19:33:56'),
(56, 13, '::1', 1, '2024-06-06 17:06:53'),
(57, 13, '::1', 1, '2024-06-06 17:08:31'),
(58, 13, '::1', 1, '2024-06-06 17:14:12'),
(59, 13, '::1', 1, '2024-06-06 17:14:34'),
(60, 11, '::1', 1, '2024-06-06 17:23:17'),
(61, 11, '::1', 1, '2024-06-06 17:26:00'),
(62, 10, '::1', 1, '2024-06-06 18:47:37'),
(63, 12, '::1', 1, '2024-06-06 18:53:10'),
(64, 13, '::1', 1, '2024-06-06 19:23:31'),
(65, 12, '::1', 1, '2024-06-07 16:33:44'),
(66, 11, '::1', 1, '2024-06-07 17:03:37'),
(67, 10, '::1', 1, '2024-06-07 17:30:50'),
(68, 11, '::1', 1, '2024-06-07 17:33:38');

-- --------------------------------------------------------

--
-- Table structure for table `pps_logs_recovery`
--

DROP TABLE IF EXISTS `pps_logs_recovery`;
CREATE TABLE IF NOT EXISTS `pps_logs_recovery` (
  `lor_id` int(11) NOT NULL AUTO_INCREMENT,
  `lor_user` int(6) NOT NULL,
  `lor_ip` varchar(12) NOT NULL,
  `lor_datetime` datetime NOT NULL,
  `lor_attempt` int(1) NOT NULL,
  `lor_lock_until` datetime DEFAULT NULL,
  PRIMARY KEY (`lor_id`),
  UNIQUE KEY `lor_id` (`lor_id`,`lor_user`),
  KEY `lor_user` (`lor_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `pps_messages`
--

DROP TABLE IF EXISTS `pps_messages`;
CREATE TABLE IF NOT EXISTS `pps_messages` (
  `msg_id` int(11) NOT NULL AUTO_INCREMENT,
  `msg_user_sender` int(6) NOT NULL,
  `msg_user_receiver` int(6) NOT NULL,
  `msg_message` varchar(500) NOT NULL,
  `msg_datetime` datetime NOT NULL,
  `msg_rol_from` varchar(1) NOT NULL,
  `msg_rol_to` varchar(1) NOT NULL,
  `msg_is_replied` varchar(1) NOT NULL,
  PRIMARY KEY (`msg_id`),
  KEY `msg_user_sender` (`msg_user_sender`),
  KEY `msg_message` (`msg_message`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `pps_messages`
--

INSERT INTO `pps_messages` (`msg_id`, `msg_user_sender`, `msg_user_receiver`, `msg_message`, `msg_datetime`, `msg_rol_from`, `msg_rol_to`, `msg_is_replied`) VALUES
(23, 11, 0, 'msg1', '2024-06-05 20:56:57', 'U', 'S', 'Y'),
(24, 13, 11, 'respuesta 1', '2024-06-05 20:57:58', 'S', 'U', 'Y'),
(25, 11, 13, 'resp v2', '2024-06-05 21:03:21', 'U', 'S', 'Y'),
(26, 13, 11, 'resp v3', '2024-06-05 21:03:33', 'S', 'U', 'Y'),
(27, 11, 13, 'la respuesta', '2024-06-05 21:08:48', 'U', 'S', 'Y'),
(28, 11, 0, 'hola tt\r\n', '2024-06-05 21:11:08', 'U', 'S', 'Y'),
(29, 11, 0, 'hola otra vez', '2024-06-05 21:11:25', 'U', 'S', 'Y'),
(30, 11, 0, 'hola otra vez', '2024-06-05 21:12:01', 'U', 'S', 'Y'),
(31, 13, 11, 'yeeeeee', '2024-06-05 21:13:01', 'S', 'U', 'N'),
(32, 13, 11, 'jelou', '2024-06-05 21:14:18', 'S', 'U', 'N'),
(33, 13, 11, 'nene', '2024-06-05 21:15:35', 'S', 'U', 'Y'),
(34, 13, 11, 'ggg', '2024-06-05 21:16:06', 'S', 'U', 'Y'),
(35, 11, 13, 'ggg2', '2024-06-05 21:16:57', 'U', 'S', 'Y'),
(36, 13, 11, 'aaaa1', '2024-06-05 21:17:26', 'S', 'U', 'Y'),
(37, 11, 13, 'adada', '2024-06-05 21:18:37', 'U', 'S', 'N'),
(38, 11, 13, 'ffff', '2024-06-05 21:18:41', 'U', 'S', 'Y'),
(39, 13, 11, 'ggg', '2024-06-05 21:18:56', 'S', 'U', 'Y'),
(40, 11, 13, 'bbbb', '2024-06-05 21:25:40', 'U', 'S', 'Y'),
(41, 13, 11, 'aaaaa', '2024-06-06 20:21:19', 'S', 'U', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `pps_orders`
--

DROP TABLE IF EXISTS `pps_orders`;
CREATE TABLE IF NOT EXISTS `pps_orders` (
  `ord_id` int(11) NOT NULL AUTO_INCREMENT,
  `ord_user_id` int(11) DEFAULT NULL,
  `ord_purchase_date` date DEFAULT NULL,
  `ord_shipping_date` date DEFAULT NULL,
  `ord_order_status` enum('Creado','PendienteEnvio','Enviado','Pendiente Devolución','Reembolsado') NOT NULL,
  `ord_shipping_address` varchar(255) NOT NULL,
  PRIMARY KEY (`ord_id`),
  KEY `ord_user_id` (`ord_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pps_orders`
--

INSERT INTO `pps_orders` (`ord_id`, `ord_user_id`, `ord_purchase_date`, `ord_shipping_date`, `ord_order_status`, `ord_shipping_address`) VALUES
(4, 1, NULL, NULL, 'Reembolsado', 'sdfds'),
(6, 1, '2024-05-16', '2024-05-15', 'Creado', 'sfsfsdf'),
(7, 1, '2024-05-16', '2024-05-15', 'Enviado', 'sfsfsdf23231'),
(10, 2, '2024-06-14', '2024-06-05', 'PendienteEnvio', 'sdfdsf'),
(12, 11, '2024-06-06', NULL, 'Creado', 'calle cita, calle zota, vlc, vlc, 45215, España');

-- --------------------------------------------------------

--
-- Table structure for table `pps_orders_history`
--

DROP TABLE IF EXISTS `pps_orders_history`;
CREATE TABLE IF NOT EXISTS `pps_orders_history` (
  `ord_hist_id` int(11) NOT NULL AUTO_INCREMENT,
  `ord_hist_order_id` int(11) DEFAULT NULL,
  `ord_hist_transaction_type` enum('Creado','PendienteEnvio','Enviado','Pendiente Devolución','Reembolsado') NOT NULL,
  `ord_hist_transaction_date` datetime DEFAULT NULL,
  `ord_hist_amount` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`ord_hist_id`),
  KEY `ord_hist_order_id` (`ord_hist_order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pps_orders_history`
--

INSERT INTO `pps_orders_history` (`ord_hist_id`, `ord_hist_order_id`, `ord_hist_transaction_type`, `ord_hist_transaction_date`, `ord_hist_amount`) VALUES
(1, 1, 'Creado', NULL, NULL),
(2, 1, 'Creado', '2024-05-10 20:33:45', NULL),
(3, 1, '', '2024-05-10 20:41:14', NULL),
(4, 1, 'Enviado', '2024-05-17 19:57:01', NULL),
(5, 1, '', '2024-05-17 20:02:53', NULL),
(6, 1, '', '2024-05-17 20:03:01', NULL),
(7, 1, '', '2024-05-17 20:05:26', NULL),
(8, 1, '', '2024-05-17 20:06:56', NULL),
(9, 1, 'Creado', '2024-05-17 20:15:28', NULL),
(10, NULL, 'PendienteEnvio', '2024-05-17 20:15:53', NULL),
(11, 1, 'Pendiente Devolución', '2024-05-17 20:17:36', NULL),
(12, 1, 'Creado', '2024-05-17 20:18:21', NULL),
(13, 4, 'Reembolsado', '2024-05-17 20:19:15', NULL),
(14, 1, 'Enviado', '2024-05-24 19:21:55', NULL),
(15, 4, 'Creado', '2024-05-24 19:40:09', NULL),
(16, 4, 'Enviado', '2024-05-31 21:39:39', NULL),
(17, 4, 'Enviado', '2024-05-31 21:40:01', NULL),
(18, 2, 'Creado', '2024-05-31 21:40:41', NULL),
(19, 2, 'Enviado', '2024-05-31 21:40:59', NULL),
(20, 2, 'Enviado', '2024-05-31 21:41:45', NULL),
(21, 2, 'PendienteEnvio', '2024-05-31 21:49:30', NULL),
(22, 2, 'Creado', '2024-05-31 21:53:47', NULL),
(23, 2, 'Creado', '2024-05-31 21:54:09', NULL),
(24, 6, 'Creado', '2024-05-31 21:58:23', NULL),
(25, 2, 'Creado', '2024-05-31 21:58:53', NULL),
(26, 1, 'Enviado', '2024-05-31 23:40:42', NULL),
(27, 1, 'PendienteEnvio', '2024-06-03 06:17:42', NULL),
(28, 7, 'Enviado', '2024-06-03 06:18:13', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pps_order_details`
--

DROP TABLE IF EXISTS `pps_order_details`;
CREATE TABLE IF NOT EXISTS `pps_order_details` (
  `ord_det_id` int(11) NOT NULL AUTO_INCREMENT,
  `ord_det_order_id` int(11) DEFAULT NULL,
  `ord_det_prod_id` int(11) DEFAULT NULL,
  `qty` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`ord_det_id`),
  KEY `ord_det_order_id` (`ord_det_order_id`),
  KEY `ord_det_prod_id` (`ord_det_prod_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `pps_order_details`
--

INSERT INTO `pps_order_details` (`ord_det_id`, `ord_det_order_id`, `ord_det_prod_id`, `qty`, `unit_price`, `subtotal`) VALUES
(1, 1, 1, 1, 5.00, 12.00),
(3, 1, 1, 4, 4.00, 4.00),
(4, 1, 2, 3, 3.00, 3.00),
(5, 1, 2, 4, 5.00, 6.00),
(6, 7, 1, 44, 4.00, 33.00),
(7, 2, 1, 4, 4.00, 4.00),
(8, 12, 1, 15, 1.60, 24.00),
(9, 12, 3, 5, 3.50, 17.50),
(10, 12, 2, 1, 1.80, 1.80);

-- --------------------------------------------------------

--
-- Table structure for table `pps_payment_methods`
--

DROP TABLE IF EXISTS `pps_payment_methods`;
CREATE TABLE IF NOT EXISTS `pps_payment_methods` (
  `pam_id` int(3) NOT NULL AUTO_INCREMENT,
  `pam_description` varchar(30) NOT NULL,
  PRIMARY KEY (`pam_id`),
  UNIQUE KEY `pam_description` (`pam_description`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `pps_payment_methods`
--

INSERT INTO `pps_payment_methods` (`pam_id`, `pam_description`) VALUES
(2, 'PayPal'),
(1, 'Tarjeta de Crédito');

-- --------------------------------------------------------

--
-- Table structure for table `pps_payment_methods_per_user`
--

DROP TABLE IF EXISTS `pps_payment_methods_per_user`;
CREATE TABLE IF NOT EXISTS `pps_payment_methods_per_user` (
  `pmu_id` int(11) NOT NULL AUTO_INCREMENT,
  `pmu_payment_method` int(1) NOT NULL,
  `pmu_user` int(3) NOT NULL,
  `pmu_account_number` varchar(30) NOT NULL,
  `pmu_swift` varchar(20) NOT NULL,
  `pmu_card_number` decimal(16,0) NOT NULL,
  `pmu_cve_number` decimal(3,0) NOT NULL,
  `pmu_cardholder` varchar(50) NOT NULL,
  `pmu_expiration_date` varchar(5) NOT NULL,
  `pmu_online_account` varchar(50) NOT NULL COMMENT 'email',
  `pmu_is_main` tinyint(1) NOT NULL,
  `pmu_online_password` varchar(300) NOT NULL,
  PRIMARY KEY (`pmu_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `pps_payment_methods_per_user`
--

INSERT INTO `pps_payment_methods_per_user` (`pmu_id`, `pmu_payment_method`, `pmu_user`, `pmu_account_number`, `pmu_swift`, `pmu_card_number`, `pmu_cve_number`, `pmu_cardholder`, `pmu_expiration_date`, `pmu_online_account`, `pmu_is_main`, `pmu_online_password`) VALUES
(5, 2, 13, 'A', 'A', 0, 0, 'A', 'A', 'a@a.com', 1, '$2y$10$8Jv5K.mz08ouvtSIl7Nt4e1aWsvXGSIP3uKlGccmXHuiF1S28izIi'),
(6, 2, 11, 'A', 'A', 0, 0, 'A', 'A', 'mi@cuenta.com', 1, '$2y$10$E9pgdJHeYUco.srLUc/b5OSySd7C8KglprjVvbca8Z.rrUUSxYJMq');

-- --------------------------------------------------------

--
-- Table structure for table `pps_permission_per_rol`
--

DROP TABLE IF EXISTS `pps_permission_per_rol`;
CREATE TABLE IF NOT EXISTS `pps_permission_per_rol` (
  `ppr_id` int(3) NOT NULL AUTO_INCREMENT,
  `ppr_rol` varchar(1) NOT NULL,
  `ppr_program` varchar(100) NOT NULL,
  `ppr_allowed` varchar(1) NOT NULL,
  PRIMARY KEY (`ppr_id`)
) ENGINE=InnoDB AUTO_INCREMENT=244 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `pps_permission_per_rol`
--

INSERT INTO `pps_permission_per_rol` (`ppr_id`, `ppr_rol`, `ppr_program`, `ppr_allowed`) VALUES
(2, 'U', 'edit_payment_method.php', 'Y'),
(3, 'V', 'edit_payment_method.php', 'Y'),
(4, 'S', 'edit_payment_method.php', 'Y'),
(5, 'A', 'edit_payment_method.php', 'Y'),
(6, 'U', 'main_profile.php', 'Y'),
(7, 'V', 'main_profile.php', 'Y'),
(8, 'S', 'main_profile.php', 'Y'),
(9, 'A', 'main_profile.php', 'Y'),
(10, 'U', 'payment_methods.php', 'Y'),
(11, 'V', 'payment_methods.php', 'Y'),
(12, 'S', 'payment_methods.php', 'Y'),
(13, 'A', 'payment_methods.php', 'Y'),
(14, 'U', 'usu_address.php', 'Y'),
(15, 'V', 'usu_address.php', 'Y'),
(16, 'S', 'usu_address.php', 'Y'),
(17, 'A', 'usu_address.php', 'Y'),
(18, 'U', 'usu_address_edit.php', 'Y'),
(19, 'V', 'usu_address_edit.php', 'Y'),
(20, 'S', 'usu_address_edit.php', 'Y'),
(21, 'A', 'usu_address_edit.php', 'Y'),
(22, 'U', 'usu_info.php', 'Y'),
(23, 'V', 'usu_info.php', 'Y'),
(24, 'S', 'usu_info.php', 'Y'),
(25, 'A', 'usu_info.php', 'Y'),
(26, 'U', 'usu_new_address.php', 'Y'),
(27, 'V', 'usu_new_address.php', 'Y'),
(28, 'S', 'usu_new_address.php', 'Y'),
(29, 'A', 'usu_new_address.php', 'Y'),
(30, 'U', 'usu_sec.php', 'Y'),
(31, 'V', 'usu_sec.php', 'Y'),
(32, 'S', 'usu_sec.php', 'Y'),
(33, 'A', 'usu_sec.php', 'Y'),
(34, 'V', 'biblioteca.php', 'Y'),
(35, 'V', 'database.php', 'Y'),
(36, 'V', 'editar.php', 'Y'),
(37, 'V', 'gestion_clientes.php', 'Y'),
(38, 'V', 'mainpage.php', 'Y'),
(39, 'V', 'nuevo_producto.php', 'Y'),
(40, 'V', 'stats.php', 'Y'),
(41, 'S', 'CreateTicket.php', 'Y'),
(42, 'S', 'DeleteTicket.php', 'Y'),
(43, 'S', 'EditTicket.php', 'Y'),
(44, 'S', 'ReplyMessage.php', 'Y'),
(45, 'S', 'RolSupport.php', 'Y'),
(46, 'S', 'SendMessage.php', 'Y'),
(47, 'S', 'valCreateTicket.php', 'Y'),
(48, 'S', 'ViewMessage.php', 'Y'),
(49, 'A', 'Comp_User.php', 'Y'),
(50, 'A', 'crear_usuario.php', 'Y'),
(51, 'A', 'Datos_Ventas.php', 'Y'),
(52, 'A', 'Exportar.php', 'Y'),
(53, 'A', 'Generar_Pdf.php', 'Y'),
(54, 'A', 'Gestion_Prod.php', 'Y'),
(55, 'A', 'Gestion_Users.php', 'Y'),
(56, 'A', 'Inventario.php', 'Y'),
(57, 'A', 'Mod_Prod.php', 'Y'),
(58, 'A', 'Mod_user.php', 'Y'),
(59, 'A', 'procesar_modificacion_producto.php', 'Y'),
(60, 'A', 'procesar_modificacion_usuario.php', 'Y'),
(61, 'A', 'Report.php', 'Y'),
(62, 'A', 'Rol_Admin.php', 'Y'),
(63, 'A', 'Trafico_Web.php', 'Y'),
(64, 'U', 'addpedido.php', 'Y'),
(65, 'V', 'addpedido.php', 'Y'),
(66, 'A', 'addpedido.php', 'Y'),
(67, 'S', 'addpedido.php', 'Y'),
(68, 'U', 'config.php', 'Y'),
(69, 'V', 'config.php', 'Y'),
(70, 'A', 'config.php', 'Y'),
(71, 'S', 'config.php', 'Y'),
(72, 'U', 'config-tables-columns.php', 'Y'),
(73, 'V', 'config-tables-columns.php', 'Y'),
(74, 'A', 'config-tables-columns.php', 'Y'),
(75, 'S', 'config-tables-columns.php', 'Y'),
(76, 'U', 'error.php', 'Y'),
(77, 'V', 'error.php', 'Y'),
(78, 'A', 'error.php', 'Y'),
(79, 'S', 'error.php', 'Y'),
(80, 'U', 'helpers.php', 'Y'),
(81, 'V', 'helpers.php', 'Y'),
(82, 'A', 'helpers.php', 'Y'),
(83, 'S', 'helpers.php', 'Y'),
(84, 'U', 'navbar.php', 'Y'),
(85, 'V', 'navbar.php', 'Y'),
(86, 'A', 'navbar.php', 'Y'),
(87, 'S', 'navbar.php', 'Y'),
(88, 'U', 'pps_order_details-create.php', 'Y'),
(89, 'V', 'pps_order_details-create.php', 'Y'),
(90, 'A', 'pps_order_details-create.php', 'Y'),
(91, 'S', 'pps_order_details-create.php', 'Y'),
(92, 'U', 'pps_order_details-index.php', 'Y'),
(93, 'V', 'pps_order_details-index.php', 'Y'),
(94, 'A', 'pps_order_details-index.php', 'Y'),
(95, 'S', 'pps_order_details-index.php', 'Y'),
(96, 'U', 'pps_orders-create.php', 'Y'),
(97, 'V', 'pps_orders-create.php', 'Y'),
(98, 'A', 'pps_orders-create.php', 'Y'),
(99, 'S', 'pps_orders-create.php', 'Y'),
(100, 'U', 'pps_orders-delete.php', 'Y'),
(101, 'V', 'pps_orders-delete.php', 'Y'),
(102, 'A', 'pps_orders-delete.php', 'Y'),
(103, 'S', 'pps_orders-delete.php', 'Y'),
(104, 'U', 'pps_orders-index.php', 'Y'),
(105, 'V', 'pps_orders-index.php', 'Y'),
(106, 'A', 'pps_orders-index.php', 'Y'),
(107, 'S', 'pps_orders-index.php', 'Y'),
(108, 'U', 'pps_orders-read.php', 'Y'),
(109, 'V', 'pps_orders-read.php', 'Y'),
(110, 'A', 'pps_orders-read.php', 'Y'),
(111, 'S', 'pps_orders-read.php', 'Y'),
(112, 'U', 'pps_orders-update.php', 'Y'),
(113, 'V', 'pps_orders-update.php', 'Y'),
(114, 'A', 'pps_orders-update.php', 'Y'),
(115, 'S', 'pps_orders-update.php', 'Y'),
(116, 'U', 'pps_orders_history-index.php', 'Y'),
(117, 'V', 'pps_orders_history-index.php', 'Y'),
(118, 'A', 'pps_orders_history-index.php', 'Y'),
(119, 'S', 'pps_orders_history-index.php', 'Y'),
(120, 'U', 'addpedido.php', 'Y'),
(121, 'V', 'addpedido.php', 'Y'),
(122, 'A', 'addpedido.php', 'Y'),
(123, 'S', 'addpedido.php', 'Y'),
(124, 'U', 'config.php', 'Y'),
(125, 'V', 'config.php', 'Y'),
(126, 'A', 'config.php', 'Y'),
(127, 'S', 'config.php', 'Y'),
(128, 'U', 'config-tables-columns.php', 'Y'),
(129, 'V', 'config-tables-columns.php', 'Y'),
(130, 'A', 'config-tables-columns.php', 'Y'),
(131, 'S', 'config-tables-columns.php', 'Y'),
(132, 'U', 'error.php', 'Y'),
(133, 'V', 'error.php', 'Y'),
(134, 'A', 'error.php', 'Y'),
(135, 'S', 'error.php', 'Y'),
(136, 'U', 'helpers.php', 'Y'),
(137, 'V', 'helpers.php', 'Y'),
(138, 'A', 'helpers.php', 'Y'),
(139, 'S', 'helpers.php', 'Y'),
(140, 'U', 'navbar.php', 'Y'),
(141, 'V', 'navbar.php', 'Y'),
(142, 'A', 'navbar.php', 'Y'),
(143, 'S', 'navbar.php', 'Y'),
(144, 'U', 'pps_order_details-create.php', 'Y'),
(145, 'V', 'pps_order_details-create.php', 'Y'),
(146, 'A', 'pps_order_details-create.php', 'Y'),
(147, 'S', 'pps_order_details-create.php', 'Y'),
(148, 'U', 'pps_order_details-index.php', 'Y'),
(149, 'V', 'pps_order_details-index.php', 'Y'),
(150, 'A', 'pps_order_details-index.php', 'Y'),
(151, 'S', 'pps_order_details-index.php', 'Y'),
(152, 'U', 'pps_orders-create.php', 'Y'),
(153, 'V', 'pps_orders-create.php', 'Y'),
(154, 'A', 'pps_orders-create.php', 'Y'),
(155, 'S', 'pps_orders-create.php', 'Y'),
(156, 'U', 'pps_orders-delete.php', 'Y'),
(157, 'V', 'pps_orders-delete.php', 'Y'),
(158, 'A', 'pps_orders-delete.php', 'Y'),
(159, 'S', 'pps_orders-delete.php', 'Y'),
(160, 'U', 'pps_orders-index.php', 'Y'),
(161, 'V', 'pps_orders-index.php', 'Y'),
(162, 'A', 'pps_orders-index.php', 'Y'),
(163, 'S', 'pps_orders-index.php', 'Y'),
(164, 'U', 'pps_orders-read.php', 'Y'),
(165, 'V', 'pps_orders-read.php', 'Y'),
(166, 'A', 'pps_orders-read.php', 'Y'),
(167, 'S', 'pps_orders-read.php', 'Y'),
(168, 'U', 'pps_orders-update.php', 'Y'),
(169, 'V', 'pps_orders-update.php', 'Y'),
(170, 'A', 'pps_orders-update.php', 'Y'),
(171, 'S', 'pps_orders-update.php', 'Y'),
(172, 'U', 'pps_orders_history-index.php', 'Y'),
(173, 'V', 'pps_orders_history-index.php', 'Y'),
(174, 'A', 'pps_orders_history-index.php', 'Y'),
(175, 'S', 'pps_orders_history-index.php', 'Y'),
(176, 'U', 'addpedido.php', 'Y'),
(177, 'V', 'addpedido.php', 'Y'),
(178, 'A', 'addpedido.php', 'Y'),
(179, 'S', 'addpedido.php', 'Y'),
(180, 'U', 'config.php', 'Y'),
(181, 'V', 'config.php', 'Y'),
(182, 'A', 'config.php', 'Y'),
(183, 'S', 'config.php', 'Y'),
(184, 'U', 'config-tables-columns.php', 'Y'),
(185, 'V', 'config-tables-columns.php', 'Y'),
(186, 'A', 'config-tables-columns.php', 'Y'),
(187, 'S', 'config-tables-columns.php', 'Y'),
(188, 'U', 'error.php', 'Y'),
(189, 'V', 'error.php', 'Y'),
(190, 'A', 'error.php', 'Y'),
(191, 'S', 'error.php', 'Y'),
(192, 'U', 'helpers.php', 'Y'),
(193, 'V', 'helpers.php', 'Y'),
(194, 'A', 'helpers.php', 'Y'),
(195, 'S', 'helpers.php', 'Y'),
(196, 'U', 'navbar.php', 'Y'),
(197, 'V', 'navbar.php', 'Y'),
(198, 'A', 'navbar.php', 'Y'),
(199, 'S', 'navbar.php', 'Y'),
(200, 'U', 'pps_order_details-create.php', 'Y'),
(201, 'V', 'pps_order_details-create.php', 'Y'),
(202, 'A', 'pps_order_details-create.php', 'Y'),
(203, 'S', 'pps_order_details-create.php', 'Y'),
(204, 'U', 'pps_order_details-index.php', 'Y'),
(205, 'V', 'pps_order_details-index.php', 'Y'),
(206, 'A', 'pps_order_details-index.php', 'Y'),
(207, 'S', 'pps_order_details-index.php', 'Y'),
(208, 'U', 'pps_orders-create.php', 'Y'),
(209, 'V', 'pps_orders-create.php', 'Y'),
(210, 'A', 'pps_orders-create.php', 'Y'),
(211, 'S', 'pps_orders-create.php', 'Y'),
(212, 'U', 'pps_orders-delete.php', 'Y'),
(213, 'V', 'pps_orders-delete.php', 'Y'),
(214, 'A', 'pps_orders-delete.php', 'Y'),
(215, 'S', 'pps_orders-delete.php', 'Y'),
(216, 'U', 'pps_orders-index.php', 'Y'),
(217, 'V', 'pps_orders-index.php', 'Y'),
(218, 'A', 'pps_orders-index.php', 'Y'),
(219, 'S', 'pps_orders-index.php', 'Y'),
(220, 'U', 'pps_orders-read.php', 'Y'),
(221, 'V', 'pps_orders-read.php', 'Y'),
(222, 'A', 'pps_orders-read.php', 'Y'),
(223, 'S', 'pps_orders-read.php', 'Y'),
(224, 'U', 'pps_orders-update.php', 'Y'),
(225, 'V', 'pps_orders-update.php', 'Y'),
(226, 'A', 'pps_orders-update.php', 'Y'),
(227, 'S', 'pps_orders-update.php', 'Y'),
(228, 'U', 'pps_orders_history-index.php', 'Y'),
(229, 'V', 'pps_orders_history-index.php', 'Y'),
(230, 'A', 'pps_orders_history-index.php', 'Y'),
(231, 'S', 'pps_orders_history-index.php', 'Y'),
(232, 'A', 'ReplyMessage.php', 'Y'),
(233, 'A', 'SendMessage.php', 'Y'),
(234, 'A', 'ViewMessage.php', 'Y'),
(235, 'U', 'ReplyMessage.php', 'Y'),
(236, 'U', 'SendMessage.php', 'Y'),
(237, 'U', 'ViewMessage.php', 'Y'),
(238, 'V', 'ReplyMessage.php', 'Y'),
(239, 'V', 'SendMessage.php', 'Y'),
(240, 'V', 'ViewMessage.php', 'Y'),
(241, 'U', 'CreateTicket.php', 'Y'),
(242, 'V', 'CreateTicket.php', 'Y'),
(243, 'A', 'CreateTicket.php', 'Y');

-- --------------------------------------------------------

--
-- Table structure for table `pps_products`
--

DROP TABLE IF EXISTS `pps_products`;
CREATE TABLE IF NOT EXISTS `pps_products` (
  `prd_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id_autoincremental',
  `prd_name` varchar(100) NOT NULL,
  `prd_category` int(3) NOT NULL,
  `prd_details` varchar(100) NOT NULL,
  `prd_price` decimal(7,2) NOT NULL,
  `prd_stock` int(11) NOT NULL,
  `prd_image` varchar(250) NOT NULL,
  `prd_on_offer` tinyint(1) DEFAULT 0,
  `prd_offer_price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`prd_id`),
  UNIQUE KEY `prd_name` (`prd_name`,`prd_category`),
  UNIQUE KEY `prd_id` (`prd_id`),
  KEY `prd_category` (`prd_category`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Products' ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `pps_products`
--

INSERT INTO `pps_products` (`prd_id`, `prd_name`, `prd_category`, `prd_details`, `prd_price`, `prd_stock`, `prd_image`, `prd_on_offer`, `prd_offer_price`) VALUES
(1, 'Endivias Espada', 3, 'Endivias Espada, frescas y crujientes, perfectas para ensaladas y platos gourmet.', 1.60, 5, '/0images/endivias-espada.png', 0, NULL),
(2, 'Uvas de Villena', 2, 'Uvas de Villena, frescas y dulces, perfectas para postres y meriendas.', 2.00, 23, '/0images/uvas_villena.png', 1, 1.80),
(3, 'Almendras de Ibiza', 7, 'Almendras de Ibiza, crujientes y sabrosas, ideales como snack o para cocinar.', 3.50, 23, '/0images/almendra-ibiza.png', 0, NULL),
(4, 'Kaki Persimon de La Ribera Alta', 2, 'Kaki Persimon de La Ribera Alta, dulce y jugoso, perfecto para postres.', 2.50, 17, '/0images/Kaki-Persimon.png', 1, 2.20),
(5, 'Tomate El Perello', 3, 'Tomate El Perello, jugoso y con mucho sabor, ideal para ensaladas y salsas.', 1.20, 40, '/0images/tomate-perello.png', 1, 1.00),
(6, 'Chufa de Valencia', 7, 'Chufa de Valencia, perfecta para hacer horchata y como snack saludable.', 4.00, 35, '/0images/chufa.png', 1, 3.50),
(7, 'Manzana Fuji', 2, 'Manzana Fuji, una variedad crujiente y dulce, ideal para comer fresca.', 0.50, 23, '/0images/manzana-fuji.png', 0, NULL),
(8, 'Manzana Granny', 2, 'Manzana Granny Smith, conocida por su sabor ácido y textura crujiente.', 0.45, 15, '/0images/manzana-granny.png', 1, 0.40),
(9, 'Manzanas Pink Lady', 2, 'Manzana Pink Lady, dulce y crujiente, perfecta para postres y ensaladas.', 0.60, 13, '/0images/manzana-pinklady.png', 0, NULL),
(10, 'Naranja', 1, 'Naranjas Valencia, conocidas por su jugosidad y sabor dulce, perfectas para zumos.', 0.30, 50, '/0images/naranja-valencia.png', 0, NULL),
(11, 'Limón', 1, 'Limones Eureka, ideales para aderezos y bebidas refrescantes con su sabor ácido.', 0.25, 40, '/0images/limon-eureka.png', 1, 0.20),
(12, 'Mandarina', 1, 'Mandarinas Clementinas, fáciles de pelar y perfectas para un snack saludable.', 0.35, 60, '/0images/mandarina-clementina.png', 0, NULL),
(13, 'Manzana Roja', 2, 'Manzanas Red Delicious, crujientes y dulces, ideales para postres y meriendas.', 0.50, 30, '/0images/manzana-red-delicious.png', 0, NULL),
(14, 'Plátano', 2, 'Plátanos de Canarias, ricos en potasio, perfectos para un snack rápido y saludable.', 0.40, 45, '/0images/platano-canarias.png', 1, 0.35),
(15, 'Pera', 2, 'Peras Conference, jugosas y dulces, ideales para comer frescas o en ensaladas.', 0.55, 35, '/0images/pera-conference.png', 1, 0.50),
(16, 'Zanahoria', 3, 'Zanahorias Nantesas, frescas y crujientes, perfectas para ensaladas y guisos.', 0.20, 70, '/0images/zanahoria-nantesa.png', 0, NULL),
(17, 'Brócoli', 3, 'Brócoli verde fresco, rico en vitaminas y minerales, ideal para una dieta saludable.', 1.20, 25, '/0images/brocoli-verde.png', 0, NULL),
(18, 'Lechuga', 3, 'Lechuga Romana, fresca y crujiente, perfecta para ensaladas y sándwiches.', 0.90, 40, '/0images/lechuga-romana.png', 0, NULL),
(19, 'Fresa', 4, 'Fresas de Huelva, dulces y jugosas, ideales para postres y batidos.', 1.80, 30, '/0images/fresa-huelva.png', 1, 1.60),
(20, 'Frambuesa', 4, 'Frambuesas rojas frescas, perfectas para postres y como snack saludable.', 2.50, 20, '/0images/frambuesa-roja.png', 0, NULL),
(21, 'Melón', 5, 'Melones Cantalupo, dulces y jugosos, perfectos para el verano.', 3.00, 15, '/0images/melon-cantalupo.png', 0, NULL),
(22, 'Sandía', 5, 'Sandías sin semillas, ideales para un refrescante snack veraniego.', 2.80, 20, '/0images/sandia-sin-semillas.png', 0, NULL),
(23, 'Piña', 6, 'Piñas tropicales frescas, dulces y jugosas, ideales para postres y ensaladas.', 3.50, 25, '/0images/pina-tropical.png', 0, NULL),
(24, 'Mango', 6, 'Mangos Ataulfo frescos, dulces y jugosos, perfectos para batidos y postres.', 2.00, 30, '/0images/mango-ataulfo.png', 1, 1.80),
(25, 'Pepino Holandés', 3, 'Pepino Holandés, ideal para ensaladas, fresco y con un sabor suave.', 0.80, 50, '/0images/pepino-holandes.png', 0, NULL),
(26, 'Tomate Cherry', 3, 'Tomate Cherry, pequeños y dulces, perfectos para ensaladas y snacks.', 2.00, 30, '/0images/tomate-cherry.png', 0, NULL),
(27, 'la fruta de Iván', 6, 'La jefa de todas las frutas', 33.00, 69, '/0images/crown.png', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pps_reviews`
--

DROP TABLE IF EXISTS `pps_reviews`;
CREATE TABLE IF NOT EXISTS `pps_reviews` (
  `rev_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id_autoincremental',
  `rev_product` int(11) NOT NULL,
  `rev_rating` int(11) NOT NULL,
  `rev_message` varchar(500) NOT NULL,
  `rev_datetime` datetime NOT NULL,
  `rev_user_id` int(11) NOT NULL,
  PRIMARY KEY (`rev_id`),
  UNIQUE KEY `rev_id` (`rev_id`,`rev_product`),
  KEY `rev_product` (`rev_product`),
  KEY `pps_reviews_ibfk_2` (`rev_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `pps_reviews`
--

INSERT INTO `pps_reviews` (`rev_id`, `rev_product`, `rev_rating`, `rev_message`, `rev_datetime`, `rev_user_id`) VALUES
(1, 1, 5, 'Las endivias espada son muy frescas y crujientes. Me encantaron en la ensalada.', '2024-05-20 10:00:00', 7),
(2, 1, 3, 'Estaban un poco amargas para mi gusto, pero en general bien.', '2024-05-21 12:00:00', 8),
(3, 1, 5, 'Perfectas para ensaladas. Muy frescas y de buena calidad.', '2024-05-22 14:00:00', 9),
(4, 2, 5, 'Las uvas de Villena son las mejores que he probado. Muy dulces y jugosas.', '2024-05-23 16:00:00', 10),
(5, 2, 4, 'Muy buenas, aunque algunas estaban un poco blandas.', '2024-05-24 18:00:00', 11),
(6, 2, 5, 'Deliciosas y frescas. Perfectas para postres.', '2024-05-25 20:00:00', 12),
(7, 3, 4, 'Las almendras de Ibiza estaban bien, aunque algunas un poco blandas.', '2024-05-26 08:00:00', 13),
(8, 3, 2, 'No me gustaron mucho, esperaban que estuvieran más crujientes.', '2024-05-27 09:00:00', 7),
(9, 3, 4, 'Están bien para un snack, pero no son las mejores almendras que he probado.', '2024-05-28 10:00:00', 8),
(10, 4, 5, 'El Kaki Persimon de La Ribera Alta es increíblemente dulce y jugoso.', '2024-05-29 11:00:00', 9),
(11, 4, 4, 'Muy bueno, aunque prefiero los más firmes.', '2024-05-30 12:00:00', 10),
(12, 4, 3, 'Estaban bien, pero no eran tan dulces como esperaba.', '2024-05-31 13:00:00', 11),
(13, 5, 5, 'Los tomates de El Perello tienen un sabor increíble. Muy recomendados.', '2024-06-01 14:00:00', 12),
(14, 5, 5, 'Muy jugosos y con mucho sabor. Perfectos para ensaladas.', '2024-06-02 15:00:00', 13),
(15, 5, 4, 'Estaban bien, pero algunos estaban un poco verdes.', '2024-06-03 16:00:00', 7),
(16, 6, 5, 'La chufa de Valencia es perfecta para hacer horchata. Muy fresca.', '2024-06-04 17:00:00', 8),
(17, 6, 4, 'Buenas chufas, aunque algunas estaban un poco duras.', '2024-06-05 18:00:00', 9),
(18, 6, 3, 'No estaban tan frescas como esperaba.', '2024-06-06 19:00:00', 10),
(19, 7, 5, 'Las manzanas Fuji son muy crujientes y dulces. Mis favoritas.', '2024-06-07 20:00:00', 11),
(20, 7, 5, 'Muy buenas manzanas, aunque algunas eran un poco pequeñas.', '2024-06-08 21:00:00', 12),
(21, 7, 4, 'Estaban bien, pero prefiero las manzanas más ácidas.', '2024-06-09 22:00:00', 13),
(22, 8, 4, 'Las manzanas Granny Smith son perfectas para quienes gustan de un sabor ácido.', '2024-06-10 23:00:00', 7),
(23, 8, 3, 'No son mis favoritas, pero estaban frescas.', '2024-06-11 08:00:00', 8),
(24, 8, 5, 'Muy frescas y crujientes. Me encantaron.', '2024-06-12 09:00:00', 9),
(25, 9, 5, 'Las manzanas Pink Lady son dulces y crujientes. Ideales para postres.', '2024-06-13 10:00:00', 10),
(26, 9, 5, 'Muy buenas manzanas, aunque algunas eran un poco pequeñas.', '2024-06-14 11:00:00', 11),
(27, 9, 4, 'Dulces y crujientes. Perfectas para comer frescas.', '2024-06-15 12:00:00', 12),
(28, 10, 5, 'Las naranjas de Valencia son muy jugosas y perfectas para zumos.', '2024-06-16 13:00:00', 13),
(29, 10, 5, 'Muy buenas, aunque algunas estaban un poco secas.', '2024-06-17 14:00:00', 7),
(30, 10, 3, 'Esperaba que fueran más dulces.', '2024-06-18 15:00:00', 8),
(31, 11, 5, 'Los limones Eureka son ideales para aderezos. Muy jugosos.', '2024-06-19 16:00:00', 9),
(32, 11, 4, 'Buenos limones, aunque algunos estaban un poco secos.', '2024-06-20 17:00:00', 10),
(33, 11, 4, 'Estaban bien, pero prefiero los limones más grandes.', '2024-06-21 18:00:00', 11),
(34, 12, 5, 'Las mandarinas Clementinas son muy dulces y fáciles de pelar.', '2024-06-22 19:00:00', 12),
(35, 12, 4, 'Buenas mandarinas, aunque algunas eran un poco pequeñas.', '2024-06-23 20:00:00', 13),
(36, 12, 4, 'Estaban bien, pero prefiero las mandarinas más grandes.', '2024-06-24 21:00:00', 7),
(37, 13, 5, 'Las manzanas Red Delicious son crujientes y dulces. Perfectas para meriendas.', '2024-06-25 22:00:00', 8),
(38, 13, 4, 'Muy buenas manzanas, aunque algunas eran un poco pequeñas.', '2024-06-26 08:00:00', 9),
(39, 13, 4, 'Estaban bien, pero prefiero las manzanas más ácidas.', '2024-06-27 09:00:00', 10),
(40, 14, 5, 'Los plátanos de Canarias son muy ricos en potasio. Perfectos para snacks.', '2024-06-28 10:00:00', 11),
(41, 14, 5, 'Muy buenos plátanos, aunque algunos estaban un poco verdes.', '2024-06-29 11:00:00', 12),
(42, 14, 4, 'Estaban bien, pero prefiero los plátanos más maduros.', '2024-06-30 12:00:00', 13),
(43, 15, 5, 'Las peras Conference son jugosas y dulces. Ideales para ensaladas.', '2024-07-01 13:00:00', 7),
(44, 15, 4, 'Buenas peras, aunque algunas eran un poco pequeñas.', '2024-07-02 14:00:00', 8),
(45, 15, 4, 'Estaban bien, pero prefiero las peras más grandes.', '2024-07-03 15:00:00', 9),
(46, 16, 5, 'Las zanahorias Nantesas son muy frescas y crujientes. Perfectas para guisos.', '2024-07-04 16:00:00', 10),
(47, 16, 4, 'Buenas zanahorias, aunque algunas eran un poco pequeñas.', '2024-07-05 17:00:00', 11),
(48, 16, 4, 'Estaban bien, pero prefiero las zanahorias más grandes.', '2024-07-06 18:00:00', 12),
(49, 17, 5, 'El brócoli verde es muy rico en vitaminas. Ideal para una dieta saludable.', '2024-07-07 19:00:00', 13),
(50, 17, 4, 'Buen brócoli, aunque algunas flores eran un poco pequeñas.', '2024-07-08 20:00:00', 7),
(51, 17, 4, 'Estaba bien, pero prefiero el brócoli más grande.', '2024-07-09 21:00:00', 8),
(52, 18, 5, 'La lechuga Romana es muy fresca y crujiente. Perfecta para ensaladas.', '2024-07-10 22:00:00', 9),
(53, 18, 4, 'Buena lechuga, aunque algunas hojas eran un poco pequeñas.', '2024-07-11 08:00:00', 10),
(54, 18, 4, 'Estaba bien, pero prefiero la lechuga más grande.', '2024-07-12 09:00:00', 11),
(55, 19, 5, 'Las fresas de Huelva son muy dulces y jugosas. Ideales para postres.', '2024-07-13 10:00:00', 12),
(56, 19, 4, 'Buenas fresas, aunque algunas eran un poco pequeñas.', '2024-07-14 11:00:00', 13),
(57, 19, 4, 'Estaban bien, pero prefiero las fresas más grandes.', '2024-07-15 12:00:00', 7),
(58, 20, 5, 'Las frambuesas rojas son perfectas para postres. Muy frescas.', '2024-07-16 13:00:00', 8),
(59, 20, 4, 'Buenas frambuesas, aunque algunas eran un poco pequeñas.', '2024-07-17 14:00:00', 9),
(60, 20, 4, 'Estaban bien, pero prefiero las frambuesas más grandes.', '2024-07-18 15:00:00', 10),
(61, 21, 5, 'Los melones Cantalupo son dulces y jugosos. Perfectos para el verano.', '2024-07-19 16:00:00', 11),
(62, 21, 4, 'Buenos melones, aunque algunos estaban un poco secos.', '2024-07-20 17:00:00', 12),
(63, 21, 4, 'Estaban bien, pero prefiero los melones más dulces.', '2024-07-21 18:00:00', 13),
(64, 22, 5, 'Las sandías sin semillas son ideales para un snack refrescante.', '2024-07-22 19:00:00', 7),
(65, 22, 4, 'Buenas sandías, aunque algunas eran un poco pequeñas.', '2024-07-23 20:00:00', 8),
(66, 22, 4, 'Estaban bien, pero prefiero las sandías más grandes.', '2024-07-24 21:00:00', 9),
(67, 23, 5, 'Las piñas tropicales son muy dulces y jugosas. Ideales para postres.', '2024-07-25 22:00:00', 10),
(68, 23, 4, 'Buenas piñas, aunque algunas eran un poco pequeñas.', '2024-07-26 08:00:00', 11),
(69, 23, 4, 'Estaban bien, pero prefiero las piñas más grandes.', '2024-07-27 09:00:00', 12),
(70, 24, 5, 'Los mangos Ataulfo son muy dulces y jugosos. Perfectos para batidos.', '2024-07-28 10:00:00', 13),
(71, 24, 4, 'Buenos mangos, aunque algunos eran un poco pequeños.', '2024-07-29 11:00:00', 7),
(72, 24, 4, 'Estaban bien, pero prefiero los mangos más grandes.', '2024-07-30 12:00:00', 8),
(73, 25, 5, 'El pepino Holandés es fresco y con un sabor suave. Ideal para ensaladas.', '2024-07-31 13:00:00', 9),
(74, 25, 4, 'Buenos pepinos, aunque algunos eran un poco pequeños.', '2024-08-01 14:00:00', 10),
(75, 25, 4, 'Estaban bien, pero prefiero los pepinos más grandes.', '2024-08-02 15:00:00', 11),
(76, 26, 5, 'Los tomates Cherry son pequeños y dulces. Perfectos para snacks.', '2024-08-03 16:00:00', 12),
(77, 26, 4, 'Buenos tomates, aunque algunos eran un poco pequeños.', '2024-08-04 17:00:00', 13),
(78, 26, 4, 'Estaban bien, pero prefiero los tomates más grandes.', '2024-08-05 18:00:00', 7);

-- --------------------------------------------------------

--
-- Table structure for table `pps_tickets`
--

DROP TABLE IF EXISTS `pps_tickets`;
CREATE TABLE IF NOT EXISTS `pps_tickets` (
  `tic_id` int(3) NOT NULL AUTO_INCREMENT,
  `tic_title` varchar(100) NOT NULL,
  `tic_message` varchar(500) NOT NULL,
  `tic_user_creator` int(6) NOT NULL,
  `tic_creation_time` datetime NOT NULL,
  `tic_user_solver` int(11) DEFAULT NULL,
  `tic_priority` varchar(1) NOT NULL,
  `tic_resolution_time` datetime DEFAULT NULL,
  PRIMARY KEY (`tic_id`),
  KEY `tic_user_creator` (`tic_user_creator`),
  KEY `pps_tickets_ibfk_2` (`tic_user_solver`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `pps_tickets`
--

INSERT INTO `pps_tickets` (`tic_id`, `tic_title`, `tic_message`, `tic_user_creator`, `tic_creation_time`, `tic_user_solver`, `tic_priority`, `tic_resolution_time`) VALUES
(7, 'help', 'help me pls', 13, '2024-05-29 17:23:15', 13, 'B', '2024-06-06 19:48:25'),
(8, 'help me', 'I\'m stuck', 13, '2024-05-29 17:24:16', NULL, 'M', '0000-00-00 00:00:00'),
(10, 'NUCLEAR FUSION', 'ALERT, NUCLEAR EXPLOSION IMMINENT', 13, '2024-05-29 18:08:43', NULL, 'A', '0000-00-00 00:00:00'),
(11, 'vv', 'vv', 12, '2024-06-07 14:46:08', NULL, 'B', NULL),
(12, 'adad', 'adad', 11, '2024-06-07 15:06:41', NULL, 'B', NULL),
(13, 'adad', 'adad', 11, '2024-06-07 15:07:54', NULL, 'B', NULL),
(14, 'bb', 'yy', 11, '2024-06-07 15:08:06', NULL, 'B', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pps_users`
--

DROP TABLE IF EXISTS `pps_users`;
CREATE TABLE IF NOT EXISTS `pps_users` (
  `usu_id` int(6) NOT NULL AUTO_INCREMENT COMMENT 'id_autoincremental',
  `usu_type` varchar(1) NOT NULL,
  `usu_rol` varchar(3) NOT NULL COMMENT 'U,V,S,A',
  `usu_status` varchar(1) NOT NULL COMMENT 'N,A,B',
  `usu_verification_code` varchar(250) NOT NULL,
  `usu_datetime` datetime NOT NULL,
  `usu_name` varchar(100) NOT NULL,
  `usu_surnames` varchar(200) NOT NULL,
  `usu_prefix` varchar(5) NOT NULL,
  `usu_phone` int(11) NOT NULL,
  `usu_email` varchar(200) NOT NULL,
  `usu_password` varchar(300) NOT NULL,
  `usu_company` varchar(100) NOT NULL,
  `usu_cif` varchar(12) NOT NULL,
  `usu_web` varchar(50) NOT NULL,
  `usu_documents` varchar(200) NOT NULL,
  `usu_2fa` char(16) DEFAULT NULL,
  `usu_reset_token` varchar(255) NOT NULL,
  `usu_image` varchar(255) NOT NULL,
  PRIMARY KEY (`usu_id`),
  UNIQUE KEY `usu_id` (`usu_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Users' ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `pps_users`
--

INSERT INTO `pps_users` (`usu_id`, `usu_type`, `usu_rol`, `usu_status`, `usu_verification_code`, `usu_datetime`, `usu_name`, `usu_surnames`, `usu_prefix`, `usu_phone`, `usu_email`, `usu_password`, `usu_company`, `usu_cif`, `usu_web`, `usu_documents`, `usu_2fa`, `usu_reset_token`, `usu_image`) VALUES
(7, 'U', 'U', 'N', '', '2024-05-05 19:18:25', 'ivan', 'martinez', '34', 941782087, 'ivan@email.com', '$2y$10$59FmINGOFYhBNua3mXHNqeuGGCvL43dsahVMgYj4QW.AQoG9EZere', '', '', '', '', '', '', ''),
(8, 'U', 'S', 'N', '', '2024-05-20 18:24:49', 'support', 'support', '+34', 654321987, 'fruteria@support.com', '$2y$10$c4J18Nfrh.uW.7PEJr2m.Oi0v/Knt.6FqkilM8H7adWu.YZ4Hkt6u', '', '', '', '', NULL, '', ''),
(9, 'V', 'V', 'N', '', '2024-05-21 17:20:00', 'supplier', '', '', 645712781, 'a@email.com', '$2y$10$A7nNu/Bh31ad/0qrnqmeGeooY7ILcpWKyjl9FnbjNoSRq0yzcJeAa', 'aaaaa', 'A54568245', 'a.com', '/var/www/uploads-eshop/A54568245CiberGAL - Automatización_de_ataques_y_emulación_de_adversarios_con_MITRE_CalderaV2.pdf^_', '', '', ''),
(10, 'U', 'A', 'A', '', '2024-05-21 17:40:45', 'admin', 'admin', '', 696969696, 'admin@admin.com', '$2y$10$jwYGuXH17AmryF.phRISquTikhF5VYtwwBgayHtZbRQtgbzKeVOKi', '', '', '', '', '', '', 'soy_admin.jpg'),
(11, 'U', 'U', 'A', '', '2024-05-21 17:43:24', 'user', 'user', '', 333333333, 'user@user.com', '$2y$10$iIZuw5VZ7f1BkITuFPn2VupQvnMAl6Boffs00AKUiocQ5eBgsEH6K', '', '', '', '', '', '', 'user.jpg'),
(12, 'V', 'V', 'A', '', '2024-05-21 17:45:44', 'company', 'company', '', 666666666, 'company@company.com', '$2y$10$Sk7CTffGw6.gyJOXtn0NauNREJFAyb3lkTB82g5sgjf2R4tx4a0Qm', '', '', '', '', '', '', 'vendor.jpeg'),
(13, 'U', 'S', 'A', '', '2024-05-21 17:58:39', 'support', 'support', '', 2147483647, 'support@support.com', '$2y$10$4MzwXMZz/qG/R3.Ys19DFO9hh3rc0Yc0K0bnhCLw3V5VE78KQdTOC', '', '', '', '', '', '', 'support.jpg');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pps_addresses_per_user`
--
ALTER TABLE `pps_addresses_per_user`
  ADD CONSTRAINT `pps_addresses_per_user_ibfk_1` FOREIGN KEY (`adr_user`) REFERENCES `pps_users` (`usu_id`),
  ADD CONSTRAINT `pps_addresses_per_user_ibfk_2` FOREIGN KEY (`adr_user`) REFERENCES `pps_users` (`usu_id`);

--
-- Constraints for table `pps_logs_login`
--
ALTER TABLE `pps_logs_login`
  ADD CONSTRAINT `pps_logs_login_ibfk_1` FOREIGN KEY (`lol_user`) REFERENCES `pps_users` (`usu_id`) ON UPDATE CASCADE;

--
-- Constraints for table `pps_messages`
--
ALTER TABLE `pps_messages`
  ADD CONSTRAINT `pps_messages_ibfk_1` FOREIGN KEY (`msg_user_sender`) REFERENCES `pps_users` (`usu_id`);

--
-- Constraints for table `pps_products`
--
ALTER TABLE `pps_products`
  ADD CONSTRAINT `pps_products_ibfk_1` FOREIGN KEY (`prd_category`) REFERENCES `pps_categories` (`cat_id`) ON UPDATE CASCADE;

--
-- Constraints for table `pps_reviews`
--
ALTER TABLE `pps_reviews`
  ADD CONSTRAINT `pps_reviews_ibfk_1` FOREIGN KEY (`rev_product`) REFERENCES `pps_products` (`prd_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pps_reviews_ibfk_2` FOREIGN KEY (`rev_user_id`) REFERENCES `pps_users` (`usu_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pps_tickets`
--
ALTER TABLE `pps_tickets`
  ADD CONSTRAINT `pps_tickets_ibfk_1` FOREIGN KEY (`tic_user_creator`) REFERENCES `pps_users` (`usu_id`),
  ADD CONSTRAINT `pps_tickets_ibfk_2` FOREIGN KEY (`tic_user_solver`) REFERENCES `pps_users` (`usu_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
