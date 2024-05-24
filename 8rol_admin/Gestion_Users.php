<?php
	require_once '../autoload.php'; // Incluye el archivo de conexión PDO

	session_start();

	// Obtener una conexión a la base de datos
	$conexion = database::LoadDatabase();

	// Generar token anti-CSRF si no está definido
	if (empty($_SESSION['csrf_token']))
	{
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}

	// Función para mostrar la lista de usuarios
	function MostrarUsuarios($conexion): void
	{
		$query = "SELECT usu_id, usu_name, usu_rol, usu_phone, usu_email FROM pps_users";
		$stmt  = $conexion->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if ($result)
		{
			echo "<h2>Lista de Usuarios</h2>";
			echo "<table class='table table-striped'>";
			echo "<tr><th>ID</th><th>Nombre</th><th>Rol</th><th>Teléfono</th><th>Correo</th><th>Acciones</th></tr>";
			foreach ($result as $row)
			{
				echo "<tr>";
				echo "<td>{$row['usu_id']}</td>";
				echo "<td>{$row['usu_name']}</td>";
				echo "<td>{$row['usu_rol']}</td>";
				echo "<td>{$row['usu_phone']}</td>";
				echo "<td>{$row['usu_email']}</td>";
				echo "<td>";
				echo "<form action='Mod_user.php' method='post' style='display:inline;'>";
				echo "<input type='hidden' name='idUsuario' value='{$row['usu_id']}'>"; // Campo oculto para enviar el ID del usuario
				echo "<button type='submit' class='btn btn-primary btn-sm'>Modificar</button>"; // Botón para enviar el formulario
				echo "</form> ";
				echo "<form method='post' style='display:inline;'>";
				echo "<input type='hidden' name='idUsuario' value='{$row['usu_id']}'>"; // Campo oculto para enviar el ID del usuario
				echo "<input type='hidden' name='csrf_token' value='{$_SESSION['csrf_token']}'>"; // Token CSRF
				echo "<button type='submit' name='eliminarUsuario' class='btn btn-danger btn-sm'>Eliminar</button>"; // Botón para enviar el formulario de eliminación
				echo "</form>";
				echo "</td>";
				echo "</tr>";
			}
			echo "</table>";
		}
		else
		{
			echo "No se encontraron usuarios.";
		}
	}

	// Validar token anti-CSRF y manejar eliminación de usuario
	if ($_SERVER['REQUEST_METHOD'] === 'POST')
	{
		if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']))
		{
			echo "Error en la validación CSRF.";
		}
		else
		{
			if (isset($_POST['eliminarUsuario']))
			{
				if (!empty($_POST['idUsuario']))
				{
					// Obtener el ID del usuario a eliminar
					$idUsuario = $_POST['idUsuario'];

					try
					{
						// Eliminar usuario de la base de datos
						$query = "DELETE FROM pps_users WHERE usu_id = ?";
						$stmt  = $conexion->prepare($query);
						$exito = $stmt->execute([$idUsuario]);

						if ($exito)
						{
							// Redirigir al usuario de nuevo a la página actual
							header("Location: {$_SERVER['REQUEST_URI']}");
							exit(); // Detener la ejecución del script para evitar más procesamiento
						}
						else
						{
							echo "Error al eliminar el usuario.";
						}
					}
					catch (Exception $e)
					{
						echo "Error al eliminar usuario: " . $e->getMessage();
					}
				}
				else
				{
					echo "No se proporcionó un ID de usuario válido.";
				}
			}
		}
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/vendor/twbs/bootstrap/dist/css/bootstrap.min.css"> <!-- Añadir CSS de Bootstrap -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- Referencia a jQuery -->
    <title>Administración de Usuarios</title>
</head>
<body>
<?php include "../nav.php" ?>
<div class="container mt-5">
    <h1>Administración de Usuarios</h1>

    <!-- Formulario para crear un nuevo usuario -->
    <h2>Crear Nuevo Usuario</h2>
    <form id="formCrearUsuario" method="post">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre:</label>
            <input type="text" id="nombre" name="nombre" pattern="[a-zA-Z\s]+" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="passwd" class="form-label">Contraseña:</label>
            <input type="password" id="passwd" name="passwd" pattern="^[a-zA-Z0-9!@#$%^&*()_+}{:;?]+$" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="telf" class="form-label">Teléfono:</label>
            <input type="text" id="telf" name="telf" pattern="\d{9}" title="El número de teléfono debe tener 9 dígitos" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="rol" class="form-label">Rol:</label>
            <select id="rol" name="rol" class="form-select" required>
                <option value="A">Administrador</option> <!-- ADMIN -->
                <option value="U">Usuario</option> <!-- USER -->
                <option value="S">Soporte</option> <!-- SOPORTE -->
            </select>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Correo:</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <button type="submit" id="btnCrearUsuario" name="crearUsuario" class="btn btn-primary">Crear Usuario</button>
    </form>
    <script>
		$(document).ready(function () {
			// AJAX para enviar el formulario de creación de usuario
			$("#btnCrearUsuario").click(function (event) {
				event.preventDefault(); // Prevenir la acción predeterminada del botón
				$.ajax({
					url: "crear_usuario.php", // Ruta del archivo PHP que procesa el formulario
					type: "POST",
					data: $("#formCrearUsuario").serialize(), // Serializar el formulario
					success: function (response) {
						alert(response); // Mostrar mensaje de respuesta
						// Redireccionar a la página de administración de usuarios
						window.location.href = "Gestion_Users.php";
					}
				});
			});
		});
    </script>
    <button class="btn btn-secondary mt-3" onclick="window.location.href='Rol_Admin.php'">Ir a Rol-Admin</button>

	<?php
		// Mostrar la lista de usuarios al final de la página
		MostrarUsuarios($conexion);
	?>
</div>

<!-- Añadir JS de Bootstrap -->
<script src="/vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
	$conexion = null;
?>




