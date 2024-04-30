<?php
session_start();
require_once 'funciones.php';
require_once 'db.php';

AddSecurityHeaders();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $Email = SanitizeInput($_POST['email']);
    $Password = SanitizeInput($_POST['password']);

    CheckLoginAttempts($Email);  // Asegúrate de pasar el correo electrónico como argumento.
    
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo "Error en la validación CSRF.";
        exit;
    }

    $LoginSuccessful = VerifyUser($Email, $Password);
    LogAttempt($Email, $LoginSuccessful === "Inicio de sesión exitoso.");
    
    if ($LoginSuccessful === "Inicio de sesión exitoso.") {
        $_SESSION['email'] = $Email;  // Usa el email para identificar al usuario en la sesión.
        if (Has2FA($Email)) {  // Asegúrate de que Has2FA pueda manejar la búsqueda por correo, no por username.
            header('Location: verify_2fa.php');
        } else {
            header('Location: ../index.php');
        }
        exit;
    } else {
        echo $LoginSuccessful;
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
    <div class="form-box">
        <h1>Iniciar Sesión</h1>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <?php if (!empty($LoginSuccessful) && $LoginSuccessful !== "Inicio de sesión exitoso."): ?>
            <div class="error"><?php echo $LoginSuccessful; ?></div>
            <?php endif; ?>
            <label for="email">Correo electrónico:</label>
            <input type="email" name="email" id="email" required>
            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password" required>
            <input type="submit" value="Iniciar Sesión">
        </form>
    </div>
</body>
</html>
