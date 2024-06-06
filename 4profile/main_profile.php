<?php

	require_once '../autoload.php';

    session_start();

	// Verificar si el usuario está autenticado
	functions::ActiveSession();

	//Comprobar permisos al programa
	functions::HasPermissions(basename(__FILE__));

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil Personal</title>
    <!-- CSS / Hoja de estilos Bootstrap -->
    <link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../vendor/fortawesome/font-awesome/css/all.min.css" rel="stylesheet">

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="/0images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/0images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/0images/favicon-16x16.png">
    <link rel="manifest" href="/0images/site.webmanifest">
    <style>
        .list-group-item {
            border: none;
        }

        .list-group-item a {
            color: #007bff;
            text-decoration: none;
        }

        .list-group-item a:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        .section {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin-bottom: 20px;
            padding: 20px;
        }

        .section-title {
            color: #007bff;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <?php include "../nav.php"; ?>

    <div class="container mt-5 mb-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="section">
                    <h1 class="text-center mb-4"><i class="fas fa-user-circle"></i> Bienvenido al Perfil Personal</h1>
                    <ul class="list-group">
                        <li class="list-group-item"><i class="fas fa-user"></i> <a href="usu_info.php">Gestión de Información del Usuario</a></li>
                        <li class="list-group-item"><i class="fas fa-map-marker-alt"></i> <a href="usu_address.php">Gestión de Direcciones</a></li>
                        <li class="list-group-item"><i class="fas fa-lock"></i> <a href="usu_sec.php">Gestión de Seguridad</a></li>
                        <li class="list-group-item"><i class="fas fa-credit-card"></i> <a href="payment_methods.php">Gestión de métodos de Pago</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <?php include "../footer.php"; ?>
</body>

</html>