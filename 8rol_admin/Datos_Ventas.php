<?php
require_once '../autoload.php';

session_start();
functions::checkAdminAccess();

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
    <link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .bar-container {
            display: flex;
            justify-content: space-around;
            align-items: flex-end;
            height: 300px; /* Altura ajustable según necesidad */
            margin-top: 20px;
        }
        .bar {
            width: 30%;
            background-color: #007bff;
            text-align: center;
            color: white;
            margin: 5px;
        }
        .bar:nth-child(2) {
            background-color: #28a745;
        }
        .bar:nth-child(3) {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
<?php include "../nav.php"; ?>
<div class="container mt-5 mb-5">
    <h2>Análisis de Ventas</h2>
    
    <h2>Tabla de Ventas</h2>
    <table class="table table-bordered">
        <tr><th>Métrica</th><th>Valor</th></tr>
        <tr><td>Total de ventas realizadas</td><td><?php echo $cantidadVentas['cantidad_ventas']; ?></td></tr>
        <tr><td>Total de ingresos</td><td><?php echo $totalIngresos['total_ingresos']; ?></td></tr>
        <tr><td>Producto más vendido (ID)</td><td><?php echo $productoMasVendido['ord_det_prod_id']; ?> (<?php echo $productoMasVendido['cantidad']; ?> ventas)</td></tr>
    </table>

    <div class="bar-container">
        <div class="bar" style="height: <?php echo $cantidadVentas['cantidad_ventas'] / 10; ?>px;">
            Total de Ventas: <?php echo $cantidadVentas['cantidad_ventas']; ?>
        </div>
        <div class="bar" style="height: <?php echo $totalIngresos['total_ingresos'] / 1000; ?>px;">
            Total de Ingresos: <?php echo $totalIngresos['total_ingresos']; ?>
        </div>
        <div class="bar" style="height: <?php echo $productoMasVendido['cantidad'] / 10; ?>px;">
            Producto Más Vendido (ID <?php echo $productoMasVendido['ord_det_prod_id']; ?>): <?php echo $productoMasVendido['cantidad']; ?> ventas
        </div>
    </div>

    <!-- Botón para redirigir a la página "Report.php" -->
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <button type="submit" formaction="Report.php" class="btn btn-primary">Ver Reporte Completo</button>
    </form>
</div>
<?php include "../footer.php"; ?>
</body>
</html>