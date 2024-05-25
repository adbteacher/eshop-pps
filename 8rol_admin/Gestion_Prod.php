<?php
require_once '../autoload.php';

session_start();

if (!isset($_SESSION['UserRol'])) {
    echo "<p class='text-danger'>Acceso denegado. No se encontró el rol de usuario en la sesión.</p>";
    exit;
}

// Verificar si el usuario es administrador
if ($_SESSION["UserRol"] !== 'A') {
    echo "<p class='text-danger'>Acceso denegado. No tienes permisos para acceder a esta página.</p>";
    exit;
}
// Obtener una conexión a la base de datos
$conexion = database::LoadDatabase();

// Generar token anti-CSRF si no está definido
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Función para mostrar la lista de productos
function MostrarProductos($conexion): void {
    $query = "SELECT * FROM pps_products";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($result) {
        echo '<div class="table-responsive">';
        echo '<h2 class="mt-4">Lista de Productos</h2>';
        echo '<table class="table table-bordered table-striped">';
        echo '<thead class="thead-dark"><tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Detalles</th><th>Precio</th><th>Cantidad en Tienda</th><th>Stock</th><th>Imagen</th><th>Descripción</th><th>Acciones</th></tr></thead>';
        echo '<tbody>';
        foreach ($result as $row) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['prd_id']) . '</td>';
            echo '<td>' . htmlspecialchars($row['prd_name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['prd_category']) . '</td>';
            echo '<td>' . htmlspecialchars($row['prd_details']) . '</td>';
            echo '<td>' . htmlspecialchars($row['prd_price']) . '</td>';
            echo '<td>' . htmlspecialchars($row['prd_quantity_shop']) . '</td>';
            echo '<td>' . htmlspecialchars($row['prd_stock']) . '</td>';
            echo '<td><img src="../0images/' . htmlspecialchars($row['prd_image']) . '" alt="' . htmlspecialchars($row['prd_name']) . '" width="50" height="50"></td>';
            echo '<td>' . htmlspecialchars($row['prd_description']) . '</td>';
            echo '<td>';
            echo '<form action="Mod_Prod.php" method="post" style="display:inline;">';
            echo '<input type="hidden" name="idProducto" value="' . htmlspecialchars($row['prd_id']) . '">';
            echo '<button type="submit" class="btn btn-warning btn-sm">Editar</button>';
            echo '</form> ';
            echo '<form method="post" style="display:inline;">';
            echo '<input type="hidden" name="idProducto" value="' . htmlspecialchars($row['prd_id']) . '">';
            echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
            echo '<button type="submit" name="eliminarProducto" class="btn btn-danger btn-sm">Eliminar</button>';
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    } else {
        echo '<div class="alert alert-info">No se encontraron productos.</div>';
    }
}

function ObtenerCategorias($conexion) {
    $query = "SELECT cat_id, cat_description FROM pps_categories";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Validar token anti-CSRF y manejar importación y eliminación de productos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo "Error en la validación CSRF.";
    } else {
        // Procesar importación desde el archivo CSV
        if (isset($_POST['importarCSV'])) {
            if (isset($_FILES['archivoCSV']) && $_FILES['archivoCSV']['error'] === UPLOAD_ERR_OK) {
                $archivoTemporal = $_FILES['archivoCSV']['tmp_name'];
                $contenidoCSV = file_get_contents($archivoTemporal);
                $filas = explode(PHP_EOL, $contenidoCSV);
                $query_insert = "INSERT INTO pps_products (prd_name, prd_category, prd_details, prd_price, prd_quantity_shop, prd_stock, prd_image, prd_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_insert = $conexion->prepare($query_insert);
                $categoria_inexistente = false;

                foreach ($filas as $fila) {
                    $datos = str_getcsv($fila);
                    if (count($datos) === 8) {
                        $nombre = $datos[0];
                        $categoria = $datos[1];
                        $detalles = $datos[2];
                        $precio = $datos[3];
                        $cantidadTienda = $datos[4];
                        $stock = $datos[5];
                        $imagen = $datos[6];
                        $descripcion = $datos[7];

                        $query_categoria = "SELECT cat_id FROM pps_categories WHERE cat_id = ?";
                        $stmt_categoria = $conexion->prepare($query_categoria);
                        $stmt_categoria->execute([$categoria]);
                        $categoria_existente = $stmt_categoria->fetchColumn();

                        if ($categoria_existente) {
                            $stmt_insert->execute([$nombre, $categoria, $detalles, $precio, $cantidadTienda, $stock, $imagen, $descripcion]);
                        } else {
                            $categoria_inexistente = true;
                        }
                    }
                }

                if (!$categoria_inexistente) {
                    echo '<div class="alert alert-success">Todos los productos del archivo CSV fueron importados exitosamente.</div>';
                }
            }
        }

        // Procesar eliminación de producto
        if (isset($_POST['eliminarProducto'])) {
            if (!empty($_POST['idProducto'])) {
                $idProducto = $_POST['idProducto'];
                $query = "DELETE FROM pps_products WHERE prd_id = ?";
                $stmt = $conexion->prepare($query);
                $exito = $stmt->execute([$idProducto]);

                if ($exito) {
                    header("Location: {$_SERVER['REQUEST_URI']}");
                    exit();
                } else {
                    echo '<div class="alert alert-danger">Error al eliminar el producto.</div>';
                }
            } else {
                echo '<div class="alert alert-warning">No se proporcionó un ID de producto válido.</div>';
            }
        }

        // Procesar formulario para agregar un nuevo producto
        if (isset($_POST['agregarProducto'])) {
            $msg = array();

            // Validar campos requeridos
            if (empty($_POST['nombre'])) {
                $msg[] = 'El nombre del producto es obligatorio.';
            } else {
                $nombre = $_POST['nombre'];
            }

            if (empty($_POST['categoria'])) {
                $msg[] = 'La categoría del producto es obligatoria.';
            } else {
                $categoria = $_POST['categoria'];
            }

            if (empty($_POST['detalles'])) {
                $msg[] = 'Los detalles del producto son obligatorios.';
            } else {
                $detalles = $_POST['detalles'];
            }

            if (empty($_POST['precio']) || !is_numeric($_POST['precio'])) {
                $msg[] = 'El precio del producto es obligatorio y debe ser un número.';
            } else {
                $precio = $_POST['precio'];
            }

            if (empty($_POST['cantidad_tienda']) || !is_numeric($_POST['cantidad_tienda'])) {
                $msg[] = 'La cantidad en tienda es obligatoria y debe ser un número.';
            } else {
                $cantidadTienda = $_POST['cantidad_tienda'];
            }

            if (empty($_POST['stock']) || !is_numeric($_POST['stock'])) {
                $msg[] = 'El stock es obligatorio y debe ser un número.';
            } else {
                $stock = $_POST['stock'];
            }

            if (empty($_POST['descripcion'])) {
                $msg[] = 'La descripción del producto es obligatoria.';
            } else {
                $descripcion = $_POST['descripcion'];
            }

            if (empty($_FILES['imagen']['name'])) {
                $msg[] = 'Debes seleccionar una imagen para el producto.';
            } else {
                $file_info = $_FILES['imagen'];
                $file_name = $file_info['name'];
                $file_tmp  = $file_info['tmp_name'];
                $file_mime = mime_content_type($file_tmp);

                if (($file_mime == 'image/jpeg' || $file_mime == 'image/png') && exif_imagetype($file_tmp) != false) {
                    $ruta_imagen = '../0images/' . $file_name;
                    if (!move_uploaded_file($file_tmp, $ruta_imagen)) {
                        $msg[] = 'Error al subir la imagen.';
                    }
                } else {
                    $msg[] = 'El archivo seleccionado no es una imagen válida.';
                }
            }

            // Si no hay errores, proceder con la inserción en la base de datos
            if (empty($msg)) {
                $ruta_imagen_db = '../0images/' . $file_name; // Ruta a guardar en la base de datos
                $query_insert = "INSERT INTO pps_products (prd_name, prd_category, prd_details, prd_price, prd_quantity_shop, prd_stock, prd_image, prd_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_insert  = $conexion->prepare($query_insert);
                if ($stmt_insert->execute([$nombre, $categoria, $detalles, $precio, $cantidadTienda, $stock, $ruta_imagen_db, $descripcion])) {
                    echo '<div class="alert alert-success">Producto agregado exitosamente.</div>';
                } else {
                    echo '<div class="alert alert-danger">Error al agregar el producto.</div>';
                }
            } else {
                // Mostrar los mensajes de error
                foreach ($msg as $texto) {
                    echo '<div class="alert alert-warning">' . htmlspecialchars($texto) . '</div>';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Gestión de Productos</title>
</head>
<body>
<?php include "../nav.php"; ?>

<div class="container mt-5">

    <!-- Formulario para importar CSV -->
    <h2>Importar/Exportar Productos desde CSV</h2>
    <form method="post" enctype="multipart/form-data" class="form-inline mb-3">
        <input type="file" name="archivoCSV" accept=".csv" class="form-control-file mr-2">
        <button type="submit" name="importarCSV" class="btn btn-primary">Importar CSV</button>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    </form>

    <!-- Botón para exportar productos a CSV -->
    <form method="post" action="Exportar.php" class="form-inline mb-4">
        <button type="submit" name="exportarCSV" class="btn btn-secondary">Exportar Productos a CSV</button>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    </form>

    <!-- Formulario para agregar un nuevo producto -->
    <h2>Agregar Nuevo Producto</h2>
    <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
        <div class="form-group">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="categoria">Categoría:</label>
            <select id="categoria" name="categoria" class="form-control" required>
                <?php
                $categorias = ObtenerCategorias($conexion);
                foreach ($categorias as $categoria) {
                    echo "<option value='{$categoria['cat_id']}'>{$categoria['cat_description']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="detalles">Detalles:</label>
            <input type="text" id="detalles" name="detalles" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="precio">Precio:</label>
            <input type="number" id="precio" name="precio" step="0.01" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="cantidad_tienda">Cantidad en Tienda:</label>
            <input type="number" id="cantidad_tienda" name="cantidad_tienda" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="stock">Stock:</label>
            <input type="number" id="stock" name="stock" class="form-control" required>
        </div>
        <br>
        <div class="form-group">
            <label for="imagen">Imagen:</label>
            <input type="file" id="imagen" name="imagen" accept="image/*" class="form-control-file" required>
        </div>
        <br>
        <div class="form-group">
            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion" class="form-control" rows="3" required></textarea>
        </div>
        <button type="submit" name="agregarProducto" class="btn btn-success">Agregar Producto</button>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <br>
    </form>
    <button class="btn btn-secondary mt-3" onclick="window.location.href='Rol_Admin.php'">Ir a Rol-Admin</button>

    <!-- Mostrar lista de productos -->
    <?php MostrarProductos($conexion); ?>

</div>

<!-- Enlace al archivo JavaScript de Bootstrap -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

