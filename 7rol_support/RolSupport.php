<?php

	require_once "../autoload.php";

	// Verificar si el usuario está autenticado
	functions::ActiveSession();

	//Comprobar permisos al programa
	functions::HasPermissions(basename(__FILE__));

	// Conexión a la base de datos
	$conn = database::LoadDatabase();

	// Obtener el filtro de prioridad, si existe
	$selected_priority = isset($_GET['priority']) ? $_GET['priority'] : '';

	// Crear ticket
	$Query = "SELECT * FROM pps_tickets";
	if ($selected_priority) {
		$Query .= " WHERE tic_priority = :priority";
	}
	$Query .= " ORDER BY tic_user_creator DESC, tic_creation_time DESC";
	$Statement = $conn->prepare($Query);
	if ($selected_priority) {
		$Statement->bindParam(':priority', $selected_priority);
	}
	$Statement->execute();

	$Tickets = $Statement->fetchAll();

	// Array asociativo de prioridades
	$Priorities = [
		'A' => 'Alta',
		'M' => 'Media',
		'B' => 'Baja'
	];

	// Array asociativo para colores de borde y texto
	$BorderColors = [
		'A' => 'border-danger text-danger',
		'M' => 'border-warning text-warning',
		'B' => 'border-success text-success'
	];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets</title>
    <link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
		function confirmDelete(ticketId) {
			if (confirm('¿Está seguro de que desea eliminar este ticket?')) {
				window.location.href = 'DeleteTicket.php?id=' + ticketId;
			}
		}
    </script>
</head>
<body>
<?php include "../nav.php" ?>

<div class="container mt-5">
    <h1 class="mb-4">Tickets</h1>
    <form method="get" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label for="priority" class="form-label">Filtrar por Prioridad</label>
                <select class="form-select" id="priority" name="priority" onchange="this.form.submit()">
                    <option value="">Todas</option>
					<?php foreach ($Priorities as $key => $value): ?>
                        <option value="<?php echo htmlspecialchars($key); ?>" <?php echo ($selected_priority == $key) ? 'selected' : ''; ?>>
							<?php echo htmlspecialchars($value); ?>
                        </option>
					<?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>
	<?php if (count($Tickets) > 0): ?>
        <div class="row">
			<?php foreach ($Tickets as $ticket):
				$User = functions::GetUser($_SESSION["UserID"]);
				$priority = $ticket['tic_priority'];
				$borderClass = isset($BorderColors[$priority]) ? $BorderColors[$priority] : '';
				?>
                <div class="col-md-4">
                    <div class="card mb-4 <?php echo $borderClass; ?>" style="border-width: 2px;">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($ticket['tic_title']); ?></h5>
                            <p class="card-text"><strong>Creado por:</strong> <?php echo htmlspecialchars($User["usu_email"]); ?></p>
                            <p class="card-text"><strong>Mensaje:</strong> <?php echo htmlspecialchars($ticket['tic_message']); ?></p>
                            <p class="card-text"><strong>Hora de creación:</strong> <?php echo htmlspecialchars($ticket['tic_creation_time']); ?></p>
                            <p class="card-text"><strong>Cerrado por:</strong> <?php echo htmlspecialchars($ticket['tic_user_solver']) ?: ""; ?></p>
                            <p class="card-text"><strong>Hora de resolución:</strong> <?php echo htmlspecialchars($ticket['tic_resolution_time']) ?: ""; ?></p>
                            <p class="card-text"><strong>Prioridad:</strong> <span class="<?php echo $borderClass; ?>"><?php echo htmlspecialchars($Priorities[$priority]); ?></span></p>
                            <div class="d-flex justify-content-between">
                                <form action="EditTicket.php" method="post">
                                    <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars($ticket['tic_id']); ?>">
                                    <button type="submit" class="btn btn-primary">Editar</button>
                                </form>
                                <form action="DeleteTicket.php" method="post" onsubmit="return confirm('¿Está seguro de que desea eliminar este ticket?');">
                                    <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars($ticket['tic_id']); ?>">
                                    <button type="submit" class="btn btn-danger">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
			<?php endforeach; ?>
        </div>
	<?php else: ?>
        <div class="alert alert-info" role="alert">
            No hay tickets disponibles.
        </div>
	<?php endif; ?>
</div>
</body>
</html>
