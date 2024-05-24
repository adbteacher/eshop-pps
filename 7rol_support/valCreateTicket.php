<?php
	require_once("../autoload.php");

	if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['title']) && !empty($_POST['message']))
	{
		// Conexión a la base de datos
		$conn = database::LoadDatabase();

		// Crear ticket
		$stmt = $conn->prepare("INSERT INTO pps_tickets (tic_title, tic_message, tic_user_creator, tic_priority) 
                            VALUES (:tic_title, :tic_message, :tic_user_creator, :tic_priority)");
		$stmt->execute([
			':tic_title' => $_POST['title'],
			':tic_message' => $_POST['message'],
			':tic_user_creator' => $_SESSION['UserID'],
			':tic_priority' => 'l',
		]);

		//FIXME PETA AL HACER INSERT

		// Redireccionar a la página principal después de procesar el formulario
		header("Location: create_ticket.php");
		exit;
	}