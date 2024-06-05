-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 03-06-2024 a las 07:19:03
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `eshop_pps`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pps_order_details`
--

CREATE TABLE `pps_order_details` (
  `ord_det_id` int(11) NOT NULL,
  `ord_det_order_id` int(11) DEFAULT NULL,
  `ord_det_prod_id` int(11) DEFAULT NULL,
  `qty` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `pps_order_details`
--

INSERT INTO `pps_order_details` (`ord_det_id`, `ord_det_order_id`, `ord_det_prod_id`, `qty`, `unit_price`, `subtotal`) VALUES
(1, 1, 1, 1, 5.00, 12.00),
(3, NULL, 1, 4, 4.00, 4.00),
(4, NULL, 2, 3, 3.00, 3.00),
(5, NULL, 2, 4, 5.00, 6.00),
(6, 7, 1, 44, 4.00, 33.00),
(7, 2, 1, 4, 4.00, 4.00);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `pps_order_details`
--
ALTER TABLE `pps_order_details`
  ADD PRIMARY KEY (`ord_det_id`),
  ADD KEY `ord_det_order_id` (`ord_det_order_id`),
  ADD KEY `ord_det_prod_id` (`ord_det_prod_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `pps_order_details`
--
ALTER TABLE `pps_order_details`
  MODIFY `ord_det_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `pps_order_details`
--
ALTER TABLE `pps_order_details`
  ADD CONSTRAINT `pps_order_details_ibfk_1` FOREIGN KEY (`ord_det_order_id`) REFERENCES `pps_orders_clase` (`ord_id`),
  ADD CONSTRAINT `pps_order_details_ibfk_2` FOREIGN KEY (`ord_det_prod_id`) REFERENCES `pps_products` (`prd_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
