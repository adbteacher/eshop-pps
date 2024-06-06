<?php
	require_once "../autoload.php";

	if (session_status() == PHP_SESSION_NONE)
	{
		session_start();
	}

	// Verificar si el usuario está autenticado
	functions::ActiveSession();

	//Comprobar permisos al programa
	functions::HasPermissions(basename(__FILE__));

	// Conexión a la base de datos
	$conn = database::LoadDatabase();

	// Verificar si se recibió el ID del ticket por POST
	$ticket_id = $_POST['ticket_id'];

	if ($ticket_id)
	{
		// Eliminar el ticket de la base de datos
		$stmt = $conn->prepare("DELETE FROM pps_tickets WHERE tic_id = :ticket_id");
		$stmt->bindParam(':ticket_id', $ticket_id);

		if ($stmt->execute())
		{
			// Redirigir a RoleSupport.php después de la eliminación
			header("Location: RolSupport.php");
			exit();
		}
		else
		{
			echo "<div class='alert alert-danger' role='alert'>Error al eliminar el ticket.</div>";
		}
	}
	else
	{
		echo "<div class='alert alert-danger' role='alert'>ID de ticket no proporcionado.</div>";
		exit();
	}
