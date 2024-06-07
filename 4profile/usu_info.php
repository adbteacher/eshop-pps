<?php
	if (session_status() == PHP_SESSION_NONE)
	{
		session_start();
	}
 // Iniciar la sesión si aún no se ha iniciado

require_once '../autoload.php';

	// Verificar si el usuario está autenticado
	functions::ActiveSession();

	//Comprobar permisos al programa
	functions::HasPermissions(basename(__FILE__));

$user_email = $_SESSION['UserEmail'];
$user_id    = $_SESSION['UserID'];
$user_name  = $_SESSION['UserName'];

// Generar un token CSRF y almacenarlo en la sesión
if (empty($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

	$csrf_token = $_SESSION['csrf_token'];

// Functions
function cleanInput($input): array|string
{
	$input = trim($input);
	$input = stripslashes($input);
	$input = str_replace(["'", '"', ";", "|", "[", "]", "x00", "<", ">", "~", "´", "/", "\\", "¿"], '', $input);
	$input = str_replace(['=', '+', '-', '#', '(', ')', '!', '$', '{', '}', '`', '?', '%'], '', $input);
	return $input;
}

// Database connection
$connection = database::LoadDatabase();

// Retrieve user data
try {
	$sql  = "SELECT * FROM pps_users WHERE usu_id = ?";
	$stmt = $connection->prepare($sql);
	$stmt->execute([$user_id]);
	$UserRow = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
	$_SESSION['error_message'] = 'Error, BBDD al cargar datos de usuario';
	header("Location: usu_info.php");
	exit;
}

if (!$UserRow) {
	$_SESSION['error_message'] = 'Usuario no encontrado.';
	header("Location: usu_info.php");
	exit;
}

// Process the personal information editing form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitPersonalInfo'])) {
	// Verificar el token CSRF
	if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
		$_SESSION['error_message'] = 'Error: Token CSRF inválido.';
		header("Location: usu_info.php");
		exit;
	}

	// Retrieve form data
	$Name     = isset($_POST['name']) ? cleanInput($_POST['name']) : '';
	$Surnames = isset($_POST['surnames']) ? cleanInput($_POST['surnames']) : '';
	$Phone    = isset($_POST['phone']) ? cleanInput($_POST['phone']) : '';
	$Email    = isset($_POST['email']) ? cleanInput($_POST['email']) : '';

	// Validations
	if (!preg_match("/^[a-zA-Z\s]{1,30}$/", $Name)) {
		$_SESSION['error_message'] = 'Nombre inválido. (Sin acentos)';
		header("Location: usu_info.php");
		exit;
	}

    if ($_SESSION["UserRol"] == "U")
	{
        if (!preg_match("/^[a-zA-Z\s]{1,30}$/", $Surnames)) {
            $_SESSION['error_message'] = 'Apellidos inválidos. (Sin acentos)';
            header("Location: usu_info.php");
            exit;
	    }
	}

	if (!preg_match("/^\d{9}$/", $Phone)) {
		$_SESSION['error_message'] = 'Teléfono inválido. Debe contener 9 dígitos.';
		header("Location: usu_info.php");
		exit;
	}

	if (!preg_match("/^[a-zA-Z0-9._+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $Email) || strlen($Email) > 30) {
		$_SESSION['error_message'] = 'Correo electrónico inválido o demasiado largo (máximo 30 caracteres).';
		header("Location: usu_info.php");
		exit;
	}

	// Update user data
	try {
        if ($_SESSION["UserRol"] == "U")
		{
			$sql = "UPDATE pps_users SET 
			usu_name = ?,  
			usu_surnames = ?,
			usu_phone = ?,
			usu_email = ? 
			WHERE usu_id = ?";

			$stmt = $connection->prepare($sql);
			$stmt->bindValue(1, $Name);
			$stmt->bindValue(2, $Surnames);
			$stmt->bindValue(3, $Phone);
			$stmt->bindValue(4, $Email);
			$stmt->bindValue(5, $user_id);

		}else
			if ($_SESSION["UserRol"] == "V")
			{
            $sql = "UPDATE pps_users SET 
			usu_company = ?,  
			usu_phone = ?,
			usu_email = ? 
			WHERE usu_id = ?";

				$stmt = $connection->prepare($sql);
				$stmt->bindValue(1, $Name);
				$stmt->bindValue(2, $Phone);
				$stmt->bindValue(3, $Email);
				$stmt->bindValue(4, $user_id);

			}

		if ($stmt->execute()) {
			if ($Email !== $user_email) {
				$_SESSION['UserEmail'] = $Email;
			}
			if ($Name !== $user_name) {
				$_SESSION['UserName'] = $Name;
			}
			$_SESSION['success_message'] = 'Información actualizada correctamente.';

			// If the user changes email, log out for security.
			if ($Email !== $user_email) {
				header("Location: ../logout.php");
				exit;
			}

			header("Location: usu_info.php");
			exit;
		} else {
			$_SESSION['error_message'] = 'Error al actualizar la información: ';
			header("Location: usu_info.php");
			exit;
		}
	} catch (PDOException $e) {
		$_SESSION['error_message'] = 'Error al actualizar la información: ';
		header("Location: usu_info.php");
		exit;
	}
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Gestión de información personal</title>
	<!-- CSS / Hoja de estilos Bootstrap -->
	<link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="../vendor/fortawesome/font-awesome/css/all.min.css" rel="stylesheet">

	<!-- Favicon -->
	<link rel="apple-touch-icon" sizes="180x180" href="/0images/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/0images/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/0images/favicon-16x16.png">
	<link rel="manifest" href="/0images/site.webmanifest">
	<style>
		.form-container {
			max-width: 400px;
			/* Ancho máximo del formulario */
			margin: 0 auto;
			/* Centra el formulario horizontalmente */
			padding: 20px;
			/* Añade espaciado interior al formulario */
		}

		.back-button-container {
			margin-top: 10px;
		}
	</style>
	<script>
		window.addEventListener('DOMContentLoaded', (event) => {
			const emailInput = document.querySelector('input[name="email"]');
			emailInput.addEventListener('change', (event) => {
				alert('Al modificar el correo, se cerrará la sesión por motivos de seguridad.');
			});
		});
	</script>
</head>

<body>

	<?php include "../nav.php"; ?>

	<div class="container">
		<div class="back-button-container">
			<a href="main_profile.php" class="btn btn-secondary"><i class='fa-solid fa-arrow-left'></i></a>
		</div>
		<div class="form-container">
			<h3 class="text-center">Información de usuario:</h3>
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
			<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="mb-3">
					<label for="name" class="form-label"><b>Nombre:</b></label>
					<input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($UserRow['usu_name'] ?:$UserRow['usu_company']); ?>" pattern="[a-zA-Z\s]{1,50}" title="Solo letras y espacios, máximo 50 caracteres" required>
				</div>

                <?php if ($_SESSION["UserRol"] == "U")
					{?>
				<div class="mb-3">
					<label for="surnames" class="form-label"><b>Apellidos:</b></label>
					<input type="text" class="form-control" name="surnames" value="<?php echo htmlspecialchars($UserRow['usu_surnames']); ?>" pattern="[a-zA-Z\s]{1,50}" title="Solo letras y espacios, máximo 50 caracteres" required>
				</div>
                <?php } ?>

				<div class="mb-3">
					<label for="email" class="form-label"><b>Email:</b></label>
					<input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($UserRow['usu_email']); ?>" pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" maxlength="50" title="Debe ser un correo electrónico válido y no más de 50 caracteres" required>
				</div>

				<div class="mb-3">
					<label for="phone" class="form-label"><b>Teléfono:</b></label>
					<input type="number" class="form-control" name="phone" value="<?php echo htmlspecialchars($UserRow['usu_phone']); ?>" pattern="\d{9}" title="Debe contener 9 dígitos" required>
				</div>

				<div class="text-center">
					<input type="submit" class="btn btn-primary" name="submitPersonalInfo" value="Guardar Cambios">
				</div>
			</form>
		</div>
	</div>
	<?php include "../footer.php"; ?>
</body>

</html>