<?php

use PHPUnit\Framework\TestCase;

require_once '../5rol_vendor/Trafico_Web.php'; 

class TraficoWebTest extends TestCase
{
    private $mockConexion;
    private $mockStmt;

    protected function setUp(): void
    {
        $this->mockConexion = $this->createMock(PDO::class);
        $this->mockStmt = $this->createMock(PDOStatement::class);

        $this->mockConexion->method('prepare')->willReturn($this->mockStmt);
    }

    public function testTotalLogins()
    {
        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetch')->willReturn(['total_logins' => 100]);

        $resultado = getTotalLogins($this->mockConexion);

        $this->assertIsArray($resultado);
        $this->assertEquals(100, $resultado['total_logins']);
    }

    public function testCorrectLogins()
    {
        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetch')->willReturn(['correct_logins' => 80]);

        $resultado = getCorrectLogins($this->mockConexion);

        $this->assertIsArray($resultado);
        $this->assertEquals(80, $resultado['correct_logins']);
    }

    public function testFailedLogins()
    {
        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetch')->willReturn(['failed_logins' => 20]);

        $resultado = getFailedLogins($this->mockConexion);

        $this->assertIsArray($resultado);
        $this->assertEquals(20, $resultado['failed_logins']);
    }

    public function testLoginsByUser()
    {
        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchAll')->willReturn([
            ['usu_name' => 'Usuario1', 'logins' => 10],
            ['usu_name' => 'Usuario2', 'logins' => 15]
        ]);

        $resultado = getLoginsByUser($this->mockConexion);

        $this->assertIsArray($resultado);
        $this->assertCount(2, $resultado);
        $this->assertEquals('Usuario1', $resultado[0]['usu_name']);
    }

    public function testLoginsByDate()
    {
        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchAll')->willReturn([
            ['login_date' => '2023-01-01', 'logins' => 5],
            ['login_date' => '2023-01-02', 'logins' => 8]
        ]);

        $resultado = getLoginsByDate($this->mockConexion);

        $this->assertIsArray($resultado);
        $this->assertCount(2, $resultado);
        $this->assertEquals('2023-01-01', $resultado[0]['login_date']);
    }
}

// Funciones mockeadas
function getTotalLogins($conexion)
{
    $query = "SELECT COUNT(*) AS total_logins FROM pps_logs_login";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getCorrectLogins($conexion)
{
    $query = "SELECT COUNT(*) AS correct_logins FROM pps_logs_login WHERE lol_was_correct_login = 1";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getFailedLogins($conexion)
{
    $query = "SELECT COUNT(*) AS failed_logins FROM pps_logs_login WHERE lol_was_correct_login = 0";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getLoginsByUser($conexion)
{
    $query = "SELECT pps_users.usu_name, COUNT(*) AS logins 
          FROM pps_logs_login 
          JOIN pps_users ON pps_logs_login.lol_user = pps_users.usu_id 
          GROUP BY pps_users.usu_name 
          ORDER BY logins DESC";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getLoginsByDate($conexion)
{
    $query = "SELECT DATE(lol_datetime) AS login_date, COUNT(*) AS logins FROM pps_logs_login GROUP BY login_date ORDER BY login_date DESC";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>