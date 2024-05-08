<?php
	session_start();

	// Verificar si el usuario está autenticado
	if (!isset($_SESSION['user_id']))
	{
		// Redireccionar al usuario a la página de inicio de sesión si no está autenticado
		header("Location: ../1login/login.php");
		exit;
	}

	$user_id = $_SESSION['user_id'];

	// Función para obtener las direcciones del usuario
	function getUserAddresses($user_id): bool|array
	{
		$connection = database::LoadDatabase();
		$sql        = "SELECT * FROM pps_addresses_per_user WHERE adr_user = ?";
		$stmt       = $connection->prepare($sql);
		$stmt->execute([$user_id]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	// Obtener las direcciones del usuario
	$addresses = getUserAddresses($user_id);

	// Manejar el envío del formulario para añadir una nueva dirección
	if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitNewAddress']))
	{
		$line1       = $_POST['adr_line1'];
		$line2       = $_POST['adr_line2'];
		$city        = $_POST['adr_city'];
		$state       = $_POST['adr_state'];
		$postal_code = $_POST['adr_postal_code'];
		$country     = $_POST['adr_country'];


		// Insertar la nueva dirección en la base de datos
		$connection = database::LoadDatabase();
		$sql        = "INSERT INTO pps_addresses_per_user (adr_user, adr_line1, adr_line2, adr_city, adr_state, adr_postal_code, adr_country) VALUES (?, ?, ?, ?, ?, ?, ?)";
		$stmt       = $connection->prepare($sql);
		$stmt->execute([$user_id, $line1, $line2, $city, $state, $postal_code, $country]);

		// Redireccionar a la página para evitar el reenvío del formulario
		header("Location: addresses.php");
		exit;
	}

	// Manejar el envío del formulario para modificar la dirección principal
	if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitMainAddress']))
	{
		$main_address_id = $_POST['main_address_id'];

		// Marcar todas las direcciones del usuario como no principales
		$connection = database::LoadDatabase();
		$sql        = "UPDATE pps_addresses_per_user SET adr_is_main = 0 WHERE adr_user = ?";
		$stmt       = $connection->prepare($sql);
		$stmt->execute([$user_id]);

		// Marcar la dirección seleccionada como principal
		$sql  = "UPDATE pps_addresses_per_user SET adr_is_main = 1 WHERE adr_id = ?";
		$stmt = $connection->prepare($sql);
		$stmt->execute([$main_address_id]);

		// Redireccionar a la página para evitar el reenvío del formulario
		header("Location: addresses.php");
		exit;
	}

	// Manejar el envío del formulario para eliminar una dirección
	if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitDeleteAddress']))
	{
		$address_id = $_POST['delete_address_id'];

		// Eliminar la dirección de la base de datos
		$connection = database::LoadDatabase();
		$sql        = "DELETE FROM pps_addresses_per_user WHERE adr_id = ?";
		$stmt       = $connection->prepare($sql);
		$stmt->execute([$address_id]);

		// Redireccionar a la página para evitar el reenvío del formulario
		header("Location: usu_addres.php");
		exit;
	}
?>

    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Direcciones de Usuario</title>
    </head>

    <body>

	<?php
		include "../nav.php";
	?>

    <h1>Direcciones de Usuario</h1>

    <!-- Mostrar las direcciones existentes del usuario -->
    <h2>Direcciones Actuales:</h2>
    <ul>
		<?php foreach ($addresses as $address) : ?>
            <li>
				<?php echo $address['adr_line1']; ?>
				<?php echo $address['adr_line2'] ? ', ' . $address['adr_line2'] : ''; ?>
                <br>
				<?php echo $address['adr_city'] . ', ' . $address['adr_state'] . ' ' . $address['adr_postal_code']; ?>
                <br>
				<?php echo $address['adr_country']; ?>
				<?php if ($address['adr_is_main']) : ?>
                    <strong>(Principal)</strong>
				<?php endif; ?>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="delete_address_id" value="<?php echo $address['adr_id']; ?>">
                    <button type="submit" name="submitDeleteAddress">Eliminar</button>
                </form>
            </li>
		<?php endforeach; ?>
    </ul>

    <!-- Formulario para añadir una nueva dirección -->
    <h2>Añadir Nueva Dirección:</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="adr_line1">Línea 1:</label>
        <input type="text" name="adr_line1" required>
        <br>
        <label for="adr_line2">Línea 2:</label>
        <input type="text" name="adr_line2">
        <br>
        <label for="adr_city">Ciudad:</label>
        <input type="text" name="adr_city" required>
        <br>
        <label for="adr_state">Estado:</label>
        <input type="text" name="adr_state">
        <br>
        <label for="adr_postal_code">Código Postal:</label>
        <input type="text" name="adr_postal_code" required>
        <br>
        <label for="adr_country">País:</label>
        <input type="text" name="adr_country" required>
        <br>
        <button type="submit" name="submitNewAddress">Añadir Dirección</button>
    </form>

    <!-- Formulario para seleccionar la dirección principal -->
    <h2>Seleccionar Dirección Principal:</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <select name="main_address_id">
			<?php foreach ($addresses as $address) : ?>
                <option value="<?php echo $address['adr_id']; ?>"><?php echo $address['adr_line1']; ?></option>
			<?php endforeach; ?>
        </select>
        <button type="submit" name="submitMainAddress">Seleccionar Principal</button>
    </form>
    </body>

    </html>

<?php
	// Cerrar la conexión a la base de datos al finalizar
	$connection = null;
?>