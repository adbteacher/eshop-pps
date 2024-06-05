<?php
session_start(); // Iniciar la sesión si aún no se ha iniciado

require_once '../autoload.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['UserEmail'])) {
	header("Location: ../1login/login.php"); // Redirigir a la página de inicio de sesión si el usuario no está autenticado
	exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Editar Dirección</title>
	<!-- CSS / Hoja de estilos Bootstrap -->
	<link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="../vendor/fortawesome/font-awesome/css/all.min.css" rel="stylesheet">

	<!-- Favicon -->
	<link rel="apple-touch-icon" sizes="180x180" href="/0images/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/0images/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/0images/favicon-16x16.png">
	<link rel="manifest" href="/0images/site.webmanifest">
	<style>
		.container {
			padding: 20px;
		}
	</style>
</head>

<body>
	<?php
	// Función de limpieza:
	function cleanInput($input)
	{
		$input = trim($input);
		$input = stripslashes($input);
		$input = str_replace(["'", '"', ";", "|", "[", "]", "x00", "<", ">", "~", "´", "/", "\\", "¿"], '', $input);
		$input = str_replace(['=', '+', '-', '#', '(', ')', '!', '$', '{', '}', '`', '?', '%'], '', $input);
		return $input;
	}

	// Función comprobación de País
	function isValidCountry($country)
	{
		return in_array($country, ['Estados Unidos', 'España', 'Alemania', 'Francia']);
	}

	// Obtener el ID de la dirección a editar
	$edit_address_id = isset($_SESSION['edit_address_id']) ? $_SESSION['edit_address_id'] : (isset($_POST['edit_address_id']) ? cleanInput($_POST['edit_address_id']) : '');

	// Obtener el ID del usuario
	$user_id = $_SESSION['UserID'];

	// Guardar el ID de la dirección en la sesión
	$_SESSION['edit_address_id'] = $edit_address_id;

	// Función para verificar que la dirección pertenece al usuario
	function verifyUserAddress($address_id, $user_id)
	{
		try {
			$connection = database::LoadDatabase();
			$sql        = "SELECT COUNT(*) FROM pps_addresses_per_user WHERE adr_id = ? AND adr_user = ?";
			$stmt       = $connection->prepare($sql);
			$stmt->execute([$address_id, $user_id]);
			return $stmt->fetchColumn() > 0;
		} catch (PDOException $e) {
			$_SESSION['error_message'] = 'Error al verificar usuario y dirección.';
			header("Location: usu_address.php");
			exit;
		}
	}

	// Verificar si el ID de la dirección pertenece al usuario
	if (!verifyUserAddress($edit_address_id, $user_id)) {
		$_SESSION['error_message'] = 'Esta dirección que intentas editar no pertenece a tu cuenta.';
		header("Location: usu_address.php");
		exit;
	}

	if (isset($_SESSION['edit_address_id'])) {

		// Obtener los detalles de la dirección a editar
		try {
			if (!empty($edit_address_id)) {
				$connection = database::LoadDatabase();
				$sql        = "SELECT * FROM pps_addresses_per_user WHERE adr_id = ?";
				$stmt       = $connection->prepare($sql);
				$stmt->execute([$edit_address_id]);
				$edit_adress = $stmt->fetch(PDO::FETCH_ASSOC);
			}
		} catch (PDOException $e) {
			$_SESSION['error_message'] = 'Error al obtener los detalles de la dirección.';
			header("Location: usu_address.php");
			exit;
		}
	} else {
		// Si no se ha almacenado el ID de la dirección en la sesión, muestra un mensaje de error apropiado o redirecciona a otra página
		$_SESSION['error_message'] = 'Error: No se pudo encontrar la dirección.';
		header("Location: usu_address.php");
		exit;
	}
	// Manejar el envío del formulario para actualizar la dirección
	if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitUpdateadress'])) {
		// Obtener el ID de la dirección a editar
		$edit_address_id = isset($_POST['edit_address_id']) ? cleanInput($_POST['edit_address_id']) : '';

		//Limpieza de campos
		$line1       = isset($_POST['adr_line1']) ? cleanInput($_POST['adr_line1']) : '';
		$line2       = isset($_POST['adr_line2']) ? cleanInput($_POST['adr_line2']) : '';
		$city        = isset($_POST['adr_city']) ? cleanInput($_POST['adr_city']) : '';
		$state       = isset($_POST['adr_state']) ? cleanInput($_POST['adr_state']) : '';
		$postal_code = isset($_POST['adr_postal_code']) ? cleanInput($_POST['adr_postal_code']) : '';
		$country     = isset($_POST['adr_country']) ? cleanInput($_POST['adr_country']) : '';

		// Verificar longitud y caracteres permitidos para la Línea 1
		if (strlen($line1) > 100 || !preg_match("/^[a-zA-Z0-9\sº]+$/", $line1)) {
			$_SESSION['error_message'] = 'Por favor, ingrese una Línea 1 válida (máximo 100 caracteres, letras y números solamente).';
			header("Location: usu_address_edit.php");
			exit;
		}

		// Verificar longitud y caracteres permitidos para la Línea 2
		if (!empty($line2) && (strlen($line2) > 100 || !preg_match("/^[a-zA-Z0-9\s]+$/", $line2))) {
			$_SESSION['error_message'] = 'Por favor, ingrese una Línea 2 válida (máximo 100 caracteres, letras y números solamente).';
			header("Location: usu_address_edit.php");
			exit;
		}

		// Verificar longitud y caracteres permitidos para la Ciudad
		if (strlen($city) > 50 || !preg_match("/^[a-zA-Z\s]+$/", $city)) {
			$_SESSION['error_message'] = 'Por favor, ingrese una Ciudad válida (máximo 50 caracteres, solo texto).';
			header("Location: usu_address_edit.php");
			exit;
		}

		// Verificar longitud y caracteres permitidos para el Estado

		if (!empty($state) && (strlen($state) > 50 || !preg_match("/^[a-zA-Z\s]+$/", $state))) {
			$_SESSION['error_message'] = 'Por favor, ingrese un Estado válido (máximo 50 caracteres, solo texto).';
			header("Location: usu_address_edit.php");
			exit;
		}

		// Verificar el formato del Código Postal
		if (!preg_match("/^\d{5}$/", $postal_code)) {
			$_SESSION['error_message'] = 'Por favor, ingrese un Código Postal válido (deben ser 5 números).';
			header("Location: usu_address_edit.php");
			exit;
		}

		// Verificar que el país seleccionado sea válido
		if (!isValidCountry($country)) {
			$_SESSION['error_message'] = 'Por favor, seleccione un País válido.';
			header("Location: usu_address_edit.php");
			exit;
		}

		// Actualizar la dirección en la base de datos
		try {
			$sql  = "UPDATE pps_addresses_per_user SET adr_line1 = ?, adr_line2 = ?, adr_city = ?, adr_state = ?, adr_postal_code = ?, adr_country = ? WHERE adr_id = ?";
			$stmt = $connection->prepare($sql);
			$stmt->execute([$line1, $line2, $city, $state, $postal_code, $country, $edit_address_id]);

			// Redireccionar a la página para evitar el reenvío del formulario
			header("Location: usu_address.php");
			exit;
		} catch (PDOException $e) {
			$_SESSION['error_message'] = 'Error al actualizar la dirección.';
			header("Location: usu_address_edit.php");
			exit;
		}
	}
	include "../nav.php";
	?>

	<div class="container">
		<div class="back-button-container">
			<a href="usu_address.php" class="btn btn-secondary"><i class='fa-solid fa-arrow-left'></i></a>
		</div>
		<!-- Formulario para editar la dirección -->
		<h1 class="text-center mb-4">Editar Dirección</h1>
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

		<?php if ($edit_adress && !empty($edit_adress['adr_id'])) : ?>
			<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
				<input type="hidden" name="edit_address_id" value="<?php echo $edit_adress['adr_id']; ?>">
				<div class="mb-3">
					<label for="adr_country" class="form-label">País:</label>
					<select id="adr_country" name="adr_country" class="form-control" required>
						<option value="Estados Unidos" <?php if ($edit_adress['adr_country'] === 'Estados Unidos')
															echo 'selected'; ?>>Estados Unidos
						</option>
						<option value="Alemania" <?php if ($edit_adress['adr_country'] === 'Alemania')
														echo 'selected'; ?>>Alemania
						</option>
						<option value="España" <?php if ($edit_adress['adr_country'] === 'España')
													echo 'selected'; ?>>España
						</option>
						<option value="Francia" <?php if ($edit_adress['adr_country'] === 'Francia')
													echo 'selected'; ?>>Francia
						</option>
					</select>
				</div>
				<div class="mb-3">
					<label for="adr_line1" class="form-label">Línea de dirección 1:</label>
					<input type="text" name="adr_line1" value="<?php echo $edit_adress['adr_line1']; ?>" class="form-control" required maxlength="100">
				</div>
				<div class="mb-3">
					<label for="adr_line1" class="form-label">Línea de dirección 2:</label>
					<input type="text" name="adr_line2" value="<?php echo $edit_adress['adr_line2']; ?>" class="form-control" maxlength="100">
				</div>
				<div class="mb-3">
					<label for="adr_postal_code" class="form-label">Código Postal:</label>
					<input type="number" name="adr_postal_code" value="<?php echo $edit_adress['adr_postal_code']; ?>" class="form-control" required minlength="5" maxlength="5">
				</div>
				<div class="mb-3">
					<label for="adr_city" class="form-label">Ciudad:</label>
					<input type="text" name="adr_city" value="<?php echo $edit_adress['adr_city']; ?>" class="form-control" required maxlength="50">
				</div>
				<div class="mb-3">
					<label for="adr_state" class="form-label">Provincia:</label>
					<input type="text" name="adr_state" value="<?php echo $edit_adress['adr_state']; ?>" class="form-control" maxlength="50">
				</div>
				<button type="submit" name="submitUpdateadress" class="btn btn-primary">Actualizar Dirección</button>
			</form>
		<?php else : ?>
			<p class="text-danger">No se pudo encontrar la dirección para editar.</p>
		<?php endif; ?>
	</div>
	<?php include "../footer.php"; // Incluye el footer 
	?>

</body>

</html>

<?php
// Cerrar la conexión a la base de datos al finalizar
$connection = null;
?>