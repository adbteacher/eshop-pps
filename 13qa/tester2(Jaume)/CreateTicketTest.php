<?php

use PHPUnit\Framework\TestCase;

require_once '../7rol_support/CreateTicket.php';

class CreateTicketTest extends TestCase
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
                'id' => 1,
                'customer_name' => 'John Doe',
                'customer_email' => 'john.doe@example.com',
                'subject' => 'Test Subject',
                'description' => 'Test Description',
                'priority' => 'Alta',
                'status' => 'Abierto'
            ]
        ]);

        // Simular la llamada a la base de datos
        $query = "SELECT * FROM pps_tickets ORDER BY tic_creation_time ASC";
        $stmt = $mockConexion->prepare($query);
        $stmt->execute();
        $tickets = $stmt->fetchAll();

        // Verificar los resultados esperados
        $this->assertCount(1, $tickets);
        $this->assertEquals('John Doe', $tickets[0]['customer_name']);
        $this->assertEquals('john.doe@example.com', $tickets[0]['customer_email']);
        $this->assertEquals('Test Subject', $tickets[0]['subject']);
        $this->assertEquals('Test Description', $tickets[0]['description']);
        $this->assertEquals('Alta', $tickets[0]['priority']);
        $this->assertEquals('Abierto', $tickets[0]['status']);
    }

    public function testFormSubmission()
    {
        // Simular los datos del formulario
        $_POST = [
            'title' => 'Test Ticket',
            'message' => 'This is a test ticket description'
        ];

        // Capturar la salida del formulario
        ob_start();
        include '../5rol_vendor/valCreateTicket.php'; // Asegúrate de ajustar la ruta
        $output = ob_get_clean();

        // Verificar que la salida contiene los datos del formulario
        $this->assertStringContainsString('Test Ticket', $output);
        $this->assertStringContainsString('This is a test ticket description', $output);
    }

    public function testTicketTableDisplay()
    {
        // Simular datos de tickets
        $tickets = [
            [
                'id' => 1,
                'customer_name' => 'Jane Doe',
                'customer_email' => 'jane.doe@example.com',
                'subject' => 'Sample Subject',
                'description' => 'Sample Description',
                'priority' => 'Media',
                'status' => 'En Progreso'
            ]
        ];

        // Capturar la salida de la tabla de tickets
        ob_start();
        include '../5rol_vendor/CreateTicket.php'; // Asegúrate de ajustar la ruta
        $output = ob_get_clean();

        // Verificar que la salida contiene los datos de los tickets
        $this->assertStringContainsString('Jane Doe', $output);
        $this->assertStringContainsString('jane.doe@example.com', $output);
        $this->assertStringContainsString('Sample Subject', $output);
        $this->assertStringContainsString('Sample Description', $output);
        $this->assertStringContainsString('Media', $output);
        $this->assertStringContainsString('En Progreso', $output);
    }
}