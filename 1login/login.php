<?php
// Include external files for additional functions and autoload
require_once 'funciones.php';
require_once '../autoload.php';

// Start the session
session_start();
// Add security headers to the HTTP response
AddSecurityHeaders();

// Generate and store a CSRF token if it doesn't exist in the session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$Email = '';

// Check if the request method is POST (form submission)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    usleep(500000); // "Está pensando"
    $Email = SanitizeInput($_POST['email']);
    $Password = SanitizeInput($_POST['password']);
    
    // Validate the email format
    if (!filter_var($Email, FILTER_VALIDATE_EMAIL)) {
        $message = "Formato de correo electrónico inválido.";
    } 
    // Check if there have been too many login attempts
    else if (CheckLoginAttempts($Email)) {
        $message = "Demasiados intentos de inicio de sesión fallidos. Inténtelo más tarde.";
    } 
    else {
        // Validate the CSRF token
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $message = "Error, vuelva a intentarlo más tarde.";
            error_log("Error en la validación CSRF para el usuario con email: $Email");
        } 
        else {
            // Verify the user's credentials
            $LoginSuccessful = VerifyUser($Email, $Password, $message);
            $UserId = GetUserIdByEmail($Email);

            // If login is successful, set session variables and redirect
            if ($LoginSuccessful) {
                $User = GetUserByEmail($Email);
                $_SESSION['UserID'] = $User['usu_id'];
                $_SESSION['UserName'] = $User['usu_name'];
                $_SESSION['UserEmail'] = $User['usu_email'];
                $_SESSION['UserRol'] = $User['usu_rol'];

                // Check if 2FA is enabled and redirect accordingly
                $redirectUrl = Has2FA($Email) ? 'verify_2fa.php' : '../10products/products.php';
                header('Location: ' . $redirectUrl);
                exit;
            } 
            else {
                // Log the failed login attempt and set the error message
                LogLoginAttempt($UserId, $_SERVER['REMOTE_ADDR'], false);
                sleep(1); // Delay to mitigate brute force attacks
                $message = "Credenciales incorrectas.";
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
    <title>Login</title>
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
            // Set the focus to the appropriate input field based on the error message
            <?php if (!empty($message) && $message === "Credenciales incorrectas."): ?>
                document.getElementById('password').focus();
            <?php else: ?>
                document.getElementById('email').focus();
            <?php endif; ?>

            // Show spinner and disable login button on form submit
            document.querySelector("form").addEventListener("submit", function() {
                var loginButton = document.querySelector(".login-button");
                var spinner = document.querySelector(".spinner-border");
                loginButton.disabled = true;
                spinner.style.display = "inline-block";
            });
        });
    </script>
</head>
<body>
    <div class="version">Versión 1.5</div>
    <div class="form-box">
        <h1>Iniciar Sesión</h1>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <?php if (!empty($message)): ?>
                <div class="error"><?php echo $message; ?></div>
            <?php endif; ?>
            <input type="email" name="email" id="email" placeholder="Correo electrónico" required maxlength="255" value="<?php echo htmlspecialchars($Email); ?>">
            <input type="password" name="password" id="password" placeholder="Contraseña" required maxlength="255">
            <button type="submit" class="login-button">
                Iniciar Sesión
                <div class="spinner-border text-light" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </button>
        </form>
        <a href="/3register/register.form.php" class="button">¿No tienes cuenta? ¡Regístrate!</a>
        <br>
        <a href="../index.php" class="button">Volver</a>
    </div>
</body>
</html>
