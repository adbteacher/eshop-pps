<?php
require_once '../autoload.php'; // Incluye el archivo de conexión PDO
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

// Generar token anti-CSRF si no está definido
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Obtener el ID del producto a modificar
$idProducto = $_POST['idProducto'];

// Obtener una conexión a la base de datos
$conexion = database::LoadDatabase();

try {
    // Preparar la consulta para obtener los datos del producto
    $query = "SELECT * FROM pps_products WHERE prd_id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->execute([$idProducto]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo "Producto no encontrado.";
        exit;
    }
} catch (PDOException $e) {
    // Manejar cualquier excepción y mostrar un mensaje genérico
    echo "Algo ha salido mal.";
    exit;
}

// Consultar las categorías desde la base de datos
$queryCategorias = "SELECT cat_id, cat_description FROM pps_categories";
$stmtCategorias = $conexion->query($queryCategorias);
$categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);

// Procesar el formulario si se ha enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'])) {
    // Verificar el token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo "<p class='text-danger'>Error en la validación CSRF.</p>";
        exit;
    }

    $nombre = $_POST['nombre'];
    $categoria = $_POST['categoria'];
    $detalles = $_POST['detalles'];
    $precio = $_POST['precio'];
    $cantidadTienda = $_POST['cantidad_tienda'];
    $stock = $_POST['stock'];
    $descripcion = $_POST['descripcion'];

    // Validar si la categoría seleccionada existe
    $queryCategoria = "SELECT cat_id FROM pps_categories WHERE cat_id = ?";
    $stmtCategoria = $conexion->prepare($queryCategoria);
    $stmtCategoria->execute([$categoria]);
    $categoriaExistente = $stmtCategoria->fetchColumn();

    if (!$categoriaExistente) {
        echo '<div class="alert alert-warning">La categoría seleccionada no existe.</div>';
    } else {
        // Procesar la imagen si se ha subido
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $file_info = $_FILES['imagen'];
            $file_name = $file_info['name'];
            $file_tmp = $file_info['tmp_name'];
            $file_mime = mime_content_type($file_tmp);

            // Validar el tipo MIME y la extensión del archivo
            $allowed_extensions = ['image/jpeg', 'image/png'];
            $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
            if (in_array($file_mime, $allowed_extensions) && in_array($file_extension, ['jpg', 'jpeg', 'png'])) {
                $ruta_imagen = '../0images/' . $file_name;
                if (move_uploaded_file($file_tmp, $ruta_imagen)) {
                    $query = "UPDATE pps_products SET prd_name = ?, prd_category = ?, prd_details = ?, prd_price = ?, prd_quantity_shop = ?, prd_stock = ?, prd_image = ?, prd_description = ? WHERE prd_id = ?";
                    $stmt = $conexion->prepare($query);
                    $exito = $stmt->execute([$nombre, $categoria, $detalles, $precio, $cantidadTienda, $stock, $file_name, $descripcion, $idProducto]);

                    if ($exito) {
                        header('Location: Gestion_Prod.php');
                        exit;
                    } else {
                        echo '<div class="alert alert-danger">Error al modificar el producto.</div>';
                    }
                } else {
                    echo '<div class="alert alert-danger">Error al subir la imagen.</div>';
                }
            } else {
                echo '<div class="alert alert-warning">El archivo seleccionado no es una imagen válida (solo JPG o PNG).</div>';
            }
        } else {
            // Si no se sube una nueva imagen, mantener la existente
            $query = "UPDATE pps_products SET prd_name = ?, prd_category = ?, prd_details = ?, prd_price = ?, prd_quantity_shop = ?, prd_stock = ?, prd_description = ? WHERE prd_id = ?";
            $stmt = $conexion->prepare($query);
            $exito = $stmt->execute([$nombre, $categoria, $detalles, $precio, $cantidadTienda, $stock, $descripcion, $idProducto]);

            if ($exito) {
                header('Location: Gestion_Prod.php');
                exit;
            } else {
                echo '<div class="alert alert-danger">Error al modificar el producto.</div>';
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
    <title>Modificar Producto</title>
    
    <link href="/vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include "../nav.php" ?>

<h1>Modificar Producto</h1>

<h2>Modificar Producto</h2>
<form id="formModificarProducto" method="post" enctype="multipart/form-data">
    <input type="hidden" name="idProducto" value="<?php echo $idProducto; ?>">
    <!-- Campo oculto con el ID del producto -->
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <label for="nombre">Nombre:</label>
    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($row['prd_name']); ?>" required>
    <br><br>
    <label for="categoria">Categoría:</label>
    <select id="categoria" name="categoria" required>
        <?php foreach ($categorias as $categoria): ?>
            <option value="<?php echo $categoria['cat_id']; ?>" <?php if ($categoria['cat_id'] == $row['prd_category']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($categoria['cat_description']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>
    <label for="detalles">Detalles:</label>
    <input type="text" id="detalles" name="detalles" value="<?php echo htmlspecialchars($row['prd_details']); ?>" required>
    <br><br>
    <label for="precio">Precio:</label>
    <input type="number" step="0.01" id="precio" name="precio" value="<?php echo htmlspecialchars($row['prd_price']); ?>" required>
    <br><br>
    <label for="cantidad_tienda">Cantidad en Tienda:</label>
    <input type="number" id="cantidad_tienda" name="cantidad_tienda" value="<?php echo htmlspecialchars($row['prd_quantity_shop']); ?>" required>
    <br><br>
    <label for="stock">Stock:</label>
    <input type="number" id="stock" name="stock" value="<?php echo htmlspecialchars($row['prd_stock']); ?>" required>
    <br><br>
    <label for="imagen">Imagen:</label>
    <input type="file" id="imagen" name="imagen" accept="image/png, image/jpeg" class="form-control-file">
    <br><br>
    <?php if (!empty($row['prd_image'])): ?>
        <img src="../0images/<?php echo htmlspecialchars($row['prd_image']); ?>" alt="Imagen del producto" width="100">
    <?php endif; ?>
    <br><br>
    <label for="descripcion">Descripción:</label>
    <textarea id="descripcion" name="descripcion" class="form-control" rows="3" required><?php echo htmlspecialchars($row['prd_description']); ?></textarea>
    <br><br>
    <button type="submit" id="btnModificarProducto">Modificar Producto</button>
    <br>
	<br>
    <button onclick="window.location.href='Gestion_Prod.php'">Volver a Gestión de Productos</button>
</form>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    $(document).ready(function () {
        // AJAX para enviar el formulario de modificación de producto
        $("#btnModificarProducto").click(function (event) {
            event.preventDefault(); // Prevenir el envío del formulario
            var formData = new FormData($("#formModificarProducto")[0]); // Crear FormData con el formulario

            $.ajax({
                url: "procesar_modificacion_producto.php", // Ruta del archivo PHP que procesa el formulario
                type: "POST",
                data: formData, // Enviar los datos del formulario
                contentType: false, // No establecer el tipo de contenido
                processData: false, // No procesar los datos
                success: function (response) {
                    var jsonResponse = JSON.parse(response);
                    if (jsonResponse.status === 'success') {
                        alert(jsonResponse.message); // Mostrar mensaje de éxito
                        // Redireccionar a la página de administración de productos
                        window.location.href = "Gestion_Prod.php";
                    } else {
                        alert(jsonResponse.message); // Mostrar mensaje de error
                    }
                }
            });
        });
    });
</script>

</body>
</html>

