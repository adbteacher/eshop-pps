<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Principal</title>
    <link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-image {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 5px;
        }

        .btn-separado {
            margin-right: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #ddd;
            column-span: 10;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: center; /* Centrar texto en las celdas */
        }

        th {
            background-color: #f2f2f2;
            border: 1px solid #ddd;
        }
    </style>
    <!-- Iconos de Bootstrap para el carrito -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

<?php 
    // Start output buffering
    ob_start();
    
    include "../nav.php"; // Incluye el Navbar

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    require_once '../autoload.php';
    require_once 'biblioteca.php';

    functions::checkVendorAccess(); // Aseguramos el acceso del vendedor

    // Generar y almacenar el token CSRF si no existe
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $conn = GetDatabaseConnection();
    if (!$conn) {
        ob_end_clean(); // Clean the output buffer
        echo "<p>No se pudo conectar a la base de datos.</p>";
        exit;
    }

    //AddSecurityHeaders();

    $message = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validar el token CSRF
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $message = "Error, vuelva a intentarlo más tarde.";
            error_log("Error en la validación CSRF.");
        } else {
            if (isset($_POST['action'])) {
                $action = $_POST['action'];
                if ($action === 'Nuevo producto') {
                    if (!headers_sent()) {
                        header('Location: nuevo_producto.php');
                        exit;
                    }
                } elseif ($action === 'Stats de ventas') {
                    if (!headers_sent()) {
                        header('Location: stats.php');
                        exit;
                    }
                } elseif ($action === 'Gestion clientes') {
                    if (!headers_sent()) {
                        header('Location: gestion_clientes.php');
                        exit;
                    }
                }
            }
        }
    }

    if (isset($_GET['eliminar_id'])) {
        $id = filter_var($_GET['eliminar_id'], FILTER_SANITIZE_NUMBER_INT);
        if (eliminar_fila($id)) {
            echo "<p>Producto eliminado correctamente</p>";
        } else {
            echo "<p>Error al eliminar el producto</p>";
        }
    }
?>

<div id="contenido" class="container mt-4">
    <?php if (!empty($message)): ?>
        <div class="error alert alert-danger"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="post" action="" id="mainform" class="mb-3">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <button type="submit" name="action" value="Nuevo producto" class="btn btn-primary btn-separado">Nuevo producto</button>
        <button type="submit" name="action" value="Stats de ventas" class="btn btn-secondary btn-separado">Stats de ventas</button>
        <button type="submit" name="action" value="Gestion clientes" class="btn btn-info btn-separado">Gestión de Clientes</button>
    </form>

    <?php
        if ($conn) {
            $result = consulta();
            if ($result) {
                echo '<table class="table table-bordered">';
                mostrar_tabla($result);
                echo '</table>';
            } else {
                echo "<p>Error al obtener los datos.</p>";
            }
        }
    ?>
</div>

<?php include "../footer.php"; // Incluye el footer ?>

<script src="/vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

<?php
// End output buffering and flush the output
ob_end_flush();
?>
</body>
</html>
