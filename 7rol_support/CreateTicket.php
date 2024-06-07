<?php
	require_once("../autoload.php");

	if (session_status() == PHP_SESSION_NONE)
	{
		session_start();
	}

	// Verificar si el usuario está autenticado
	functions::ActiveSession();

	//Comprobar permisos al programa
	functions::HasPermissions(basename(__FILE__));

	// Procesar el formulario al enviarlo
	if ($_SERVER['REQUEST_METHOD'] === 'POST')
	{
		// Conexión a la base de datos
		$conn = database::LoadDatabase();

		// Valores a insertar
		$tic_title         = $_POST['tic_title'];
		$SanitizedTitle = strip_tags($tic_title);
		$SanitizedTitle = htmlspecialchars($SanitizedTitle, ENT_QUOTES, 'UTF-8');

		$tic_user_creator  = $_SESSION['UserID'];
		$tic_message       = $_POST['tic_message'];
		$SanitizedMessage = strip_tags($tic_message);
		$SanitizedMessage = htmlspecialchars($SanitizedMessage, ENT_QUOTES, 'UTF-8');

		$tic_creation_time = date('Y-m-d H:i:s'); // Hora actual
		$tic_priority = "B";

		// Preparar y ejecutar la consulta
		$stmt = $conn->prepare("INSERT INTO pps_tickets (tic_title, tic_user_creator, tic_message, tic_creation_time, tic_priority) 
                                        VALUES (:tic_title, :tic_user_creator, :tic_message, :tic_creation_time, :tic_priority)");

		$stmt->bindParam(':tic_title', $SanitizedTitle);
		$stmt->bindParam(':tic_user_creator', $tic_user_creator);
		$stmt->bindParam(':tic_message', $SanitizedMessage);
		$stmt->bindParam(':tic_creation_time', $tic_creation_time);
		$stmt->bindParam(':tic_priority', $tic_priority);
		//$stmt->bindParam(':tic_resolution_time', $tic_resolution_time);

		if ($stmt->execute())
		{
			echo "<div class='alert alert-success' role='alert'>Ticket creado con éxito. Te atenderemos lo antes posible.</div>";
		}
		else
		{
			echo "<div class='alert alert-danger' role='alert'>Error al crear el ticket.</div>";
		}
	}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Ticket</title>
    <link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include "../nav.php"; ?>

<div class="container mt-5">
    <h1 class="mb-4">Crear Nuevo Ticket</h1>
    <form method="post">
        <div class="mb-3">
            <label for="tic_title" class="form-label">Asunto</label>
            <input type="text" class="form-control" id="tic_title" name="tic_title" required>
        </div>
        <div class="mb-3">
            <label for="tic_message" class="form-label">Detalles</label>
            <textarea class="form-control" id="tic_message" name="tic_message" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Crear Ticket</button>
    </form>
</div>

</body>
</html>
