<meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">

<?php
/**
 * Josevi
 * CETI
 * PPS - Puesta en Producción Segura
 *
 */

if(session_status() != PHP_SESSION_ACTIVE) session_start();

require("functions.php");
include "../nav.php";

$Conn = database::LoadDatabase();

// Control de problemas
if (!isset($UserID) OR !is_int($UserID) OR !isset($_SESSION['UserStatus']))
{
	header('Location: ../1login/login.php');
	exit();
}

// Usuario validado
if ($_SESSION['UserStatus'] == 'A' OR $_SESSION['UserStatus'] == 'B')
{
	echo "Tu cuenta ya está verificada.";
	exit();
}

$Fields = array(
	'VerifyCode' => 'Código inválido.',
);

$Errors = array();

// Submit button
if (isset($_POST['VerifyCode']))
{
	// Guardamos la respuesta para verificarla
	$VerificationCodeRequested = htmlspecialchars($_POST['VerificationCode']); // functions.php

	if (!VerificationCodeValidation($VerificationCodeRequested))
	{
		// Guardar los errores
		$Errors[] = 'VerifyCode';

		// Redirigir a la página de registro
		header('Location: verifycode.php');
		exit;
	}

	// Query para guardar el código que el usuario debe verificar
	$Query = ("SELECT usu_verification_code FROM pps_users WHERE usu_id = $UserID;");

	try
	{
		// Query a la base de datos
		$Stmt = $Conn->prepare($Query);
		if ($Stmt->execute())
		{
			// Código de verificación
			$VerificationCode = reset($Stmt->fetch(PDO::FETCH_ASSOC));

			if ($VerificationCode == $VerificationCodeRequested)
			{
				// Actualizar el estado del usuario
			    $Query = ("UPDATE pps_users SET usu_status = 'A' WHERE usu_id = $UserID;");
				$Stmt = $Conn->prepare($Query);

				if ($Stmt->execute())
				{
					$_SESSION['UserStatus'] = 'A';
					echo "Verificación exitosa. Tu cuenta ha sido activada.";
					header('Refresh: 1; Location: ../1login/login.php');
					exit();
				}
				else
				{
					// Si no hay filas afectadas, asumimos un error
					throw new Exception("Error.");
				}
			}
			else
			{
				$Errors[] = 'VerifyCode';
			}
		}
		else
		{
			// Si no hay filas afectadas, asumimos un error
			throw new Exception("Error.");
		}
	}
	finally
	{
		// Cierra la conexión
		$Conn = null;
	}
}

?>

<br>
<h2 class="mb3">Verificación de Cuenta</h2>
<form method="post">
	<label class="mb3" for="VerificationCode">Código de Verificación:</label>
	<input type="text" id="VerificationCode" name="VerificationCode" required>
	<div class="dataError <?php echo in_array('VerifyCode', $Errors) ? '' : ' hidden';?>"><?php echo $Fields['VerifyCode'];?></div>
	<br>
	<button class="btn btn-primary" name="VerifyCode" type="submit">Verificar</button>
</form>

<style type="text/css">
.hidden
{
	display: none;
}
.dataError
{
	color: #c00;
	font-size: 0.8em
}
</style>