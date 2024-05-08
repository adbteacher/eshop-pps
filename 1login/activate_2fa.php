<?php
	session_start();
	require_once 'funciones.php';

	use RobThree\Auth\TwoFactorAuth;

	AddSecurityHeaders();

	if (!isset($_SESSION['username']))
	{
		echo '<div class="warning">No está autorizado para ver esta página.</div>';
		exit;
	}

	$Username = $_SESSION['username'];

	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		$Tfa         = new TwoFactorAuth();
		$Secret      = $Tfa->createSecret();
		$QrCodeImage = $Tfa->getQRCodeImageAsDataUri($Username, $Secret);

		if (UpdateUser2FASecret($Username, $Secret))
		{
			echo '<div class="info">2FA activado con éxito.<br><img src="' . htmlspecialchars($QrCodeImage) . '" alt="Código QR para autenticación 2FA"><br>Escanee el código QR con su aplicación de autenticación.</div>';
			echo '<img src="' . htmlspecialchars($QrCodeImage) . '" alt="Código QR para autenticación 2FA"><br>';
			echo "Escanee el código QR con su aplicación de autenticación.";
		}
		else
		{
			echo "Error al activar 2FA.";
		}
	}

	function UpdateUser2FASecret($Username, $Secret): bool
	{
		$Connection = database::LoadDatabase();
		$Query      = $Connection->prepare("UPDATE pps_users SET usu_verification_code = ? WHERE usu_name = ?");
		$Query->bindParam(1, $Secret);
		$Query->bindParam(2, $Username);
		try
		{
			$Query->execute();
			return $Query->rowCount() > 0;
		}
		catch (PDOException $e)
		{
			error_log("Error al actualizar el código secreto de 2FA: " . $e->getMessage());
			return false;
		}
	}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Activar 2FA</title>
    <link rel="stylesheet" type="text/css" href="estilo.css">
</head>
<body>
<div class="form-box">
    <h1>Activar 2FA</h1>
    <form method="post">
        <input type="submit" value="Generar Código 2FA">
    </form>
</div>
</body>
</html>
