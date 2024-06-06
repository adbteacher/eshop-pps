<?php
/*
	Esta pagina permite modificar los usuarios de la tienda, controlando
	todo lo psoible inyección SQL y diferentes vuln.
	 */
	require_once '../autoload.php'; // Incluye el archivo de conexión PDO

    session_start();
	functions::checkAdminAccess();

	// Generar token CSRF si no está definido
	if (empty($_SESSION['csrf_token']))
	{
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}

	// Verificar que se ha enviado el formulario con el ID del usuario
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idUsuario']))
	{
		// Verificar el token CSRF
		if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']))
		{
			echo "<p class='text-danger'>Error en la validación CSRF.</p>";
			exit;
		}

		$idUsuario = $_POST['idUsuario'];

		// Obtener una conexión a la base de datos
		$conexion = database::LoadDatabase();

		try
		{
			// Preparar la consulta para obtener los datos del usuario
			$query = "SELECT * FROM pps_users WHERE usu_id = ?";
			$stmt  = $conexion->prepare($query);
			$stmt->execute([$idUsuario]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);

			if (!$row)
			{
				echo "Usuario no encontrado.";
				exit;
			}
		}
		catch (PDOException $e)
		{
			// Manejar cualquier excepción y mostrar un mensaje genérico
			echo "Algo ha salido mal.";
			exit;
		}
	}
	else
	{
		echo "<p class='text-danger'>Solicitud no válida.</p>";
		exit;
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Usuario</title>
    <!-- Añadir CSS de Bootstrap -->
    <link href="/vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include "../nav.php"; ?>

<div class="container mt-5 mb-5">
    <h1>Modificar Usuario</h1>
    <form id="formModificarUsuario" method="post" class="needs-validation" novalidate>
        <input type="hidden" name="idUsuario" value="<?php echo $idUsuario; ?>">
        <!-- Campo oculto con el ID del usuario -->
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>"> <!-- Token CSRF -->
        <div class="form-group">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($row['usu_name']); ?>" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="nueva_passwd">Nueva Contraseña:</label>
            <input type="password" id="nueva_passwd" name="nueva_passwd" class="form-control">
        </div>
        <div class="form-group">
            <label for="confirmar_passwd">Confirmar Nueva Contraseña:</label>
            <input type="password" id="confirmar_passwd" name="confirmar_passwd" class="form-control">
        </div>
        <div class="form-group">
            <label for="telf">Teléfono:</label>
            <input type="text" id="telf" name="telf" value="<?php echo htmlspecialchars($row['usu_phone']); ?>" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="rol">Rol:</label>
            <select id="rol" name="rol" class="form-control">
                <option value="A" <?php if ($row['usu_rol'] == 'A')
					echo 'selected'; ?>>Administrador
                </option>
                <option value="U" <?php if ($row['usu_rol'] == 'U')
					echo 'selected'; ?>>Usuario
                </option>
                <option value="S" <?php if ($row['usu_rol'] == 'S')
					echo 'selected'; ?>>Soporte
                </option>
            </select>
        </div>
        <div class="form-group">
            <label for="email">Correo:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($row['usu_email']); ?>" class="form-control" required>
        </div>
        <br>
        <button type="button" id="btnModificarUsuario" class="btn btn-primary">Modificar Usuario</button>
		<br>
		<br>
		<a href="Rol_Admin.php" class="btn btn-secondary">Volver a Gestión</a>
    </form>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
	$(document).ready(function () {
		// AJAX para enviar el formulario de modificación de usuario
		$("#btnModificarUsuario").click(function (event) {
			event.preventDefault();
			$.ajax({
				url: "procesar_modificacion_usuario.php", // Ruta del archivo PHP que procesa el formulario
				type: "POST",
				data: $("#formModificarUsuario").serialize(), // Serializar el formulario
				success: function (response) {
					// Suponiendo que el archivo procesar_modificacion_usuario.php retorna una respuesta JSON con el estado y mensaje
					var res = JSON.parse(response);
					alert(res.message); // Mostrar mensaje de respuesta
					if (res.status === "success") {
						window.location.href = "Gestion_Users.php"; // Redireccionar si la modificación fue exitosa
					}
				},
				error: function () {
					alert("Error en la solicitud. Por favor, inténtelo de nuevo.");
				}
			});
		});
	});
</script>
<?php include "../footer.php"; ?>
</body>
</html>