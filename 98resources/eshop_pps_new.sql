-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-05-2024 a las 01:36:28
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

--
-- Volcado de datos para la tabla `pps_addresses_per_user`
--

INSERT INTO `pps_addresses_per_user` (`adr_id`, `adr_user`, `adr_line1`, `adr_line2`, `adr_city`, `adr_state`, `adr_postal_code`, `adr_country`, `adr_is_main`) VALUES
(3, 10, 'calle 1', 'calle 2', 'vlc', 'vlc', '46035', 'España', 1);

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
(1, 'Frutas cítricas'),
(2, 'Frutas dulces'),
(3, 'Verduras'),
(4, 'Bayas'),
(5, 'Melones'),
(6, 'Frutas tropicales'),
(7, 'Frutos secos');

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

--
-- Volcado de datos para la tabla `pps_logs_login`
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
-- Estructura de tabla para la tabla `pps_logs_recovery`
--

DROP TABLE IF EXISTS `pps_logs_recovery`;
CREATE TABLE IF NOT EXISTS `pps_logs_recovery` (
  `lor_id` int(11) NOT NULL AUTO_INCREMENT,
  `lor_user` int(6) NOT NULL,
  `lor_ip` varchar(12) NOT NULL,
  `lor_datetime` datetime NOT NULL,
  `lor_attempt` int(1) NOT NULL,
  `lor_lock_until` datetime,
  PRIMARY KEY (`lor_id`),
  UNIQUE KEY `lor_id` (`lor_id`,`lor_user`),
  KEY `lor_user` (`lor_user`)
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

--
-- Volcado de datos para la tabla `pps_payment_methods`
--

INSERT INTO `pps_payment_methods` (`pam_id`, `pam_description`) VALUES
(2, 'PayPal'),
(1, 'Tarjeta de Crédito');

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
  `pmu_card_number` decimal(16,0) NOT NULL,
  `pmu_cve_number` decimal(3,0) NOT NULL,
  `pmu_cardholder` varchar(50) NOT NULL,
  `pmu_expiration_date` varchar(5) NOT NULL,
  `pmu_online_account` varchar(50) NOT NULL COMMENT 'email',
  `pmu_online_password` varchar(300) NOT NULL,
  `pmu_is_main` tinyint(1) NOT NULL
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

--
-- Volcado de datos para la tabla `pps_permission_per_rol`
--

INSERT INTO `pps_permission_per_rol` (`ppr_id`, `ppr_rol`, `ppr_program`, `ppr_allowed`) VALUES
(1, 'A', 'products.php', 'S');

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
  `prd_stock` int(11) NOT NULL,
  `prd_image` varchar(250) NOT NULL,
  `prd_on_offer` tinyint(1) DEFAULT 0,
  `prd_offer_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Products' ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `pps_products`
--

INSERT INTO `pps_products` (`prd_id`, `prd_name`, `prd_category`, `prd_details`, `prd_price`, `prd_stock`, `prd_image`, `prd_on_offer`, `prd_offer_price`) VALUES
(1, 'Endivias Espada', 3, 'Endivias Espada, frescas y crujientes, perfectas para ensaladas y platos gourmet.', '1.50', 20, '/0images/endivias-espada.png', 0, NULL),
(2, 'Uvas de Villena', 2, 'Uvas de Villena, frescas y dulces, perfectas para postres y meriendas.', '2.00', 25, '/0images/uvas_villena.png', 1, '1.80'),
(3, 'Almendras de Ibiza', 7, 'Almendras de Ibiza, crujientes y sabrosas, ideales como snack o para cocinar.', '3.50', 30, '/0images/almendra-ibiza.png', 0, NULL),
(4, 'Kaki Persimon de La Ribera Alta', 2, 'Kaki Persimon de La Ribera Alta, dulce y jugoso, perfecto para postres.', '2.50', 20, '/0images/Kaki-Persimon.png', 1, '2.20'),
(5, 'Tomate El Perello', 3, 'Tomate El Perello, jugoso y con mucho sabor, ideal para ensaladas y salsas.', '1.20', 40, '/0images/tomate-perello.png', 1, '1.00'),
(6, 'Chufa de Valencia', 7, 'Chufa de Valencia, perfecta para hacer horchata y como snack saludable.', '4.00', 35, '/0images/chufa.png', 1, '3.50'),
(7, 'Manzana Fuji', 2, 'Manzana Fuji, una variedad crujiente y dulce, ideal para comer fresca.', '0.50', 23, '/0images/manzana-fuji.png', 0, NULL),
(8, 'Manzana Granny', 2, 'Manzana Granny Smith, conocida por su sabor ácido y textura crujiente.', '0.45', 15, '/0images/manzana-granny.png', 1, '0.40'),
(9, 'Manzanas Pink Lady', 2, 'Manzana Pink Lady, dulce y crujiente, perfecta para postres y ensaladas.', '0.60', 13, '/0images/manzana-pinklady.png', 0, NULL),
(10, 'Naranja', 1, 'Naranjas Valencia, conocidas por su jugosidad y sabor dulce, perfectas para zumos.', '0.30', 50, '/0images/naranja-valencia.png', 0, NULL),
(11, 'Limón', 1, 'Limones Eureka, ideales para aderezos y bebidas refrescantes con su sabor ácido.', '0.25', 40, '/0images/limon-eureka.png', 1, '0.20'),
(12, 'Mandarina', 1, 'Mandarinas Clementinas, fáciles de pelar y perfectas para un snack saludable.', '0.35', 60, '/0images/mandarina-clementina.png', 0, NULL),
(13, 'Manzana Roja', 2, 'Manzanas Red Delicious, crujientes y dulces, ideales para postres y meriendas.', '0.50', 30, '/0images/manzana-red-delicious.png', 0, NULL),
(14, 'Plátano', 2, 'Plátanos de Canarias, ricos en potasio, perfectos para un snack rápido y saludable.', '0.40', 45, '/0images/platano-canarias.png', 1, '0.35'),
(15, 'Pera', 2, 'Peras Conference, jugosas y dulces, ideales para comer frescas o en ensaladas.', '0.55', 35, '/0images/pera-conference.png', 1, '0.50'),
(16, 'Zanahoria', 3, 'Zanahorias Nantesas, frescas y crujientes, perfectas para ensaladas y guisos.', '0.20', 70, '/0images/zanahoria-nantesa.png', 0, NULL),
(17, 'Brócoli', 3, 'Brócoli verde fresco, rico en vitaminas y minerales, ideal para una dieta saludable.', '1.20', 25, '/0images/brocoli-verde.png', 0, NULL),
(18, 'Lechuga', 3, 'Lechuga Romana, fresca y crujiente, perfecta para ensaladas y sándwiches.', '0.90', 40, '/0images/lechuga-romana.png', 0, NULL),
(19, 'Fresa', 4, 'Fresas de Huelva, dulces y jugosas, ideales para postres y batidos.', '1.80', 30, '/0images/fresa-huelva.png', 1, '1.60'),
(20, 'Frambuesa', 4, 'Frambuesas rojas frescas, perfectas para postres y como snack saludable.', '2.50', 20, '/0images/frambuesa-roja.png', 0, NULL),
(21, 'Melón', 5, 'Melones Cantalupo, dulces y jugosos, perfectos para el verano.', '3.00', 15, '/0images/melon-cantalupo.png', 0, NULL),
(22, 'Sandía', 5, 'Sandías sin semillas, ideales para un refrescante snack veraniego.', '2.80', 20, '/0images/sandia-sin-semillas.png', 0, NULL),
(23, 'Piña', 6, 'Piñas tropicales frescas, dulces y jugosas, ideales para postres y ensaladas.', '3.50', 25, '/0images/pina-tropical.png', 0, NULL),
(24, 'Mango', 6, 'Mangos Ataulfo frescos, dulces y jugosos, perfectos para batidos y postres.', '2.00', 30, '/0images/mango-ataulfo.png', 1, '1.80'),
(25, 'Pepino Holandés', 3, 'Pepino Holandés, ideal para ensaladas, fresco y con un sabor suave.', '0.80', 50, '/0images/pepino-holandes.png', 0, NULL),
(26, 'Tomate Cherry', 3, 'Tomate Cherry, pequeños y dulces, perfectos para ensaladas y snacks.', '2.00', 30, '/0images/tomate-cherry.png', 0, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pps_reviews`
--

CREATE TABLE `pps_reviews` (
  `rev_id` int(11) NOT NULL COMMENT 'id_autoincremental',
  `rev_product` int(11) NOT NULL,
  `rev_rating` int(11) NOT NULL,
  `rev_message` varchar(500) NOT NULL,
  `rev_datetime` datetime NOT NULL,
  `rev_user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `pps_reviews`
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
-- Estructura de tabla para la tabla `pps_tickets`
--

CREATE TABLE `pps_tickets` (
  `tic_id` int(3) NOT NULL,
  `tic_title` varchar(100) NOT NULL,
  `tic_message` varchar(500) NOT NULL,
  `tic_user_creator` int(6) NOT NULL,
  `tic_creation_time` datetime NOT NULL,
  `tic_user_solver` int(11) DEFAULT NULL,
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
  `usu_documents` varchar(200) NOT NULL,
  `usu_2fa` char(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Users' ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `pps_users`
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
  ADD KEY `pps_payment_methods_per_user_ibfk_1` (`pmu_user`),
  ADD KEY `pps_payment_methods_per_user_ibfk_2` (`pmu_payment_method`);

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
  ADD UNIQUE KEY `prd_id` (`prd_id`),
  ADD KEY `prd_category` (`prd_category`);

--
-- Indices de la tabla `pps_reviews`
--
ALTER TABLE `pps_reviews`
  ADD PRIMARY KEY (`rev_id`),
  ADD UNIQUE KEY `rev_id` (`rev_id`,`rev_product`),
  ADD KEY `rev_product` (`rev_product`),
  ADD KEY `pps_reviews_ibfk_2` (`rev_user_id`);

--
-- Indices de la tabla `pps_tickets`
--
ALTER TABLE `pps_tickets`
  ADD PRIMARY KEY (`tic_id`),
  ADD KEY `tic_user_creator` (`tic_user_creator`),
  ADD KEY `pps_tickets_ibfk_2` (`tic_user_solver`);

--
-- Indices de la tabla `pps_users`
--
ALTER TABLE `pps_users`
  ADD PRIMARY KEY (`usu_id`),
  ADD UNIQUE KEY `usu_id` (`usu_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `pps_addresses_per_user`
--
ALTER TABLE `pps_addresses_per_user`
  MODIFY `adr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `pps_categories`
--
ALTER TABLE `pps_categories`
  MODIFY `cat_id` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `pps_coupons`
--
ALTER TABLE `pps_coupons`
  MODIFY `cou_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pps_logs_login`
--
ALTER TABLE `pps_logs_login`
  MODIFY `lol_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

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
  MODIFY `pam_id` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `pps_payment_methods_per_user`
--
ALTER TABLE `pps_payment_methods_per_user`
  MODIFY `pmu_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `pps_permission_per_rol`
--
ALTER TABLE `pps_permission_per_rol`
  MODIFY `ppr_id` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `pps_products`
--
ALTER TABLE `pps_products`
  MODIFY `prd_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id_autoincremental', AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `pps_reviews`
--
ALTER TABLE `pps_reviews`
  MODIFY `rev_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id_autoincremental', AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT de la tabla `pps_tickets`
--
ALTER TABLE `pps_tickets`
  MODIFY `tic_id` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `pps_users`
--
ALTER TABLE `pps_users`
  MODIFY `usu_id` int(6) NOT NULL AUTO_INCREMENT COMMENT 'id_autoincremental', AUTO_INCREMENT=14;

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
  ADD CONSTRAINT `pps_reviews_ibfk_1` FOREIGN KEY (`rev_product`) REFERENCES `pps_products` (`prd_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pps_reviews_ibfk_2` FOREIGN KEY (`rev_user_id`) REFERENCES `pps_users` (`usu_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
