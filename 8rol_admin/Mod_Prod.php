<?php
require_once '../Database.php'; // Incluye el archivo de conexión PDO

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Producto</title>
    <link rel="stylesheet" href="estilo.css"> <!-- Referencia al archivo de estilo CSS -->
</head>
<body>
    <h1>Modificar Producto</h1>

    <h2>Modificar Producto</h2>
    <form id="formModificarProducto" method="post">
        <input type="hidden" name="idProducto" value="<?php echo $idProducto; ?>"> <!-- Campo oculto con el ID del producto -->
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($row['prd_name']); ?>" required>
        <br><br>
        <label for="categoria">Categoría:</label>
        <select id="categoria" name="categoria">
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
        <input type="text" id="precio" name="precio" value="<?php echo htmlspecialchars($row['prd_price']); ?>" required>
        <br><br>
        <label for="cantidad_tienda">Cantidad en Tienda:</label>
        <input type="text" id="cantidad_tienda" name="cantidad_tienda" value="<?php echo htmlspecialchars($row['prd_quantity_shop']); ?>" required>
        <br><br>
        <label for="stock">Stock:</label>
        <input type="text" id="stock" name="stock" value="<?php echo htmlspecialchars($row['prd_stock']); ?>" required>
        <br><br>
        <label for="imagen">Imagen:</label>
        <input type="text" id="imagen" name="imagen" value="<?php echo htmlspecialchars($row['prd_image']); ?>" required>
        <br><br>
        <label for="descripcion">Descripción:</label>
        <input type="text" id="descripcion" name="descripcion" value="<?php echo htmlspecialchars($row['prd_description']); ?>" required>
        <br><br>
        <button type="button" id="btnModificarProducto">Modificar Producto</button>
    </form>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
    $(document).ready(function(){
        // AJAX para cargar las opciones de categoría desde la base de datos
        $.ajax({
            url: "obtener_categorias.php", // Ruta del archivo PHP que obtiene las categorías
            type: "GET",
            success: function(response){
                // Insertar las opciones de categoría en el elemento select
                $("#categoria").html(response);
                // Seleccionar la categoría actual del producto
                $("#categoria").val(<?php echo $row['prd_category']; ?>);
            }
        });

        // AJAX para enviar el formulario de modificación de producto
        $("#btnModificarProducto").click(function(){
            $.ajax({
                url: "procesar_modificacion_producto.php", // Ruta del archivo PHP que procesa el formulario
                type: "POST",
                data: $("#formModificarProducto").serialize(), // Serializar el formulario
                success: function(response){
                    alert(response); // Mostrar mensaje de respuesta
                    // Redireccionar a la página de administración de productos
                    window.location.href = "Gestion_Prod.php";
                }
            });
        });
    });
    </script>

</body>
</html>

