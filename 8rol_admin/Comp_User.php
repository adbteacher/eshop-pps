<?php
/*
	 Este código muestra las tendencias de los usaurios junto con el promedio de ventas
     por cada usuario y las reseñas de los productos por categoria
	 */
session_start();
require_once '../autoload.php';

	// Verificar si el usuario está autenticado
	functions::ActiveSession();

	//Comprobar permisos al programa
	functions::HasPermissions(basename(__FILE__));

functions::checkAdminAccess(); // Aseguramos el acceso del administrador

// Conexión a la base de datos
$conexion = database::LoadDatabase();

$nombreUsuario = '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

if (isset($_POST['buscarUsuario'])) {
    $nombreUsuario = trim($_POST['nombreUsuario']);
}

// Consulta para obtener la fruta más comprada por cada usuario con paginación
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
        pps_users ON pps_orders.ord_user_id = pps_users.usu_id";

if (!empty($nombreUsuario)) {
    $queryFrutaPorUsuario .= " WHERE pps_users.usu_name LIKE :nombreUsuario";
}

$queryFrutaPorUsuario .= "
    GROUP BY 
        pps_users.usu_id, pps_products.prd_id
    ORDER BY 
        pps_users.usu_id, cantidad_total DESC
    LIMIT :limit OFFSET :offset";

$stmt = $conexion->prepare($queryFrutaPorUsuario);

if (!empty($nombreUsuario)) {
    $stmt->bindValue(':nombreUsuario', '%' . $nombreUsuario . '%', PDO::PARAM_STR);
}

$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$frutaPorUsuario = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener el número total de filas (para la paginación)
$queryCount = "
    SELECT COUNT(DISTINCT pps_users.usu_id, pps_products.prd_id) as total
    FROM 
        pps_order_details
    JOIN 
        pps_orders ON pps_order_details.ord_det_order_id = pps_orders.ord_id
    JOIN 
        pps_products ON pps_order_details.ord_det_prod_id = pps_products.prd_id
    JOIN 
        pps_users ON pps_orders.ord_user_id = pps_users.usu_id";

if (!empty($nombreUsuario)) {
    $queryCount .= " WHERE pps_users.usu_name LIKE :nombreUsuario";
}

$stmt = $conexion->prepare($queryCount);

if (!empty($nombreUsuario)) {
    $stmt->bindValue(':nombreUsuario', '%' . $nombreUsuario . '%', PDO::PARAM_STR);
}

$stmt->execute();
$totalRows = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalRows / $limit);

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

$nombreUsuarioGasto = '';
if (isset($_POST['buscarUsuarioGasto'])) {
    $nombreUsuarioGasto = trim($_POST['nombreUsuarioGasto']);
}

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
        pps_users ON pps_orders.ord_user_id = pps_users.usu_id";

if (!empty($nombreUsuarioGasto)) {
    $queryGastoPromedioUsuario .= " WHERE pps_users.usu_name LIKE :nombreUsuarioGasto";
}

$queryGastoPromedioUsuario .= "
    GROUP BY 
        pps_users.usu_id
    ORDER BY 
        gasto_promedio DESC";

$stmt = $conexion->prepare($queryGastoPromedioUsuario);

if (!empty($nombreUsuarioGasto)) {
    $stmt->bindValue(':nombreUsuarioGasto', '%' . $nombreUsuarioGasto . '%', PDO::PARAM_STR);
}

$stmt->execute();
$gastoPromedioUsuario = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener todas las categorías
$queryCategorias = "SELECT cat_id, cat_description FROM pps_categories";
$stmtCategorias  = $conexion->prepare($queryCategorias);
$stmtCategorias->execute();
$categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);

// Obtener las reseñas si se ha seleccionado una categoría
$resenas = [];
$resenaPage = isset($_GET['resenaPage']) ? (int)$_GET['resenaPage'] : 1;
$resenaLimit = 10;
$resenaOffset = ($resenaPage - 1) * $resenaLimit;
$categoriaSeleccionada = isset($_POST['categoria']) ? $_POST['categoria'] : (isset($_GET['categoria']) ? $_GET['categoria'] : '');

if (!empty($categoriaSeleccionada)) {
    $queryResenas = "
        SELECT 
            pps_products.prd_name,
            pps_reviews.rev_rating,
            pps_reviews.rev_message
        FROM 
            pps_reviews
        JOIN 
            pps_products ON pps_reviews.rev_product = pps_products.prd_id
        WHERE 
            pps_products.prd_category = :categoria
        LIMIT :resenaLimit OFFSET :resenaOffset";
    
    $stmtResenas = $conexion->prepare($queryResenas);
    $stmtResenas->bindValue(':categoria', $categoriaSeleccionada, PDO::PARAM_INT);
    $stmtResenas->bindValue(':resenaLimit', $resenaLimit, PDO::PARAM_INT);
    $stmtResenas->bindValue(':resenaOffset', $resenaOffset, PDO::PARAM_INT);
    $stmtResenas->execute();
    $resenas = $stmtResenas->fetchAll(PDO::FETCH_ASSOC);

    // Consulta para obtener el número total de reseñas (para la paginación)
    $queryResenaCount = "
        SELECT COUNT(*) as total
        FROM 
            pps_reviews
        JOIN 
            pps_products ON pps_reviews.rev_product = pps_products.prd_id
        WHERE 
            pps_products.prd_category = :categoria";
    
    $stmtResenaCount = $conexion->prepare($queryResenaCount);
    $stmtResenaCount->bindValue(':categoria', $categoriaSeleccionada, PDO::PARAM_INT);
    $stmtResenaCount->execute();
    $totalResenaRows = $stmtResenaCount->fetch(PDO::FETCH_ASSOC)['total'];
    $totalResenaPages = ceil($totalResenaRows / $resenaLimit);
}

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
    <script>
        function validateForm() {
            const nombreUsuario = document.getElementById('nombreUsuario').value;
            const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;

            if (!regex.test(nombreUsuario)) {
                document.getElementById('error-message').innerText = 'Por favor, ingrese solo letras.';
                return false;
            }

            document.getElementById('error-message').innerText = '';
            return true;
        }
    </script>
</head>
<body>
<?php include "../nav.php"; ?>

<div class="container mt-5 mb-5">
    <h1>Informes de Compras</h1>

    <!-- Formulario de búsqueda de usuario -->
    <form method="post" class="mb-4" onsubmit="return validateForm()">
        <div class="form-group">
            <label for="nombreUsuario">Buscar por Usuario:</label>
            <input type="text" id="nombreUsuario" name="nombreUsuario" class="form-control" value="<?php echo htmlspecialchars($nombreUsuario); ?>">
            <small id="error-message" class="text-danger"></small>
        </div>
        <br>
        <button type="submit" name="buscarUsuario" class="btn btn-primary">Buscar</button>
        <button type="submit" name="refrescar" class="btn btn-secondary">Refrescar</button>
    </form>

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
    
    <!-- Botones de paginación -->
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Anterior">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php if ($page >= $totalPages) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Siguiente">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Informe de Gasto Promedio por Compra de Usuario -->
    <h2>Gasto Promedio por Compra de Usuario</h2>
    
     <!-- Formulario de búsqueda de usuario para gasto promedio -->
     <form method="post" class="mb-4">
        <div class="form-group">
            <label for="nombreUsuarioGasto">Buscar por Usuario (Gasto Promedio):</label>
            <input type="text" id="nombreUsuarioGasto" name="nombreUsuarioGasto" class="form-control" value="<?php echo htmlspecialchars($nombreUsuarioGasto); ?>">
        </div>
        <br>
        <button type="submit" name="buscarUsuarioGasto" class="btn btn-primary">Buscar</button>
        <button type="submit" name="refrescarGasto" class="btn btn-secondary">Refrescar</button>
    </form>
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
                <td><?php echo "€" . number_format($gasto['gasto_promedio'], 2); ?></td>
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

    <!-- Sección de Reseñas -->
    <h2>Reseñas de Productos</h2>
    <form method="post" class="mb-4">
        <div class="form-group">
            <label for="categoria">Seleccionar Categoría:</label>
            <select id="categoria" name="categoria" class="form-control">
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?= htmlspecialchars($categoria['cat_id']); ?>" <?= isset($categoriaSeleccionada) && $categoriaSeleccionada == $categoria['cat_id'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($categoria['cat_description']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <br>
        <button type="submit" class="btn btn-primary">Mostrar Reseñas</button>
    </form>

    <!-- Mostrar las reseñas -->
    <?php if (!empty($resenas)): ?>
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

        <!-- Botones de paginación para reseñas -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php if ($resenaPage <= 1) echo 'disabled'; ?>">
                    <a class="page-link" href="?resenaPage=<?php echo $resenaPage - 1; ?>&categoria=<?= htmlspecialchars($categoriaSeleccionada) ?>" aria-label="Anterior">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $totalResenaPages; $i++): ?>
                    <li class="page-item <?php if ($resenaPage == $i) echo 'active'; ?>">
                        <a class="page-link" href="?resenaPage=<?php echo $i; ?>&categoria=<?= htmlspecialchars($categoriaSeleccionada) ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php if ($resenaPage >= $totalResenaPages) echo 'disabled'; ?>">
                    <a class="page-link" href="?resenaPage=<?php echo $resenaPage + 1; ?>&categoria=<?= htmlspecialchars($categoriaSeleccionada) ?>" aria-label="Siguiente">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
    <a href="Report.php" class="btn btn-primary">Ver reporte completo</a>
</div>
<?php include "../footer.php"; ?>
</body>
</html>