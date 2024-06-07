<?php

use PHPUnit\Framework\TestCase;

require_once '../8rol_admin/procesar_modificacion_producto.php'; 

class ProcesarModificacionProductoTest extends TestCase
{
    public function testValidarTexto()
    {
        // Casos de prueba válidos
        $this->assertTrue(validarTexto('Producto A'));
        $this->assertTrue(validarTexto('Producto B, S.A.'));
        $this->assertTrue(validarTexto('Producto C.'));

        // Casos de prueba no válidos
        $this->assertFalse(validarTexto('Producto123'));
        $this->assertFalse(validarTexto('Producto@#$'));
        $this->assertFalse(validarTexto('<script>alert(1)</script>'));
    }

    public function testValidarNumerico()
    {
        // Casos de prueba válidos
        $this->assertEquals('123.45', validarNumerico('123.45'));
        $this->assertEquals('678', validarNumerico('678'));

        // Casos de prueba con caracteres no numéricos
        $this->assertEquals('123.45', validarNumerico('123.45abc'));
        $this->assertEquals('678', validarNumerico('678xyz'));
        $this->assertEquals('123', validarNumerico('1a2b3c'));
    }

    public function testProcesarModificacionProducto()
    {
        // Mock de la conexión a la base de datos y declaración preparada
        $mockConexion = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockConexion->method('prepare')->willReturn($mockStmt);

        // Simular los datos del formulario
        $_POST = [
            'idProducto' => 1,
            'nombre' => 'Producto Test',
            'categoria' => '1',
            'detalles' => 'Detalles del producto',
            'precio' => '100.50',
            'stock' => '20',
            'on_offer' => '0',
            'offer_price' => ''
        ];

        // Simular el método de ejecución y los resultados
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetch')->willReturn([
            'prd_name' => 'Producto Anterior',
            'prd_details' => 'Detalles Anteriores',
            'prd_image' => 'imagen.jpg'
        ]);

        // Llamar a la función que procesa el formulario
        ob_start();
        $response = procesarFormulario($mockConexion);
        ob_end_clean();

        // Verificar los resultados esperados
        $this->assertIsArray($response);
        $this->assertEquals('success', $response['status']);
        $this->assertEquals('Producto modificado exitosamente.', $response['message']);
    }
}

// Función mock para simular el procesamiento del formulario
function procesarFormulario($conexion)
{
    // Obtener los datos del formulario
    $idProducto  = $_POST['idProducto'];
    $nombre      = $_POST['nombre'];
    $categoria   = $_POST['categoria'];
    $detalles    = $_POST['detalles'];
    $precio      = validarNumerico($_POST['precio']);
    $stock       = validarNumerico($_POST['stock']);
    $on_offer    = $_POST['on_offer'];
    $offer_price = validarNumerico($_POST['offer_price']);

    // Obtener los valores actuales del producto
    $query = "SELECT prd_name, prd_details, prd_image FROM pps_products WHERE prd_id = ?";
    $stmt  = $conexion->prepare($query);
    $stmt->execute([$idProducto]);
    $productoActual = $stmt->fetch(PDO::FETCH_ASSOC);

    // Validar campos de texto
    $nombreValido   = validarTexto($nombre);
    $detallesValido = validarTexto($detalles);

    // Usar los valores anteriores si los nuevos no son válidos
    if (!$nombreValido) {
        $nombre = $productoActual['prd_name'];
    }

    if (!$detallesValido) {
        $detalles = $productoActual['prd_details'];
    }

    // Validar precio de oferta si el producto está en oferta
    if ($on_offer == '1' && empty($offer_price)) {
        return [
            'status' => 'error',
            'message' => 'Necesario precio de oferta.'
        ];
    }

    if ($on_offer == '0') {
        $offer_price = null; // Borrar el precio de oferta si el producto no está en oferta
    }

    if ($nombreValido && $detallesValido && ($on_offer == '0' || ($on_offer == '1' && !empty($offer_price)))) {
        // Preparar la consulta para actualizar la información del producto
        $query = "UPDATE pps_products SET prd_name=?, prd_category=?, prd_details=?, prd_price=?, prd_stock=?, prd_image=?, prd_on_offer=?, prd_offer_price=? WHERE prd_id=?";
        $stmt  = $conexion->prepare($query);
        $stmt->execute([$nombre, $categoria, $detalles, $precio, $stock, $productoActual['prd_image'], $on_offer, $offer_price, $idProducto]);

        if ($stmt->rowCount() > 0 || $stmt->errorCode() === '00000') {
            return [
                'status' => 'success',
                'message' => 'Producto modificado exitosamente.'
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'No se realizaron cambios en el producto.'
            ];
        }
    }

    return [
        'status' => 'error',
        'message' => 'Error al validar los datos del producto.'
    ];
}
?>