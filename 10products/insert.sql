-- Elimina todos los registros de las tablas
DELETE FROM `pps_reviews`;
DELETE FROM `pps_products`;
DELETE FROM `pps_categories`;

-- Reinicia los contadores de auto-incremento
ALTER TABLE `pps_reviews` AUTO_INCREMENT = 1;
ALTER TABLE `pps_products` AUTO_INCREMENT = 1;
ALTER TABLE `pps_categories` AUTO_INCREMENT = 1;

-- MODIFICACIÓN DE LA BBDD

-- Añadir el campo user_id a la tabla pps_reviews
ALTER TABLE `pps_reviews` ADD `rev_user_id` INT NOT NULL;

-- Eliminar las restricciones de claves foráneas existentes si las hay
ALTER TABLE `pps_reviews` DROP FOREIGN KEY `pps_reviews_ibfk_1`;

-- Modificar las tablas para asegurar la integridad referencial
-- Crear o asegurar que las tablas `pps_users` y `pps_products` tengan índices únicos en los campos referenciados
ALTER TABLE `pps_users` ADD UNIQUE (`usu_id`);
ALTER TABLE `pps_products` ADD UNIQUE (`prd_id`);

-- Modificar la tabla `pps_reviews` para añadir las claves foráneas con ON DELETE CASCADE
ALTER TABLE `pps_reviews` 
ADD CONSTRAINT `pps_reviews_ibfk_1` FOREIGN KEY (`rev_product`) REFERENCES `pps_products` (`prd_id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `pps_reviews_ibfk_2` FOREIGN KEY (`rev_user_id`) REFERENCES `pps_users` (`usu_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Eliminar las columnas prd_description y prd_quantity_shop
ALTER TABLE `pps_products`
DROP COLUMN `prd_description`,
DROP COLUMN `prd_quantity_shop`;

-- Añadir las columnas nuevas para ofertas
ALTER TABLE `pps_products`
ADD COLUMN `prd_on_offer` BOOLEAN DEFAULT FALSE,
ADD COLUMN `prd_offer_price` DECIMAL(10, 2) DEFAULT NULL;


-- Inserta las nuevas categorías
INSERT INTO `pps_categories` (`cat_id`, `cat_description`) VALUES
(1, 'Frutas cítricas'),
(2, 'Frutas dulces'),
(3, 'Verduras'),
(4, 'Bayas'),
(5, 'Melones'),
(6, 'Frutas tropicales'),
(7, 'Frutos secos');

-- Inserta los productos
INSERT INTO `pps_products` (`prd_id`, `prd_name`, `prd_category`, `prd_details`, `prd_price`, `prd_stock`, `prd_image`, `prd_on_offer`, `prd_offer_price`) VALUES
(1, 'Endivias Espada', 3, 'Endivias Espada, frescas y crujientes, perfectas para ensaladas y platos gourmet.', '1.50', 20, '/0images/endivias-espada.png', FALSE, NULL),
(2, 'Uvas de Villena', 2, 'Uvas de Villena, frescas y dulces, perfectas para postres y meriendas.', '2.00', 25, '/0images/uvas_villena.png', TRUE, '1.80'),
(3, 'Almendras de Ibiza', 7, 'Almendras de Ibiza, crujientes y sabrosas, ideales como snack o para cocinar.', '3.50', 30, '/0images/almendra-ibiza.png', FALSE, NULL),
(4, 'Kaki Persimon de La Ribera Alta', 2, 'Kaki Persimon de La Ribera Alta, dulce y jugoso, perfecto para postres.', '2.50', 20, '/0images/Kaki-Persimon.png', TRUE, '2.20'),
(5, 'Tomate El Perello', 3, 'Tomate El Perello, jugoso y con mucho sabor, ideal para ensaladas y salsas.', '1.20', 40, '/0images/tomate-perello.png', TRUE, '1.00'),
(6, 'Chufa de Valencia', 7, 'Chufa de Valencia, perfecta para hacer horchata y como snack saludable.', '4.00', 35, '/0images/chufa.png', TRUE, '3.50'),
(7, 'Manzana Fuji', 2, 'Manzana Fuji, una variedad crujiente y dulce, ideal para comer fresca.', '0.50', 23, '/0images/manzana-fuji.png', FALSE, NULL),
(8, 'Manzana Granny', 2, 'Manzana Granny Smith, conocida por su sabor ácido y textura crujiente.', '0.45', 15, '/0images/manzana-granny.png', TRUE, '0.40'),
(9, 'Manzanas Pink Lady', 2, 'Manzana Pink Lady, dulce y crujiente, perfecta para postres y ensaladas.', '0.60', 13, '/0images/manzana-pinklady.png', FALSE, NULL),
(10, 'Naranja', 1, 'Naranjas Valencia, conocidas por su jugosidad y sabor dulce, perfectas para zumos.', '0.30', 50, '/0images/naranja-valencia.png', FALSE, NULL),
(11, 'Limón', 1, 'Limones Eureka, ideales para aderezos y bebidas refrescantes con su sabor ácido.', '0.25', 40, '/0images/limon-eureka.png', TRUE, '0.20'),
(12, 'Mandarina', 1, 'Mandarinas Clementinas, fáciles de pelar y perfectas para un snack saludable.', '0.35', 60, '/0images/mandarina-clementina.png', FALSE, NULL),
(13, 'Manzana Roja', 2, 'Manzanas Red Delicious, crujientes y dulces, ideales para postres y meriendas.', '0.50', 30, '/0images/manzana-red-delicious.png', FALSE, NULL),
(14, 'Plátano', 2, 'Plátanos de Canarias, ricos en potasio, perfectos para un snack rápido y saludable.', '0.40', 45, '/0images/platano-canarias.png', TRUE, '0.35'),
(15, 'Pera', 2, 'Peras Conference, jugosas y dulces, ideales para comer frescas o en ensaladas.', '0.55', 35, '/0images/pera-conference.png', TRUE, '0.50'),
(16, 'Zanahoria', 3, 'Zanahorias Nantesas, frescas y crujientes, perfectas para ensaladas y guisos.', '0.20', 70, '/0images/zanahoria-nantesa.png', FALSE, NULL),
(17, 'Brócoli', 3, 'Brócoli verde fresco, rico en vitaminas y minerales, ideal para una dieta saludable.', '1.20', 25, '/0images/brocoli-verde.png', FALSE, NULL),
(18, 'Lechuga', 3, 'Lechuga Romana, fresca y crujiente, perfecta para ensaladas y sándwiches.', '0.90', 40, '/0images/lechuga-romana.png', FALSE, NULL),
(19, 'Fresa', 4, 'Fresas de Huelva, dulces y jugosas, ideales para postres y batidos.', '1.80', 30, '/0images/fresa-huelva.png', TRUE, '1.60'),
(20, 'Frambuesa', 4, 'Frambuesas rojas frescas, perfectas para postres y como snack saludable.', '2.50', 20, '/0images/frambuesa-roja.png', FALSE, NULL),
(21, 'Melón', 5, 'Melones Cantalupo, dulces y jugosos, perfectos para el verano.', '3.00', 15, '/0images/melon-cantalupo.png', FALSE, NULL),
(22, 'Sandía', 5, 'Sandías sin semillas, ideales para un refrescante snack veraniego.', '2.80', 20, '/0images/sandia-sin-semillas.png', FALSE, NULL),
(23, 'Piña', 6, 'Piñas tropicales frescas, dulces y jugosas, ideales para postres y ensaladas.', '3.50', 25, '/0images/pina-tropical.png', FALSE, NULL),
(24, 'Mango', 6, 'Mangos Ataulfo frescos, dulces y jugosos, perfectos para batidos y postres.', '2.00', 30, '/0images/mango-ataulfo.png', TRUE, '1.80'),
(25, 'Pepino Holandés', 3, 'Pepino Holandés, ideal para ensaladas, fresco y con un sabor suave.', '0.80', 50, '/0images/pepino-holandes.png', FALSE, NULL),
(26, 'Tomate Cherry', 3, 'Tomate Cherry, pequeños y dulces, perfectos para ensaladas y snacks.', '2.00', 30, '/0images/tomate-cherry.png', FALSE, NULL);

-- Añadir reseñas
INSERT INTO `pps_reviews` (`rev_id`, `rev_product`, `rev_rating`, `rev_message`, `rev_datetime`, `rev_user_id`) VALUES
(1, 1, 4.5, 'Las endivias espada son muy frescas y crujientes. Me encantaron en la ensalada.', '2024-05-20 10:00:00', 7),
(2, 1, 3, 'Estaban un poco amargas para mi gusto, pero en general bien.', '2024-05-21 12:00:00', 8),
(3, 1, 5, 'Perfectas para ensaladas. Muy frescas y de buena calidad.', '2024-05-22 14:00:00', 9),
(4, 2, 5, 'Las uvas de Villena son las mejores que he probado. Muy dulces y jugosas.', '2024-05-23 16:00:00', 10),
(5, 2, 4, 'Muy buenas, aunque algunas estaban un poco blandas.', '2024-05-24 18:00:00', 11),
(6, 2, 4.5, 'Deliciosas y frescas. Perfectas para postres.', '2024-05-25 20:00:00', 12),
(7, 3, 3.5, 'Las almendras de Ibiza estaban bien, aunque algunas un poco blandas.', '2024-05-26 08:00:00', 13),
(8, 3, 2, 'No me gustaron mucho, esperaban que estuvieran más crujientes.', '2024-05-27 09:00:00', 7),
(9, 3, 4, 'Están bien para un snack, pero no son las mejores almendras que he probado.', '2024-05-28 10:00:00', 8),
(10, 4, 5, 'El Kaki Persimon de La Ribera Alta es increíblemente dulce y jugoso.', '2024-05-29 11:00:00', 9),
(11, 4, 4, 'Muy bueno, aunque prefiero los más firmes.', '2024-05-30 12:00:00', 10),
(12, 4, 3, 'Estaban bien, pero no eran tan dulces como esperaba.', '2024-05-31 13:00:00', 11),
(13, 5, 5, 'Los tomates de El Perello tienen un sabor increíble. Muy recomendados.', '2024-06-01 14:00:00', 12),
(14, 5, 4.5, 'Muy jugosos y con mucho sabor. Perfectos para ensaladas.', '2024-06-02 15:00:00', 13),
(15, 5, 3.5, 'Estaban bien, pero algunos estaban un poco verdes.', '2024-06-03 16:00:00', 7),
(16, 6, 5, 'La chufa de Valencia es perfecta para hacer horchata. Muy fresca.', '2024-06-04 17:00:00', 8),
(17, 6, 4, 'Buenas chufas, aunque algunas estaban un poco duras.', '2024-06-05 18:00:00', 9),
(18, 6, 3, 'No estaban tan frescas como esperaba.', '2024-06-06 19:00:00', 10),
(19, 7, 5, 'Las manzanas Fuji son muy crujientes y dulces. Mis favoritas.', '2024-06-07 20:00:00', 11),
(20, 7, 4.5, 'Muy buenas manzanas, aunque algunas eran un poco pequeñas.', '2024-06-08 21:00:00', 12),
(21, 7, 3.5, 'Estaban bien, pero prefiero las manzanas más ácidas.', '2024-06-09 22:00:00', 13),
(22, 8, 4, 'Las manzanas Granny Smith son perfectas para quienes gustan de un sabor ácido.', '2024-06-10 23:00:00', 7),
(23, 8, 3, 'No son mis favoritas, pero estaban frescas.', '2024-06-11 08:00:00', 8),
(24, 8, 5, 'Muy frescas y crujientes. Me encantaron.', '2024-06-12 09:00:00', 9),
(25, 9, 5, 'Las manzanas Pink Lady son dulces y crujientes. Ideales para postres.', '2024-06-13 10:00:00', 10),
(26, 9, 4.5, 'Muy buenas manzanas, aunque algunas eran un poco pequeñas.', '2024-06-14 11:00:00', 11),
(27, 9, 4, 'Dulces y crujientes. Perfectas para comer frescas.', '2024-06-15 12:00:00', 12),
(28, 10, 5, 'Las naranjas de Valencia son muy jugosas y perfectas para zumos.', '2024-06-16 13:00:00', 13),
(29, 10, 4.5, 'Muy buenas, aunque algunas estaban un poco secas.', '2024-06-17 14:00:00', 7),
(30, 10, 3, 'Esperaba que fueran más dulces.', '2024-06-18 15:00:00', 8),
(31, 11, 5, 'Los limones Eureka son ideales para aderezos. Muy jugosos.', '2024-06-19 16:00:00', 9),
(32, 11, 4, 'Buenos limones, aunque algunos estaban un poco secos.', '2024-06-20 17:00:00', 10),
(33, 11, 3.5, 'Estaban bien, pero prefiero los limones más grandes.', '2024-06-21 18:00:00', 11),
(34, 12, 5, 'Las mandarinas Clementinas son muy dulces y fáciles de pelar.', '2024-06-22 19:00:00', 12),
(35, 12, 4, 'Buenas mandarinas, aunque algunas eran un poco pequeñas.', '2024-06-23 20:00:00', 13),
(36, 12, 3.5, 'Estaban bien, pero prefiero las mandarinas más grandes.', '2024-06-24 21:00:00', 7),
(37, 13, 4.5, 'Las manzanas Red Delicious son crujientes y dulces. Perfectas para meriendas.', '2024-06-25 22:00:00', 8),
(38, 13, 4, 'Muy buenas manzanas, aunque algunas eran un poco pequeñas.', '2024-06-26 08:00:00', 9),
(39, 13, 3.5, 'Estaban bien, pero prefiero las manzanas más ácidas.', '2024-06-27 09:00:00', 10),
(40, 14, 5, 'Los plátanos de Canarias son muy ricos en potasio. Perfectos para snacks.', '2024-06-28 10:00:00', 11),
(41, 14, 4.5, 'Muy buenos plátanos, aunque algunos estaban un poco verdes.', '2024-06-29 11:00:00', 12),
(42, 14, 3.5, 'Estaban bien, pero prefiero los plátanos más maduros.', '2024-06-30 12:00:00', 13),
(43, 15, 5, 'Las peras Conference son jugosas y dulces. Ideales para ensaladas.', '2024-07-01 13:00:00', 7),
(44, 15, 4, 'Buenas peras, aunque algunas eran un poco pequeñas.', '2024-07-02 14:00:00', 8),
(45, 15, 3.5, 'Estaban bien, pero prefiero las peras más grandes.', '2024-07-03 15:00:00', 9),
(46, 16, 5, 'Las zanahorias Nantesas son muy frescas y crujientes. Perfectas para guisos.', '2024-07-04 16:00:00', 10),
(47, 16, 4, 'Buenas zanahorias, aunque algunas eran un poco pequeñas.', '2024-07-05 17:00:00', 11),
(48, 16, 3.5, 'Estaban bien, pero prefiero las zanahorias más grandes.', '2024-07-06 18:00:00', 12),
(49, 17, 5, 'El brócoli verde es muy rico en vitaminas. Ideal para una dieta saludable.', '2024-07-07 19:00:00', 13),
(50, 17, 4, 'Buen brócoli, aunque algunas flores eran un poco pequeñas.', '2024-07-08 20:00:00', 7),
(51, 17, 3.5, 'Estaba bien, pero prefiero el brócoli más grande.', '2024-07-09 21:00:00', 8),
(52, 18, 5, 'La lechuga Romana es muy fresca y crujiente. Perfecta para ensaladas.', '2024-07-10 22:00:00', 9),
(53, 18, 4, 'Buena lechuga, aunque algunas hojas eran un poco pequeñas.', '2024-07-11 08:00:00', 10),
(54, 18, 3.5, 'Estaba bien, pero prefiero la lechuga más grande.', '2024-07-12 09:00:00', 11),
(55, 19, 5, 'Las fresas de Huelva son muy dulces y jugosas. Ideales para postres.', '2024-07-13 10:00:00', 12),
(56, 19, 4, 'Buenas fresas, aunque algunas eran un poco pequeñas.', '2024-07-14 11:00:00', 13),
(57, 19, 3.5, 'Estaban bien, pero prefiero las fresas más grandes.', '2024-07-15 12:00:00', 7),
(58, 20, 5, 'Las frambuesas rojas son perfectas para postres. Muy frescas.', '2024-07-16 13:00:00', 8),
(59, 20, 4, 'Buenas frambuesas, aunque algunas eran un poco pequeñas.', '2024-07-17 14:00:00', 9),
(60, 20, 3.5, 'Estaban bien, pero prefiero las frambuesas más grandes.', '2024-07-18 15:00:00', 10),
(61, 21, 5, 'Los melones Cantalupo son dulces y jugosos. Perfectos para el verano.', '2024-07-19 16:00:00', 11),
(62, 21, 4, 'Buenos melones, aunque algunos estaban un poco secos.', '2024-07-20 17:00:00', 12),
(63, 21, 3.5, 'Estaban bien, pero prefiero los melones más dulces.', '2024-07-21 18:00:00', 13),
(64, 22, 5, 'Las sandías sin semillas son ideales para un snack refrescante.', '2024-07-22 19:00:00', 7),
(65, 22, 4, 'Buenas sandías, aunque algunas eran un poco pequeñas.', '2024-07-23 20:00:00', 8),
(66, 22, 3.5, 'Estaban bien, pero prefiero las sandías más grandes.', '2024-07-24 21:00:00', 9),
(67, 23, 5, 'Las piñas tropicales son muy dulces y jugosas. Ideales para postres.', '2024-07-25 22:00:00', 10),
(68, 23, 4, 'Buenas piñas, aunque algunas eran un poco pequeñas.', '2024-07-26 08:00:00', 11),
(69, 23, 3.5, 'Estaban bien, pero prefiero las piñas más grandes.', '2024-07-27 09:00:00', 12),
(70, 24, 5, 'Los mangos Ataulfo son muy dulces y jugosos. Perfectos para batidos.', '2024-07-28 10:00:00', 13),
(71, 24, 4, 'Buenos mangos, aunque algunos eran un poco pequeños.', '2024-07-29 11:00:00', 7),
(72, 24, 3.5, 'Estaban bien, pero prefiero los mangos más grandes.', '2024-07-30 12:00:00', 8),
(73, 25, 5, 'El pepino Holandés es fresco y con un sabor suave. Ideal para ensaladas.', '2024-07-31 13:00:00', 9),
(74, 25, 4, 'Buenos pepinos, aunque algunos eran un poco pequeños.', '2024-08-01 14:00:00', 10),
(75, 25, 3.5, 'Estaban bien, pero prefiero los pepinos más grandes.', '2024-08-02 15:00:00', 11),
(76, 26, 5, 'Los tomates Cherry son pequeños y dulces. Perfectos para snacks.', '2024-08-03 16:00:00', 12),
(77, 26, 4, 'Buenos tomates, aunque algunos eran un poco pequeños.', '2024-08-04 17:00:00', 13),
(78, 26, 3.5, 'Estaban bien, pero prefiero los tomates más grandes.', '2024-08-05 18:00:00', 7);