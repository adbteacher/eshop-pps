<?php
	session_start();  // Inicia o continúa una sesión existente.
	require_once 'funciones.php';  // Incluye funciones auxiliares y de seguridad
	require_once '../vendor/autoload.php';  // Incluye el autoload de Composer para dependencias externas

	use RobThree\Auth\TwoFactorAuth;  // Usa la biblioteca para manejo de autenticación de dos factores

	AddSecurityHeaders();  // Añade cabeceras de seguridad HTTP

	// Verifica si el usuario está autenticado y autorizado para ver la página
	if (!isset($_SESSION['email']))
	{
		echo '<div class="warning">Error 333 - No está autorizado para ver esta página. (Sal, por favor)</div>';
		exit;  // Termina la ejecución si el usuario no está autenticado
	}

	$Email = $_SESSION['email'];  // Almacena el email de la sesión

	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		$Code2FA    = SanitizeInput($_POST['code']);  // Limpia el código 2FA ingresado por el usuario
		$Tfa        = new TwoFactorAuth();  // Crea una instancia de TwoFactorAuth
		$Connection = GetDatabaseConnection();  // Obtiene la conexión a la base de datos
		$Query      = $Connection->prepare("SELECT usu_verification_code FROM pps_users WHERE usu_email = ?");  // Prepara la consulta SQL para obtener el secreto de 2FA
		$Query->bindParam(1, $Email);  // Vincula el email a la consulta
		$Query->execute();  // Ejecuta la consulta
		$Result = $Query->fetch(PDO::FETCH_ASSOC);  // Recupera el resultado de la consulta
		$Secret = $Result['usu_verification_code'];  // Extrae el secreto de 2FA del resultado

		// Verifica el código 2FA ingresado contra el secreto almacenado
		if ($Tfa->verifyCode($Secret, $Code2FA))
		{
			header('Location: ../4profile/main_profile.php');  // Redirige al índice si la verificación es exitosa
			exit;  // Finaliza la ejecución después de la redirección
		}
		else
		{
			sleep(1);
			echo "Código 2FA incorrecto.";  // Informa al usuario que el código 2FA es incorrecto
		}
	}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificar 2FA</title>
    <link rel="stylesheet" type="text/css" href="estilo.css">  <!-- Enlace al archivo de estilo -->
</head>
<body>
<div class="form-box">
    <h1>Verificar 2FA</h1>
    <form method="post">
        Código 2FA: <input type="text" name="code" required><br>  <!-- Campo para ingresar el código 2FA -->
        <input type="submit" value="Verificar">  <!-- Botón para enviar el formulario y verificar el código -->
    </form>
</div>
</body>
</html>
