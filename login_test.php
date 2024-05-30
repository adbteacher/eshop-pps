<?php

use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase
{
    // Simulación del método de sanitización
    private function SanitizeInput($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    // Simulación del método de verificación de usuario
    private function VerifyUser($email, $password, &$message) {
        if ($email === 'pepe@pepe.com' && $password === 'pepe1') {
            return true;
        } else {
            $message = 'Credenciales incorrectas.';
            return false;
        }
    }

    // Simulación del método de verificación de intentos de login
    private function CheckLoginAttempts($email) {
        return false; // Suponemos que no hay demasiados intentos fallidos
    }

    // Simulación del método de verificación de 2FA
    private function Has2FA($email) {
        return false; // Suponemos que el usuario no tiene 2FA activado
    }

    // Simulación del método para obtener el usuario por email
    private function GetUserByEmail($email) {
        return [
            'usu_id' => 1,
            'usu_name' => 'Usuario',
            'usu_email' => 'usuario@usuario.com',
            'usu_rol' => 'user',
        ];
    }

    // Simulación del método para obtener el ID del usuario por email
    private function GetUserIdByEmail($email) {
        return 1; // Suponemos que el ID de usuario es 1
    }

    // Simulación del método para registrar un intento de login
    private function LogLoginAttempt($userId, $ip, $success) {
        // No hacemos nada en esta simulación
    }

    // Simulación del método para agregar encabezados de seguridad
    private function AddSecurityHeaders() {
        // No hacemos nada en esta simulación
    }

    // Simulación del inicio de sesión
    public function testLoginSuccess()
    {
        $_POST['email'] = 'usuario@usuario.com';
        $_POST['password'] = 'usuario';
        $_POST['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $_POST['csrf_token'];
        
        $Email = $this->SanitizeInput($_POST['email']);
        $Password = $this->SanitizeInput($_POST['password']);
        $message = '';

        $this->assertTrue(filter_var($Email, FILTER_VALIDATE_EMAIL));
        $this->assertFalse($this->CheckLoginAttempts($Email));
        $this->assertTrue(hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']));

        $LoginSuccessful = $this->VerifyUser($Email, $Password, $message);
        $this->assertTrue($LoginSuccessful);

        $User = $this->GetUserByEmail($Email);
        $this->assertEquals(1, $User['usu_id']);
        $this->assertEquals('Usuario', $User['usu_name']);
        $this->assertEquals('usuario@usuario.com', $User['usu_email']);
        $this->assertEquals('user', $User['usu_rol']);
    }

    public function testLoginFailure()
    {
        $_POST['email'] = 'usuario1@usuario1.com';
        $_POST['password'] = 'usuario1';
        $_POST['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $_POST['csrf_token'];
        
        $Email = $this->SanitizeInput($_POST['email']);
        $Password = $this->SanitizeInput($_POST['password']);
        $message = '';

        $this->assertTrue(filter_var($Email, FILTER_VALIDATE_EMAIL));
        $this->assertFalse($this->CheckLoginAttempts($Email));
        $this->assertTrue(hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']));

        $LoginSuccessful = $this->VerifyUser($Email, $Password, $message);
        $this->assertFalse($LoginSuccessful);
        $this->assertEquals('Credenciales incorrectas.', $message);
    }

    public function testInvalidEmailFormat()
    {
        $_POST['email'] = 'invalid-email';
        $_POST['password'] = 'password';
        $_POST['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $_POST['csrf_token'];
        
        $Email = $this->SanitizeInput($_POST['email']);
        $Password = $this->SanitizeInput($_POST['password']);
        $message = '';

        $this->assertFalse(filter_var($Email, FILTER_VALIDATE_EMAIL));
        $this->assertEquals('Formato de correo electrónico inválido.', $message);
    }

    public function testCsrfTokenMismatch()
    {
        $_POST['email'] = 'usuario3@usuario3.com';
        $_POST['password'] = 'oiweqiyuo';
        $_POST['csrf_token'] = 'wrongtoken';
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        $Email = $this->SanitizeInput($_POST['email']);
        $Password = $this->SanitizeInput($_POST['password']);
        $message = '';

        $this->assertTrue(filter_var($Email, FILTER_VALIDATE_EMAIL));
        $this->assertFalse($this->CheckLoginAttempts($Email));
        $this->assertFalse(hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']));
        $this->assertEquals('Error, vuelva a intentarlo más tarde.', $message);
    }
}
?>
