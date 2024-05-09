<?php
require_once '../Database.php'; // Incluye el archivo de conexión PDO

// Verificar si la solicitud es mediante POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario
    $idProducto = $_POST['idProducto'];
    $nombre = validarTexto($_POST['nombre']);
    $categoria = $_POST['categoria'];
    $detalles = validarTexto($_POST['detalles']);
    $precio = validarNumerico($_POST['precio']);
    $cantidadTienda = validarNumerico($_POST['cantidad_tienda']);
    $stock = validarNumerico($_POST['stock']);
    $imagen = $_POST['imagen'];
    $descripcion = validarTexto($_POST['descripcion']);

    // Obtener una conexión a la base de datos
    $conexion = database::LoadDatabase();

    try {
        // Preparar la consulta para actualizar la información del producto
        $query = "UPDATE pps_products SET prd_name=?, prd_category=?, prd_details=?, prd_price=?, prd_quantity_shop=?, prd_stock=?, prd_image=?, prd_description=? WHERE prd_id=?";
        $stmt = $conexion->prepare($query);
        $stmt->execute([$nombre, $categoria, $detalles, $precio, $cantidadTienda, $stock, $imagen, $descripcion, $idProducto]);

        if ($stmt->rowCount() > 0) {
            echo "Producto modificado exitosamente.";
        } else {
            echo "No se realizaron cambios en el producto.";
        }
    } catch (PDOException $e) {
        // Manejar cualquier excepción y mostrar un mensaje genérico
        echo "Error al modificar el producto: " . $e->getMessage();
    }

    // Cerrar la conexión
    $conexion = null;
} else {
    echo "Acceso denegado.";
}

// Función para validar campos de texto
function validarTexto($texto) {
    // Eliminar caracteres especiales y escapar comillas simples y dobles para evitar inyección de SQL
    return preg_replace("/[^a-zA-Z\s]/", "", htmlspecialchars($texto));
}

// Función para validar campos numéricos
function validarNumerico($numero) {
    // Permitir dígitos y un punto decimal opcional
    return preg_replace("/[^0-9.]/", "", $numero);
}
?>


