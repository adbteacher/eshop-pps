<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes</title>
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

    $message = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validar el token CSRF
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $message = "Error, vuelva a intentarlo más tarde.";
            error_log("Error en la validación CSRF.");
        } else {
            // Lógica adicional del formulario
        }
    }

    // Consulta para obtener la lista de todos los clientes con su dirección
    $sql = "
    SELECT u.usu_name, u.usu_email, a.adr_line1, a.adr_line2, a.adr_city, a.adr_state, a.adr_postal_code, a.adr_country
    FROM pps_users u
    LEFT JOIN pps_addresses_per_user a ON u.usu_id = a.adr_user
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="contenido" class="container mt-4">
    <h1>Lista de Clientes</h1>
    <?php if (!empty($message)): ?>
        <div class="error alert alert-danger"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if (!empty($clientes)): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Dirección</th>
                    <th>Ciudad</th>
                    <th>Estado</th>
                    <th>Código Postal</th>
                    <th>País</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cliente['usu_name'] ?: ""); ?></td>
                        <td><?php echo htmlspecialchars($cliente['usu_email'] ?: ""); ?></td>
                        <td><?php echo htmlspecialchars($cliente['adr_line1'] ?: ""); ?><?php echo ($cliente['adr_line2'] != null) ? ', ' . htmlspecialchars($cliente['adr_line2'] ?: "") : ''; ?></td>
                        <td><?php echo htmlspecialchars($cliente['adr_city'] ?: ""); ?></td>
                        <td><?php echo htmlspecialchars($cliente['adr_state'] ?: ""); ?></td>
                        <td><?php echo htmlspecialchars($cliente['adr_postal_code'] ?: ""); ?></td>
                        <td><?php echo htmlspecialchars($cliente['adr_country'] ?: ""); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No hay clientes registrados.</p>
    <?php endif; ?>
    <form method="post" action="mainpage.php" class="mb-3">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <button type="submit" class="btn btn-primary btn-separado">Volver</button>
    </form>
</div>

<?php include "../footer.php"; // Incluye el footer ?>

<script src="/vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>