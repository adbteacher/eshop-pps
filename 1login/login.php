<?php
	session_start();  // Inicia o continúa una sesión existente
	require_once 'funciones.php';  // Incluye las funciones auxiliares
	require_once 'db.php';  // Incluye la conexión a la base de datos

	AddSecurityHeaders();  // Añade cabeceras de seguridad HTTP

	// Genera un token CSRF si no existe uno en la sesión actual
	if (empty($_SESSION['csrf_token']))
	{
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}

	// Procesa el formulario si se envió mediante POST
	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		// Limpia y asigna las variables de Email y Password ingresadas por el usuario
		$Email    = SanitizeInput($_POST['email']);
		$Password = SanitizeInput($_POST['password']);

		CheckLoginAttempts($Email);  // Verifica los intentos de inicio de sesión fallidos

		// Comprueba si el token CSRF es válido
		if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']))
		{
			echo "Error en la validación CSRF.";
			exit;  // Detiene la ejecución si la validación CSRF falla
		}

		// Verifica las credenciales del usuario y guarda el resultado
		$LoginSuccessful = VerifyUser($Email, $Password);
		LogAttempt($Email, $LoginSuccessful === "Inicio de sesión exitoso.");  // Registra el intento de inicio de sesión

		// Si el inicio de sesión es exitoso, redirecciona según tenga o no activada la autenticación de dos factores
		if ($LoginSuccessful === "Inicio de sesión exitoso.")
		{
			$_SESSION['email'] = $Email;  // Guarda el email en la sesión para identificar al usuario
			if (Has2FA($Email))  // Verifica si el usuario tiene activado 2FA
			{
				header('Location: verify_2fa.php');  // Redirige a la página de verificación 2FA
			}
			else
			{
				header('Location: ../index.php');  // Redirige al índice si no tiene 2FA
			}
			exit;  // Finaliza la ejecución después de la redirección
		}
		else
		{
			echo $LoginSuccessful;  // Muestra el mensaje de error si el inicio de sesión no es exitoso
		}
	}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" type="text/css" href="estilo.css">  <!-- Enlace a la hoja de estilos CSS -->
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
<div class="version">Versión 1.2</div>
<div class="form-box">
    <h1>Iniciar Sesión</h1>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
		<?php if (!empty($LoginSuccessful) && $LoginSuccessful !== "Inicio de sesión exitoso."): ?>
            <div class="error"><?php echo $LoginSuccessful; ?></div>  <!-- Muestra errores de inicio de sesión si los hay -->
		<?php endif; ?>
        <label for="email">Correo electrónico:</label>
        <input type="email" name="email" id="email" required>
        <label for="password">Contraseña:</label>
        <input type="password" name="password" id="password" required>
        <input type="submit" value="Iniciar Sesión">
    </form>
</div>
</body>
</html>
