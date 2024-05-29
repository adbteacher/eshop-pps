<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Añadir Nueva Dirección</title>
	<link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
	<style>
		.container {
			padding: 20px;
		}
	</style>

</head>

<body>
	<?php
	session_start(); // Iniciar la sesión si aún no se ha iniciado

	// Verificar si el usuario está autenticado
	if (!isset($_SESSION['UserEmail']) || !isset($_SESSION['UserID'])) {
		header("Location: ../1login/login.php"); // Redirigir a la página de inicio de sesión si el usuario no está autenticado
		exit;
	}

	require_once '../autoload.php'; // Conexión a la PDO.

	$UserID = $_SESSION['UserID']; // ID de usuario

	// Generar un token CSRF y almacenarlo en la sesión
	if (empty($_SESSION['csrf_token'])) {
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}

	$csrf_token = $_SESSION['csrf_token']; // CSRF TOKEN

	// Función de limpieza:
	function cleanInput($input): array|string
	{
		$input = trim($input);
		$input = stripslashes($input);
		$input = str_replace(["'", '"', ";", "|", "[", "]", "x00", "<", ">", "~", "´", "/", "\\", "¿"], '', $input);
		$input = str_replace(['=', '+', '-', '#', '(', ')', '!', '$', '{', '}', '`', '?'], '', $input);
		return $input;
	}

	// Función para añadir una nueva dirección
	function addNewadress($user_id, $line1, $line2, $city, $state, $postal_code, $country): void
	{
		$connection = database::LoadDatabase();
		$sql        = "INSERT INTO pps_addresses_per_user (adr_user, adr_line1, adr_line2, adr_city, adr_state, adr_postal_code, adr_country, adr_is_main) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

		// Verificar si es la primera dirección del usuario
		$is_first_address = isFirstAddress($user_id);

		// Establecer si es la dirección principal
		$is_main = $is_first_address ? 1 : 0;

		$stmt = $connection->prepare($sql);
		$stmt->execute([$user_id, $line1, $line2, $city, $state, $postal_code, $country, $is_main]);
	}

	// Función para verificar si es la primera dirección del usuario
	function isFirstAddress($user_id): bool
	{
		$connection = database::LoadDatabase();
		$sql        = "SELECT COUNT(*) FROM pps_addresses_per_user WHERE adr_user = ?";
		$stmt       = $connection->prepare($sql);
		$stmt->execute([$user_id]);
		$count = $stmt->fetchColumn();

		return $count == 0;
	}

	// Función para verificar si el usuario tiene cuatro o más direcciones.
	function isFourthAddress($user_id): bool
	{
		$connection = database::LoadDatabase();
		$sql = "SELECT COUNT(*) FROM pps_addresses_per_user WHERE adr_user = ?";
		$stmt = $connection->prepare($sql);
		$stmt->execute([$user_id]);
		$count = $stmt->fetchColumn();

		return $count >= 4;  // Devuelve true si el usuario tiene cuatro o más direcciones.
	}


	// Función comprobación de País
	function isValidCountry($country): bool
	{
		return in_array($country, ['Estados Unidos', 'España', 'Alemania', 'Francia']);
	}


	// Manejar el envío del formulario para añadir una nueva dirección
	if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitNewadress'])) {
		// Verificar el token CSRF
		if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
			$_SESSION['error_message'] = 'Error: Token CSRF inválido.';
			header("Location: usu_new_address.php");
			exit;
		}

		// Verificar si el usuario tiene cuatro o más direcciones antes de permitir agregar una nueva.
		if (isFourthAddress($UserID)) {
			$_SESSION['error_message'] = 'Solo puedes tener un máximo de cuatro direcciones.';
			header("Location: usu_address.php"); // Redirige a la página de información del usuario u otra página adecuada
			exit;
		}

		$line1       = isset($_POST['adr_line1']) ? cleanInput($_POST['adr_line1']) : '';
		$line2       = isset($_POST['adr_line2']) ? cleanInput($_POST['adr_line2']) : '';
		$city        = isset($_POST['adr_city']) ? cleanInput($_POST['adr_city']) : '';
		$state       = isset($_POST['adr_state']) ? cleanInput($_POST['adr_state']) : '';
		$postal_code = isset($_POST['adr_postal_code']) ? cleanInput($_POST['adr_postal_code']) : '';
		$country     = isset($_POST['adr_country']) ? cleanInput($_POST['adr_country']) : '';

		// Verificar longitud y caracteres permitidos para la Línea 1
		if (strlen($line1) > 100 || !preg_match("/^[a-zA-Z0-9\sº]+$/", $line1)) {
			$_SESSION['error_message'] = 'Por favor, ingrese una Línea 1 válida (máximo 100 caracteres, letras y números solamente).';
			header("Location: usu_new_address.php");
			exit;
		}

		// Verificar longitud y caracteres permitidos para la Línea 2
		if (!empty($line2) && (strlen($line2) > 100 || !preg_match("/^[a-zA-Z0-9\s]+$/", $line2))) {
			$_SESSION['error_message'] = 'Por favor, ingrese una Línea 2 válida (máximo 100 caracteres, letras y números solamente).';
			header("Location: usu_new_address.php");
			exit;
		}

		// Verificar longitud y caracteres permitidos para la Ciudad
		if (strlen($city) > 50 || !preg_match("/^[a-zA-Z\s]+$/", $city)) {
			$_SESSION['error_message'] = 'Por favor, ingrese una Ciudad válida (máximo 50 caracteres, solo texto).';
			header("Location: usu_new_address.php");
			exit;
		}

		// Verificar longitud y caracteres permitidos para el Estado
		if (!empty($state) && (strlen($state) > 50 || !preg_match("/^[a-zA-Z\s]+$/", $state))) {
			$_SESSION['error_message'] = 'Por favor, ingrese un Estado válido (máximo 50 caracteres, solo texto).';
			header("Location: usu_new_address.php");
			exit;
		}

		// Verificar el formato del Código Postal
		if (!preg_match("/^\d{5}$/", $postal_code)) {
			$_SESSION['error_message'] = 'Por favor, ingrese un Código Postal válido (deben ser 5 números).';
			header("Location: usu_new_address.php");
			exit;
		}

		// Verificar que el país seleccionado sea válido
		if (!isValidCountry($country)) {
			$_SESSION['error_message'] = 'Por favor, seleccione un País válido.';
			header("Location: usu_new_address.php");
			exit;
		}


		// Añadir la nueva dirección a la base de datos
		addNewadress($UserID, $line1, $line2, $city, $state, $postal_code, $country);

		// Redireccionar a la página para evitar el reenvío del formulario
		header("Location: usu_address.php");
		exit;
	}
	include "../nav.php";
	?>

	<div class="container">
		<!-- Mensajes de éxito y error -->
		<?php
		if (isset($_SESSION['error_message'])) {
			echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
			unset($_SESSION['error_message']);
		}
		if (isset($_SESSION['success_message'])) {
			echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
			unset($_SESSION['success_message']);
		}
		?>

		<!-- Formulario para añadir una nueva dirección -->
		<h1 class="text-center mb-4">Añadir Nueva Dirección</h1>
		<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
			<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
			<div class="mb-3">
				<label for="adr_country" class="form-label">País:</label>
				<select id="adr_country" name="adr_country" class="form-control" required>
					<option value="Estados Unidos">Estados Unidos</option>
					<option value="España" selected>España</option>
					<option value="Alemania">Alemania</option>
					<option value="Francia">Francia</option>
				</select>
			</div>
			<div class="mb-3">
				<label for="adr_line1" class="form-label">Línea de dirección 1:</label>
				<input type="text" id="adr_line1" name="adr_line1" class="form-control" maxlength="100" required>
			</div>
			<div class="mb-3">
				<label for="adr_line2" class="form-label">Línea de dirección 2:</label>
				<input type="text" id="adr_line2" name="adr_line2" class="form-control" maxlength="100">
			</div>
			<div class="mb-3">
				<label for="adr_postal_code" class="form-label">Código Postal:</label>
				<input type="number" id="adr_postal_code" name="adr_postal_code" class="form-control" minlength="5" maxlength="5" required>
			</div>
			<div class="mb-3">
				<label for="adr_city" class="form-label">Ciudad:</label>
				<input type="text" id="adr_city" name="adr_city" class="form-control" maxlength="50" required>
			</div>
			<div class="mb-3">
				<label for="adr_state" class="form-label">Provincia:</label>
				<input type="text" id="adr_state" name="adr_state" class="form-control" maxlength="50">
			</div>
			<button type="submit" name="submitNewadress" class="btn btn-primary">Añadir Dirección</button>
		</form>
	</div>
	<?php include "../footer.php"; ?>
</body>

</html>