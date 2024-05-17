<?php
/**
 * Josevi
 * CETI
 * PPS - Puesta en Producción Segura
 * 
 */

header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header("Content-Security-Policy: img-src 'self' data:;");
header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
header('X-Content-Type-Options: nosniff');

define('SI_NO_EXISTE_PETA', true);

if(isset($_POST['action']) && $_POST['action'] == 'register')
{
	include('3register/register.php');
	die();
}

?>
<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" href="style.css">
		<title><?php echo _('Eshop');?></title>
	</head>
	<body>
<?php

include('3register/register.form.php');
die();

?>
	</body>
</html>
<?