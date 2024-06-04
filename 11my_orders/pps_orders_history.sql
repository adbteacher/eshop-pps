-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 03-06-2024 a las 07:21:03
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
-- Estructura de tabla para la tabla `pps_orders_history`
--

CREATE TABLE `pps_orders_history` (
  `ord_hist_id` int(11) NOT NULL,
  `ord_hist_order_id` int(11) DEFAULT NULL,
  `ord_hist_transaction_type` enum('Creado','PendienteEnvio','Enviado','Pendiente Devolución','Reembolsado') NOT NULL,
  `ord_hist_transaction_date` datetime DEFAULT NULL,
  `ord_hist_amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pps_orders_history`
--

INSERT INTO `pps_orders_history` (`ord_hist_id`, `ord_hist_order_id`, `ord_hist_transaction_type`, `ord_hist_transaction_date`, `ord_hist_amount`) VALUES
(1, 2, 'Creado', NULL, NULL),
(2, 2, 'Creado', '2024-05-10 20:33:45', NULL),
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

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `pps_orders_history`
--
ALTER TABLE `pps_orders_history`
  ADD PRIMARY KEY (`ord_hist_id`),
  ADD KEY `ord_hist_order_id` (`ord_hist_order_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `pps_orders_history`
--
ALTER TABLE `pps_orders_history`
  MODIFY `ord_hist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `pps_orders_history`
--
ALTER TABLE `pps_orders_history`
  ADD CONSTRAINT `pps_orders_history_ibfk_1` FOREIGN KEY (`ord_hist_order_id`) REFERENCES `pps_orders_clase` (`ord_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
