<?php
	require_once '../autoload.php'; // Incluye el archivo de conexión PDO
	session_start();
	functions::checkAdminAccess();
	// Verificar el token CSRF
	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']))
		{
			echo json_encode(['status' => 'error', 'message' => 'Error en la validación CSRF.']);
			exit;
		}

		// Obtener una conexión a la base de datos
		$conexion = database::LoadDatabase();

		$idUsuario        = $_POST['idUsuario'];
		$nombre           = $_POST['nombre'];
		$telf             = $_POST['telf'];
		$rol              = $_POST['rol'];
		$email            = $_POST['email'];
		$nueva_passwd     = $_POST['nueva_passwd']; // Obtener la nueva contraseña del formulario
		$confirmar_passwd = $_POST['confirmar_passwd']; // Obtener la confirmación de la nueva contraseña

		// Validar que el nombre no contenga caracteres susceptibles a inyección SQL
		if (!preg_match("/^[a-zA-Z\s]+$/", $nombre))
		{
			echo json_encode(['status' => 'error', 'message' => 'El nombre contiene caracteres inválidos.']);
			exit; // Detener la ejecución si el nombre es inválido
		}

		// Validar que el número de teléfono tenga exactamente 9 caracteres y sean todos numéricos
		if (strlen($telf) !== 9 || !ctype_digit($telf))
		{
			echo json_encode(['status' => 'error', 'message' => 'El número de teléfono debe tener exactamente 9 dígitos y ser numérico.']);
			exit; // Detener la ejecución si el número de teléfono es inválido
		}

		// Validar que el email sea una dirección de correo electrónico válida y sea de Gmail
		if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.(com|es)$/", $email))
		{
			echo json_encode(['status' => 'error', 'message' => 'El correo electrónico debe ser una dirección válida de email.']);
			exit; // Detener la ejecución si el correo electrónico es inválido
		}

		// Preparar la consulta de actualización
		$query  = "UPDATE pps_users SET usu_name=?, usu_phone=?, usu_rol=?, usu_email=?"; // Query base sin contraseña
		$params = [$nombre, $telf, $rol, $email];

		// Verificar si se proporcionó una nueva contraseña
		if (!empty($nueva_passwd) && !empty($confirmar_passwd))
		{
			// Validar que la nueva contraseña y la confirmación coincidan
			if ($nueva_passwd !== $confirmar_passwd)
			{
				echo json_encode(['status' => 'error', 'message' => 'Las contraseñas no coinciden.']);
				exit; // Detener la ejecución si las contraseñas no coinciden
			}

			// Validar que la contraseña tenga al menos 8 caracteres y no contenga caracteres susceptibles a inyección SQL
			if (strlen($nueva_passwd) < 8 || !preg_match("/^[a-zA-Z0-9!@#$%^&*-_+}{:;?]+$/", $nueva_passwd))
			{
				echo json_encode(['status' => 'error', 'message' => 'La contraseña debe tener al menos 8 caracteres y no contener caracteres inválidos.']);
				exit; // Detener la ejecución si la contraseña es inválida
			}

			// Hashear la nueva contraseña
			$hashed_passwd = password_hash($nueva_passwd, PASSWORD_DEFAULT);
			// Agregar la contraseña hasheada a la consulta y parámetros
			$query    .= ", usu_password=?";
			$params[] = $hashed_passwd;
		}

		// Agregar la condición WHERE para filtrar por ID de usuario
		$query    .= " WHERE usu_id=?";
		$params[] = $idUsuario;

		// Ejecutar la consulta de actualización
		$stmt = $conexion->prepare($query);
		$stmt->execute($params);

		if ($stmt->rowCount() > 0)
		{
			echo json_encode(['status' => 'success', 'message' => 'Usuario actualizado exitosamente.']);
		}
		else
		{
			echo json_encode(['status' => 'error', 'message' => 'No se realizaron cambios en los datos del usuario.']);
		}

		// Cerrar la conexión
		$conexion = null;
	}
	else
	{
		echo json_encode(['status' => 'error', 'message' => 'Solicitud no válida.']);
	}