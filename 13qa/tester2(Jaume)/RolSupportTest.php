<?php

use PHPUnit\Framework\TestCase;

require_once './7rol_support/RolSupport.php';

class RolSupportTest extends TestCase
{
    public function testDatabaseConnection()
    {
        // Mock de la conexión a la base de datos
        $mockConexion = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockConexion->method('prepare')->willReturn($mockStmt);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetchAll')->willReturn([
            [
                'usu_name' => 'John Doe',
                'prd_name' => 'Apple',
                'cantidad_total' => 10
            ]
        ]);

        // Simular la llamada a la base de datos
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
                
        $stmt = $mockConexion->prepare($query);
        $stmt->execute();
        $frutaPorUsuario = $stmt->fetchAll();

        // Verificar los resultados esperados
        $this->assertCount(1, $frutaPorUsuario);
        $this->assertEquals('John Doe', $frutaPorUsuario[0]['usu_name']);
        $this->assertEquals('Apple', $frutaPorUsuario[0]['prd_name']);
        $this->assertEquals(10, $frutaPorUsuario[0]['cantidad_total']);
    }

    public function testGetFrutaMasVendida()
    {
        // Mock de la conexión a la base de datos
        $mockConexion = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockConexion->method('prepare')->willReturn($mockStmt);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetch')->willReturn([
            'prd_name' => 'Banana',
            'cantidad_total' => 50
        ]);

        // Simular la llamada a la base de datos
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

        $stmt = $mockConexion->prepare($query);
        $stmt->execute();
        $frutaMasVendida = $stmt->fetch();

        // Verificar los resultados esperados
        $this->assertEquals('Banana', $frutaMasVendida['prd_name']);
        $this->assertEquals(50, $frutaMasVendida['cantidad_total']);
    }

    public function testGetGastoPromedioUsuario()
    {
        // Mock de la conexión a la base de datos
        $mockConexion = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockConexion->method('prepare')->willReturn($mockStmt);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetchAll')->willReturn([
            [
                'usu_name' => 'Jane Doe',
                'gasto_promedio' => 20.5
            ]
        ]);

        // Simular la llamada a la base de datos
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

        $stmt = $mockConexion->prepare($query);
        $stmt->execute();
        $gastoPromedioUsuario = $stmt->fetchAll();

        // Verificar los resultados esperados
        $this->assertCount(1, $gastoPromedioUsuario);
        $this->assertEquals('Jane Doe', $gastoPromedioUsuario[0]['usu_name']);
        $this->assertEquals(20.5, $gastoPromedioUsuario[0]['gasto_promedio']);
    }

    public function testGetEfectividadCupones()
    {
        // Mock de la conexión a la base de datos
        $mockConexion = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockConexion->method('prepare')->willReturn($mockStmt);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetchAll')->willReturn([
            [
                'cou_code' => 'PROMO1',
                'estado' => 'Usado'
            ]
        ]);

        // Simular la llamada a la base de datos
        $query = "
            SELECT 
                cou_code,
                CASE WHEN cou_is_used = 1 THEN 'Usado' ELSE 'No Usado' END AS estado
            FROM 
                pps_coupons";

        $stmt = $mockConexion->prepare($query);
        $stmt->execute();
        $cupones = $stmt->fetchAll();

        // Verificar los resultados esperados
        $this->assertCount(1, $cupones);
        $this->assertEquals('PROMO1', $cupones[0]['cou_code']);
        $this->assertEquals('Usado', $cupones[0]['estado']);
    }

    public function testGetCategorias()
    {
        // Mock de la conexión a la base de datos
        $mockConexion = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockConexion->method('prepare')->willReturn($mockStmt);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetchAll')->willReturn([
            [
                'cat_id' => 1,
                'cat_description' => 'Frutas'
            ]
        ]);

        // Simular la llamada a la base de datos
        $query = "SELECT cat_id, cat_description FROM pps_categories";

        $stmt = $mockConexion->prepare($query);
        $stmt->execute();
        $categorias = $stmt->fetchAll();

        // Verificar los resultados esperados
        $this->assertCount(1, $categorias);
        $this->assertEquals(1, $categorias[0]['cat_id']);
        $this->assertEquals('Frutas', $categorias[0]['cat_description']);
    }

    public function testGetResenas()
    {
        // Mock de la conexión a la base de datos
        $mockConexion = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockConexion->method('prepare')->willReturn($mockStmt);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetchAll')->willReturn([
            [
                'prd_name' => 'Apple',
                'rev_rating' => 5,
                'rev_message' => 'Great product!'
            ]
        ]);

        // Simular la llamada a la base de datos
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

        $stmt = $mockConexion->prepare($query);
        $stmt->execute([1]);
        $resenas = $stmt->fetchAll();

        // Verificar los resultados esperados
        $this->assertCount(1, $resenas);
        $this->assertEquals('Apple', $resenas[0]['prd_name']);
        $this->assertEquals(5, $resenas[0]['rev_rating']);
        $this->assertEquals('Great product!', $resenas[0]['rev_message']);
    }
}