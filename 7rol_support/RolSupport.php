<?php

	/**
	 * Gestión de tickets de soporte: Permitir al usuario  de soporte técnico recibir, gestionar
	 * y responder a tickets de soporte generados  por los clientes . Habilitar la clasificación
	 * y priorización  de los tickets según la gravedad  del problema y el tiempo de respuesta requerido.
	 *
	 * Comunicación  con clientes:Facilitar  la comunicación  bidireccional  entre el equipo de soporte técnico
	 * y los clientes, permitiendo  responder  a consultas, proporcionar  actualizaciones  sobre el estado de los
	 * problemas y ofrecer soluciones  o asistencia  técnica.
	 *
	 * Registro de actividades:Registrar todas las interacciones  y acciones realizadas
	 * por el equipo de soporte técnico  en relación con los tickets de soporte,
	 * incluyendo  notas, cambios de estado y resolución  de problemas
	 *
	 */

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Meta Etiquetas -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Gestión de tickets de soporte técnico">
    <meta name="keywords" content="soporte técnico, tickets, gestión, priorización">
    <meta name="author" content="Soporte Técnico">

    <!-- Titulo -->
    <title>Gestión de Tickets de Soporte Técnico</title>

    <!-- CSS / Hoja de estilos Bootstrap -->
    <!--    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">-->
    <link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="/0images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/0images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/0images/favicon-16x16.png">
    <link rel="manifest" href="/0images/site.webmanifest">
</head>

<body>
<?php include "../nav.php"; // Incluye el Navbar ?>

<div class="container mt-4 mb-4">
    <div class="jumbotron">
        <h1 class="display-4">Gestión de Tickets de Soporte Técnico</h1>
        <p class="lead">Recibe, gestiona y responde a los tickets de soporte generados por los clientes.</p>
        <hr class="my-4">
        <p>Clasifica y prioriza los tickets según la gravedad del problema y el tiempo de respuesta requerido.</p>
    </div>

    <!-- Formulario de creación de tickets -->
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="mb-4">
        <div class="form-group">
            <label for="customer_name">Nombre del Cliente</label>
            <input type="text" class="form-control" id="customer_name" name="customer_name" required>
        </div>
        <div class="form-group">
            <label for="customer_email">Email del Cliente</label>
            <input type="email" class="form-control" id="customer_email" name="customer_email" required>
        </div>
        <div class="form-group">
            <label for="ticket_subject">Asunto</label>
            <input type="text" class="form-control" id="ticket_subject" name="ticket_subject" required>
        </div>
        <div class="form-group">
            <label for="ticket_description">Descripción del Problema</label>
            <textarea class="form-control" id="ticket_description" name="ticket_description" rows="3" required></textarea>
        </div>
        <div class="form-group">
            <label for="ticket_priority">Prioridad</label>
            <select class="form-control" id="ticket_priority" name="ticket_priority" required>
                <option value="Baja">Baja</option>
                <option value="Media">Media</option>
                <option value="Alta">Alta</option>
                <option value="Urgente">Urgente</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Crear Ticket</button>
    </form>

	<?php
		// Conexión a la base de datos
		$conn = new PDO('mysql:host=localhost;dbname=soporte_tecnico', 'usuario', 'contraseña');

		// Crear ticket
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['customer_name']) && !empty($_POST['customer_email']) && !empty($_POST['ticket_subject']) && !empty($_POST['ticket_description']) && !empty($_POST['ticket_priority']))
		{
			$stmt = $conn->prepare("INSERT INTO tickets (customer_name, customer_email, subject, description, priority, status) VALUES (:customer_name, :customer_email, :subject, :description, :priority, 'Abierto')");
			$stmt->execute([
				':customer_name' => $_POST['customer_name'],
				':customer_email' => $_POST['customer_email'],
				':subject' => $_POST['ticket_subject'],
				':description' => $_POST['ticket_description'],
				':priority' => $_POST['ticket_priority'],
			]);
			echo '<div class="alert alert-success" role="alert">Ticket creado correctamente.</div>';
		}

		// Actualizar estado del ticket
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status']) && is_numeric($_POST['ticket_id']))
		{
			$stmt = $conn->prepare("UPDATE tickets SET status = :status WHERE id = :ticket_id");
			$stmt->execute([
				':status' => $_POST['update_status'],
				':ticket_id' => $_POST['ticket_id'],
			]);
			echo '<div class="alert alert-success" role="alert">Estado del ticket actualizado correctamente.</div>';
		}

		// Obtener y mostrar tickets
		$stmt = $conn->prepare("SELECT * FROM tickets ORDER BY priority DESC, created_at ASC");
		$stmt->execute();
		$tickets = $stmt->fetchAll();

		if (!empty($tickets))
		{
			echo '<div class="table-responsive">';
			echo '<table class="table table-bordered">';
			echo '<thead class="thead-dark">';
			echo '<tr>';
			echo '<th>ID</th>';
			echo '<th>Cliente</th>';
			echo '<th>Email</th>';
			echo '<th>Asunto</th>';
			echo '<th>Descripción</th>';
			echo '<th>Prioridad</th>';
			echo '<th>Estado</th>';
			echo '<th>Acciones</th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			foreach ($tickets as $ticket)
			{
				echo '<tr>';
				echo '<td>' . htmlspecialchars($ticket['id']) . '</td>';
				echo '<td>' . htmlspecialchars($ticket['customer_name']) . '</td>';
				echo '<td>' . htmlspecialchars($ticket['customer_email']) . '</td>';
				echo '<td>' . htmlspecialchars($ticket['subject']) . '</td>';
				echo '<td>' . htmlspecialchars($ticket['description']) . '</td>';
				echo '<td>' . htmlspecialchars($ticket['priority']) . '</td>';
				echo '<td>' . htmlspecialchars($ticket['status']) . '</td>';
				echo '<td>';
				echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '" method="post">';
				echo '<input type="hidden" name="ticket_id" value="' . $ticket['id'] . '">';
				echo '<select class="form-control mb-2" name="update_status">';
				echo '<option value="Abierto"' . ($ticket['status'] == 'Abierto' ? ' selected' : '') . '>Abierto</option>';
				echo '<option value="En Progreso"' . ($ticket['status'] == 'En Progreso' ? ' selected' : '') . '>En Progreso</option>';
				echo '<option value="Cerrado"' . ($ticket['status'] == 'Cerrado' ? ' selected' : '') . '>Cerrado</option>';
				echo '</select>';
				echo '<button type="submit" class="btn btn-primary">Actualizar</button>';
				echo '</form>';
				echo '</td>';
				echo '</tr>';
			}
			echo '</tbody>';
			echo '</table>';
			echo '</div>';
		}
		else
		{
			echo '<div class="alert alert-info" role="alert">No hay tickets disponibles.</div>';
		}

		// Cerrar conexión
		$conn = null;
	?>
</div>

<?php include "../footer.php"; // Incluye el footer ?>

<!-- Script de Bootstrap -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>

</html>

