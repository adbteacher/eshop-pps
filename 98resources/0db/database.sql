-- phpMyAdmin SQL Dump
-- version 5.2.1-1.el8.remi
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 16-04-2024 a las 20:06:59
-- Versión del servidor: 8.0.36
-- Versión de PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `qajh438`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pps_coupons`
--

CREATE TABLE `pps_coupons` (
  `cou_id` int NOT NULL,
  `cou_code` varchar(12) COLLATE utf8mb4_general_ci NOT NULL,
  `cou_discount` int NOT NULL,
  `cou_is_used` varchar(1) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pps_products`
--

CREATE TABLE `pps_products` (
  `prd_id` int NOT NULL COMMENT 'id_autoincremental',
  `prd_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `prd_category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `prd_details` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `prd_price` decimal(7,2) NOT NULL,
  `prd_quantity_shop` int NOT NULL,
  `prd_stock` int NOT NULL,
  `prd_image` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `prd_description` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Products';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pps_reviews`
--

CREATE TABLE `pps_reviews` (
  `rev_id` int NOT NULL COMMENT 'id_autoincremental',
  `rev_id_product` int NOT NULL,
  `rev_rating` int NOT NULL,
  `rev_message` varchar(500) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pps_users`
--

CREATE TABLE `pps_users` (
  `usu_id` int NOT NULL COMMENT 'id_autoincremental',
  `usu_type` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `usu_rol` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `usu_status` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `usu_verification_code` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `usu_datetime` int NOT NULL COMMENT 'YYYYMMDDhhmmss',
  `usu_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `usu_surnames` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `usu_prefix` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `usu_phone` int NOT NULL,
  `usu_address` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `usu_email` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `usu_password` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `usu_company` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `usu_cif` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `usu_web` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `usu_documents` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Users';

--
-- Estructura de tabla para la tabla `pps_users_addresses`
--
CREATE TABLE pps_user_addresses (
  addr_id INT AUTO_INCREMENT PRIMARY KEY,
  addr_user_id INT NOT NULL,
  addr_line1 VARCHAR(200) NOT NULL,
  addr_line2 VARCHAR(200),
  addr_city VARCHAR(100) NOT NULL,
  addr_state VARCHAR(100),
  addr_postal_code VARCHAR(20) NOT NULL,
  addr_country VARCHAR(100) NOT NULL,
  addr_is_main BOOLEAN NOT NULL DEFAULT FALSE,
  FOREIGN KEY (addr_user_id) REFERENCES pps_users(usu_id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Addresses Users';

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `pps_coupons`
--
ALTER TABLE `pps_coupons`
  ADD PRIMARY KEY (`cou_id`);

--
-- Indices de la tabla `pps_products`
--
ALTER TABLE `pps_products`
  ADD PRIMARY KEY (`prd_id`);

--
-- Indices de la tabla `pps_reviews`
--
ALTER TABLE `pps_reviews`
  ADD PRIMARY KEY (`rev_id`);

--
-- Indices de la tabla `pps_users`
--
ALTER TABLE `pps_users`
  ADD PRIMARY KEY (`usu_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `pps_coupons`
--
ALTER TABLE `pps_coupons`
  MODIFY `cou_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pps_products`
--
ALTER TABLE `pps_products`
  MODIFY `prd_id` int NOT NULL AUTO_INCREMENT COMMENT 'id_autoincremental';

--
-- AUTO_INCREMENT de la tabla `pps_reviews`
--
ALTER TABLE `pps_reviews`
  MODIFY `rev_id` int NOT NULL AUTO_INCREMENT COMMENT 'id_autoincremental';

--
-- AUTO_INCREMENT de la tabla `pps_users`
--
ALTER TABLE `pps_users`
  MODIFY `usu_id` int NOT NULL AUTO_INCREMENT COMMENT 'id_autoincremental';
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

--
-- ALTER TABLE pps_users DROP COLUMN usu_address;
--
--Borro el campo de usu_address, debido a que ahora va a ser una tabal independiente.
