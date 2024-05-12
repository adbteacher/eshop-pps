<?php
    // Inicia una nueva sesión o continúa la existente para mantener el estado del usuario.
    session_start();

    // Incluye las funciones necesarias y la biblioteca de autenticación de dos factores.
    require_once 'funciones.php';
    require_once '../vendor/autoload.php';

    // Usa la clase TwoFactorAuth para la gestión de la autenticación de dos factores.
    use RobThree\Auth\TwoFactorAuth;

    // Establece cabeceras de seguridad HTTP para proteger la página.
    AddSecurityHeaders();

    // Variable para manejar mensajes de error relacionados con la verificación de 2FA.
    $error = '';

    // Comprueba si el usuario está autenticado con una sesión válida.
    if (!isset($_SESSION['email'])) {
        echo '<div class="warning">Error 333 - No está autorizado para ver esta página. (Sal, por favor)</div>';
        exit; // Finaliza la ejecución si el usuario no está autorizado.
    }

    // Recupera el email del usuario desde la sesión para identificarlo en el proceso de 2FA.
    $Email = $_SESSION['email'];

    // Procesa el formulario cuando se envía mediante POST.
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Limpia el código de 2FA ingresado para evitar inyecciones SQL o XSS.
        $Code2FA = SanitizeInput($_POST['code']);

        // Crea una instancia de la clase TwoFactorAuth.
        $Tfa = new TwoFactorAuth();

        // Obtiene una conexión a la base de datos para realizar consultas.
        $Connection = GetDatabaseConnection();

        // Prepara y ejecuta una consulta SQL para obtener el secreto de 2FA del usuario.
        $Query = $Connection->prepare("SELECT usu_2fa FROM pps_users WHERE usu_email = ?");
        $Query->bindParam(1, $Email);
        $Query->execute();
        $Result = $Query->fetch(PDO::FETCH_ASSOC);

        // Extrae el secreto de 2FA almacenado en la base de datos.
        $Secret = $Result['usu_2fa'];

        // Verifica si el código de 2FA ingresado coincide con el secreto guardado.
        if ($Tfa->verifyCode($Secret, $Code2FA)) {
            // Si el código es correcto, redirige al perfil principal del usuario.
            header('Location: ../4profile/main_profile.php');
            exit;
        } else {
            // Introduce un retraso para mitigar ataques de fuerza bruta.
            sleep(1);
            // Establece un mensaje de error si el código de 2FA es incorrecto.
            $error = "Código 2FA incorrecto.";
        }
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificar 2FA</title>
    <link rel="stylesheet" type="text/css" href="estilo.css">  <!-- Enlace al archivo de estilo CSS para el diseño de la página. -->
</head>
<body>
<div class="form-box">
    <h1>Verificar 2FA</h1>
    <form method="post">
        <!-- Campo de entrada para el código de 2FA que el usuario debe proporcionar. -->
        Código 2FA: <input type="text" name="code" required><br>
        <!-- Botón para enviar el formulario y verificar el código 2FA ingresado. -->
        <input type="submit" value="Verificar">
    </form>
</div>
</body>
</html>
