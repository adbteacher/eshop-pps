<?php

use PHPUnit\Framework\TestCase;

class RegisterTest extends TestCase
{
    protected function setUp(): void
    {
        // Simulamos el entorno POST y la sesión
        $_POST = [];
        $_SESSION = [];
    }

    public function testEmailMismatch()
    {
        $_POST['register'] = true;
        $_POST['Email'] = 'juan@juan.com';
        $_POST['ConfirmEmail'] = 'juan@juan.com';

        ob_start();
        require 'path_to_your_script.php';
        $output = ob_get_clean();

        $this->assertStringContainsString("Los correos electrónicos no coinciden.", $output);
    }

    public function testInvalidEmailFormat()
    {
        $_POST['register'] = true;
        $_POST['Email'] = 'invalid-email';
        $_POST['ConfirmEmail'] = 'invalid-email';

        ob_start();
        require 'path_to_your_script.php';
        $output = ob_get_clean();

        $this->assertStringContainsString("El correo electrónico no es válido.", $output);
    }

    public function testPasswordMismatch()
    {
        $_POST['register'] = true;
        $_POST['Password'] = 'Password123!';
        $_POST['ConfirmPassword'] = 'DifferentPassword123!';

        ob_start();
        require 'path_to_your_script.php';
        $output = ob_get_clean();

        $this->assertStringContainsString("Las contraseñas no coinciden.", $output);
    }

    public function testInvalidPhoneNumber()
    {
        $_POST['register'] = true;
        $_POST['PhoneNumber'] = 'invalid-phone';

        ob_start();
        require 'path_to_your_script.php';
        $output = ob_get_clean();

        $this->assertStringContainsString("El número de teléfono es inválido.", $output);
    }

    public function testPasswordRequirements()
    {
        $_POST['register'] = true;
        $_POST['Password'] = 'short';
        $_POST['ConfirmPassword'] = 'short';

        ob_start();
        require 'path_to_your_script.php';
        $output = ob_get_clean();

        $this->assertStringContainsString("La contraseña no cumple los requisitos mínimos.", $output);
    }

    public function testUserAlreadyExists()
    {
        // Simulamos la conexión a la base de datos y el resultado de la consulta
        $mockDb = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('rowCount')->willReturn(1); // Simulamos que el usuario ya existe

        $mockDb->method('query')->willReturn($mockStmt);

        // Reemplazamos la conexión real con la simulada
        $GLOBALS['Conn'] = $mockDb;

        $_POST['register'] = true;
        $_POST['UserType'] = 'cus';
        $_POST['Email'] = 'juan@juan.com';
        $_POST['ConfirmEmail'] = 'juan@juan.com';
        $_POST['Password'] = 'Password123!';
        $_POST['ConfirmPassword'] = 'Password123!';
        $_POST['PhoneNumber'] = '123456789';

        ob_start();
        require 'path_to_your_script.php';
        $output = ob_get_clean();

        $this->assertStringContainsString("El usuario con email: 'test@example.com,' ya existe.", $output);
    }

    public function testSuccessfulCustomerRegistration()
    {
        // Simulamos la conexión a la base de datos y el resultado de la consulta
        $mockDb = $this->createMock(PDO::class);
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->method('rowCount')->willReturn(0); // Simulamos que el usuario no existe

        $mockDb->method('query')->willReturn($mockStmt);
        $mockDb->method('prepare')->willReturn($mockStmt);
        $mockStmt->method('execute')->willReturn(true);

        // Reemplazamos la conexión real con la simulada
        $GLOBALS['Conn'] = $mockDb;

        $_POST['register'] = true;
        $_POST['UserType'] = 'cus';
        $_POST['Email'] = 'juan@juan.com';
        $_POST['ConfirmEmail'] = 'juan@juan.com';
        $_POST['Password'] = 'Password123!';
        $_POST['ConfirmPassword'] = 'Password123!';
        $_POST['PhoneNumber'] = '123456789';
        $_POST['CustomerName'] = 'Juan';
        $_POST['CustomerSurNames'] = 'Juan';

        ob_start();
        require 'path_to_your_script.php';
        $output = ob_get_clean();

        $this->assertStringContainsString("Te has registrado correctamente", $output);
    }


}

?>

