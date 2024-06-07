<?php
use PHPUnit\Framework\TestCase;

class InventarioTest extends TestCase {

    protected function setUp(): void {
        // Reiniciar superglobales antes de cada prueba
        $_SERVER = [];
        $_POST = [];
        $_GET = [];
        $_SESSION = [];
    }

    public function testCheckAdminAccessNoRole() {
        // Simula la ausencia de un rol de usuario en la sesión
        $_SESSION['UserRol'] = null;

        // Capturar la salida de la función
        ob_start();
        try {
            functions::checkAdminAccess();
        } catch (Exception $e) {
            // Capturar excepción
        }
        $output = ob_get_clean();

        $this->assertStringContainsString('Acceso denegado. No se encontró el rol de usuario en la sesión.', $output);
    }

    public function testCheckAdminAccessNotAdmin() {
        // Simula que el usuario no es un administrador
        $_SESSION['UserRol'] = 'U';

        // Capturar la salida de la función
        ob_start();
        try {
            functions::checkAdminAccess();
        } catch (Exception $e) {
            // Capturar excepción
        }
        $output = ob_get_clean();

        $this->assertStringContainsString('Acceso denegado. No tienes permisos para acceder a esta página.', $output);
    }

    public function testCheckAdminAccessAdmin() {
        // Simula que el usuario es un administrador
        $_SESSION['UserRol'] = 'A';

        // Capturar la salida de la función
        ob_start();
        try {
            functions::checkAdminAccess();
        } catch (Exception $e) {
            // Capturar excepción
        }
        $output = ob_get_clean();

        $this->assertStringNotContainsString('Acceso denegado', $output);
    }

    public function testGenerateCsrfToken() {
        // Simula la generación de un token CSRF
        $_SESSION = [];

        // Ejecutar la lógica de generación de token
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $this->assertNotEmpty($_SESSION['csrf_token']);
        $this->assertEquals(64, strlen($_SESSION['csrf_token']));
    }

    public function testValidateCsrfTokenSuccess() {
        // Simula una solicitud POST con un token CSRF válido
        $_SESSION['csrf_token'] = 'test_csrf_token';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['csrf_token'] = 'test_csrf_token';

        // Capturar la salida de la función
        ob_start();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                echo "<p class='text-danger'>Error en la validación CSRF.</p>";
                exit;
            }
        }
        $output = ob_get_clean();

        $this->assertStringNotContainsString('Error en la validación CSRF', $output);
    }

    public function testValidateCsrfTokenFailure() {
        // Simula una solicitud POST con un token CSRF no válido
        $_SESSION['csrf_token'] = 'test_csrf_token';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['csrf_token'] = 'invalid_csrf_token';

        // Capturar la salida de la función
        ob_start();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                echo "<p class='text-danger'>Error en la validación CSRF.</p>";
                exit;
            }
        }
        $output = ob_get_clean();

        $this->assertStringContainsString('Error en la validación CSRF', $output);
    }
}
?>

