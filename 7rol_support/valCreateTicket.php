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

	if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['title']) && !empty($_POST['message']))
	{
		// Conexión a la base de datos
		$conn = database::LoadDatabase();

		$Title          = $_POST['title'];
		$SanitizedTitle = strip_tags($Title);
		$SanitizedTitle = htmlspecialchars($SanitizedTitle, ENT_QUOTES, 'UTF-8');

		$Message          = $_POST['message'];
		$SanitizedMessage = strip_tags($Message);
		$SanitizedMessage = htmlspecialchars($SanitizedMessage, ENT_QUOTES, 'UTF-8');

		// Crear ticket
		$stmt = $conn->prepare("INSERT INTO pps_tickets (tic_title, tic_message, tic_user_creator, tic_priority) 
                            VALUES (:tic_title, :tic_message, :tic_user_creator, :tic_priority)");
		$stmt->execute([
			':tic_title' => $SanitizedTitle,
			':tic_message' => $SanitizedMessage,
			':tic_user_creator' => $_SESSION['UserID'],
			':tic_priority' => 'l',
		]);

		// Redireccionar a la página principal después de procesar el formulario
		header("Location: create_ticket.php");
		exit;
	}