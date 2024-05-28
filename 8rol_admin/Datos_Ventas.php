<?php
// Establecer conexión a la base de datos
require_once '../Database.php';
require_once '../Functions.php';
session_start();
functions::checkAdminAccess();

/*if (!isset($_SESSION['UserRol'])) {
    echo "<p class='text-danger'>Acceso denegado. No se encontró el rol de usuario en la sesión.</p>";
    exit;
}

// Verificar si el usuario es administrador
if ($_SESSION["UserRol"] !== 'A') {
    echo "<p class='text-danger'>Acceso denegado. No tienes permisos para acceder a esta página.</p>";
    exit;
}*/

// Generar token CSRF si no está definido
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo "<p class='text-danger'>Error en la validación CSRF.</p>";
        exit;
    }
}

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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis de Ventas</title>
    <link href="/vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Incluye la biblioteca Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include "../nav.php" ?>
<div class="container mt-5">
    <h2>Gráfico de Ventas</h2>
    <canvas id="grafico_ventas" width="400" height="200"></canvas>
    
    <?php
    echo "<h2>Tabla de Ventas</h2>";
    echo "<table border='1' class='table table-bordered'>";
    echo "<tr><th>Métrica</th><th>Valor</th></tr>";
    echo "<tr><td>Total de ventas realizadas</td><td>{$cantidadVentas['cantidad_ventas']}</td></tr>";
    echo "<tr><td>Total de ingresos</td><td>{$totalIngresos['total_ingresos']}</td></tr>";
    echo "<tr><td>Producto más vendido (ID)</td><td>{$productoMasVendido['ord_det_prod_id']} ({$productoMasVendido['cantidad']} ventas)</td></tr>";
    echo "</table>";
    echo "<br>";
    ?>

    <!-- Botón para redirigir a la página "Report.php" -->
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <button type="submit" formaction="Report.php" class="btn btn-primary">Ver Reporte Completo</button>
    </form>
</div>

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
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>

