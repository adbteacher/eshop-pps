<?php
use PHPUnit\Framework\TestCase;

require_once './5rol_vendor/gestion_clientes.php';

class GestionClientesTest extends TestCase {

    public function testContenidoConClientes() {
        
        $clientes = [
            [
                'usu_name' => 'Juan Pérez',
                'usu_email' => 'juan@example.com',
                'adr_line1' => 'Calle Falsa 123',
                'adr_line2' => '',
                'adr_city' => 'Ciudad',
                'adr_state' => 'Estado',
                'adr_postal_code' => '12345',
                'adr_country' => 'País'
            ],
            [
                'usu_name' => 'María López',
                'usu_email' => 'maria@example.com',
                'adr_line1' => 'Avenida Siempre Viva 456',
                'adr_line2' => 'Piso 2',
                'adr_city' => 'Ciudad',
                'adr_state' => 'Estado',
                'adr_postal_code' => '67890',
                'adr_country' => 'País'
            ]
        ];

        
        ob_start();
        ?>
        <div id="contenido" class="container mt-4">
            <h1>Lista de Clientes</h1>
            <?php if (!empty($clientes)): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Dirección</th>
                            <th>Ciudad</th>
                            <th>Estado</th>
                            <th>Código Postal</th>
                            <th>País</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $cliente): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cliente['usu_name']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['usu_email']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['adr_line1']); ?><?php echo ($cliente['adr_line2'] != null) ? ', ' . htmlspecialchars($cliente['adr_line2']) : ''; ?></td>
                                <td><?php echo htmlspecialchars($cliente['adr_city']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['adr_state']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['adr_postal_code']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['adr_country']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay clientes registrados.</p>
            <?php endif; ?>
            <form method="post" action="mainpage.php" class="mb-3">
                <button type="submit" class="btn btn-primary btn-separado">Volver</button>
            </form>
        </div>
        <?php
        
        $output = ob_get_clean();

        
        $this->assertStringContainsString('id="contenido"', $output);
        $this->assertStringContainsString('Lista de Clientes', $output);
        $this->assertStringContainsString('<td>Juan Pérez</td>', $output);
        $this->assertStringContainsString('<td>juan@example.com</td>', $output);
        $this->assertStringContainsString('<td>Calle Falsa 123</td>', $output);
        $this->assertStringContainsString('<td>Ciudad</td>', $output);
        $this->assertStringContainsString('<td>Estado</td>', $output);
        $this->assertStringContainsString('<td>12345</td>', $output);
        $this->assertStringContainsString('<td>País</td>', $output);

        $this->assertStringContainsString('<td>María López</td>', $output);
        $this->assertStringContainsString('<td>maria@example.com</td>', $output);
        $this->assertStringContainsString('<td>Avenida Siempre Viva 456</td>', $output);
        $this->assertStringContainsString('<td>Piso 2</td>', $output);
        $this->assertStringContainsString('<td>67890</td>', $output);
    }

    public function testContenidoSinClientes() {
        
        $clientes = [];

        
        ob_start();
        ?>
        <div id="contenido" class="container mt-4">
            <h1>Lista de Clientes</h1>
            <?php if (!empty($clientes)): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Dirección</th>
                            <th>Ciudad</th>
                            <th>Estado</th>
                            <th>Código Postal</th>
                            <th>País</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $cliente): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cliente['usu_name']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['usu_email']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['adr_line1']); ?><?php echo ($cliente['adr_line2'] != null) ? ', ' . htmlspecialchars($cliente['adr_line2']) : ''; ?></td>
                                <td><?php echo htmlspecialchars($cliente['adr_city']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['adr_state']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['adr_postal_code']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['adr_country']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay clientes registrados.</p>
            <?php endif; ?>
            <form method="post" action="mainpage.php" class="mb-3">
                <button type="submit" class="btn btn-primary btn-separado">Volver</button>
            </form>
        </div>
        <?php
        
        $output = ob_get_clean();

        
        $this->assertStringContainsString('id="contenido"', $output);
        $this->assertStringContainsString('No hay clientes registrados.', $output);
    }
}
?>
