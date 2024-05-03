<?php

/*Este archivo se eliminará cuando se implemente el login de JV. De momento sirve para registrar usuarios y poder testear el login*/

	session_start();
	require_once 'funciones.php';

	AddSecurityHeaders();
	AddSecurityHeaders();

	if (empty($_SESSION['csrf_token']))
	{
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}
	if (empty($_SESSION['csrf_token']))
	{
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}

	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']))
		{
			echo "Error en la validación CSRF.";
			exit;
		}
	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']))
		{
			echo "Error en la validación CSRF.";
			exit;
		}

		$Email    = SanitizeInput($_POST['email']);
		$Password = SanitizeInput($_POST['password']);

		if (RegisterUser($Email, $Password))
		{
			echo "Usuario registrado con éxito.<br>";
			echo "Redireccionando a la página de login...";
			header('Refresh: 2; URL=login.php');
			exit;
		}
		else
		{
			echo "Error al registrar el usuario o el usuario ya existe.";
		}
	}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" type="text/css" href="estilo.css">
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
</head>
<body>
<div class="form-box">
    <h1>Registro de Usuario</h1>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <label for="email">Correo electrónico:</label>
        <input type="email" id="email" name="email" required><br>
        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required><br>
        <input type="submit" value="Registrar">
    </form>
</div>
</div>
</body>
</html>
