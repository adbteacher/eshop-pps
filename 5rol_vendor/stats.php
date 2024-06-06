<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas de Ventas</title>
    <link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php
	session_start();
	require_once '../autoload.php';
	require_once 'biblioteca.php';

	// Verificar si el usuario está autenticado
	functions::ActiveSession();

	//Comprobar permisos al programa
	functions::HasPermissions(basename(__FILE__));

	// Generar y almacenar el token CSRF si no existe
	if (empty($_SESSION['csrf_token']))
	{
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}

	include "../nav.php"; // Incluye el Navbar
?>
<div class="container mt-5">
    <h1>Estadísticas de Ventas</h1>
	<?php
		$conn = GetDatabaseConnection();

		$ventas_totales_sql = "SELECT COUNT(*) AS total_ventas, SUM(subtotal) AS ingresos_totales FROM pps_order_details";
		$stmt               = $conn->query($ventas_totales_sql);
		$ventas_totales     = $stmt->fetch(PDO::FETCH_ASSOC);

		$productos_mas_vendidos_sql = "SELECT p.prd_name, SUM(od.qty) AS cantidad_vendida 
                                   FROM pps_order_details od 
                                   JOIN pps_products p ON od.ord_det_prod_id = p.prd_id 
                                   GROUP BY p.prd_name 
                                   ORDER BY cantidad_vendida DESC 
                                   LIMIT 5";
		$stmt                       = $conn->query($productos_mas_vendidos_sql);
		$productos_mas_vendidos     = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$tendencias_ventas_sql = "SELECT DATE_FORMAT(o.ord_purchase_date, '%Y-%m') AS mes, SUM(od.subtotal) AS ingresos_mensuales 
                              FROM pps_order_details od 
                              JOIN pps_orders o ON od.ord_det_order_id = o.ord_id 
                              GROUP BY mes 
                              ORDER BY mes";
		$stmt                  = $conn->query($tendencias_ventas_sql);
		$tendencias_ventas     = $stmt->fetchAll(PDO::FETCH_ASSOC);

		cerrar_conexion();
	?>
    <div class="my-4">
        <h2>Resumen de Ventas</h2>
        <p>Número total de ventas: <?php echo $ventas_totales['total_ventas']; ?></p>
        <p>Ingresos totales generados: €<?php echo number_format($ventas_totales['ingresos_totales'], 2); ?></p>
    </div>

    <div class="my-4">
        <h2>Productos Más Vendidos</h2>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad Vendida</th>
            </tr>
            </thead>
            <tbody>
			<?php foreach ($productos_mas_vendidos as $producto) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($producto['prd_name']); ?></td>
                    <td><?php echo $producto['cantidad_vendida']; ?></td>
                </tr>
			<?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="my-4">
        <h2>Tendencias de Ventas Mensuales</h2>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Mes</th>
                <th>Ingresos Mensuales</th>
            </tr>
            </thead>
            <tbody>
			<?php foreach ($tendencias_ventas as $tendencia) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($tendencia['mes']); ?></td>
                    <td>€<?php echo number_format($tendencia['ingresos_mensuales'], 2); ?></td>
                </tr>
			<?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <form action="mainpage.php" method="post" class="my-4">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <button type="submit" name="Volver" formaction="mainpage.php" class="btn btn-primary">Volver</button>
    </form>
</div>
<?php include "../footer.php"; // Incluye el footer ?>
<script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
	if ($_SERVER['REQUEST_METHOD'] === 'POST')
	{
		// Validar el token CSRF
		if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']))
		{
			echo "<div class='alert alert-danger'>Error, vuelva a intentarlo más tarde.</div>";
			error_log("Error en la validación CSRF.");
			exit();
		}

		if (isset($_POST['Volver']))
		{
			header('Location: mainpage.php');
			exit();
		}
	}
?>
