-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 19, 2024 at 05:57 PM
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
-- Table structure for table `pps_categories`
--

DROP TABLE IF EXISTS `pps_categories`;
CREATE TABLE IF NOT EXISTS `pps_categories` (
  `cat_id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_description` varchar(100) NOT NULL,
  PRIMARY KEY (`cat_id`),
  UNIQUE KEY `cat_id` (`cat_id`,`cat_description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pps_products`
--

DROP TABLE IF EXISTS `pps_products`;
CREATE TABLE IF NOT EXISTS `pps_products` (
  `prd_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id_autoincremental',
  `prd_name` varchar(100) NOT NULL,
  `prd_category` varchar(50) NOT NULL,
  `prd_details` varchar(100) NOT NULL,
  `prd_price` decimal(7,2) NOT NULL,
  `prd_quantity_shop` int(11) NOT NULL,
  `prd_stock` int(11) NOT NULL,
  `prd_image` varchar(250) NOT NULL,
  `prd_description` varchar(100) NOT NULL,
  PRIMARY KEY (`prd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Products';

-- --------------------------------------------------------

--
-- Table structure for table `pps_records_login`
--

DROP TABLE IF EXISTS `pps_records_login`;
CREATE TABLE IF NOT EXISTS `pps_records_login` (
  `rlo_id` int(11) NOT NULL AUTO_INCREMENT,
  `rlo_user` int(11) NOT NULL,
  `rlo_ip` int(11) NOT NULL,
  `rlo_was_correct_login` int(11) NOT NULL,
  `rlo_datetime` int(11) NOT NULL,
  PRIMARY KEY (`rlo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pps_reviews`
--

DROP TABLE IF EXISTS `pps_reviews`;
CREATE TABLE IF NOT EXISTS `pps_reviews` (
  `rev_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id_autoincremental',
  `rev_id_product` int(11) NOT NULL,
  `rev_rating` int(11) NOT NULL,
  `rev_message` varchar(500) NOT NULL,
  `rev_date` int(12) NOT NULL,
  PRIMARY KEY (`rev_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pps_tickets`
--

DROP TABLE IF EXISTS `pps_tickets`;
CREATE TABLE IF NOT EXISTS `pps_tickets` (
  `tic_id` int(3) NOT NULL AUTO_INCREMENT,
  `tic_title` varchar(100) NOT NULL,
  `tic_message` varchar(500) NOT NULL,
  `tic_user_creator` int(11) NOT NULL,
  `tic_user_solver` int(11) NOT NULL,
  `tic_priority` varchar(1) NOT NULL,
  `tic_resolution_time` int(11) NOT NULL,
  PRIMARY KEY (`tic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pps_users`
--

DROP TABLE IF EXISTS `pps_users`;
CREATE TABLE IF NOT EXISTS `pps_users` (
  `usu_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id_autoincremental',
  `usu_type` varchar(1) NOT NULL,
  `usu_rol` varchar(1) NOT NULL,
  `usu_status` varchar(1) NOT NULL,
  `usu_verification_code` varchar(250) NOT NULL,
  `usu_datetime` int(11) NOT NULL COMMENT 'YYYYMMDDhhmmss',
  `usu_name` varchar(100) NOT NULL,
  `usu_surnames` varchar(200) NOT NULL,
  `usu_prefix` varchar(5) NOT NULL,
  `usu_phone` int(11) NOT NULL,
  `usu_address` varchar(200) NOT NULL,
  `usu_email` varchar(200) NOT NULL,
  `usu_password` varchar(300) NOT NULL,
  `usu_company` varchar(100) NOT NULL,
  `usu_cif` varchar(12) NOT NULL,
  `usu_web` varchar(50) NOT NULL,
  `usu_documents` varchar(200) NOT NULL,
  PRIMARY KEY (`usu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Users';
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
