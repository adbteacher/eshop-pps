<?php
session_start();

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
    <div class="container">
        <h1 class="mt-5">Bienvenido al:</h1>
        <h2>Panel de Control del Administrador</h2>
        <form method="post" class="mt-3">
            <button type="submit" name="action" value="usuarios" class="btn btn-primary">Gestionar Usuarios</button>
            <button type="submit" name="action" value="productos" class="btn btn-primary">Gestionar Productos</button>
            <button type="submit" name="action" value="analisis" class="btn btn-primary">Análisis y Reporting</button>
            <button type="submit" name="action" value="perfil" class="btn btn-secondary">Volver a mi Perfil</button>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        </form>

        <?php
        // Validar token CSRF
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            echo "<p class='mt-3 text-danger'>Error en la validación CSRF.</p>";
        } else {
            // Manejo de acciones
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $action = $_POST["action"];

                // Redirecciona a la página correspondiente según la acción seleccionada
                switch ($action) {
                    case 'usuarios':
                        header("Location: Gestion_Users.php");
                        exit;
                        break;
                    case 'productos':
                        header("Location: Gestion_Prod.php");
                        exit;
                        break;
                    case 'analisis':
                        header("Location: Report.php");
                        exit;
                        break;
                    case 'perfil':
                        header("Location: /4profile/main_profile.php");
                        exit;
                        break;
                    default:
                        echo "<p class='mt-3 text-danger'>Acción no válida</p>";
                        break;
                }
            }
        }
        ?>
    </div>

    <!-- Añadir JS de Bootstrap -->
    <script src="/vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

