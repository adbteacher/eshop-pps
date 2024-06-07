<?php
use PHPUnit\Framework\TestCase;

require_once './5rol_vendor/editar.php';

class EditarTest extends TestCase {

    public function testRedireccionar() {
        $mensaje = "La información ha sido actualizada.";
        $url = "mainpage.php";

        $this->expectOutputString("<p>$mensaje</p><p><a href='$url'>Volver</a></p>");
        
      
        ob_start();
        try {
            redireccionar($mensaje, $url);
        } catch (Exception $e) {
          
        }
        $output = ob_get_clean();
        
        $this->assertEquals("<p>$mensaje</p><p><a href='$url'>Volver</a></p>", $output);
    }

    public function testValidarCamposTodosLlenos() {
        $nombre = "Producto";
        $categoria = 1;
        $detalles = "Detalles del producto";
        $precio = 10.5;
        $cantidad = 100;
        $stock = 50;

        $campos_vacios = validarCampos($nombre, $categoria, $detalles, $precio, $cantidad, $stock);

        $this->assertEmpty($campos_vacios);
    }

    public function testValidarCamposVacios() {
        $nombre = "";
        $categoria = "";
        $detalles = "";
        $precio = "";
        $cantidad = "";
        $stock = "";

        $campos_vacios = validarCampos($nombre, $categoria, $detalles, $precio, $cantidad, $stock);

        $this->assertEquals(['nombre', 'categoria', 'detalles', 'precio', 'cantidad', 'stock'], $campos_vacios);
    }

    public function testValidarCamposAlgunosVacios() {
        $nombre = "Producto";
        $categoria = 1;
        $detalles = "";
        $precio = 10.5;
        $cantidad = 100;
        $stock = "";

        $campos_vacios = validarCampos($nombre, $categoria, $detalles, $precio, $cantidad, $stock);

        $this->assertEquals(['detalles', 'stock'], $campos_vacios);
    }
}
?>
