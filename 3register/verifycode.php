<?php
if (session_status() != PHP_SESSION_ACTIVE) session_start();

require("functions.php");
include "../nav.php";
require_once '../autoload.php';

$Conn = Database::LoadDatabase();

// Control de problemas
if (!isset($_SESSION['VerificationPending'])) {
    header('Location: ../1login/login.php');
    exit();
}

$Email = $_SESSION['VerificationPending'];
$Fields = array(
    'VerifyCode' => 'Código inválido.',
);
$Errors = array();

// Añadir un token CSRF para prevenir ataques Cross-Site Request Forgery
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Submit button
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar el token CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Token CSRF inválido.');
    }

    // Añadir un pequeño retraso anti-fuerza bruta
    usleep(500000); // 500,000 microsegundos = 0.5 segundos

    // Guardamos la respuesta para verificarla
    $VerificationCodeRequested = htmlspecialchars($_POST['VerificationCode'], ENT_QUOTES, 'UTF-8');

	if (!VerificationCodeValidation($VerificationCodeRequested)) {
		$Errors[] = 'VerifyCode';
	
		if (!isset($_SESSION['verification_attempts'])) {
			$_SESSION['verification_attempts'] = 0;
		}
	
		$_SESSION['verification_attempts']++;
	
		// Si el usuario ha superado los 5 intentos fallidos
		if ($_SESSION['verification_attempts'] > 5) {
			$message = "Demasiados intentos fallidos. Inténtelo más tarde.";
			// Añadir un tiempo de espera
			sleep(5);  // Espera de 5 segundos
			header('Location: login.php');
			exit();
		}
	}	
	

    // Query para obtener el código de verificación del usuario
    $Query = ("SELECT usu_verification_code, usu_id, usu_name, usu_email, usu_rol FROM pps_users WHERE usu_email = :email LIMIT 1;");
    $Stmt = $Conn->prepare($Query);
    $Stmt->bindParam(':email', $Email, PDO::PARAM_STR);
    $Stmt->execute();

    // Código de verificación
    $User = $Stmt->fetch(PDO::FETCH_ASSOC);

    if ($User && hash_equals($User['usu_verification_code'], $VerificationCodeRequested)) {
        // Actualizar el estado del usuario
        $Query = ("UPDATE pps_users SET usu_status = 'A' WHERE usu_email = :email;");
        $Stmt = $Conn->prepare($Query);
        $Stmt->bindParam(':email', $Email, PDO::PARAM_STR);
        $Stmt->execute();

        if ($Stmt->rowCount() > 0) {
            // Establecer las variables de sesión completas
            $_SESSION['UserID'] = $User['usu_id'];
            $_SESSION['UserName'] = $User['usu_name'];
            $_SESSION['UserEmail'] = $User['usu_email'];
            $_SESSION['UserRol'] = $User['usu_rol'];
            unset($_SESSION['VerificationPending']);
            unset($_SESSION['csrf_token']);

            // Redirigir al usuario a la página de productos
            header('Location: ../10products/products.php');
            exit();
        } else {
            throw new Exception("Error.");
        }
    } else {
        $Errors[] = 'VerifyCode';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <style type="text/css">
        .hidden { display: none; }
        .dataError { color: #c00; font-size: 0.8em; }
    </style>
</head>
<body>
    <br>
    <h2 class="mb3">Verificación de Cuenta</h2>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        <label class="mb3" for="VerificationCode">Código de Verificación:</label>
        <input type="text" id="VerificationCode" name="VerificationCode" required>
        <div class="dataError <?php echo in_array('VerifyCode', $Errors) ? '' : 'hidden';?>"><?php echo htmlspecialchars($Fields['VerifyCode'], ENT_QUOTES, 'UTF-8');?></div>
        <br>
        <button class="btn btn-primary" name="VerifyCode" type="submit">Verificar</button>
    </form>
</body>
</html>
