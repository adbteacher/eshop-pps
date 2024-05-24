<?php
	/**
	 * Gestión de tickets de soporte: Permitir al usuario de soporte técnico recibir, gestionar
	 * y responder a tickets de soporte generados por los clientes. Habilitar la clasificación
	 * y priorización de los tickets según la gravedad del problema y el tiempo de respuesta requerido.
	 *
	 * Comunicación con clientes: Facilitar la comunicación bidireccional entre el equipo de soporte técnico
	 * y los clientes, permitiendo responder a consultas, proporcionar actualizaciones sobre el estado de los
	 * problemas y ofrecer soluciones o asistencia técnica.
	 *
	 * Registro de actividades: Registrar todas las interacciones y acciones realizadas
	 * por el equipo de soporte técnico en relación con los tickets de soporte,
	 * incluyendo notas, cambios de estado y resolución de problemas
	 *
	 */

	require_once("../autoload.php");

	// Conexión a la base de datos
	$conn = database::LoadDatabase();

	// Obtener y mostrar tickets
	$stmt = $conn->prepare("SELECT * FROM pps_tickets ORDER BY tic_creation_time ASC");
	$stmt->execute();
	$tickets = $stmt->fetchAll();
	$conn    = null;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Meta Etiquetas -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Crear ticket de soporte técnico">
    <meta name="keywords" content="soporte técnico, tickets, gestión, priorización">
    <meta name="author" content="Soporte Técnico">

    <!-- Titulo -->
    <title>Gestión de Tickets de Soporte Técnico</title>

    <!-- CSS / Hoja de estilos Bootstrap -->
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
        <h1 class="display-4">Creación de tickets de soporte técnico</h1>
        <p class="lead">Explícanos tu problema y te atenderemos lo antes posible.</p>
        <hr class="my-4">
    </div>

    <!-- Formulario de creación de tickets -->
    <form action="valCreateTicket.php" method="post" class="mb-4">
        <div class="form-group">
            <label for="title">Asunto</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="message">Descripción del Problema</label>
            <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Crear Ticket</button>
    </form>

	<?php
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
	?>
</div>

<?php include "../footer.php"; // Incluye el footer ?>

<!-- Script de Bootstrap -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>

</html>
