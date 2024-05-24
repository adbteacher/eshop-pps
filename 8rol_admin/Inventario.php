<?php
	// Establecer conexión a la base de datos
	require_once '../autoload.php';
	$conexion = database::LoadDatabase();

	// Consulta para obtener el inventario de productos con campos relevantes
	$query = "SELECT prd_id, prd_name, prd_price, prd_quantity_shop, prd_stock FROM pps_products";
	$stmt  = $conexion->prepare($query);
	$stmt->execute();
	$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

	// Consulta para obtener la suma de productos por categoría en tienda
	$queryCategoriaTienda = "SELECT pps_categories.cat_description, SUM(pps_products.prd_quantity_shop) as total_tienda
                         FROM pps_products 
                         JOIN pps_categories ON pps_products.prd_category = pps_categories.cat_id 
                         GROUP BY pps_categories.cat_description";
	$stmtCategoriaTienda  = $conexion->prepare($queryCategoriaTienda);
	$stmtCategoriaTienda->execute();
	$productosPorCategoriaTienda = $stmtCategoriaTienda->fetchAll(PDO::FETCH_ASSOC);

	// Consulta para obtener la suma de productos por categoría en stock
	$queryCategoriaStock = "SELECT pps_categories.cat_description, SUM(pps_products.prd_stock) as total_stock
                        FROM pps_products 
                        JOIN pps_categories ON pps_products.prd_category = pps_categories.cat_id 
                        GROUP BY pps_categories.cat_description";
	$stmtCategoriaStock  = $conexion->prepare($queryCategoriaStock);
	$stmtCategoriaStock->execute();
	$productosPorCategoriaStock = $stmtCategoriaStock->fetchAll(PDO::FETCH_ASSOC);

	// Cerrar la conexión a la base de datos
	$conexion = null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario de Productos</title>
    <!-- Incluye Bootstrap CSS -->
<!--    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .table td, .table th {
            vertical-align: middle;
            white-space: nowrap;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <!-- Mostrar la tabla de inventario de productos con campos relevantes -->
    <h2>Inventario de Productos</h2>
    <table class="table table-bordered table-striped w-auto">
        <thead class="thead-dark">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Precio</th>
            <th>Cantidad en Tienda</th>
            <th>Stock</th>
        </tr>
        </thead>
        <tbody>
		<?php foreach ($productos as $producto): ?>
            <tr>
                <td><?= htmlspecialchars($producto['prd_id']); ?></td>
                <td><?= htmlspecialchars($producto['prd_name']); ?></td>
                <td><?= htmlspecialchars($producto['prd_price']); ?></td>
                <td><?= htmlspecialchars($producto['prd_quantity_shop']); ?></td>
                <td><?= htmlspecialchars($producto['prd_stock']); ?></td>
            </tr>
		<?php endforeach; ?>
        </tbody>
    </table>

    <!-- Mostrar la suma de productos por categoría en tienda -->
    <h2>Suma de Productos por Categoría (En Tienda)</h2>
    <table class="table table-bordered table-striped w-auto">
        <thead class="thead-dark">
        <tr>
            <th>Categoría</th>
            <th>Total en Tienda</th>
        </tr>
        </thead>
        <tbody>
		<?php foreach ($productosPorCategoriaTienda as $categoria): ?>
            <tr>
                <td><?= htmlspecialchars($categoria['cat_description']); ?></td>
                <td><?= htmlspecialchars($categoria['total_tienda']); ?></td>
            </tr>
		<?php endforeach; ?>
        </tbody>
    </table>

    <!-- Mostrar la suma de productos por categoría en stock -->
    <h2>Suma de Productos por Categoría (En Stock)</h2>
    <table class="table table-bordered table-striped w-auto">
        <thead class="thead-dark">
        <tr>
            <th>Categoría</th>
            <th>Total en Stock</th>
        </tr>
        </thead>
        <tbody>
		<?php foreach ($productosPorCategoriaStock as $categoria): ?>
            <tr>
                <td><?= htmlspecialchars($categoria['cat_description']); ?></td>
                <td><?= htmlspecialchars($categoria['total_stock']); ?></td>
            </tr>
		<?php endforeach; ?>
        </tbody>
    </table>

    <!-- Botones -->
    <a href="Report.php" class="btn btn-primary">Ver Inventario Completo</a>
    <a href="Generar_Pdf.php" class="btn btn-secondary">Generar Informe en PDF</a>
</div>

<!-- Incluye Bootstrap JS y dependencias -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="/vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

