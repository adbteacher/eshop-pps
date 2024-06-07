<?php
use PHPUnit\Framework\TestCase;

require_once './5rol_vendor/biblioteca.php';

class BibliotecaTest extends TestCase {

    public function testMostrarTabla() {
        $data = [
            [
                'prd_id' => 1,
                'prd_name' => 'Producto 1',
                'prd_category' => 'Categoría 1',
                'prd_details' => 'Detalles del producto 1',
                'prd_price' => '10.00',
                'prd_quantity_shop' => 5,
                'prd_stock' => 10,
            ],
            [
                'prd_id' => 2,
                'prd_name' => 'Producto 2',
                'prd_category' => 'Categoría 2',
                'prd_details' => 'Detalles del producto 2',
                'prd_price' => '20.00',
                'prd_quantity_shop' => 0,
                'prd_stock' => 0,
            ]
        ];

        ob_start();
        mostrar_tabla($data);
        $output = ob_get_clean();

        $this->assertStringContainsString('<table>', $output);
        $this->assertStringContainsString('</table>', $output);
        $this->assertStringContainsString('<td>1</td>', $output);
        $this->assertStringContainsString('<td>Producto 1</td>', $output);
        $this->assertStringContainsString('<td>Producto 2</td>', $output);
        $this->assertStringContainsString('<td>No disponible</td>', $output);
    }

    public function testEliminarFilaConIDInvalido() {
        $result = eliminar_fila(-1);
        $this->assertFalse($result);
    }

    public function testCerrarConexion() {
        $this->expectNotToPerformAssertions();
        cerrar_conexion();
    }
}
?>


