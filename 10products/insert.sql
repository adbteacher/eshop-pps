-- Elimina todos los registros de las tablas
DELETE FROM `pps_reviews`;
DELETE FROM `pps_products`;
DELETE FROM `pps_categories`;

-- Reinicia los contadores de auto-incremento
ALTER TABLE `pps_reviews` AUTO_INCREMENT = 1;
ALTER TABLE `pps_products` AUTO_INCREMENT = 1;
ALTER TABLE `pps_categories` AUTO_INCREMENT = 1;

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
INSERT INTO `pps_products` (`prd_id`, `prd_name`, `prd_category`, `prd_details`, `prd_price`, `prd_quantity_shop`, `prd_stock`, `prd_image`, `prd_description`) VALUES
(1, 'Endivias Espada', 3, 'Endivias Espada, frescas y crujientes, perfectas para ensaladas y platos gourmet.', '1.50', 20, 20, '/0images/endivias-espada.png', 'Endivias Espada frescas'),
(2, 'Uvas de Villena', 2, 'Uvas de Villena, frescas y dulces, perfectas para postres y meriendas.', '2.00', 25, 25, '/0images/uvas_villena.png', 'Uvas frescas de Villena'),
(3, 'Almendras de Ibiza', 7, 'Almendras de Ibiza, crujientes y sabrosas, ideales como snack o para cocinar.', '3.50', 30, 30, '/0images/almendra-ibiza.png', 'Almendras frescas de Ibiza'),
(4, 'Kaki Persimon de La Ribera Alta', 2, 'Kaki Persimon de La Ribera Alta, dulce y jugoso, perfecto para postres.', '2.50', 20, 20, '/0images/Kaki-Persimon.png', 'Kaki Persimon fresco de La Ribera Alta'),
(5, 'Tomate El Perello', 3, 'Tomate El Perello, jugoso y con mucho sabor, ideal para ensaladas y salsas.', '1.20', 40, 40, '/0images/tomate-perello.png', 'Tomate fresco de El Perello'),
(6, 'Chufa de Valencia', 7, 'Chufa de Valencia, perfecta para hacer horchata y como snack saludable.', '4.00', 35, 35, '/0images/chufa.png', 'Chufa fresca de Valencia'),
(7, 'Manzana Fuji', 2, 'Manzana Fuji, una variedad crujiente y dulce, ideal para comer fresca.', '0.50', 23, 23, '/0images/manzana-fuji.png', 'Manzanas Fuji frescas'),
(8, 'Manzana Granny', 2, 'Manzana Granny Smith, conocida por su sabor ácido y textura crujiente.', '0.45', 15, 15, '/0images/manzana-granny.png', 'Manzanas Granny Smith'),
(9, 'Manzanas Pink Lady', 2, 'Manzana Pink Lady, dulce y crujiente, perfecta para postres y ensaladas.', '0.60', 13, 13, '/0images/manzana-pinklady.png', 'Manzanas Pink Lady frescas'),
(10, 'Naranja', 1, 'Naranjas Valencia, conocidas por su jugosidad y sabor dulce, perfectas para zumos.', '0.30', 50, 50, '/0images/naranja-valencia.png', 'Naranjas frescas de Valencia'),
(11, 'Limón', 1, 'Limones Eureka, ideales para aderezos y bebidas refrescantes con su sabor ácido.', '0.25', 40, 40, '/0images/limon-eureka.png', 'Limones Eureka jugosos'),
(12, 'Mandarina', 1, 'Mandarinas Clementinas, fáciles de pelar y perfectas para un snack saludable.', '0.35', 60, 60, '/0images/mandarina-clementina.png', 'Mandarinas Clementinas dulces'),
(13, 'Manzana Roja', 2, 'Manzanas Red Delicious, crujientes y dulces, ideales para postres y meriendas.', '0.50', 30, 30, '/0images/manzana-red-delicious.png', 'Manzanas Red Delicious crujientes'),
(14, 'Plátano', 2, 'Plátanos de Canarias, ricos en potasio, perfectos para un snack rápido y saludable.', '0.40', 45, 45, '/0images/platano-canarias.png', 'Plátanos de Canarias frescos'),
(15, 'Pera', 2, 'Peras Conference, jugosas y dulces, ideales para comer frescas o en ensaladas.', '0.55', 35, 35, '/0images/pera-conference.png', 'Peras Conference jugosas'),
(16, 'Zanahoria', 3, 'Zanahorias Nantesas, frescas y crujientes, perfectas para ensaladas y guisos.', '0.20', 70, 70, '/0images/zanahoria-nantesa.png', 'Zanahorias Nantesas frescas'),
(17, 'Brócoli', 3, 'Brócoli verde fresco, rico en vitaminas y minerales, ideal para una dieta saludable.', '1.20', 25, 25, '/0images/brocoli-verde.png', 'Brócoli verde fresco'),
(18, 'Lechuga', 3, 'Lechuga Romana, fresca y crujiente, perfecta para ensaladas y sándwiches.', '0.90', 40, 40, '/0images/lechuga-romana.png', 'Lechuga romana fresca'),
(19, 'Fresa', 4, 'Fresas de Huelva, dulces y jugosas, ideales para postres y batidos.', '1.80', 30, 30, '/0images/fresa-huelva.png', 'Fresas frescas de Huelva'),
(20, 'Frambuesa', 4, 'Frambuesas rojas frescas, perfectas para postres y como snack saludable.', '2.50', 20, 20, '/0images/frambuesa-roja.png', 'Frambuesas rojas frescas'),
(21, 'Melón', 5, 'Melones Cantalupo, dulces y jugosos, perfectos para el verano.', '3.00', 15, 15, '/0images/melon-cantalupo.png', 'Melones Cantalupo frescos'),
(22, 'Sandía', 5, 'Sandías sin semillas, ideales para un refrescante snack veraniego.', '2.80', 20, 20, '/0images/sandia-sin-semillas.png', 'Sandías frescas sin semillas'),
(23, 'Piña', 6, 'Piñas tropicales frescas, dulces y jugosas, ideales para postres y ensaladas.', '3.50', 25, 25, '/0images/pina-tropical.png', 'Piñas tropicales frescas'),
(24, 'Mango', 6, 'Mangos Ataulfo frescos, dulces y jugosos, perfectos para batidos y postres.', '2.00', 30, 30, '/0images/mango-ataulfo.png', 'Mangos Ataulfo frescos'),
(25, 'Pepino Holandés', 3, 'Pepino Holandés, ideal para ensaladas, fresco y con un sabor suave.', '0.80', 50, 50, '/0images/pepino-holandes.png', 'Pepino Holandés fresco'),
(26, 'Tomate Cherry', 3, 'Tomate Cherry, pequeños y dulces, perfectos para ensaladas y snacks.', '2.00', 30, 30, '/0images/tomate-cherry.png', 'Tomates Cherry frescos');


-- Reviews de prueba
/*INSERT INTO `pps_reviews` (`rev_id`, `rev_product`, `rev_rating`, `rev_message`, `rev_datetime`) VALUES
(2, 1, 1, 'wad', '2024-05-22 12:11:52'),
(3, 1, 1, 'dawdw', '2024-05-22 12:12:09'),
(4, 1, 1, 'wadwa', '2024-05-22 12:13:10'),
(5, 1, 3, 'awdaw', '2024-05-22 12:13:15'),
(6, 6, 4, 'w', '2024-05-22 13:20:59');*/
