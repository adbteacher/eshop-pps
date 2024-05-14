<?php
session_start(); // Iniciar la sesión si aún no se ha iniciado

// Verificar si el usuario está autenticado
if (!isset($_SESSION['email'])) {
    header("Location: ../1login/login.php"); // Redirigir a la página de inicio de sesión si el usuario no está autenticado
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil Personal</title>
    <link rel="stylesheet" href="main_styles.css"> <!-- CSS STYLE -->
</head>

<body>
    <div class="container">
        <h1>Bienvenido al Perfil Personal</h1>

        <!-- Enlaces a las diferentes secciones -->
        <ul>
            <li><a href="usu_info.php">Gestión de Información del Usuario</a></li>
            <li><a href="usu_addres.php">Gestión de Direcciones</a></li>
            <li><a href="usu_sec.php">Gestión de Seguridad</a></li>
        </ul>
    </div>
</body>

</html>