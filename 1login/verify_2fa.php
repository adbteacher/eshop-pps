<?php
	session_start();

	require_once("../vendor/autoload.php");
	require_once("../autoload.php");

	require_once 'funciones.php';

	use RobThree\Auth\TwoFactorAuth;

	AddSecurityHeaders();

    //TODO AQUI NO LLEGA LA SESION CARGADA
	if (!isset($_SESSION['UserID']))
	{
		echo '<div class="warning">No está autorizado para ver esta página.</div>';
		exit;
	}

	$UserID = $_SESSION['UserID'];

	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		$Code2FA    = SanitizeInput($_POST['code']);
		$Tfa        = new TwoFactorAuth();
		$Connection = database::LoadDatabase();
		$Query      = $Connection->prepare("SELECT usu_verification_code FROM pps_users WHERE usu_id = ?");
		$Query->bindParam(1, $UserID);
		$Query->execute();
		$Result = $Query->fetch(PDO::FETCH_ASSOC);
		$Secret = $Result['usu_verification_code'];

        //TODO ESTO NO DEBE DE IR ASÍ, ESTO NO TENGO NI IDEA DE COMO VA,
        // SE SUPONE QUE ESTOS CODIGOS SALEN POR LA APP DEL MOVIL, NO? - IVÁN
        //TODO AUN ASÍ, HAY UNA EXCEPTION SIN PILLAR QUE HACE QUE REVIENTE EL PROGRAMA
		//if ($Tfa->verifyCode($Secret, $Code2FA))
		if ($Secret == $Code2FA)
		{
            //TODO FALTA QUE SI ES CORRECTO BORRAR EL USU_VERIFICATION_CODE DEL USUARIO EN LA BD
			header('Location: ../index.php');
			exit;
		}
		else
		{
			echo "Código 2FA incorrecto.";
		}
	}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificar 2FA</title>
    <link rel="stylesheet" type="text/css" href="estilo.css">
</head>
<body>
<div class="form-box">
    <h1>Verificar 2FA</h1>
    <form method="post">
        Código 2FA: <input type="text" name="code" required><br>
        <input type="submit" value="Verificar">
    </form>
</div>
</body>
</html>
