<?php
	require_once "../autoload.php";

	// Conexión a la base de datos
	$conn = database::LoadDatabase();

	// Obtener el ID del ticket desde el POST
	$ticket_id = isset($_POST['ticket_id']) ? $_POST['ticket_id'] : null;

	if ($ticket_id)
	{
		// Consultar los datos del ticket
		$stmt = $conn->prepare("SELECT * FROM pps_tickets WHERE tic_id = :ticket_id");
		$stmt->bindParam(':ticket_id', $ticket_id);
		$stmt->execute();
		$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

		// Si el ticket no existe, redirigir o mostrar un mensaje de error
		if (!$ticket)
		{
			echo "<div class='alert alert-danger' role='alert'>Ticket no encontrado.</div>";
			exit;
		}
	}
	else
	{
		echo "<div class='alert alert-danger' role='alert'>ID de ticket no proporcionado.</div>";
		exit;
	}

	// Array asociativo de prioridades
	$prioridades = [
		'A' => 'Alta',
		'M' => 'Media',
		'B' => 'Baja',
	];

	// Procesar el formulario al enviarlo
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update']))
	{
		$tic_title    = $_POST['tic_title'];
		$tic_message  = $_POST['tic_message'];
		$tic_priority = $_POST['tic_priority'];

		// Actualizar los datos del ticket en la base de datos
		$update_stmt = $conn->prepare("UPDATE pps_tickets SET tic_title = :tic_title, tic_message = :tic_message, tic_priority = :tic_priority WHERE tic_id = :ticket_id");
		$update_stmt->bindParam(':tic_title', $tic_title);
		$update_stmt->bindParam(':tic_message', $tic_message);
		$update_stmt->bindParam(':tic_priority', $tic_priority);
		$update_stmt->bindParam(':ticket_id', $ticket_id);

		if ($update_stmt->execute())
		{
			echo "<div class='alert alert-success' role='alert'>Ticket actualizado exitosamente.</div>";
            //sleep(2);
			header("Location: RolSupport.php");
            exit;

		}
		else
		{
			echo "<div class='alert alert-danger' role='alert'>Error al actualizar el ticket.</div>";
		}
	}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Ticket</title>
    <link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include "../nav.php" ?>

<div class="container mt-5">
    <h1 class="mb-4">Editar Ticket</h1>
    <form method="post">
        <input type="hidden" name="ticket_id" value="<?php echo htmlspecialchars($ticket_id); ?>">
        <input type="hidden" name="update" value="1">
        <div class="mb-3">
            <label for="tic_title" class="form-label">Título</label>
            <input type="text" class="form-control" id="tic_title" name="tic_title" value="<?php echo htmlspecialchars($ticket['tic_title']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="tic_message" class="form-label">Mensaje</label>
            <textarea class="form-control" id="tic_message" name="tic_message" rows="3" required><?php echo htmlspecialchars($ticket['tic_message']); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="tic_priority" class="form-label">Prioridad</label>
            <select class="form-select" id="tic_priority" name="tic_priority" required>
				<?php foreach ($prioridades as $key => $value): ?>
                    <option value="<?php echo htmlspecialchars($key); ?>" <?php echo ($ticket['tic_priority'] == $key) ? 'selected' : ''; ?>>
						<?php echo htmlspecialchars($value); ?>
                    </option>
				<?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar Ticket</button>
    </form>
</div>

</body>
</html>
