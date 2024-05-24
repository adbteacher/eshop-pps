<?php
require_once '../Database.php'; // Incluye el archivo de conexión PDO

$response = [
    'status' => 'error',
    'message' => 'Acceso denegado.'
];

// Verificar si la solicitud es mediante POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario
    $idProducto = $_POST['idProducto'];
    $nombre = $_POST['nombre'];
    $categoria = $_POST['categoria'];
    $detalles = $_POST['detalles'];
    $precio = validarNumerico($_POST['precio']);
    $cantidadTienda = validarNumerico($_POST['cantidad_tienda']);
    $stock = validarNumerico($_POST['stock']);
    $descripcion = $_POST['descripcion'];

    // Obtener una conexión a la base de datos
    $conexion = database::LoadDatabase();

    // Obtener los valores actuales del producto
    $query = "SELECT prd_name, prd_details, prd_description, prd_image FROM pps_products WHERE prd_id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->execute([$idProducto]);
    $productoActual = $stmt->fetch(PDO::FETCH_ASSOC);

    // Validar campos de texto
    $nombreValido = validarTexto($nombre);
    $detallesValido = validarTexto($detalles);
    $descripcionValido = validarTexto($descripcion);

    // Usar los valores anteriores si los nuevos no son válidos
    if (!$nombreValido) {
        $nombre = $productoActual['prd_name'];
        $response['message'] = "Error al actualizar producto, uso de caracteres no válidos en el nombre.";
    }
    if (!$detallesValido) {
        $detalles = $productoActual['prd_details'];
        $response['message'] = "Error al actualizar producto, uso de caracteres no válidos en los detalles.";
    }
    if (!$descripcionValido) {
        $descripcion = $productoActual['prd_description'];
        $response['message'] = "Error al actualizar producto, uso de caracteres no válidos en la descripción.";
    }

    if ($nombreValido && $detallesValido && $descripcionValido) {
        try {
            // Comprobar si se ha subido una nueva imagen
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
                        $imagen = $file_name;
                    } else {
                        $response['message'] = "Error al subir la nueva imagen.";
                        echo json_encode($response);
                        exit;
                    }
                } else {
                    $response['message'] = "El archivo seleccionado no es una imagen válida (solo JPG o PNG).";
                    echo json_encode($response);
                    exit;
                }
            } else {
                // Mantener la imagen existente si no se sube una nueva
                $imagen = $productoActual['prd_image'];
            }

            // Preparar la consulta para actualizar la información del producto
            $query = "UPDATE pps_products SET prd_name=?, prd_category=?, prd_details=?, prd_price=?, prd_quantity_shop=?, prd_stock=?, prd_image=?, prd_description=? WHERE prd_id=?";
            $stmt = $conexion->prepare($query);
            $stmt->execute([$nombre, $categoria, $detalles, $precio, $cantidadTienda, $stock, $imagen, $descripcion, $idProducto]);

            if ($stmt->rowCount() > 0) {
                $response['status'] = 'success';
                $response['message'] = 'Producto modificado exitosamente.';
            } else {
                $response['message'] = 'No se realizaron cambios en el producto.';
            }
        } catch (PDOException $e) {
            // Manejar cualquier excepción y mostrar un mensaje genérico
            $response['message'] = "Error al modificar el producto: " . $e->getMessage();
        }
    }

    // Cerrar la conexión
    $conexion = null;
}

echo json_encode($response);

// Función para validar campos de texto
function validarTexto($texto) {
    // Verificar si el texto contiene solo letras y espacios
    return preg_match("/^[a-zA-Z\s]+$/", $texto);
}

// Función para validar campos numéricos
function validarNumerico($numero) {
    // Permitir dígitos y un punto decimal opcional
    return preg_replace("/[^0-9.]/", "", $numero);
}
?>








