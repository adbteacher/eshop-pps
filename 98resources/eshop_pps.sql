-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 24, 2024 at 05:10 PM
-- Server version: 10.6.5-MariaDB
-- PHP Version: 8.1.0

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COMMENT='Addresses per User' ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `pps_addresses_per_user`
--

INSERT INTO `pps_addresses_per_user` (`adr_id`, `adr_user`, `adr_line1`, `adr_line2`, `adr_city`, `adr_state`, `adr_postal_code`, `adr_country`, `adr_is_main`) VALUES
(3, 10, 'calle 1', 'calle 2', 'vlc', 'vlc', '46035', 'España', 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `pps_categories`
--

INSERT INTO `pps_categories` (`cat_id`, `cat_description`) VALUES
(1, 'Frutas cítricas'),
(2, 'Frutas dulces'),
(3, 'Verduras'),
(4, 'Varios');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COMMENT='Registro de intentos de login' ROW_FORMAT=DYNAMIC;

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
(24, 10, '192.168.56.1', 1, '2024-05-24 18:51:43');

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
  PRIMARY KEY (`lor_id`),
  UNIQUE KEY `lor_id` (`lor_id`,`lor_user`),
  KEY `lor_user` (`lor_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

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
  PRIMARY KEY (`msg_id`),
  KEY `msg_user_sender` (`msg_user_sender`),
  KEY `msg_user_receiver` (`msg_user_receiver`),
  KEY `msg_message` (`msg_message`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `pps_orders`
--

DROP TABLE IF EXISTS `pps_orders`;
CREATE TABLE IF NOT EXISTS `pps_orders` (
  `ord_id` int(11) NOT NULL AUTO_INCREMENT,
  `ord_user_id` int(6) NOT NULL,
  `ord_purchase_date` date DEFAULT NULL,
  `ord_shipping_date` date DEFAULT NULL,
  `ord_order_status` enum('In Process','Shipped','Delivered') NOT NULL,
  `ord_shipping_address` varchar(255) NOT NULL,
  PRIMARY KEY (`ord_id`),
  KEY `ord_user_id` (`ord_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

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
  `pmu_online_password` varchar(50) NOT NULL,
  `pmu_is_main` tinyint(1) NOT NULL,
  PRIMARY KEY (`pmu_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `pps_permission_per_rol`
--

INSERT INTO `pps_permission_per_rol` (`ppr_id`, `ppr_rol`, `ppr_program`, `ppr_allowed`) VALUES
(1, 'A', 'products.php', 'S');

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
  `prd_quantity_shop` int(11) NOT NULL,
  `prd_stock` int(11) NOT NULL,
  `prd_image` varchar(250) NOT NULL,
  `prd_description` varchar(100) NOT NULL,
  PRIMARY KEY (`prd_id`),
  UNIQUE KEY `prd_name` (`prd_name`,`prd_category`),
  KEY `prd_category` (`prd_category`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COMMENT='Products' ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `pps_products`
--

INSERT INTO `pps_products` (`prd_id`, `prd_name`, `prd_category`, `prd_details`, `prd_price`, `prd_quantity_shop`, `prd_stock`, `prd_image`, `prd_description`) VALUES
(1, 'Manzana Fuji', 3, 'Manzana Fuji', '0.55', 23, 23, '/0images/manzana-fuji.png', 'Manzanas Fuji'),
(2, 'Manzana Granny', 3, 'Manzana Granny', '0.45', 15, 15, '../0images/manzana-granny.png', 'Manzana Granny'),
(3, 'Manzanas Pink Lady', 3, 'Manzana Pink Lady', '0.60', 13, 13, '../0images/manzana-pinklady.png', 'Manzana Pink Lady'),
(4, 'Verduras', 3, 'Verduras', '1.50', 115, 115, '../0images/manzana-fuji.png', 'Verduras'),
(5, 'Aaa', 3, 'Aaa', '2.50', 25, 25, '../0images/manzana-fuji.png', 'Aaa'),
(6, 'Bbb', 4, 'Bbb', '4.50', 75, 75, '../0images/manzana-fuji.png', 'Bbb');

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
  PRIMARY KEY (`rev_id`),
  UNIQUE KEY `rev_id` (`rev_id`,`rev_product`),
  KEY `rev_product` (`rev_product`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

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
  `tic_resolution_time` int(11) NOT NULL,
  PRIMARY KEY (`tic_id`),
  KEY `tic_user_creator` (`tic_user_creator`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

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
  PRIMARY KEY (`usu_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COMMENT='Users' ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `pps_users`
--

INSERT INTO `pps_users` (`usu_id`, `usu_type`, `usu_rol`, `usu_status`, `usu_verification_code`, `usu_datetime`, `usu_name`, `usu_surnames`, `usu_prefix`, `usu_phone`, `usu_email`, `usu_password`, `usu_company`, `usu_cif`, `usu_web`, `usu_documents`, `usu_2fa`) VALUES
(7, 'U', 'U', 'N', '', '2024-05-05 19:18:25', 'ivan', 'martinez', '34', 941782088, 'ivan@email.com', '$2y$10$59FmINGOFYhBNua3mXHNqeuGGCvL43dsahVMgYj4QW.AQoG9EZere', '', '', '', '', ''),
(8, 'U', 'S', 'N', '', '2024-05-20 18:24:49', 'support', 'support', '+34', 654321987, 'fruteria@support.com', '$2y$10$c4J18Nfrh.uW.7PEJr2m.Oi0v/Knt.6FqkilM8H7adWu.YZ4Hkt6u', '', '', '', '', NULL),
(9, 'V', 'V', 'N', '', '2024-05-21 17:20:00', 'supplier', '', '', 645712781, 'a@email.com', '$2y$10$A7nNu/Bh31ad/0qrnqmeGeooY7ILcpWKyjl9FnbjNoSRq0yzcJeAa', 'aaaaa', 'A54568245', 'a.com', '/var/www/uploads-eshop/A54568245CiberGAL - Automatización_de_ataques_y_emulación_de_adversarios_con_MITRE_CalderaV2.pdf^_', ''),
(10, 'U', 'A', 'A', '', '2024-05-21 17:40:45', 'admin', 'admin', '', 696969696, 'admin@admin.com', '$2y$10$jwYGuXH17AmryF.phRISquTikhF5VYtwwBgayHtZbRQtgbzKeVOKi', '', '', '', '', ''),
(11, 'U', 'U', 'A', '', '2024-05-21 17:43:24', 'user', 'user', '', 333333333, 'user@user.com', '$2y$10$iIZuw5VZ7f1BkITuFPn2VupQvnMAl6Boffs00AKUiocQ5eBgsEH6K', '', '', '', '', ''),
(12, 'V', 'V', 'A', '', '2024-05-21 17:45:44', 'company', 'company', '', 666666666, 'company@company.com', '$2y$10$Sk7CTffGw6.gyJOXtn0NauNREJFAyb3lkTB82g5sgjf2R4tx4a0Qm', '', '', '', '', ''),
(13, 'U', 'S', 'A', '', '2024-05-21 17:58:39', 'support', 'support', '', 2147483647, 'support@support.com', '$2y$10$4MzwXMZz/qG/R3.Ys19DFO9hh3rc0Yc0K0bnhCLw3V5VE78KQdTOC', '', '', '', '', '');

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
-- Constraints for table `pps_logs_recovery`
--
ALTER TABLE `pps_logs_recovery`
  ADD CONSTRAINT `pps_logs_recovery_ibfk_1` FOREIGN KEY (`lor_user`) REFERENCES `pps_users` (`usu_id`) ON UPDATE CASCADE;

--
-- Constraints for table `pps_messages`
--
ALTER TABLE `pps_messages`
  ADD CONSTRAINT `pps_messages_ibfk_1` FOREIGN KEY (`msg_user_sender`) REFERENCES `pps_users` (`usu_id`),
  ADD CONSTRAINT `pps_messages_ibfk_2` FOREIGN KEY (`msg_user_receiver`) REFERENCES `pps_users` (`usu_id`);

--
-- Constraints for table `pps_orders`
--
ALTER TABLE `pps_orders`
  ADD CONSTRAINT `pps_orders` FOREIGN KEY (`ord_user_id`) REFERENCES `pps_users` (`usu_id`);

--
-- Constraints for table `pps_order_details`
--
ALTER TABLE `pps_order_details`
  ADD CONSTRAINT `pps_order_details_ibfk_1` FOREIGN KEY (`ord_det_order_id`) REFERENCES `pps_orders` (`ord_id`),
  ADD CONSTRAINT `pps_order_details_ibfk_2` FOREIGN KEY (`ord_det_prod_id`) REFERENCES `pps_products` (`prd_id`);

--
-- Constraints for table `pps_payment_methods_per_user`
--
ALTER TABLE `pps_payment_methods_per_user`
  ADD CONSTRAINT `pps_payment_methods_per_user_ibfk_1` FOREIGN KEY (`pmu_user`) REFERENCES `pps_users` (`usu_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pps_payment_methods_per_user_ibfk_2` FOREIGN KEY (`pmu_payment_method`) REFERENCES `pps_payment_methods` (`pam_id`) ON UPDATE CASCADE;

--
-- Constraints for table `pps_products`
--
ALTER TABLE `pps_products`
  ADD CONSTRAINT `pps_products_ibfk_1` FOREIGN KEY (`prd_category`) REFERENCES `pps_categories` (`cat_id`) ON UPDATE CASCADE;

--
-- Constraints for table `pps_reviews`
--
ALTER TABLE `pps_reviews`
  ADD CONSTRAINT `pps_reviews_ibfk_1` FOREIGN KEY (`rev_product`) REFERENCES `pps_products` (`prd_id`) ON UPDATE CASCADE;

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