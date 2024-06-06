<?php
	// Verificar si el usuario está autenticado
	functions::ActiveSession();

	//Comprobar permisos al programa
	functions::HasPermissions(basename(__FILE__));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analítica de Tienda</title>
    <link href="/vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include "../nav.php"; ?>

<div class="container mt-5 mb-5">
    <header class="text-center mb-4">
        <h1>Analítica de Tienda</h1>
    </header>

    <div class="row text-center">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="card-title">Ventas</h2>
                    <p class="card-text">Analiza tus ventas para identificar patrones y tendencias.</p>
                    <a href="Datos_Ventas.php" class="btn btn-primary">Ir a análisis de ventas</a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="card-title">Inventario</h2>
                    <p class="card-text">Controla tu inventario para asegurarte de mantener stock suficiente.</p>
                    <a href="Inventario.php" class="btn btn-primary">Ir a análisis de inventario</a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="card-title">Tráfico Web</h2>
                    <p class="card-text">Monitoriza incicios de sesión de los usuarios de la web.</p>
                    <a href="Trafico_Web.php" class="btn btn-primary">Ir a análisis de tráfico web</a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="card-title">Comportamiento del Usuario</h2>
                    <p class="card-text">Entiende el comportamiento de los usuarios .</p>
                    <a href="Comp_User.php" class="btn btn-primary">Analisis del comp del usuario</a>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="Rol_Admin.php" class="btn btn-secondary">Volver a Rol Administrador</a>
    </div>
</div>

<?php include "../footer.php"; ?>
</body>
</html>