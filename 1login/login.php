<?php
	session_start();
	require_once("../vendor/autoload.php");
	require_once("../autoload.php");

	require_once 'funciones.php';

	AddSecurityHeaders();

	if (empty($_SESSION['csrf_token']))
	{
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}

	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		$Email    = SanitizeInput($_POST['email']);
		$Password = SanitizeInput($_POST['password']);

		CheckLoginAttempts($Email);  // Asegúrate de pasar el correo electrónico como argumento.

		//if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']))
		//{
		//	echo "Error en la validación CSRF.";
		//	exit;
		//}

		$LoginSuccessful = VerifyUser($Email, $Password, $msg);
		LogAttempt($Email, $LoginSuccessful);

		if ($LoginSuccessful)
		{
			$User = getUserByEmail($Email);

			$_SESSION["UserID"]    = $User["usu_id"];
			$_SESSION["UserName"]  = $User["usu_name"];
			$_SESSION["UserEmail"] = $User["usu_email"];

			if (Has2FA($Email))
			{
				header('Location: verify_2fa.php');
				exit;
			}
			else
			{
				header('Location: ../index.php');
				exit;
			}
		}
		else
		{
			echo $msg;
		}
	}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <!--    <link rel="stylesheet" type="text/css" href="estilo.css">-->
</head>
<body>

<?php
	include "../nav.php";
?>

<div class="form-box">
    <h1>Iniciar Sesión</h1>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
		<?php if (!empty($LoginSuccessful) && $LoginSuccessful !== "Inicio de sesión exitoso."): ?>
            <div class="error"><?php echo $LoginSuccessful; ?></div>
		<?php endif; ?>
        <label for="email">Correo electrónico:</label>
        <input type="email" name="email" id="email" required>
        <label for="password">Contraseña:</label>
        <input type="password" name="password" id="password" required>
        <input type="submit" value="Iniciar Sesión">
    </form>
    <a href="/3register/register.form.php">¿No tienes cuenta? ¡Regístrate!</a>
</div>

</body>

</html>