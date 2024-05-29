<?php
	session_start(); // Iniciar la sesión si aún no se ha iniciado

	require_once '../autoload.php';

	// Verificar si el usuario está autenticado
	if (!isset($_SESSION['UserEmail']) || !isset($_SESSION['UserID']))
	{
		header("Location: ../1login/login.php"); // Redirigir a la página de inicio de sesión si el usuario no está autenticado
		exit;
	}

	$user_email = $_SESSION['UserEmail'];
	$user_id    = $_SESSION['UserID'];
	$user_name  = $_SESSION['UserName'];

	// Generar un token CSRF y almacenarlo en la sesión
	if (empty($_SESSION['csrf_token']))
	{
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}

	$csrf_token = $_SESSION['csrf_token'];

	// Functions
	function cleanInput($input): array|string
	{
		$input = trim($input);
		$input = stripslashes($input);
		$input = str_replace(["'", '"', ";", "|", "[", "]", "x00", "<", ">", "~", "´", "/", "\\", "¿"], '', $input);
		$input = str_replace(['=', '+', '-', '#', '(', ')', '!', '$', '{', '}', '`', '?'], '', $input);
		return $input;
	}

	// Database connection
	$connection = database::LoadDatabase();

	// Retrieve user data
	$sql  = "SELECT * FROM pps_users WHERE usu_id = ?";
	$stmt = $connection->prepare($sql);
	$stmt->execute([$user_id]);
	$UserRow = $stmt->fetch(PDO::FETCH_ASSOC);

	if (!$UserRow)
	{
		$_SESSION['error_message'] = 'Usuario no encontrado.';
		header("Location: usu_info.php");
		exit;
	}

	// Process the personal information editing form
	if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitPersonalInfo']))
	{
		// Verificar el token CSRF
		if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'])
		{
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
		if (!preg_match("/^[a-zA-Z\s]{1,50}$/", $Name))
		{
			$_SESSION['error_message'] = 'Nombre inválido.';
			header("Location: usu_info.php");
			exit;
		}

		if (!preg_match("/^[a-zA-Z\s]{1,50}$/", $Surnames))
		{
			$_SESSION['error_message'] = 'Apellidos inválidos.';
			header("Location: usu_info.php");
			exit;
		}

		if (!preg_match("/^\d{9}$/", $Phone))
		{
			$_SESSION['error_message'] = 'Teléfono inválido. Debe contener 9 dígitos.';
			header("Location: usu_info.php");
			exit;
		}

		if (!filter_var($Email, FILTER_VALIDATE_EMAIL) || strlen($Email) > 50)
		{
			$_SESSION['error_message'] = 'Correo electrónico inválido o demasiado largo (máximo 50 caracteres).';
			header("Location: usu_info.php");
			exit;
		}


		// Update information in the database
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

		if ($stmt->execute())
		{
			if ($Email !== $user_email)
			{
				$_SESSION['UserEmail'] = $Email;
			}
			if ($Name !== $user_name)
			{
				$_SESSION['UserName'] = $Name;
			}
			$_SESSION['success_message'] = 'Información actualizada correctamente.';

			// Si el usuario cambia de correo electrónico, cierra sesión por seguridad.
			if ($Email !== $user_email)
			{
				header("Location: ../logout.php");
				exit;
			}

			header("Location: usu_info.php");
			exit;
		}
		else
		{
			$_SESSION['error_message'] = 'Error al actualizar la información: ' . $stmt->errorInfo()[2];
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
    <link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <style>
        .form-container {
            max-width: 400px;
            /* Ancho máximo del formulario */
            margin: 0 auto;
            /* Centra el formulario horizontalmente */
            padding: 20px;
            /* Añade espaciado interior al formulario */
        }
    </style>
    <script>
		window.addEventListener('DOMContentLoaded', (event) => {
			const emailInput = document.querySelector('input[name="email"]');
			emailInput.addEventListener('change', (event) => {
				confirm('Al modificar el correo, se cerrará la sesión por motivos de seguridad.');
			});
		});
    </script>
</head>

<body>

<?php include "../nav.php"; ?>

<div class="container">
    <div class="form-container">
        <h3 class="text-center">Información de usuario:</h3>
		<?php
			if (isset($_SESSION['error_message']))
			{
				echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
				unset($_SESSION['error_message']);
			}
			if (isset($_SESSION['success_message']))
			{
				echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
				unset($_SESSION['success_message']);
			}
		?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <div class="mb-3">
                <label for="name" class="form-label"><b>Nombre:</b></label>
                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($UserRow['usu_name']); ?>" pattern="[a-zA-Z\s]{1,50}" title="Solo letras y espacios, máximo 50 caracteres" required>
            </div>

            <div class="mb-3">
                <label for="surnames" class="form-label"><b>Apellidos:</b></label>
                <input type="text" class="form-control" name="surnames" value="<?php echo htmlspecialchars($UserRow['usu_surnames']); ?>" pattern="[a-zA-Z\s]{1,50}" title="Solo letras y espacios, máximo 50 caracteres" required>
            </div>

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