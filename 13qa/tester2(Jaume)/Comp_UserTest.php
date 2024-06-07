<?php

use PHPUnit\Framework\TestCase;

require_once '../autoload.php'; 
require_once './8rol_admin/Comp_User.php'; 

class CompUserTest extends TestCase
{
    private $mockConexion;
    private $mockStmt;

    protected function setUp(): void
    {
        $this->mockConexion = $this->createMock(PDO::class);
        $this->mockStmt = $this->createMock(PDOStatement::class);

        $this->mockConexion->method('prepare')->willReturn($this->mockStmt);
    }

    public function testFrutaMasCompradaPorUsuario()
    {
        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchAll')->willReturn([
            ['usu_name' => 'Usuario1', 'prd_name' => 'Manzana', 'cantidad_total' => 10],
            ['usu_name' => 'Usuario2', 'prd_name' => 'Pera', 'cantidad_total' => 15]
        ]);

        $resultado = getFrutaMasCompradaPorUsuario($this->mockConexion);

        $this->assertIsArray($resultado);
        $this->assertCount(2, $resultado);
        $this->assertEquals('Usuario1', $resultado[0]['usu_name']);
    }

    public function testFrutaMasVendida()
    {
        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetch')->willReturn(['prd_name' => 'Manzana', 'cantidad_total' => 50]);

        $resultado = getFrutaMasVendida($this->mockConexion);

        $this->assertIsArray($resultado);
        $this->assertEquals('Manzana', $resultado['prd_name']);
    }

    public function testGastoPromedioPorUsuario()
    {
        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchAll')->willReturn([
            ['usu_name' => 'Usuario1', 'gasto_promedio' => 100],
            ['usu_name' => 'Usuario2', 'gasto_promedio' => 150]
        ]);

        $resultado = getGastoPromedioPorUsuario($this->mockConexion);

        $this->assertIsArray($resultado);
        $this->assertCount(2, $resultado);
        $this->assertEquals(100, $resultado[0]['gasto_promedio']);
    }

    public function testEfectividadCupones()
    {
        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchAll')->willReturn([
            ['cou_code' => 'CUPON1', 'estado' => 'Usado'],
            ['cou_code' => 'CUPON2', 'estado' => 'No Usado']
        ]);

        $resultado = getEfectividadCupones($this->mockConexion);

        $this->assertIsArray($resultado);
        $this->assertCount(2, $resultado);
        $this->assertEquals('CUPON1', $resultado[0]['cou_code']);
    }

    public function testCategorias()
    {
        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchAll')->willReturn([
            ['cat_id' => 1, 'cat_description' => 'Frutas'],
            ['cat_id' => 2, 'cat_description' => 'Verduras']
        ]);

        $resultado = getCategorias($this->mockConexion);

        $this->assertIsArray($resultado);
        $this->assertCount(2, $resultado);
        $this->assertEquals('Frutas', $resultado[0]['cat_description']);
    }

    public function testResenasPorCategoria()
    {
        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchAll')->willReturn([
            ['prd_name' => 'Manzana', 'rev_rating' => 5, 'rev_message' => 'Excelente'],
            ['prd_name' => 'Pera', 'rev_rating' => 4, 'rev_message' => 'Muy buena']
        ]);

        $resultado = getResenasPorCategoria($this->mockConexion, 1);

        $this->assertIsArray($resultado);
        $this->assertCount(2, $resultado);
        $this->assertEquals('Manzana', $resultado[0]['prd_name']);
    }
}

// Funciones mockeadas
function getFrutaMasCompradaPorUsuario($conexion)
{
    $query = "
    SELECT 
        pps_users.usu_name,
        pps_products.prd_name,
        SUM(pps_order_details.qty) as cantidad_total
    FROM 
        pps_order_details
    JOIN 
        pps_orders ON pps_order_details.ord_det_order_id = pps_orders.ord_id
    JOIN 
        pps_products ON pps_order_details.ord_det_prod_id = pps_products.prd_id
    JOIN 
        pps_users ON pps_orders.ord_user_id = pps_users.usu_id
    GROUP BY 
        pps_users.usu_id, pps_products.prd_id
    ORDER BY 
        pps_users.usu_id, cantidad_total DESC";

    $stmt = $conexion->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getFrutaMasVendida($conexion)
{
    $query = "
    SELECT 
        pps_products.prd_name,
        SUM(pps_order_details.qty) as cantidad_total
    FROM 
        pps_order_details
    JOIN 
        pps_products ON pps_order_details.ord_det_prod_id = pps_products.prd_id
    GROUP BY 
        pps_products.prd_id
    ORDER BY 
        cantidad_total DESC
    LIMIT 1";

    $stmt = $conexion->prepare($query);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getGastoPromedioPorUsuario($conexion)
{
    $query = "
    SELECT 
        pps_users.usu_name,
        AVG(pps_order_details.subtotal) as gasto_promedio
    FROM 
        pps_order_details
    JOIN 
        pps_orders ON pps_order_details.ord_det_order_id = pps_orders.ord_id
    JOIN 
        pps_users ON pps_orders.ord_user_id = pps_users.usu_id
    GROUP BY 
        pps_users.usu_id
    ORDER BY 
        gasto_promedio DESC";

    $stmt = $conexion->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getEfectividadCupones($conexion)
{
    $query = "
    SELECT 
        cou_code,
        CASE WHEN cou_is_used = 1 THEN 'Usado' ELSE 'No Usado' END AS estado
    FROM 
        pps_coupons";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCategorias($conexion)
{
    $query = "SELECT cat_id, cat_description FROM pps_categories";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getResenasPorCategoria($conexion, $categoriaSeleccionada)
{
    $query = "
    SELECT 
        pps_products.prd_name,
        pps_reviews.rev_rating,
        pps_reviews.rev_message
    FROM 
        pps_reviews
    JOIN 
        pps_products ON pps_reviews.rev_product = pps_products.prd_id
    WHERE 
        pps_products.prd_category = ?";
    $stmt = $conexion->prepare($query);
    $stmt->execute([$categoriaSeleccionada]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>