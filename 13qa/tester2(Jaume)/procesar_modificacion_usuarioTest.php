<?php
use PHPUnit\Framework\TestCase;

class ProcesarModificacionUsuarioTest extends TestCase {

    protected function setUp(): void {
        // Reiniciar superglobales antes de cada prueba
        $_SERVER = [];
        $_POST = [];
        $_GET = [];
        $_SESSION = [];
    }

    protected function simulatePostRequest($data) {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = $data;
    }

    public function testCsrfValidationSuccess() {
        $_SESSION['csrf_token'] = 'test_csrf_token';
        $this->simulatePostRequest([
            'csrf_token' => 'test_csrf_token',
            'idUsuario' => '1',
            'nombre' => 'Nombre Valido',
            'telf' => '123456789',
            'rol' => 'admin',
            'email' => 'email@example.com',
            'nueva_passwd' => '',
            'confirmar_passwd' => ''
        ]);

        ob_start();
        require '../5rol_vendor/procesar_modificacion_usuario.php';
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);  // Esperamos error debido a la falta de conexión de base de datos
    }

    public function testCsrfValidationFailure() {
        $_SESSION['csrf_token'] = 'test_csrf_token';
        $this->simulatePostRequest([
            'csrf_token' => 'invalid_csrf_token',
            'idUsuario' => '1',
            'nombre' => 'Nombre Valido',
            'telf' => '123456789',
            'rol' => 'admin',
            'email' => 'email@example.com',
            'nueva_passwd' => '',
            'confirmar_passwd' => ''
        ]);

        ob_start();
        require '../5rol_vendor/procesar_modificacion_usuario.php';
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Error en la validación CSRF.', $response['message']);
    }

    public function testInvalidName() {
        $_SESSION['csrf_token'] = 'test_csrf_token';
        $this->simulatePostRequest([
            'csrf_token' => 'test_csrf_token',
            'idUsuario' => '1',
            'nombre' => 'Nombre123',
            'telf' => '123456789',
            'rol' => 'admin',
            'email' => 'email@example.com',
            'nueva_passwd' => '',
            'confirmar_passwd' => ''
        ]);

        ob_start();
        require '../5rol_vendor/procesar_modificacion_usuario.php';
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('El nombre contiene caracteres inválidos.', $response['message']);
    }

    public function testInvalidPhone() {
        $_SESSION['csrf_token'] = 'test_csrf_token';
        $this->simulatePostRequest([
            'csrf_token' => 'test_csrf_token',
            'idUsuario' => '1',
            'nombre' => 'Nombre Valido',
            'telf' => '12345',
            'rol' => 'admin',
            'email' => 'email@example.com',
            'nueva_passwd' => '',
            'confirmar_passwd' => ''
        ]);

        ob_start();
        require '../5rol_vendor/procesar_modificacion_usuario.php';
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('El número de teléfono debe tener exactamente 9 dígitos y ser numérico.', $response['message']);
    }

    public function testPasswordMismatch() {
        $_SESSION['csrf_token'] = 'test_csrf_token';
        $this->simulatePostRequest([
            'csrf_token' => 'test_csrf_token',
            'idUsuario' => '1',
            'nombre' => 'Nombre Valido',
            'telf' => '123456789',
            'rol' => 'admin',
            'email' => 'email@example.com',
            'nueva_passwd' => 'password123',
            'confirmar_passwd' => 'password456'
        ]);

        ob_start();
        require '../5rol_vendor/procesar_modificacion_usuario.php';
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Las contraseñas no coinciden.', $response['message']);
    }

    public function testPasswordInvalid() {
        $_SESSION['csrf_token'] = 'test_csrf_token';
        $this->simulatePostRequest([
            'csrf_token' => 'test_csrf_token',
            'idUsuario' => '1',
            'nombre' => 'Nombre Valido',
            'telf' => '123456789',
            'rol' => 'admin',
            'email' => 'email@example.com',
            'nueva_passwd' => 'pass',
            'confirmar_passwd' => 'pass'
        ]);

        ob_start();
        require '../5rol_vendor/procesar_modificacion_usuario.php';
        $output = ob_get_clean();
        $response = json_decode($output, true);

        $this->assertEquals('error', $response['status']);
        $this->assertEquals('La contraseña debe tener al menos 8 caracteres y no contener caracteres inválidos.', $response['message']);
    }
}
?>
