<?php
session_start();
require_once 'funciones.php';
require_once 'db.php';

AddSecurityHeaders();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $Username = SanitizeInput($_POST['username']);
    $Password = SanitizeInput($_POST['password']);

    CheckLoginAttempts();
    
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo "Error en la validación CSRF.";
        exit;
    }

    $LoginSuccessful = VerifyUser($Username, $Password);
    LogAttempt($Username, $LoginSuccessful === "Inicio de sesión exitoso.");
    
    if ($LoginSuccessful === "Inicio de sesión exitoso.") {
        $_SESSION['username'] = $Username;
        if (Has2FA($Username)) {
            header('Location: verify_2fa.php');
        } else {
            header('Location: activate_2fa.php');
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
    <div class="version">Versión 0.7</div>
    <div class="form-box">
        <h1>Login</h1>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            Nombre de usuario: <input type="text" name="username" required><br>
            Contraseña: <input type="password" name="password" required><br>
            <input type="submit" value="Iniciar Sesión">
        </form>
    </div>
</body>
</html>
