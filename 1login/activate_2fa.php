<?php
session_start();
require_once 'funciones.php';

// Utiliza la clase TwoFactorAuth de la biblioteca.
use RobThree\Auth\TwoFactorAuth;

// Aplica las cabeceras de seguridad predefinidas para proteger la página.
AddSecurityHeaders();

// Inicializa la variable para mensajes de feedback a usuarios.
$message = '';

// Verifica si el usuario está autenticado para acceder a esta página.
if (!isset($_SESSION['UserEmail'])) {
    echo '<div class="warning">Error 333 - No está autorizado para ver esta página. (Sal, por favor)</div>';
    exit;  // Termina la ejecución si no está autorizado.
}

// Recupera el email del usuario desde la sesión.
$Email = $_SESSION['UserEmail'];

// Procesa el formulario cuando se envía.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Crea una instancia de TwoFactorAuth.
    $Tfa = new TwoFactorAuth();

    // Verifica si el usuario tiene 2FA activado.
    if (Has2FA($Email)) {
        // Si el 2FA ya está activado, procede a desactivarlo.
        if (UpdateUser2FASecret($Email, "")) {  // Elimina el secreto de 2FA para desactivar.
            $message = "2FA desactivado con éxito.";
        } else {
            $message = "Error al desactivar 2FA.";
        }
    } else {
        // Si el 2FA no está activado, procede a activarlo.
        $Secret = $Tfa->createSecret();  // Genera un nuevo secreto para 2FA.
        $QrCodeImage = $Tfa->getQRCodeImageAsDataUri($Email, $Secret);  // Genera la imagen QR para el nuevo secreto.
        if (UpdateUser2FASecret($Email, $Secret)) {  // Actualiza el secreto en la base de datos.
            $message = '2FA activado con éxito.<br>GUÁRDALO BIEN, POR EL AMOR DE DIOS<br><br><img src="' . htmlspecialchars($QrCodeImage) . '" alt="Código QR para autenticación 2FA"><br>Escanee el código QR con su aplicación de autenticación. (Google Authenticator)';
        } else {
            $message = "Error al activar 2FA.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?php echo Has2FA($Email) ? 'Desactivar 2FA' : 'Activar 2FA'; ?></title>
    <link rel="stylesheet" type="text/css" href="estilo.css">
</head>

<body>
    <div class="form-box">
        <h1><?php echo Has2FA($Email) ? 'Desactivar 2FA' : 'Activar 2FA'; ?></h1>
        <form method="post">
            <!-- Muestra mensajes de feedback al usuario. -->
            <?php if (!empty($message)) : ?>
                <div class="info"><?php echo $message; ?></div>
            <?php endif; ?>

            <!-- Muestra el botón correspondiente según el estado del 2FA. -->
            <?php if (Has2FA($Email)) : ?>
                <input type="submit" value="Desactivar 2FA">
            <?php else : ?>
                <input type="submit" value="Activar 2FA">
            <?php endif; ?>

            <!-- Proporciona un enlace para regresar al perfil principal. -->
            <a href="../4profile/main_profile.php" class="button">Volver</a>
        </form>
    </div>
</body>

</html>