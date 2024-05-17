INSERT INTO `pps_categories` (`cat_id`, `cat_description`) VALUES
(1, 'Frutas cítricas'),
(2, 'Frutas dulces'),
(3, 'Verduras'),
(4, 'Varios');
INSERT INTO `pps_products` (`prd_id`, `prd_name`, `prd_category`, `prd_details`, `prd_price`, `prd_quantity_shop`, `prd_stock`, `prd_image`, `prd_description`) VALUES
(1, 'Manzana Fuji', 3, 'Manzana Fuji', '0.50', 23, 23, '/0images/manzana-fuji.png', 'Manzanas Fuji'),
(2, 'Manzana Granny', 3, 'Manzana Granny', '0.45', 15, 15, '../0images/manzana-granny.png', 'Manzana Granny'),
(3, 'Manzanas Pink Lady', 3, 'Manzana Pink Lady', '0.60', 13, 13, '../0images/manzana-pinklady.png', 'Manzana Pink Lady'),
(4, 'Verduras', 3, 'Verduras', '1.50', 115, 115, '../0images/manzana-fuji.png', 'Verduras'),
(5, 'Aaa', 3, 'Aaa', '2.50', 25, 25, '../0images/manzana-fuji.png', 'Aaa'),
(6, 'Bbb', 4, 'Bbb', '4.50', 75, 75, '../0images/manzana-fuji.png', 'Bbb');