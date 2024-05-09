<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control</title>
</head>
<body>
    <h1>Bienvenido al: </h1>
    <h2>Panel de Control del Aministrador</h2>
    <form method="post">
        <button type="submit" name="action" value="usuarios">Gestionar Usuarios</button>
        <button type="submit" name="action" value="productos">Gestionar Productos</button>
        <button type="submit" name="action" value="analisis">Análisis y Reporting</button>
    </form>

    <?php
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
            default:
                echo "Acción no válida";
                break;
        }
    }
    ?>

</body>
</html>