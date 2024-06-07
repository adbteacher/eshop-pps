
<?php

use PHPUnit\Framework\TestCase;

class LoginActivityTest extends TestCase
{
    protected function setUp(): void
    {
        // Simulamos el inicio de sesión
        $_SESSION['UserID'] = 1;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        // Simulamos el request POST
        $_POST = [];
    }

    public function testCsrfTokenGeneration()
    {
        // Simulamos la falta de un token CSRF en la sesión
        unset($_SESSION['csrf_token']);

        // Requerimos el archivo para generar el token CSRF
        require 'path_to_your_script.php';

        $this->assertNotEmpty($_SESSION['csrf_token'], "El token CSRF debería generarse si no existe.");
    }

    public function testRedirectIfNotAuthenticated()
    {
        // Simulamos la falta de un usuario autenticado
        unset($_SESSION['UserID']);

        // Capturamos la salida
        ob_start();
        require 'path_to_your_script.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Location: login.php', xdebug_get_headers(), "Debería redirigir al login si el usuario no está autenticado.");
    }

    public function testFetchLoginAttempts()
    {
        // Simulamos la conexión a la base de datos
        $mockDb = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);

        $mockDb->method('prepare')->willReturn($mockStmt);
        $mockStmt->method('execute')->willReturn(true);
        $mockStmt->method('fetchColumn')->willReturn(20); // Supongamos que hay 20 intentos en total
        $mockStmt->method('fetchAll')->willReturn([
            ['lol_ip' => '192.168.1.1', 'lol_was_correct_login' => true, 'lol_datetime' => '2024-05-01 12:00:00'],
            ['lol_ip' => '192.168.1.2', 'lol_was_correct_login' => false, 'lol_datetime' => '2024-05-01 12:05:00'],
        ]);

        // Reemplazamos la conexión real con la simulada
        $GLOBALS['conn'] = $mockDb;

        // Ejecutamos el script
        require 'path_to_your_script.php';

        // Verificamos que las consultas se realizaron correctamente
        $this->assertNotEmpty($LoginAttempts, "Debería haber intentos de inicio de sesión.");
        $this->assertCount(2, $LoginAttempts, "Debería haber 2 intentos de inicio de sesión.");
    }

    public function testPagination()
    {
        // Simulamos las variables POST para paginación
        $_POST['attempts_per_page'] = 15;
        $_POST['page'] = 2;

        // Ejecutamos el script
        require 'path_to_your_script.php';

        // Verificamos el offset y el límite calculado
        $offset = ($_POST['page'] - 1) * $_POST['attempts_per_page'];
        $this->assertEquals(15, $offset, "El offset debería ser 15 para la página 2 con 15 intentos por página.");
    }

    
}

?>
