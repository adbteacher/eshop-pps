<?php
// Include necessary files for additional functions and autoload
require_once 'funciones.php';
require_once '../autoload.php';
// Use the TwoFactorAuth library from RobThree
use RobThree\Auth\TwoFactorAuth;

// Start the session
session_start();
// Add security headers to the HTTP response
AddSecurityHeaders();

$message = '';
// If the user's email is not set in the session, redirect them to the login page
if (!isset($_SESSION['UserEmail'])) {
    RedirectUnauthorizedUser();
}

// Get the user's email from the session
$Email = $_SESSION['UserEmail'];
// Check if the user has 2FA enabled
$has2FA = Has2FA($Email);

// Check if the request method is POST (form submission)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    usleep(500000); // "Está pensando"
    // Validate the CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message = "Error, vuelva a intentarlo más tarde.";
        error_log("Error en la validación CSRF para el usuario con email: $Email");
        // Regenerate the CSRF token after a failed attempt
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } else {
        // Initialize the TwoFactorAuth object
        $Tfa = new TwoFactorAuth();
        // If 2FA is enabled, attempt to disable it
        if ($has2FA) {
            if (UpdateUser2FASecret($Email, "")) {
                $message = "2FA desactivado con éxito.";
                $has2FA = false;
                // Regenerate the CSRF token
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            } else {
                $message = "Error al desactivar 2FA.";
                error_log("Error al desactivar 2FA para el usuario con email: $Email");
            }
        } else {
            // If 2FA is not enabled, attempt to enable it
            $Secret = $Tfa->createSecret();
            $QrCodeImage = $Tfa->getQRCodeImageAsDataUri($Email, $Secret);
            if (UpdateUser2FASecret($Email, $Secret)) {
                $message = '2FA activado con éxito.<br>GUÁRDALO BIEN, POR EL AMOR DE DIOS<br><br><img src="' . htmlspecialchars($QrCodeImage) . '" alt="Código QR para autenticación 2FA"><br><br>Escanee el código QR con su aplicación de autenticación. (Google Authenticator)';
                $has2FA = true;
                // Regenerate the CSRF token
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            } else {
                $message = "Error al activar 2FA.";
                error_log("Error al activar 2FA para el usuario con email: $Email");
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title><?php echo $has2FA ? 'Desactivar 2FA' : 'Activar 2FA'; ?></title>
    <link rel="stylesheet" type="text/css" href="estilo.css">
    <link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <style>
        .spinner-border {
            display: none;
            width: 1rem;
            height: 1rem;
            border-width: 0.2em;
            vertical-align: middle;
            margin-left: 10px;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Show spinner and disable the 2FA button on form submit
            document.querySelector("form").addEventListener("submit", function() {
                var activateButton = document.querySelector(".btn-2fa");
                var spinner = document.querySelector(".spinner-border");
                activateButton.disabled = true;
                spinner.style.display = "inline-block";
            });
        });
    </script>
</head>
<body>
<div class="form-box">
    <h1><?php echo $has2FA ? 'Desactivar 2FA' : 'Activar 2FA'; ?></h1>
    <form method="post">
        <!-- Include the CSRF token as a hidden input -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <!-- Display the message if it's not empty -->
        <?php if (!empty($message)): ?>
            <div class="info"><?php echo $message; ?></div>
        <?php endif; ?>
        <!-- 2FA activation/deactivation button with a spinner -->
        <button type="submit" class="btn-2fa">
            <?php echo $has2FA ? 'Desactivar 2FA' : 'Activar 2FA'; ?>
            <div class="spinner-border text-light" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </button>
        <!-- Link to return to the profile security page -->
        <a href="../4profile/usu_sec.php" class="button">Volver</a>
    </form>
</div>
</body>
</html>
