<?php
	session_start(); // Iniciar la sesión si aún no se ha iniciado

	// Verificar si el usuario está autenticado
	if (!isset($_SESSION['UserEmail']))
	{
		header("Location: ../1login/login.php"); // Redirigir a la página de inicio de sesión si el usuario no está autenticado
		exit;
	}

	require_once '../Database.php';

	$UserID = $_SESSION['UserID'];

	// Función para generar un token CSRF
	function generateCSRFToken(): string
	{
		return bin2hex(random_bytes(32));
	}

	// Generar un token CSRF y almacenarlo en la sesión si aún no está definido
	if (empty($_SESSION['csrf_token']))
	{
		$_SESSION['csrf_token'] = generateCSRFToken();
	}

	$csrf_token = $_SESSION['csrf_token'];

	// Función de limpieza:
	function cleanInput($input): array|string
	{
		$input = trim($input);
		$input = stripslashes($input);
		$input = str_replace(["'", '"', ";", "|", "[", "]", "x00", "<", ">", "~", "´", "/", "\\", "¿"], '', $input);
		$input = str_replace(['=', '#', '(', ')', '!', '$', '{', '}', '`', '?'], '', $input);
		return $input;
	}

	// Función para obtener las direcciones del usuario
	function getUserAddresses($user): bool|array
	{
		$connection = database::LoadDatabase();
		$sql        = "SELECT * FROM pps_addresses_per_user WHERE adr_user = ?";
		$stmt       = $connection->prepare($sql);
		$stmt->execute([$user]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	// Función para verificar que la dirección pertenece al usuario
	function verifyUserAddress($address_id, $user_id): bool
	{
		$connection = database::LoadDatabase();
		$sql        = "SELECT COUNT(*) FROM pps_addresses_per_user WHERE adr_id = ? AND adr_user = ?";
		$stmt       = $connection->prepare($sql);
		$stmt->execute([$address_id, $user_id]);
		return $stmt->fetchColumn() > 0;
	}

	// Obtener las direcciones del usuario
	$addresses = getUserAddresses($UserID);

	// Generar tokens para cada dirección y almacenarlos en la sesión
	if (!isset($_SESSION['address_tokens']))
	{
		$_SESSION['address_tokens'] = [];
	}
	$address_tokens = $_SESSION['address_tokens'];

	foreach ($addresses as $address)
	{
		if (!isset($address_tokens[$address['adr_id']]))
		{
			$address_tokens[$address['adr_id']] = bin2hex(random_bytes(16));
		}
	}

	// Almacenar los tokens actualizados en la sesión
	$_SESSION['address_tokens'] = $address_tokens;

	// Manejar el envío del formulario para modificar la dirección principal
	if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitMainAddress']))
	{
		// Verificar el token CSRF
		if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'])
		{
			echo "Error: Invalid CSRF token.";
			exit;
		}

		$main_address_token = isset($_POST['main_address_token']) ? cleanInput($_POST['main_address_token']) : '';

		// Obtener el ID de la dirección a partir del token
		$main_address_id = array_search($main_address_token, $_SESSION['address_tokens']);
		if ($main_address_id === false || !verifyUserAddress($main_address_id, $UserID))
		{
			echo "Error: Invalid address token.";
			exit;
		}

		// Marcar todas las direcciones del usuario como no principales
		$connection = database::LoadDatabase();
		$sql        = "UPDATE pps_addresses_per_user SET adr_is_main = 0 WHERE adr_user = ?";
		$stmt       = $connection->prepare($sql);
		$stmt->execute([$UserID]);

		// Marcar la dirección seleccionada como principal
		$sql = "UPDATE pps_addresses_per_user SET adr_is_main = 1 WHERE adr_id = ?";
		$stmt = $connection->prepare($sql);
		$stmt->execute([$main_address_id]);

		// Redireccionar a la página para evitar el reenvío del formulario
		header("Location: usu_address.php");
		exit;
	}

	// Manejar el envío del formulario para eliminar una dirección
	if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitDeleteAddress']))
	{
		// Verificar el token CSRF
		if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'])
		{
			echo "Error: Invalid CSRF token.";
			exit;
		}

		$delete_address_token = isset($_POST['delete_address_token']) ? cleanInput($_POST['delete_address_token']) : '';

		// Obtener el ID de la dirección a partir del token
		$delete_address_id = array_search($delete_address_token, $_SESSION['address_tokens']);
		if ($delete_address_id === false || !verifyUserAddress($delete_address_id, $UserID))
		{
			echo "Error: Invalid address token.";
			exit;
		}

		// Eliminar la dirección de la base de datos
		$connection = database::LoadDatabase();
		$sql        = "DELETE FROM pps_addresses_per_user WHERE adr_id = ?";
		$stmt       = $connection->prepare($sql);
		$stmt->execute([$delete_address_id]);

		// Redireccionar a la página para evitar el reenvío del formulario
		header("Location: usu_address.php");
		exit;
	}

	// Manejar el envío del formulario para editar una dirección
	if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitEditAddress']))
	{
		// Verificar el token CSRF
		if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'])
		{
			echo "Error: Invalid CSRF token.";
			exit;
		}

		$edit_address_token = isset($_POST['edit_address_token']) ? cleanInput($_POST['edit_address_token']) : '';

		// Obtener el ID de la dirección a partir del token
		$edit_address_id = array_search($edit_address_token, $_SESSION['address_tokens']);
		if ($edit_address_id === false || !verifyUserAddress($edit_address_id, $UserID))
		{
			echo "Error: Invalid address token.";
			exit;
		}

		// Almacenar el ID en la sesión
		$_SESSION['edit_address_id'] = $edit_address_id;
		header("Location: usu_address_edit.php");
		exit;
	}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direcciones de Usuario</title>
    <link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <style>
        /* Estilos adicionales */
        .custom-button {
            display: block;
            margin: 20px auto;
        }

        .custom-strong {
            background-color: #df7d7d;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            position: absolute;
            top: 10px;
            right: 10px;
        }
    </style>
    <script>
		function confirmDelete() {
			return confirm("¿Está seguro de que desea eliminar esta dirección?");
		}
    </script>
</head>

<body>
<!-- Navbar -->
<?php include "../nav.php"; ?>

<div class="container mt-4 p-4 bg-white rounded">
    <h1 class="text-center text-dark bg-light p-2 rounded">Direcciones de Usuario</h1>

    <!-- Mostrar las direcciones existentes del usuario -->
    <div class="row">
		<?php foreach ($addresses as $address) : ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 position-relative">
                    <div class="card-body">
                        <p class="card-text">
							<?php echo $address['adr_line1']; ?>
							<?php echo $address['adr_line2'] ? ', ' . $address['adr_line2'] : ''; ?><br>
							<?php echo $address['adr_city'] . ', ' . $address['adr_state'] . ' ' . $address['adr_postal_code']; ?>
                            <br>
							<?php echo $address['adr_country']; ?>
                        </p>
						<?php if ($address['adr_is_main']) : ?>
                            <strong class="custom-strong">Principal</strong>
						<?php endif; ?>
                        <div class="d-flex justify-content-between mt-3">
							<?php if (!$address['adr_is_main']) : ?>
                                <!-- Botón para hacer esta dirección como principal -->
                                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                    <input type="hidden" name="main_address_token" value="<?php echo $address_tokens[$address['adr_id']]; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <button type="submit" name="submitMainAddress" class="btn btn-success">Principal
                                    </button>
                                </form>
							<?php endif; ?>
                            <!-- Botón para editar la dirección -->
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="ms-2">
                                <input type="hidden" name="edit_address_token" value="<?php echo $address_tokens[$address['adr_id']]; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <button type="submit" name="submitEditAddress" class="btn btn-primary">Editar</button>
                            </form>
                            <!-- Botón para eliminar la dirección -->
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="ms-2" onsubmit="return confirmDelete();">
                                <input type="hidden" name="delete_address_token" value="<?php echo $address_tokens[$address['adr_id']]; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <button type="submit" name="submitDeleteAddress" class="btn btn-danger">Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
		<?php endforeach; ?>
    </div>
    <!-- Botón para crear una nueva dirección -->
    <form method="post" action="usu_new_address.php">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <button type="submit" class="btn btn-primary custom-button">Crear Dirección</button>
    </form>
</div>

<!-- Footer -->
<?php include "../footer.php"; ?>

</body>

</html>