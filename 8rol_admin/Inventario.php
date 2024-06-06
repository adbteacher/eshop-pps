<?php
/*
	 Esta pagina muestra el inventario de productos junto con la capacidad de generar 
     un informe PDF con la libreria TCPDF
	 */
	// Establecer conexión a la base de datos
	require_once '../autoload.php'; // Incluye el archivo de conexión PDO

	session_start();

	// Verificar si el usuario está autenticado
	functions::ActiveSession();

	//Comprobar permisos al programa
	functions::HasPermissions(basename(__FILE__));

	functions::checkAdminAccess();

	// Generar token CSRF si no está definido
	if (empty($_SESSION['csrf_token']))
	{
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}

	// Verificar el token CSRF si se envía un formulario
	if ($_SERVER['REQUEST_METHOD'] === 'POST')
	{
		if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']))
		{
			echo "<p class='text-danger'>Error en la validación CSRF.</p>";
			exit;
		}
	}

	$conexion = database::LoadDatabase();

	// Consulta para obtener el inventario de productos con campos relevantes
	$query = "SELECT prd_name, pps_categories.cat_description AS prd_category, prd_details, prd_price, prd_stock, prd_on_offer, prd_offer_price 
          FROM pps_products 
          JOIN pps_categories ON pps_products.prd_category = pps_categories.cat_id";
	$stmt  = $conexion->prepare($query);
	$stmt->execute();
	$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

	// Consulta para obtener solo los productos en oferta
	$queryOfertas = "SELECT prd_name, pps_categories.cat_description AS prd_category, prd_details, prd_price, prd_stock, prd_on_offer, prd_offer_price 
                 FROM pps_products 
                 JOIN pps_categories ON pps_products.prd_category = pps_categories.cat_id 
                 WHERE prd_on_offer = 1";
	$stmtOfertas  = $conexion->prepare($queryOfertas);
	$stmtOfertas->execute();
	$productosEnOferta = $stmtOfertas->fetchAll(PDO::FETCH_ASSOC);

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
    <link href="/vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table td, .table th {
            vertical-align: middle;
            white-space: nowrap;
        }
    </style>
</head>
<body>
<?php include "../nav.php" ?>
<div class="container mt-5 mb-5">
    <!-- Mostrar la tabla de inventario de productos con campos relevantes -->
    <h2>Inventario de Productos</h2>
    <table class="table table-bordered table-striped w-auto">
        <thead class="thead-dark">
        <tr>
            <th>Nombre</th>
            <th>Categoría</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>En Oferta</th>
            <th>Precio de Oferta</th>
        </tr>
        </thead>
        <tbody>
		<?php foreach ($productos as $producto): ?>
            <tr>
                <td><?= htmlspecialchars($producto['prd_name']); ?></td>
                <td><?= htmlspecialchars($producto['prd_category']); ?></td>
                <td><?= htmlspecialchars($producto['prd_price']); ?></td>
                <td><?= htmlspecialchars($producto['prd_stock']); ?></td>
                <td><?= $producto['prd_on_offer'] ? 'Sí' : 'No'; ?></td>
                <td><?= htmlspecialchars($producto['prd_offer_price']); ?></td>
            </tr>
		<?php endforeach; ?>
        </tbody>
    </table>

    <!-- Mostrar la tabla de productos en oferta -->
    <h2>Productos en Oferta</h2>
    <table class="table table-bordered table-striped w-auto">
        <thead class="thead-dark">
        <tr>
            <th>Nombre</th>
            <th>Categoría</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>Precio de Oferta</th>
        </tr>
        </thead>
        <tbody>
		<?php foreach ($productosEnOferta as $producto): ?>
            <tr>
                <td><?= htmlspecialchars($producto['prd_name']); ?></td>
                <td><?= htmlspecialchars($producto['prd_category']); ?></td>
                <td><?= htmlspecialchars($producto['prd_price']); ?></td>
                <td><?= htmlspecialchars($producto['prd_stock']); ?></td>
                <td><?= htmlspecialchars($producto['prd_offer_price']); ?></td>
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
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <a href="Report.php" class="btn btn-primary">Ver Inventario Completo</a>
        <button type="submit" formaction="Generar_Pdf.php" class="btn btn-secondary">Generar Informe en PDF</button>
    </form>
</div>

<!-- Incluye Bootstrap JS y dependencias -->
<!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> -->
<?php include "../footer.php"; ?>
</body>
</html>