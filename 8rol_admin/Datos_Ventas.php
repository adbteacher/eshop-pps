<?php
/*
	 Esta pagina muestra los datos referente a las ventas con una serie de graficas
     y una pequeña tabla.
	 */
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

// Consulta para obtener el total de ventas semanales
$query = "
    SELECT 
        DATE_FORMAT(pps_orders.ord_purchase_date, '%Y-%u') AS week, 
        COUNT(*) AS cantidad_ventas 
    FROM 
        pps_order_details 
    JOIN 
        pps_orders ON pps_order_details.ord_det_order_id = pps_orders.ord_id 
    GROUP BY 
        week 
    ORDER BY 
        week ASC";
$stmt = $conexion->prepare($query);
$stmt->execute();
$ventasSemanales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener el total de ingresos semanales
$query = "
    SELECT 
        DATE_FORMAT(pps_orders.ord_purchase_date, '%Y-%u') AS week, 
        SUM(pps_order_details.subtotal) AS total_ingresos 
    FROM 
        pps_order_details 
    JOIN 
        pps_orders ON pps_order_details.ord_det_order_id = pps_orders.ord_id 
    GROUP BY 
        week 
    ORDER BY 
        week ASC";
$stmt = $conexion->prepare($query);
$stmt->execute();
$ingresosSemanales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener el producto más vendido semanalmente
$query = "
    SELECT 
        DATE_FORMAT(pps_orders.ord_purchase_date, '%Y-%u') AS week, 
        ord_det_prod_id, 
        SUM(qty) AS cantidad 
    FROM 
        pps_order_details 
    JOIN 
        pps_orders ON pps_order_details.ord_det_order_id = pps_orders.ord_id 
    GROUP BY 
        week, ord_det_prod_id 
    ORDER BY 
        week ASC, cantidad DESC";
$stmt = $conexion->prepare($query);
$stmt->execute();
$productoMasVendidoSemanal = $stmt->fetchAll(PDO::FETCH_ASSOC);

$conexion = null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis de Ventas</title>
    <link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include "../nav.php"; ?>
<div class="container mt-5 mb-5">
    <h2>Análisis de Ventas</h2>
    
    <h2>Tabla de Ventas</h2>
    <table class="table table-bordered">
        <tr><th>Semana</th><th>Total de Ventas</th><th>Total de Ingresos (€)</th><th>Producto Más Vendido (ID)</th><th>Cantidad Vendida</th></tr>
        <?php
        $productosMasVendidosPorSemana = [];
        foreach ($productoMasVendidoSemanal as $producto) {
            $productosMasVendidosPorSemana[$producto['week']] = $producto;
        }
        foreach ($ventasSemanales as $i => $venta) {
            echo "<tr>";
            echo "<td>" . $venta['week'] . "</td>";
            echo "<td>" . $venta['cantidad_ventas'] . "</td>";
            echo "<td>" . "€" . number_format($ingresosSemanales[$i]['total_ingresos'], 2) . "</td>";
            echo "<td>" . $productosMasVendidosPorSemana[$venta['week']]['ord_det_prod_id'] . "</td>";
            echo "<td>" . $productosMasVendidosPorSemana[$venta['week']]['cantidad'] . "</td>";
            echo "</tr>";
        }
        ?>
    </table>

    <div class="row">
        <div class="col-md-4">
            <canvas id="ventasChart" width="300" height="150"></canvas>
        </div>
        <div class="col-md-4">
            <canvas id="ingresosChart" width="300" height="150"></canvas>
        </div>
        <div class="col-md-4">
            <canvas id="productoMasVendidoChart" width="300" height="150"></canvas>
        </div>
    </div>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <button type="submit" formaction="Report.php" class="btn btn-primary">Ver Reporte Completo</button>
    </form>
</div>
<?php include "../footer.php"; ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var ventasCtx = document.getElementById('ventasChart').getContext('2d');
    var ingresosCtx = document.getElementById('ingresosChart').getContext('2d');
    var productoMasVendidoCtx = document.getElementById('productoMasVendidoChart').getContext('2d');

    var semanas = <?php echo json_encode(array_column($ventasSemanales, 'week')); ?>;
    var ventas = <?php echo json_encode(array_column($ventasSemanales, 'cantidad_ventas')); ?>;
    var ingresos = <?php echo json_encode(array_column($ingresosSemanales, 'total_ingresos')); ?>;
    var productos = <?php echo json_encode(array_column($productoMasVendidoSemanal, 'ord_det_prod_id')); ?>;
    var cantidades = <?php echo json_encode(array_column($productoMasVendidoSemanal, 'cantidad')); ?>;

    var ventasChart = new Chart(ventasCtx, {
        type: 'bar',
        data: {
            labels: semanas,
            datasets: [{
                label: 'Ventas',
                data: ventas,
                backgroundColor: '#007bff'
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

    var ingresosChart = new Chart(ingresosCtx, {
        type: 'bar',
        data: {
            labels: semanas,
            datasets: [{
                label: 'Ingresos (€)',
                data: ingresos,
                backgroundColor: '#28a745'
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

    var productoMasVendidoChart = new Chart(productoMasVendidoCtx, {
        type: 'bar',
        data: {
            labels: semanas,
            datasets: [{
                label: 'Producto Más Vendido (ID)',
                data: productos,
                backgroundColor: '#dc3545'
            },
            {
                label: 'Cantidad Vendida',
                data: cantidades,
                backgroundColor: '#ffc107'
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
});
</script>
</body>
</html>