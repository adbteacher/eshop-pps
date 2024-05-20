<?php
	session_start(); // Iniciar la sesión si aún no se ha iniciado

	// Verificar si el usuario está autenticado
	if (!isset($_SESSION['UserEmail']) || !isset($_SESSION['UserID']))
	{
		header("Location: ../1login/login.php"); // Redirigir a la página de inicio de sesión si el usuario no está autenticado
		exit;
	}

	require_once '../Database.php';

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
		echo "User not found";
		exit;
	}

	// Process the personal information editing form
	if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitPersonalInfo']))
	{
		// Verificar el token CSRF
		if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'])
		{
			echo "Error: Invalid CSRF token.";
			exit;
		}

		// Retrieve form data
		$Name     = isset($_POST['name']) ? cleanInput($_POST['name']) : '';
		$Surnames = isset($_POST['surnames']) ? cleanInput($_POST['surnames']) : '';
		$Phone    = isset($_POST['phone']) ? cleanInput($_POST['phone']) : '';
		$Email    = isset($_POST['email']) ? cleanInput($_POST['email']) : '';

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
			header("Location: usu_info.php");
			// Update session if email has changed
			if ($Email !== $user_email)
			{
				$_SESSION['UserEmail'] = $Email;
			}
			if ($Name !== $user_name)
			{
				$_SESSION['UserName'] = $Name;
			}
		}
		else
		{
			echo "Error updating information: " . $stmt->errorInfo()[2];
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
    </head>

    <body>

	<?php
		include "../nav.php";
	?>

    <div class="container">
        <div class="form-container">
            <h3 class="text-center">Información de usuario:</h3>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="mb-3">
                    <label for="name" class="form-label"><b>Nombre:</b></label>
                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($UserRow['usu_name']); ?>">
                </div>

                <div class="mb-3">
                    <label for="surnames" class="form-label"><b>Apellidos:</b></label>
                    <input type="text" class="form-control" name="surnames" value="<?php echo htmlspecialchars($UserRow['usu_surnames']); ?>">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label"><b>Email:</b></label>
                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($UserRow['usu_email']); ?>" readonly>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label"><b>Teléfono:</b></label>
                    <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($UserRow['usu_phone']); ?>">
                </div>

                <div class="text-center">
                    <input type="submit" class="btn btn-primary" name="submitPersonalInfo" value="Guardar Cambios">
                </div>
            </form>
        </div>
    </div>

    </body>

    </html>

<?php
	// Close the database connection
	$connection = null;
?>