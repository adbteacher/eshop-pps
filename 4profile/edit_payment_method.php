<?php
	session_start();

	require_once '../autoload.php';
	// Verificar si el usuario está autenticado
	if (!isset($_SESSION['UserEmail']))
	{
		header("Location: ../1login/login.php");
		exit;
	}

	// Función de limpieza:
	function cleanInput($input)
	{
		$input = trim($input);
		$input = stripslashes($input);
		$input = str_replace(["'", '"', ";", "|", "[", "]", "x00", "<", ">", "~", "´", "\\", "¿"], '', $input);
		$input = str_replace(['=', '#', '(', ')', '!', '$', '{', '}', '`', '?'], '', $input);
		return $input;
	}

	// Generar y almacenar el token CSRF si no existe
	if (empty($_SESSION['csrf_token']))
	{
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}

	// Obtener el ID del usuario
	$user_id = $_SESSION['UserID'];

	// Verificar si se ha recibido el id del método de pago a editar
	if (!isset($_SESSION['edit_pmu_id']))
	{
		$_SESSION['error_message'] = 'ID de método de pago no encontrado.';
		header("Location: payment_methods.php");
		exit;
	}

	$pmu_id     = $_SESSION['edit_pmu_id'];
	$connection = database::LoadDatabase();

	// Obtener los datos del método de pago a editar
	$sql  = "SELECT * FROM pps_payment_methods_per_user WHERE pmu_id = :pmu_id AND pmu_user = :pmu_user";
	$stmt = $connection->prepare($sql);
	$stmt->execute(['pmu_id' => $pmu_id, 'pmu_user' => $user_id]);
	$method = $stmt->fetch(PDO::FETCH_ASSOC);

	if (!$method)
	{
		$_SESSION['error_message'] = 'Método de pago no encontrado.';
		header("Location: payment_methods.php");
		exit;
	}

	// Manejar el envío del formulario para editar un método de pago
	if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitEditPaymentMethod']))
	{
		// Verificar el token CSRF
		if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'])
		{
			$_SESSION['error_message'] = 'Token CSRF inválido.';
			header("Location: edit_payment_method.php");
			exit;
		}

		// Obtener el método de pago actual del usuario
		$pmu_id     = $_SESSION['edit_pmu_id'];
		$connection = database::LoadDatabase();
		$user_id    = $_SESSION['UserID'];
		$sql        = "SELECT * FROM pps_payment_methods_per_user WHERE pmu_id = :pmu_id AND pmu_user = :pmu_user";
		$stmt       = $connection->prepare($sql);
		$stmt->execute(['pmu_id' => $pmu_id, 'pmu_user' => $user_id]);
		$method = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$method)
		{
			$_SESSION['error_message'] = 'Método de pago no encontrado.';
			header("Location: payment_methods.php");
			exit;
		}

		// Limpiar los campos según el método de pago
		$payment_method  = cleanInput($method['pmu_payment_method']);
		$account_number  = '';
		$swift           = '';
		$card_number     = '';
		$cve_number      = '';
		$cardholder      = '';
		$expiration_date = '';
		$online_account  = '';
		$online_password = '';

		// Actualizar campos según el método de pago seleccionado
		if ($payment_method == "1")
		{ // Tarjeta de Crédito
			$card_number     = cleanInput($_POST['pmu_card_number'] ?? '');
			$cve_number      = cleanInput($_POST['pmu_cve_number'] ?? '');
			$cardholder      = cleanInput($_POST['pmu_cardholder'] ?? '');
			$expiration_date = cleanInput($_POST['pmu_expiration_date'] ?? '');

			// Validar los campos
			if (empty($card_number) || empty($cve_number) || empty($cardholder) || empty($expiration_date))
			{
				$_SESSION['error_message'] = 'Por favor, complete todos los campos de la tarjeta de crédito.';
				header("Location: edit_payment_method.php");
				exit;
			}
			if (!preg_match('/^[0-9]{16}$/', $card_number))
			{
				$_SESSION['error_message'] = 'Número de tarjeta inválido.';
				header("Location: edit_payment_method.php");
				exit;
			}
			if (!preg_match('/^[0-9]{3}$/', $cve_number))
			{
				$_SESSION['error_message'] = 'Número CVV inválido.';
				header("Location: edit_payment_method.php");
				exit;
			}
			if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $expiration_date))
			{
				$_SESSION['error_message'] = 'Fecha de expiración inválida.';
				header("Location: edit_payment_method.php");
				exit;
			}

			// Actualizar campos de la tarjeta de crédito
			$sql    = "UPDATE pps_payment_methods_per_user SET pmu_payment_method = :pmu_payment_method, pmu_card_number = :pmu_card_number, pmu_cve_number = :pmu_cve_number, pmu_cardholder = :pmu_cardholder, pmu_expiration_date = :pmu_expiration_date WHERE pmu_id = :pmu_id AND pmu_user = :pmu_user";
			$stmt   = $connection->prepare($sql);
			$params = [
				'pmu_payment_method' => $payment_method,
				'pmu_card_number' => $card_number,
				'pmu_cve_number' => $cve_number,
				'pmu_cardholder' => $cardholder,
				'pmu_expiration_date' => $expiration_date,
				'pmu_id' => $pmu_id,
				'pmu_user' => $user_id,
			];
		}
        elseif ($payment_method == "2")
		{ // PayPal
			$online_account  = cleanInput($_POST['pmu_online_account'] ?? '');
			$online_password = cleanInput($_POST['pmu_online_password'] ?? '');

			// Validar los campos
			if (empty($online_account) || empty($online_password))
			{
				$_SESSION['error_message'] = 'Por favor, complete todos los campos de PayPal.';
				header("Location: edit_payment_method.php");
				exit;
			}
			if (!filter_var($online_account, FILTER_VALIDATE_EMAIL))
			{
				$_SESSION['error_message'] = 'Correo electrónico de PayPal inválido.';
				header("Location: edit_payment_method.php");
				exit;
			}

			// Actualizar campos de PayPal
			$sql    = "UPDATE pps_payment_methods_per_user SET pmu_payment_method = :pmu_payment_method, pmu_online_account = :pmu_online_account, pmu_online_password = :pmu_online_password WHERE pmu_id = :pmu_id AND pmu_user = :pmu_user";
			$stmt   = $connection->prepare($sql);
			$params = [
				'pmu_payment_method' => $payment_method,
				'pmu_online_account' => $online_account,
				'pmu_online_password' => password_hash($online_password, PASSWORD_BCRYPT),
				'pmu_id' => $pmu_id,
				'pmu_user' => $user_id,
			];
		}

		// Ejecutar la consulta de actualización
		if ($stmt->execute($params))
		{
			$_SESSION['success_message'] = 'Método de pago actualizado exitosamente.';
			unset($_SESSION['edit_pmu_id']); // Eliminar el ID de la sesión después de la actualización
			header("Location: payment_methods.php");
			exit;
		}
		else
		{
			$_SESSION['error_message'] = 'Hubo un error al actualizar el método de pago.';
			header("Location: edit_payment_method.php");
			exit;
		}
	}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Método de Pago</title>
    <link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <style>
        .container {
            padding: 20px;
        }
    </style>
</head>

<body>
<?php include "../nav.php"; ?>

<div class="container">
    <h1 class="text-center mb-4">Editar Método de Pago</h1>

	<?php if (isset($_SESSION['error_message'])) : ?>
        <div class="alert alert-danger">
			<?php
				echo $_SESSION['error_message'];
				unset($_SESSION['error_message']);
			?>
        </div>
	<?php endif; ?>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

		<?php
			// Mostrar campos según el método de pago
			if ($method['pmu_payment_method'] == 1)
			{ // Tarjeta de Crédito
				?>
                <div class="mb-3">
                    <label for="pmu_card_number" class="form-label">Número de Tarjeta:</label>
                    <input type="text" id="pmu_card_number" name="pmu_card_number" class="form-control" value="<?php echo htmlspecialchars($method['pmu_card_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" maxlength="16" required>
                </div>
                <div class="mb-3">
                    <label for="pmu_cve_number" class="form-label">CVV:</label>
                    <input type="text" id="pmu_cve_number" name="pmu_cve_number" class="form-control" value="<?php echo htmlspecialchars($method['pmu_cve_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" maxlength="3" required>
                </div>
                <div class="mb-3">
                    <label for="pmu_cardholder" class="form-label">Nombre del Titular:</label>
                    <input type="text" id="pmu_cardholder" name="pmu_cardholder" class="form-control" value="<?php echo htmlspecialchars($method['pmu_cardholder'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="pmu_expiration_date" class="form-label">Fecha de Expiración (MM/AA):</label>
                    <input type="text" id="pmu_expiration_date" name="pmu_expiration_date" class="form-control" value="<?php echo htmlspecialchars($method['pmu_expiration_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" maxlength="5" required>
                </div>
			<?php }
            elseif ($method['pmu_payment_method'] == 2)
			{ // PayPal
				?>
                <div class="mb-3">
                    <label for="pmu_online_account" class="form-label">Cuenta de PayPal (email):</label>
                    <input type="email" id="pmu_online_account" name="pmu_online_account" class="form-control" value="<?php echo htmlspecialchars($method['pmu_online_account'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="pmu_online_password" class="form-label">Contraseña de PayPal:</label>
                    <input type="password" id="pmu_online_password" name="pmu_online_password" class="form-control" required>
                </div>
			<?php } ?>

        <button type="submit" name="submitEditPaymentMethod" class="btn btn-primary">Actualizar Método de Pago</button>
    </form>
</div>
<?php include "../footer.php"; ?>
</body>

</html>