<?php
require_once '../Database.php';

session_start();

// Obtener una conexión a la base de datos
$conexion = database::LoadDatabase();

// Generar token anti-CSRF si no está definido
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Función para mostrar la lista de productos
function MostrarProductos($conexion) {
    $query = "SELECT * FROM pps_products";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($result) {
        echo "<h2>Lista de Productos</h2>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Detalles</th><th>Precio</th><th>Cantidad en Tienda</th><th>Stock</th><th>Imagen</th><th>Descripción</th><th>Acciones</th></tr>";
        foreach ($result as $row) {
            echo "<tr>";
            echo "<td>{$row['prd_id']}</td>";
            echo "<td>{$row['prd_name']}</td>";
            echo "<td>{$row['prd_category']}</td>";
            echo "<td>{$row['prd_details']}</td>";
            echo "<td>{$row['prd_price']}</td>";
            echo "<td>{$row['prd_quantity_shop']}</td>";
            echo "<td>{$row['prd_stock']}</td>";
            echo "<td><img src='../0images/{$row['prd_image']}' alt='{$row['prd_name']}' width='50' height='50'></td>";
            echo "<td>{$row['prd_description']}</td>";
            echo "<td>";
            echo "<form action='Mod_Prod.php' method='post' style='display:inline;'>";
            echo "<input type='hidden' name='idProducto' value='{$row['prd_id']}'>";
            echo "<button type='submit'>Editar</button>";
            echo "</form> ";
            echo "<form method='post' style='display:inline;'>";
            echo "<input type='hidden' name='idProducto' value='{$row['prd_id']}'>";
            echo "<input type='hidden' name='csrf_token' value='{$_SESSION['csrf_token']}'>";
            echo "<button type='submit' name='eliminarProducto'>Eliminar</button>";
            echo "</form>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No se encontraron productos.";
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
                    echo "Todos los productos del archivo CSV fueron importados exitosamente.";
                }
            }
        }

        // Procesar eliminación de producto
        if (isset($_POST['eliminarProducto'])) {
            if (isset($_POST['idProducto']) && !empty($_POST['idProducto'])) {
                $idProducto = $_POST['idProducto'];
                $query = "DELETE FROM pps_products WHERE prd_id = ?";
                $stmt = $conexion->prepare($query);
                $exito = $stmt->execute([$idProducto]);

                if ($exito) {
                    header("Location: {$_SERVER['REQUEST_URI']}");
                    exit();
                } else {
                    echo "Error al eliminar el producto.";
                }
            } else {
                echo "No se proporcionó un ID de producto válido.";
            }
        }

        // Procesar formulario para agregar un nuevo producto
        if (isset($_POST['agregarProducto'])) {
            $nombre = $_POST['nombre'];
            $categoria = $_POST['categoria'];
            $detalles = $_POST['detalles'];
            $precio = $_POST['precio'];
            $cantidadTienda = $_POST['cantidad_tienda'];
            $stock = $_POST['stock'];
            $descripcion = $_POST['descripcion'];

            if (!empty($_FILES['imagen']['name'])) {
                $file_info = $_FILES['imagen'];
                $file_name = $file_info['name'];
                $file_tmp = $file_info['tmp_name'];
                $file_mime = mime_content_type($file_tmp);

                if (($file_mime == 'image/jpeg' || $file_mime == 'image/png') && exif_imagetype($file_tmp) != false) {
                    $ruta_imagen = '../0images/' . $file_name;
                    if (move_uploaded_file($file_tmp, $ruta_imagen)) {
                        $query_insert = "INSERT INTO pps_products (prd_name, prd_category, prd_details, prd_price, prd_quantity_shop, prd_stock, prd_image, prd_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt_insert = $conexion->prepare($query_insert);
                        if ($stmt_insert->execute([$nombre, $categoria, $detalles, $precio, $cantidadTienda, $stock, $file_name, $descripcion])) {
                            echo "Producto agregado exitosamente.";
                        } else {
                            echo "Error al agregar el producto.";
                        }
                    } else {
                        echo "Error al subir la imagen.";
                    }
                } else {
                    echo "El archivo seleccionado no es una imagen válida.";
                }
            } else {
                echo "Debes seleccionar una imagen para el producto.";
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
    <link rel="stylesheet" href="estilo.css">
    <title>Gestión de Productos</title>
</head>
<body>

<!-- Formulario para importar CSV -->
<h2>Importar/Exportar Productos desde CSV</h2>
<form method="post" enctype="multipart/form-data">
    <input type="file" name="archivoCSV" accept=".csv">
    <button type="submit" name="importarCSV">Importar CSV</button>
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
</form>
<br>
<!-- Botón para exportar productos a CSV -->
<form method="post" action="Exportar.php">
    <button type="submit" name="exportarCSV">Exportar Productos a CSV</button>
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
</form>

<!-- Formulario para agregar un nuevo producto -->
<h2>Agregar Nuevo Producto</h2>
<form method="post" enctype="multipart/form-data">
    <label for="nombre">Nombre:</label>
    <input type="text" id="nombre" name="nombre" required><br><br>

    <label for="categoria">Categoría:</label>
    <select id="categoria" name="categoria" required>
    <?php
        $categorias = ObtenerCategorias($conexion);
        foreach ($categorias as $categoria) {
            echo "<option value='{$categoria['cat_id']}'>{$categoria['cat_description']}</option>";
        }
        ?>
    </select><br><br>

    <label for="detalles">Detalles:</label>
    <input type="text" id="detalles" name="detalles" required><br><br>

    <label for="precio">Precio:</label>
    <input type="number" id="precio" name="precio" step="0.01" required><br><br>

    <label for="cantidad_tienda">Cantidad en Tienda:</label>
    <input type="number" id="cantidad_tienda" name="cantidad_tienda" required><br><br>

    <label for="stock">Stock:</label>
    <input type="number" id="stock" name="stock" required><br><br>

    <label for="imagen">Imagen:</label>
    <input type="file" id="imagen" name="imagen" accept="image/*" required><br><br>

    <label for="descripcion">Descripción:</label>
    <textarea id="descripcion" name="descripcion" required></textarea><br><br>

    <button type="submit" name="agregarProducto">Agregar Producto</button>
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
</form>

<!-- Mostrar lista de productos -->
<?php MostrarProductos($conexion); ?>

</body>
</html>

