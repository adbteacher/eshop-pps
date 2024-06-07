<?php
use PHPUnit\Framework\TestCase;

require_once './8rol_admin/Datos_Ventas.php'; 

class DatosVentasTest extends TestCase {

    public function testGetTotalVentas() {
        $mockConexion = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockConexion->method('prepare')->willReturn($mockStmt);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetch')->willReturn(['cantidad_ventas' => 100]);

        $totalVentas = getTotalVentas($mockConexion);
        $this->assertEquals(100, $totalVentas);
    }

    public function testGetProductoMasVendido() {
        $mockConexion = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockConexion->method('prepare')->willReturn($mockStmt);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetch')->willReturn(['ord_det_prod_id' => 1, 'cantidad' => 50]);

        $productoMasVendido = getProductoMasVendido($mockConexion);
        $this->assertEquals(['ord_det_prod_id' => 1, 'cantidad' => 50], $productoMasVendido);
    }

    public function testGetTotalIngresos() {
        $mockConexion = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockConexion->method('prepare')->willReturn($mockStmt);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetch')->willReturn(['total_ingresos' => 2000]);

        $totalIngresos = getTotalIngresos($mockConexion);
        $this->assertEquals(2000, $totalIngresos);
    }
}
?>
