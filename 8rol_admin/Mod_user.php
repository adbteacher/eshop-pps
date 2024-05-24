<?php
	require_once '../autoload.php'; // Incluye el archivo de conexión PDO

	// Obtener el ID del usuario a modificar
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Usuario</title>
    <link rel="stylesheet" href="estilo.css"> <!-- Referencia al archivo de estilo CSS -->
</head>
<body>
<?php include "../nav.php" ?>

<h1>Modificar Usuario</h1>

<h2>Modificar Usuario</h2>
<form id="formModificarUsuario" method="post">
    <input type="hidden" name="idUsuario" value="<?php echo $idUsuario; ?>"> <!-- Campo oculto con el ID del usuario -->
    <label for="nombre">Nombre:</label>
    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($row['usu_name']); ?>" required>
    <br><br>
    <label for="passwd">Contraseña:</label>
    <input type="password" id="passwd" name="passwd" value="<?php echo htmlspecialchars($row['usu_password']); ?>" required>
    <br><br>
    <label for="telf">Teléfono:</label>
    <input type="text" id="telf" name="telf" value="<?php echo htmlspecialchars($row['usu_phone']); ?>" required>
    <br><br>
    <label for="rol">Rol:</label>
    <select id="rol" name="rol">
        <option value="A" <?php if ($row['usu_rol'] == 'A')
			echo 'selected'; ?>>Administrador
        </option> <!-- ADMIN   -->
        <option value="U" <?php if ($row['usu_rol'] == 'U')
			echo 'selected'; ?>>Usuario
        </option> <!-- USER   -->
        <option value="S" <?php if ($row['usu_rol'] == 'S')
			echo 'selected'; ?>>Soporte
        </option> <!-- SOPORTE   -->
    </select>
    <br><br>
    <label for="email">Correo:</label>
    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($row['usu_email']); ?>" required>
    <br><br>
    <button type="button" id="btnModificarUsuario">Modificar Usuario</button>
</form>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
	$(document).ready(function () {
		// AJAX para enviar el formulario de modificación de usuario
		$("#btnModificarUsuario").click(function () {
			$.ajax({
				url: "procesar_modificacion_usuario.php", // Ruta del archivo PHP que procesa el formulario
				type: "POST",
				data: $("#formModificarUsuario").serialize(), // Serializar el formulario
				success: function (response) {
					alert(response); // Mostrar mensaje de respuesta
					// Redireccionar a la página de administración de usuarios
					window.location.href = "Gestion_Users.php";
				}
			});
		});
	});
</script>

</body>
</html>
