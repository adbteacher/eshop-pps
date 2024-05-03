<?php
	session_start();  // Inicia o continúa una sesión existente
	require_once 'funciones.php';  // Incluye funciones auxiliares y de seguridad
	require_once 'vendor/autoload.php';  // Incluye el autoload de Composer para dependencias externas

	use RobThree\Auth\TwoFactorAuth;  // Usa la biblioteca para manejo de autenticación de dos factores

	AddSecurityHeaders();  // Añade cabeceras de seguridad HTTP

	// Verifica si el usuario está autenticado y autorizado para ver la página
	if (!isset($_SESSION['username']))
	{
		echo '<div class="warning">No está autorizado para ver esta página.</div>';
		exit;  // Termina la ejecución si el usuario no está autenticado
	}

	$Username = $_SESSION['username'];  // Almacena el nombre de usuario de la sesión

	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		$Tfa         = new TwoFactorAuth();  // Crea una instancia de TwoFactorAuth
		$Secret      = $Tfa->createSecret();  // Genera un secreto para la configuración de 2FA
		$QrCodeImage = $Tfa->getQRCodeImageAsDataUri($Username, $Secret);  // Genera un código QR como URI de datos

		// Intenta actualizar el secreto de 2FA en la base de datos y muestra el resultado
		if (UpdateUser2FASecret($Username, $Secret))
		{
			echo '<div class="info">2FA activado con éxito.<br><img src="' . htmlspecialchars($QrCodeImage) . '" alt="Código QR para autenticación 2FA"><br>Escanee el código QR con su aplicación de autenticación.</div>';
		}
		else
		{
			echo "Error al activar 2FA.";  // Informa al usuario sobre un fallo al activar 2FA
		}
	}

	/**
	 * Actualiza el secreto de 2FA en la base de datos para un usuario específico.
	 *
	 * @param string $Username Nombre de usuario.
	 * @param string $Secret Secreto de 2FA generado.
	 * @return bool Retorna verdadero si la actualización fue exitosa, falso si hubo errores.
	 */
	function UpdateUser2FASecret($Username, $Secret): bool
	{
		$Connection = GetDatabaseConnection();  // Obtiene la conexión a la base de datos
		$Query      = $Connection->prepare("UPDATE pps_users SET usu_verification_code = ? WHERE usu_name = ?");  // Prepara la consulta SQL
		$Query->bindParam(1, $Secret);
		$Query->bindParam(2, $Username);
		try
		{
			$Query->execute();
			return $Query->rowCount() > 0;  // Retorna verdadero si se actualizó al menos un registro
		}
		catch (PDOException $e)
		{
			error_log("Error al actualizar el código secreto de 2FA: " . $e->getMessage());
			return false;  // Retorna falso si hay un error en la consulta
		}
	}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Activar 2FA</title>
    <link rel="stylesheet" type="text/css" href="estilo.css">  // Enlace al archivo de estilo
</head>
<body>
<div class="form-box">
    <h1>Activar 2FA</h1>
    <form method="post">
        <input type="submit" value="Generar Código 2FA">  // Botón para iniciar el proceso de activación de 2FA
    </form>
</div>
</body>
</html>
