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

// Verificar el token CSRF si se envía un formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo "<p class='text-danger'>Error en la validación CSRF.</p>";
        exit;
    }
}

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
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <a href="Report.php" class="btn btn-primary">Ver Inventario Completo</a>
        <button type="submit" formaction="Generar_Pdf.php" class="btn btn-secondary">Generar Informe en PDF</button>
    </form>
</div>

<!-- Incluye Bootstrap JS y dependencias -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>


