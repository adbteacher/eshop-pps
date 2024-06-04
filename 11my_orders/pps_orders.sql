-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 03-06-2024 a las 07:20:36
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
-- Estructura de tabla para la tabla `pps_orders`
--

CREATE TABLE `pps_orders` (
  `ord_id` int(11) NOT NULL,
  `ord_user_id` int(11) DEFAULT NULL,
  `ord_purchase_date` date DEFAULT NULL,
  `ord_shipping_date` date DEFAULT NULL,
  `ord_order_status` enum('Creado','PendienteEnvio','Enviado','Pendiente Devolución','Reembolsado') NOT NULL,
  `ord_shipping_address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pps_orders`
--

INSERT INTO `pps_orders` (`ord_id`, `ord_user_id`, `ord_purchase_date`, `ord_shipping_date`, `ord_order_status`, `ord_shipping_address`) VALUES
(1, 1, '2019-01-31', '2019-01-30', 'PendienteEnvio', 'dsfdasfds'),
(2, 1, '0666-05-08', NULL, 'Enviado', 'afdsf'),
(4, 1, NULL, NULL, 'Reembolsado', 'sdfds'),
(6, 1, '2024-05-16', '2024-05-15', 'Creado', 'sfsfsdf'),
(7, 1, '2024-05-16', '2024-05-15', 'Enviado', 'sfsfsdf23231'),
(10, 2, '2024-06-14', '2024-06-05', 'PendienteEnvio', 'sdfdsf'),
(11, 7, NULL, NULL, 'Enviado', '');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `pps_orders`
--
ALTER TABLE `pps_orders`
  ADD PRIMARY KEY (`ord_id`),
  ADD KEY `ord_user_id` (`ord_user_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `pps_orders`
--
ALTER TABLE `pps_orders`
  MODIFY `ord_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `pps_orders`
--
ALTER TABLE `pps_orders`
  ADD CONSTRAINT `pps_orders_ibfk_1` FOREIGN KEY (`ord_user_id`) REFERENCES `pps_users` (`usu_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
