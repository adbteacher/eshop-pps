<?php
session_start();
require_once '../autoload.php';
require_once '../Functions.php';
functions::checkAdminAccess(); // Aseguramos el acceso del administrador

// Conexión a la base de datos
$conexion = database::LoadDatabase();

// Consulta para obtener la fruta más comprada por cada usuario
$queryFrutaPorUsuario = "
    SELECT 
        pps_users.usu_name,
        pps_products.prd_name,
        SUM(pps_order_details.qty) as cantidad_total
    FROM 
        pps_order_details
    JOIN 
        pps_orders ON pps_order_details.ord_det_order_id = pps_orders.ord_id
    JOIN 
        pps_products ON pps_order_details.ord_det_prod_id = pps_products.prd_id
    JOIN 
        pps_users ON pps_orders.ord_user_id = pps_users.usu_id
    GROUP BY 
        pps_users.usu_id, pps_products.prd_id
    ORDER BY 
        pps_users.usu_name, cantidad_total DESC";
$stmt = $conexion->prepare($queryFrutaPorUsuario);
$stmt->execute();
$frutaPorUsuario = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener la fruta más vendida en general
$queryFrutaMasVendida = "
    SELECT 
        pps_products.prd_name,
        SUM(pps_order_details.qty) as cantidad_total
    FROM 
        pps_order_details
    JOIN 
        pps_products ON pps_order_details.ord_det_prod_id = pps_products.prd_id
    GROUP BY 
        pps_products.prd_id
    ORDER BY 
        cantidad_total DESC
    LIMIT 1";
$stmt = $conexion->prepare($queryFrutaMasVendida);
$stmt->execute();
$frutaMasVendida = $stmt->fetch(PDO::FETCH_ASSOC);

// Consulta para obtener el gasto promedio por compra de cada usuario
$queryGastoPromedioUsuario = "
    SELECT 
        pps_users.usu_name,
        AVG(pps_order_details.subtotal) as gasto_promedio
    FROM 
        pps_order_details
    JOIN 
        pps_orders ON pps_order_details.ord_det_order_id = pps_orders.ord_id
    JOIN 
        pps_users ON pps_orders.ord_user_id = pps_users.usu_id
    GROUP BY 
        pps_users.usu_id
    ORDER BY 
        gasto_promedio DESC";
$stmt = $conexion->prepare($queryGastoPromedioUsuario);
$stmt->execute();
$gastoPromedioUsuario = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener la efectividad de los cupones
$queryCupones = "
    SELECT 
        cou_code,
        CASE WHEN cou_is_used = 1 THEN 'Usado' ELSE 'No Usado' END AS estado
    FROM 
        pps_coupons";
$stmt = $conexion->prepare($queryCupones);
$stmt->execute();
$cupones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener las reseñas de productos
$queryResenas = "
    SELECT 
        pps_products.prd_name,
        pps_reviews.rev_rating,
        pps_reviews.rev_message
    FROM 
        pps_reviews
    JOIN 
        pps_products ON pps_reviews.rev_product = pps_products.prd_id";
$stmt = $conexion->prepare($queryResenas);
$stmt->execute();
$resenas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cerrar la conexión
$conexion = null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informes de Compras</title>
    <link href="/vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include "../nav.php"; ?>

<div class="container mt-5 mb-5">
    <h1>Informes de Compras</h1>

    <!-- Informe de Fruta Más Comprada por Usuario -->
    <h2>Fruta Más Comprada por Usuario</h2>
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Usuario</th>
                <th>Fruta</th>
                <th>Cantidad Comprada</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($frutaPorUsuario as $fruta): ?>
            <tr>
                <td><?php echo htmlspecialchars($fruta['usu_name']); ?></td>
                <td><?php echo htmlspecialchars($fruta['prd_name']); ?></td>
                <td><?php echo htmlspecialchars($fruta['cantidad_total']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Informe de Fruta Más Vendida en General -->
    <h2>Fruta Más Vendida en General</h2>
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Fruta</th>
                <th>Cantidad Vendida</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php echo htmlspecialchars($frutaMasVendida['prd_name']); ?></td>
                <td><?php echo htmlspecialchars($frutaMasVendida['cantidad_total']); ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Informe de Gasto Promedio por Compra de Usuario -->
    <h2>Gasto Promedio por Compra de Usuario</h2>
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Usuario</th>
                <th>Gasto Promedio</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($gastoPromedioUsuario as $gasto): ?>
            <tr>
                <td><?php echo htmlspecialchars($gasto['usu_name']); ?></td>
                <td><?php echo htmlspecialchars($gasto['gasto_promedio']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Informe de Efectividad de los Cupones -->
    <h2>Efectividad de los Cupones</h2>
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Código de Cupón</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cupones as $cupon): ?>
            <tr>
                <td><?php echo htmlspecialchars($cupon['cou_code']); ?></td>
                <td><?php echo htmlspecialchars($cupon['estado']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Informe de Reseñas de Productos -->
    <h2>Reseñas de Productos</h2>
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Producto</th>
                <th>Calificación</th>
                <th>Mensaje</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($resenas as $resena): ?>
            <tr>
                <td><?php echo htmlspecialchars($resena['prd_name']); ?></td>
                <td><?php echo htmlspecialchars($resena['rev_rating']); ?></td>
                <td><?php echo htmlspecialchars($resena['rev_message']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<?php include "../footer.php"; ?>
</body>
</html>
