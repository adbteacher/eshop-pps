-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-05-2024 a las 19:43:12
-- Versión del servidor: 10.4.25-MariaDB
-- Versión de PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `pps`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pps_addresses_per_user`
--

CREATE TABLE `pps_addresses_per_user` (
  `adr_id` int(11) NOT NULL,
  `adr_user` int(6) NOT NULL,
  `adr_line1` varchar(200) NOT NULL,
  `adr_line2` varchar(200) DEFAULT NULL,
  `adr_city` varchar(100) NOT NULL,
  `adr_state` varchar(100) DEFAULT NULL,
  `adr_postal_code` varchar(20) NOT NULL,
  `adr_country` varchar(100) NOT NULL,
  `adr_is_main` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Addresses per User' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pps_categories`
--

CREATE TABLE `pps_categories` (
  `cat_id` int(3) NOT NULL,
  `cat_description` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `pps_categories`
--

INSERT INTO `pps_categories` (`cat_id`, `cat_description`) VALUES
(1, 'Frutas'),
(2, 'Verduras'),
(3, 'Alimentos'),
(4, 'AA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pps_coupons`
--

CREATE TABLE `pps_coupons` (
  `cou_id` int(11) NOT NULL,
  `cou_code` varchar(12) NOT NULL,
  `cou_discount` int(11) NOT NULL,
  `cou_is_used` varchar(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pps_logs_login`
--

CREATE TABLE `pps_logs_login` (
  `lol_id` int(11) NOT NULL,
  `lol_user` int(6) NOT NULL,
  `lol_ip` varchar(40) NOT NULL,
  `lol_was_correct_login` tinyint(1) NOT NULL COMMENT 'True si el login fue exitoso, False si fue fallido',
  `lol_datetime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Registro de intentos de login' ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pps_logs_recovery`
--

CREATE TABLE `pps_logs_recovery` (
  `lor_id` int(11) NOT NULL,
  `lor_user` int(6) NOT NULL,
  `lor_ip` varchar(12) NOT NULL,
  `lor_datetime` datetime NOT NULL,
  `lor_attempt` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pps_messages`
--

CREATE TABLE `pps_messages` (
  `msg_id` int(11) NOT NULL,
  `msg_user_sender` int(6) NOT NULL,
  `msg_user_receiver` int(6) NOT NULL,
  `msg_message` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pps_orders`
--

CREATE TABLE `pps_orders` (
  `ord_id` int(11) NOT NULL,
  `ord_user_id` int(6) NOT NULL,
  `ord_purchase_date` date DEFAULT NULL,
  `ord_shipping_date` date DEFAULT NULL,
  `ord_order_status` enum('In Process','Shipped','Delivered') NOT NULL,
  `ord_shipping_address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pps_payment_methods`
--

CREATE TABLE `pps_payment_methods` (
  `pam_id` int(3) NOT NULL,
  `pam_description` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pps_payment_methods_per_user`
--

CREATE TABLE `pps_payment_methods_per_user` (
  `pmu_id` int(11) NOT NULL,
  `pmu_payment_method` int(1) NOT NULL,
  `pmu_user` int(3) NOT NULL,
  `pmu_account_number` varchar(30) NOT NULL,
  `pmu_swift` varchar(20) NOT NULL,
  `pmu_card_number` int(20) NOT NULL,
  `pmu_cve_number` int(3) NOT NULL,
  `pmu_cardholder` varchar(50) NOT NULL,
  `pmu_expiration_date` varchar(5) NOT NULL,
  `pmu_online_account` varchar(50) NOT NULL COMMENT 'email',
  `pmu_online_password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pps_permission_per_rol`
--

CREATE TABLE `pps_permission_per_rol` (
  `ppr_id` int(3) NOT NULL,
  `ppr_rol` varchar(1) NOT NULL,
  `ppr_program` varchar(100) NOT NULL,
  `ppr_allowed` varchar(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pps_products`
--

CREATE TABLE `pps_products` (
  `prd_id` int(11) NOT NULL COMMENT 'id_autoincremental',
  `prd_name` varchar(100) NOT NULL,
  `prd_category` int(3) NOT NULL,
  `prd_details` varchar(100) NOT NULL,
  `prd_price` decimal(7,2) NOT NULL,
  `prd_quantity_shop` int(11) NOT NULL,
  `prd_stock` int(11) NOT NULL,
  `prd_image` varchar(250) NOT NULL,
  `prd_description` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Products' ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `pps_products`
--

INSERT INTO `pps_products` (`prd_id`, `prd_name`, `prd_category`, `prd_details`, `prd_price`, `prd_quantity_shop`, `prd_stock`, `prd_image`, `prd_description`) VALUES
(1, 'Manzanas', 1, 'Manzanas', '3.50', 15, 15, '../0images/manzana-fuji.png', 'Manzanas'),
(2, 'Manzanas', 2, 'Manzanas', '3.50', 15, 15, '../0images/manzana-fuji.png', 'Manzanas'),
(3, 'Manzanas Granny', 2, 'Manzanas', '3.50', 15, 15, '../0images/manzana-fuji.png', 'Manzanas'),
(4, 'Manzanas Verduras', 3, 'Manzanas', '3.50', 15, 15, '../0images/manzana-fuji.png', 'Manzanas'),
(5, 'ManzanasA', 3, 'Manzanas', '3.50', 15, 15, '../0images/manzana-fuji.png', 'Manzanas'),
(6, 'ManzanasBV', 4, 'Manzanas', '3.50', 15, 15, '../0images/manzana-fuji.png', 'Manzanas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pps_reviews`
--

CREATE TABLE `pps_reviews` (
  `rev_id` int(11) NOT NULL COMMENT 'id_autoincremental',
  `rev_product` int(11) NOT NULL,
  `rev_rating` int(11) NOT NULL,
  `rev_message` varchar(500) NOT NULL,
  `rev_datetime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pps_tickets`
--

CREATE TABLE `pps_tickets` (
  `tic_id` int(3) NOT NULL,
  `tic_title` varchar(100) NOT NULL,
  `tic_message` varchar(500) NOT NULL,
  `tic_user_creator` int(6) NOT NULL,
  `tic_user_solver` int(6) NOT NULL,
  `tic_priority` varchar(1) NOT NULL,
  `tic_resolution_time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pps_users`
--

CREATE TABLE `pps_users` (
  `usu_id` int(6) NOT NULL COMMENT 'id_autoincremental',
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
  `usu_documents` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Users' ROW_FORMAT=DYNAMIC;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `pps_addresses_per_user`
--
ALTER TABLE `pps_addresses_per_user`
  ADD PRIMARY KEY (`adr_id`),
  ADD KEY `adr_user` (`adr_user`);

--
-- Indices de la tabla `pps_categories`
--
ALTER TABLE `pps_categories`
  ADD PRIMARY KEY (`cat_id`),
  ADD UNIQUE KEY `cat_id` (`cat_id`,`cat_description`);

--
-- Indices de la tabla `pps_coupons`
--
ALTER TABLE `pps_coupons`
  ADD PRIMARY KEY (`cou_id`);

--
-- Indices de la tabla `pps_logs_login`
--
ALTER TABLE `pps_logs_login`
  ADD PRIMARY KEY (`lol_id`),
  ADD UNIQUE KEY `rlo_id` (`lol_id`,`lol_user`),
  ADD KEY `lol_user` (`lol_user`);

--
-- Indices de la tabla `pps_logs_recovery`
--
ALTER TABLE `pps_logs_recovery`
  ADD PRIMARY KEY (`lor_id`),
  ADD UNIQUE KEY `lor_id` (`lor_id`,`lor_user`),
  ADD KEY `lor_user` (`lor_user`);

--
-- Indices de la tabla `pps_messages`
--
ALTER TABLE `pps_messages`
  ADD PRIMARY KEY (`msg_id`),
  ADD KEY `msg_user_sender` (`msg_user_sender`),
  ADD KEY `msg_user_receiver` (`msg_user_receiver`),
  ADD KEY `msg_message` (`msg_message`);

--
-- Indices de la tabla `pps_orders`
--
ALTER TABLE `pps_orders`
  ADD PRIMARY KEY (`ord_id`),
  ADD KEY `ord_user_id` (`ord_user_id`);

--
-- Indices de la tabla `pps_order_details`
--
ALTER TABLE `pps_order_details`
  ADD PRIMARY KEY (`ord_det_id`),
  ADD KEY `ord_det_order_id` (`ord_det_order_id`),
  ADD KEY `ord_det_prod_id` (`ord_det_prod_id`);

--
-- Indices de la tabla `pps_payment_methods`
--
ALTER TABLE `pps_payment_methods`
  ADD PRIMARY KEY (`pam_id`),
  ADD UNIQUE KEY `pam_description` (`pam_description`);

--
-- Indices de la tabla `pps_payment_methods_per_user`
--
ALTER TABLE `pps_payment_methods_per_user`
  ADD PRIMARY KEY (`pmu_id`),
  ADD UNIQUE KEY `pmu_payment_method` (`pmu_payment_method`,`pmu_user`),
  ADD UNIQUE KEY `pmu_account_number` (`pmu_account_number`,`pmu_swift`),
  ADD UNIQUE KEY `pmu_card_number` (`pmu_card_number`,`pmu_cve_number`,`pmu_cardholder`,`pmu_expiration_date`),
  ADD UNIQUE KEY `pmu_online_account` (`pmu_online_account`,`pmu_online_password`),
  ADD KEY `pmu_user` (`pmu_user`);

--
-- Indices de la tabla `pps_permission_per_rol`
--
ALTER TABLE `pps_permission_per_rol`
  ADD PRIMARY KEY (`ppr_id`);

--
-- Indices de la tabla `pps_products`
--
ALTER TABLE `pps_products`
  ADD PRIMARY KEY (`prd_id`),
  ADD UNIQUE KEY `prd_name` (`prd_name`,`prd_category`),
  ADD KEY `prd_category` (`prd_category`);

--
-- Indices de la tabla `pps_reviews`
--
ALTER TABLE `pps_reviews`
  ADD PRIMARY KEY (`rev_id`),
  ADD UNIQUE KEY `rev_id` (`rev_id`,`rev_product`),
  ADD KEY `rev_product` (`rev_product`);

--
-- Indices de la tabla `pps_tickets`
--
ALTER TABLE `pps_tickets`
  ADD PRIMARY KEY (`tic_id`),
  ADD KEY `tic_user_creator` (`tic_user_creator`),
  ADD KEY `tic_user_solver` (`tic_user_solver`);

--
-- Indices de la tabla `pps_users`
--
ALTER TABLE `pps_users`
  ADD PRIMARY KEY (`usu_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `pps_addresses_per_user`
--
ALTER TABLE `pps_addresses_per_user`
  MODIFY `adr_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pps_categories`
--
ALTER TABLE `pps_categories`
  MODIFY `cat_id` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `pps_coupons`
--
ALTER TABLE `pps_coupons`
  MODIFY `cou_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pps_logs_login`
--
ALTER TABLE `pps_logs_login`
  MODIFY `lol_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pps_logs_recovery`
--
ALTER TABLE `pps_logs_recovery`
  MODIFY `lor_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pps_messages`
--
ALTER TABLE `pps_messages`
  MODIFY `msg_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pps_orders`
--
ALTER TABLE `pps_orders`
  MODIFY `ord_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `pps_order_details`
--
ALTER TABLE `pps_order_details`
  MODIFY `ord_det_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pps_payment_methods`
--
ALTER TABLE `pps_payment_methods`
  MODIFY `pam_id` int(3) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pps_payment_methods_per_user`
--
ALTER TABLE `pps_payment_methods_per_user`
  MODIFY `pmu_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pps_permission_per_rol`
--
ALTER TABLE `pps_permission_per_rol`
  MODIFY `ppr_id` int(3) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pps_products`
--
ALTER TABLE `pps_products`
  MODIFY `prd_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id_autoincremental', AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `pps_reviews`
--
ALTER TABLE `pps_reviews`
  MODIFY `rev_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id_autoincremental';

--
-- AUTO_INCREMENT de la tabla `pps_tickets`
--
ALTER TABLE `pps_tickets`
  MODIFY `tic_id` int(3) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pps_users`
--
ALTER TABLE `pps_users`
  MODIFY `usu_id` int(6) NOT NULL AUTO_INCREMENT COMMENT 'id_autoincremental', AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `pps_addresses_per_user`
--
ALTER TABLE `pps_addresses_per_user`
  ADD CONSTRAINT `pps_addresses_per_user_ibfk_1` FOREIGN KEY (`adr_user`) REFERENCES `pps_users` (`usu_id`),
  ADD CONSTRAINT `pps_addresses_per_user_ibfk_2` FOREIGN KEY (`adr_user`) REFERENCES `pps_users` (`usu_id`);

--
-- Filtros para la tabla `pps_logs_login`
--
ALTER TABLE `pps_logs_login`
  ADD CONSTRAINT `pps_logs_login_ibfk_1` FOREIGN KEY (`lol_user`) REFERENCES `pps_users` (`usu_id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `pps_logs_recovery`
--
ALTER TABLE `pps_logs_recovery`
  ADD CONSTRAINT `pps_logs_recovery_ibfk_1` FOREIGN KEY (`lor_user`) REFERENCES `pps_users` (`usu_id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `pps_messages`
--
ALTER TABLE `pps_messages`
  ADD CONSTRAINT `pps_messages_ibfk_1` FOREIGN KEY (`msg_user_sender`) REFERENCES `pps_users` (`usu_id`),
  ADD CONSTRAINT `pps_messages_ibfk_2` FOREIGN KEY (`msg_user_receiver`) REFERENCES `pps_users` (`usu_id`);

--
-- Filtros para la tabla `pps_orders`
--
ALTER TABLE `pps_orders`
  ADD CONSTRAINT `pps_orders` FOREIGN KEY (`ord_user_id`) REFERENCES `pps_users` (`usu_id`);

--
-- Filtros para la tabla `pps_order_details`
--
ALTER TABLE `pps_order_details`
  ADD CONSTRAINT `pps_order_details_ibfk_1` FOREIGN KEY (`ord_det_order_id`) REFERENCES `pps_orders` (`ord_id`),
  ADD CONSTRAINT `pps_order_details_ibfk_2` FOREIGN KEY (`ord_det_prod_id`) REFERENCES `pps_products` (`prd_id`);

--
-- Filtros para la tabla `pps_payment_methods_per_user`
--
ALTER TABLE `pps_payment_methods_per_user`
  ADD CONSTRAINT `pps_payment_methods_per_user_ibfk_1` FOREIGN KEY (`pmu_user`) REFERENCES `pps_users` (`usu_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pps_payment_methods_per_user_ibfk_2` FOREIGN KEY (`pmu_payment_method`) REFERENCES `pps_payment_methods` (`pam_id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `pps_products`
--
ALTER TABLE `pps_products`
  ADD CONSTRAINT `pps_products_ibfk_1` FOREIGN KEY (`prd_category`) REFERENCES `pps_categories` (`cat_id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `pps_reviews`
--
ALTER TABLE `pps_reviews`
  ADD CONSTRAINT `pps_reviews_ibfk_1` FOREIGN KEY (`rev_product`) REFERENCES `pps_products` (`prd_id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `pps_tickets`
--
ALTER TABLE `pps_tickets`
  ADD CONSTRAINT `pps_tickets_ibfk_1` FOREIGN KEY (`tic_user_creator`) REFERENCES `pps_users` (`usu_id`),
  ADD CONSTRAINT `pps_tickets_ibfk_2` FOREIGN KEY (`tic_user_solver`) REFERENCES `pps_users` (`usu_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
