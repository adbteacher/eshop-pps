<?php

use PHPUnit\Framework\TestCase;

require_once './7rol_support/valCreateTicket.php'; // Ajusta la ruta según tu estructura

class ValCreateTicketTest extends TestCase
{
    protected $pdo;

    protected function setUp(): void
    {
        // Mock de la conexión a la base de datos
        $this->pdo = $this->createMock(PDO::class);
        $stmt = $this->createMock(PDOStatement::class);

        $this->pdo->method('prepare')->willReturn($stmt);
        $stmt->method('execute')->willReturn(true);

        // Reemplazar la conexión real con el mock
        database::method('LoadDatabase')->willReturn($this->pdo);
    }

    public function testFormSubmission()
    {
        // Simular los datos del formulario y la sesión
        $_POST['title'] = 'Test Ticket';
        $_POST['message'] = 'This is a test ticket description';
        $_SESSION['UserID'] = 1;

        // Capturar la salida del formulario
        ob_start();
        include '../5rol_vendor/valCreateTicket.php'; // Ajusta la ruta según tu estructura
        ob_end_clean();

        // Verificar que la redirección se realiza correctamente
        $this->assertStringContainsString('Location: create_ticket.php', xdebug_get_headers());
    }

    public function testFormSubmissionMissingTitle()
    {
        // Simular los datos del formulario sin título
        $_POST['title'] = '';
        $_POST['message'] = 'This is a test ticket description';
        $_SESSION['UserID'] = 1;

        // Capturar la salida del formulario
        ob_start();
        include '../5rol_vendor/valCreateTicket.php'; // Ajusta la ruta según tu estructura
        ob_end_clean();

        // Verificar que no hay redirección debido a la falta del título
        $this->assertStringNotContainsString('Location: create_ticket.php', xdebug_get_headers());
    }

    public function testFormSubmissionMissingMessage()
    {
        // Simular los datos del formulario sin mensaje
        $_POST['title'] = 'Test Ticket';
        $_POST['message'] = '';
        $_SESSION['UserID'] = 1;

        // Capturar la salida del formulario
        ob_start();
        include '../5rol_vendor/valCreateTicket.php'; // Ajusta la ruta según tu estructura
        ob_end_clean();

        // Verificar que no hay redirección debido a la falta del mensaje
        $this->assertStringNotContainsString('Location: create_ticket.php', xdebug_get_headers());
    }

    protected function tearDown(): void
    {
        unset($this->pdo);
    }
}
?>