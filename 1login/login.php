<?php
    // Inicia una nueva sesión o continúa la existente para mantener el estado de autenticación del usuario.
    session_start();

    // Incluye el script con las funciones.
    require_once 'funciones.php';

    // Añade cabeceras de seguridad HTTP para prevenir vulnerabilidades comunes.
    AddSecurityHeaders();

    // Variable para almacenar mensajes de error durante el proceso de inicio de sesión.
    $message = '';

    // Genera y almacena un token CSRF en la sesión si aún no se ha generado.
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // Maneja el formulario cuando se envía por método POST.
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Limpia y recupera el correo electrónico y la contraseña del formulario.
        $Email = SanitizeInput($_POST['email']);
        $Password = SanitizeInput($_POST['password']);

        // Verifica si hay múltiples intentos fallidos de inicio de sesión para prevenir ataques de fuerza bruta.
        CheckLoginAttempts($Email);

        // Verifica la validez del token CSRF para prevenir ataques de falsificación de solicitud en sitios cruzados.
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $message = "Error en la validación CSRF.";
        } else {
            // Verifica las credenciales del usuario y registra el intento.
            $LoginSuccessful = VerifyUser($Email, $Password);
            LogAttempt($Email, $LoginSuccessful === "Inicio de sesión exitoso.");

            // Si las credenciales son correctas, procede según si tiene o no 2FA activado.
            if ($LoginSuccessful === "Inicio de sesión exitoso.") {
                // Almacena el email en la sesión para identificar al usuario en futuras solicitudes.
                $_SESSION['email'] = $Email;

                // Redirige al usuario a la página de verificación de 2FA o al perfil principal.
                $redirectUrl = Has2FA($Email) ? 'verify_2fa.php' : '../4profile/main_profile.php';
                header('Location: ' . $redirectUrl);
                exit;
            } else {
                // Introduce un retraso para mitigar ataques de fuerza bruta y muestra el mensaje de error.
				sleep(1);
                $message = $LoginSuccessful;
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <link rel="stylesheet" type="text/css" href="estilo.css">
        <meta charset="UTF-8">
        <title>Login</title>
    </head>
    <body>
        <div class="version">Versión 1.4</div>
        <div class="form-box">
            <h1>Iniciar Sesión</h1>
            <form method="post">
                <!-- Campo oculto para manejar el token CSRF. -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <!-- Muestra el mensaje de error si hay alguno. -->
                <?php if (!empty($message)): ?>
                    <div class="error"><?php echo $message; ?></div>
                <?php endif; ?>
                <!-- Campos para ingresar el correo electrónico y la contraseña. -->
                <label for="email">Correo electrónico:</label>
                <input type="email" name="email" id="email" required>
                <label for="password">Contraseña:</label>
                <input type="password" name="password" id="password" required>
                <input type="submit" value="Iniciar Sesión">
            </form>
        </div>
    </body>
</html>
