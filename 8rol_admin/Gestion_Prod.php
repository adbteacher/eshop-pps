<?php
require_once '../Database.php';

// Obtener una conexión a la base de datos
$conexion = database::LoadDatabase();

// Procesar el formulario para agregar un nuevo producto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['agregarProducto'])) {
    $nombre = $_POST['nombre'];
    $categoria = $_POST['categoria'];
    $detalles = $_POST['detalles'];
    $precio = $_POST['precio'];
    $cantidadTienda = $_POST['cantidad_tienda'];
    $stock = $_POST['stock'];
    $imagen = $_POST['imagen'];
    $descripcion = $_POST['descripcion'];

    // Insertar nuevo producto en la base de datos
    $query_insert = "INSERT INTO pps_products (prd_name, prd_category, prd_details, prd_price, prd_quantity_shop, prd_stock, prd_image, prd_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $conexion->prepare($query_insert);
    if ($stmt_insert->execute([$nombre, $categoria, $detalles, $precio, $cantidadTienda, $stock, $imagen, $descripcion])) {
        echo "Producto agregado exitosamente.";
    } else {
        echo "Error al agregar el producto.";
    }
}

// Mostrar la lista de productos
function MostrarProductos($conexion) {
    $query = "SELECT * FROM pps_products";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($result) {
        echo "<h2>Lista de Productos</h2>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Detalles</th><th>Precio</th><th>Cantidad en Tienda</th><th>Stock</th><th>Imagen</th><th>Descripción</th></tr>";
        foreach ($result as $row) {
            echo "<tr>";
            echo "<td>{$row['prd_id']}</td>";
            echo "<td>{$row['prd_name']}</td>";
            echo "<td>{$row['prd_category']}</td>";
            echo "<td>{$row['prd_details']}</td>";
            echo "<td>{$row['prd_price']}</td>";
            echo "<td>{$row['prd_quantity_shop']}</td>";
            echo "<td>{$row['prd_stock']}</td>";
            echo "<td>{$row['prd_image']}</td>";
            echo "<td>{$row['prd_description']}</td>";
            echo "<td>";
            echo "<form action='Mod_Prod.php' method='post'>";
            echo "<input type='hidden' name='idProducto' value='{$row['prd_id']}'>";
            echo "<button type='submit'>Editar</button>";
            echo "</form>";
            echo "</td>";
            echo "<td>";
            echo "<form method='post'>";
            echo "<input type='hidden' name='idProducto' value='{$row['prd_id']}'>";
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
<button onclick="window.location.href='Rol_Admin.php'">Ir a Rol-Admin</button>
<!-- Formulario para importar CSV -->
<h2>Importar Productos desde CSV</h2>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="archivoCSV" accept=".csv">
        <button type="submit" name="importarCSV">Importar CSV</button>
    </form>

    <?php
    // Verificar si se ha enviado el formulario para importar CSV
    if (isset($_POST['importarCSV'])) {
        // Verificar si se ha seleccionado un archivo
        if (isset($_FILES['archivoCSV']) && $_FILES['archivoCSV']['error'] === UPLOAD_ERR_OK) {
            // Ruta del archivo temporal
            $archivoTemporal = $_FILES['archivoCSV']['tmp_name'];

            // Leer el contenido del archivo CSV
            $contenidoCSV = file_get_contents($archivoTemporal);

            // Parsear el contenido CSV
            $filas = explode(PHP_EOL, $contenidoCSV);

            // Obtener una conexión a la base de datos
            $conexion = database::LoadDatabase();

            // Preparar la consulta para insertar productos
            $query_insert = "INSERT INTO pps_products (prd_name, prd_category, prd_details, prd_price, prd_quantity_shop, prd_stock, prd_image, prd_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert = $conexion->prepare($query_insert);

            // Iterar sobre cada fila del CSV y insertar los productos en la base de datos
            foreach ($filas as $fila) {
                $datos = str_getcsv($fila);

                // Verificar si la fila contiene datos válidos
                if (count($datos) === 8) {
                    $nombre = $datos[0];
                    $categoria = $datos[1];
                    $detalles = $datos[2];
                    $precio = $datos[3];
                    $cantidadTienda = $datos[4];
                    $stock = $datos[5];
                    $imagen = $datos[6];
                    $descripcion = $datos[7];

                    // Ejecutar la consulta para insertar el producto
                    $stmt_insert->execute([$nombre, $categoria, $detalles, $precio, $cantidadTienda, $stock, $imagen, $descripcion]);
                }
            }

            // Cerrar la conexión
            $conexion = null;

            echo "Productos importados exitosamente.";
        } else {
            echo "Error al cargar el archivo CSV.";
        }
    }
    ?>

    <h1>Gestión de Productos</h1>

    <!-- Formulario para agregar un nuevo producto -->
    <h2>Agregar Nuevo Producto</h2>
    <form method="post">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required><br><br>

        <label for="categoria">Categoría:</label>
        <select id="categoria" name="categoria" required>
            <!-- Aquí cargarías las categorías desde la base de datos -->
            <option value="1">Frutas cítricas</option>
            <option value="2">Frutas tropicales</option>
            <!-- Agregar otras opciones aquí -->
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
        <input type="file" id="imagen" name="imagen" accept="image/jpeg, image/png" required><br><br>

        <label for="descripcion">Descripción:</label><br>
        <textarea id="descripcion" name="descripcion" rows="4" cols="50" required></textarea><br><br>

        <button type="submit" name="agregarProducto">Agregar Producto</button>
    </form>
    <?php

if (isset($_POST['eliminarProducto'])) {
    if (isset($_POST['idProducto']) && !empty($_POST['idProducto'])) {
        $idProducto = $_POST['idProducto'];

        // Eliminar producto de la base de datos
        $query = "DELETE FROM pps_products WHERE prd_id = ?";
        $stmt = $conexion->prepare($query);
        $stmt->execute([$idProducto]);

        if ($stmt->rowCount() > 0) {
            echo "Producto eliminado exitosamente.";
        } else {
            echo "Error al eliminar el producto.";
        }
    } else {
        echo "No se proporcionó un ID de producto válido.";
    }
}
?>
    <?php
    // Mostrar la lista de productos
    MostrarProductos($conexion);
    ?>

</body>
</html>

<?php
// Cerrar la conexión
$conexion = null;
//hola
?>
