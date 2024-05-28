<?php
session_start();
require_once '../autoload.php';
require_once '../Functions.php';
// Verificar si el rol del usuario está en la sesión
/*if (!isset($_SESSION['UserRol'])) {
    echo "<p class='text-danger'>Acceso denegado. No se encontró el rol de usuario en la sesión.</p>";
    exit;
}

// Verificar si el usuario es administrador
if ($_SESSION["UserRol"] !== 'A') {
    echo "<p class='text-danger'>Acceso denegado. No tienes permisos para acceder a esta página.</p>";
    exit;
}*/
functions::checkAdminAccess();

// Generar token CSRF si no está definido
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control</title>
    <!-- Añadir CSS de Bootstrap -->
    <link href="/vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include "../nav.php"; ?>
<div class="container">
    <h1 class="mt-5">Bienvenido al:</h1>
    <h2>Panel de Control del Administrador</h2>
    <div class="mt-3">
        <a href="Gestion_Users.php" class="btn btn-primary">Gestionar Usuarios</a>
        <a href="Gestion_Prod.php" class="btn btn-primary">Gestionar Productos</a>
        <a href="Report.php" class="btn btn-primary">Análisis y Reporting</a>
        <a href="/4profile/main_profile.php" class="btn btn-secondary">Volver a mi Perfil</a>
    </div>
</div>

<!-- Añadir JS de Bootstrap -->
</body>
</html>

