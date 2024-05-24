<?php
	// Establecer conexión a la base de datos
	require_once '../Database.php';
	$conexion = database::LoadDatabase();

	// Consulta para obtener el total de ventas
	$query = "SELECT COUNT(*) AS cantidad_ventas FROM pps_order_details";
	$stmt  = $conexion->prepare($query);
	$stmt->execute();
	$cantidadVentas = $stmt->fetch(PDO::FETCH_ASSOC);

	// Consulta para obtener el producto más vendido
	$query = "SELECT ord_det_prod_id, SUM(qty) AS cantidad FROM pps_order_details GROUP BY ord_det_prod_id ORDER BY cantidad DESC LIMIT 1";
	$stmt  = $conexion->prepare($query);
	$stmt->execute();
	$productoMasVendido = $stmt->fetch(PDO::FETCH_ASSOC);

	// Consulta para obtener el total de ingresos
	$query = "SELECT SUM(subtotal) AS total_ingresos FROM pps_order_details";
	$stmt  = $conexion->prepare($query);
	$stmt->execute();
	$totalIngresos = $stmt->fetch(PDO::FETCH_ASSOC);

	// Cerrar la conexión a la base de datos
	$conexion = null;

	echo "<h2>Tabla de Ventas</h2>";
	echo "<table border='1'>";
	echo "<tr><th>Métrica</th><th>Valor</th></tr>";
	echo "<tr><td>Total de ventas realizadas</td><td>{$cantidadVentas['cantidad_ventas']}</td></tr>";
	echo "<tr><td>Total de ingresos</td><td>{$totalIngresos['total_ingresos']}</td></tr>";
	echo "<tr><td>Producto más vendido (ID)</td><td>{$productoMasVendido['ord_det_prod_id']} ({$productoMasVendido['cantidad']} ventas)</td></tr>";
	echo "</table>";
	echo "<br>";
	// Botón para redirigir a la página "Report.php"
	echo "<button onclick=\"window.location.href = 'Report.php';\">Ver Reporte Completo</button>";

	// Ahora, añadimos el código JavaScript para crear el gráfico con Chart.js
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis de Ventas</title>
    <!-- Incluye la biblioteca Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<h2>Gráfico de Ventas</h2>
<canvas id="grafico_ventas" width="400" height="200"></canvas>

<script>
	// Datos para el gráfico
	var cantidadVentas = <?php echo $cantidadVentas['cantidad_ventas']; ?>;
	var totalIngresos = <?php echo $totalIngresos['total_ingresos']; ?>;
	var productoMasVendidoID = '<?php echo $productoMasVendido['ord_det_prod_id']; ?>';
	var cantidadProductoMasVendido = <?php echo $productoMasVendido['cantidad']; ?>;

	// Configuración del gráfico
	var ctx = document.getElementById('grafico_ventas').getContext('2d');
	var myChart = new Chart(ctx, {
		type: 'bar',
		data: {
			labels: ['Total de Ventas', 'Total de Ingresos', 'Producto Más Vendido'],
			datasets: [{
				label: 'Valor',
				data: [cantidadVentas, totalIngresos, cantidadProductoMasVendido],
				backgroundColor: [
					'rgba(255, 99, 132, 0.2)',
					'rgba(54, 162, 235, 0.2)',
					'rgba(255, 206, 86, 0.2)'
				],
				borderColor: [
					'rgba(255, 99, 132, 1)',
					'rgba(54, 162, 235, 1)',
					'rgba(255, 206, 86, 1)'
				],
				borderWidth: 1
			}]
		},
		options: {
			scales: {
				y: {
					beginAtZero: true
				}
			}
		}
	});
</script>
</body>
</html>

