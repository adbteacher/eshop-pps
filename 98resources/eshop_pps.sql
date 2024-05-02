-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 26, 2024 at 06:07 PM
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Addresses per User' ROW_FORMAT=DYNAMIC;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Registro de intentos de login' ROW_FORMAT=DYNAMIC;

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
-- Table structure for table `invalid_tokens` store tokens used in recovery
--

DROP TABLE IF EXISTS `invalid_tokens`;
CREATE TABLE IF NOT EXISTS `invalid_tokens` (
    `token` VARCHAR(255) NOT NULL,
    `expiry_date` DATETIME NOT NULL,
    PRIMARY KEY (`token`)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

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
  `pmu_card_number` int(20) NOT NULL,
  `pmu_cve_number` int(3) NOT NULL,
  `pmu_cardholder` varchar(50) NOT NULL,
  `pmu_expiration_date` varchar(5) NOT NULL,
  `pmu_online_account` varchar(50) NOT NULL COMMENT 'email',
  `pmu_online_password` varchar(50) NOT NULL,
  PRIMARY KEY (`pmu_id`),
  UNIQUE KEY `pmu_payment_method` (`pmu_payment_method`,`pmu_user`),
  UNIQUE KEY `pmu_account_number` (`pmu_account_number`,`pmu_swift`),
  UNIQUE KEY `pmu_card_number` (`pmu_card_number`,`pmu_cve_number`,`pmu_cardholder`,`pmu_expiration_date`),
  UNIQUE KEY `pmu_online_account` (`pmu_online_account`,`pmu_online_password`),
  KEY `pmu_user` (`pmu_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Products' ROW_FORMAT=DYNAMIC;

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
  `tic_user_solver` int(6) NOT NULL,
  `tic_priority` varchar(1) NOT NULL,
  `tic_resolution_time` int(11) NOT NULL,
  PRIMARY KEY (`tic_id`),
  KEY `tic_user_creator` (`tic_user_creator`),
  KEY `tic_user_solver` (`tic_user_solver`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

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
  PRIMARY KEY (`usu_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='Users' ROW_FORMAT=DYNAMIC;

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
