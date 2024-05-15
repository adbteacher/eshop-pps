<?php
	session_start();

	require_once("../autoload.php");

	$_SESSION["test"] = "test";

	$Error = "<h1>Permission denied</h1>";

	$Allowed = functions::HasPermissions("A", "products.php");

	if (!$Allowed)
	{
		echo $Error;
        die;
	}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frutería del Barrio</title>
    <link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
</head>

<body>

<?php
	include "../nav.php";
?>
<script> console.log('<?php echo($_SESSION["test"])?>')</script>

<div class="container mt-4">
    <div class="jumbotron">
        <h1 class="display-4">¡Bienvenidos a la Frutería del CETI!</h1>
        <p class="lead">Los mejores productos, del campo a tu mesa, con arenilla y todo.</p>
        <hr class="my-4">
        <p>Visita nuestra sección de ofertas especiales.</p>
        <a class="btn btn-primary btn-lg" href="#" role="button">Ver Ofertas</a>
    </div>

    <div class="row row-cols-1 row-cols-md-3 g-4">
        <div class="col">
            <div class="card">
                <img src="https://via.placeholder.com/150" class="card-img-top" alt="Manzanas">
                <div class="card-body">
                    <h5 class="card-title">Manzanas</h5>
                    <p class="card-text">Frescas y jugosas manzanas rojas listas para tu consumo.</p>
                    <a href="#" class="btn btn-primary">Comprar</a>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <img src="https://via.placeholder.com/150" class="card-img-top" alt="Naranjas">
                <div class="card-body">
                    <h5 class="card-title">Naranjas</h5>
                    <p class="card-text">Perfectas para un jugo lleno de vitaminas por las mañanas.</p>
                    <a href="#" class="btn btn-primary">Comprar</a>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <img src="https://via.placeholder.com/150" class="card-img-top" alt="Plátanos">
                <div class="card-body">
                    <h5 class="card-title">Plátanos</h5>
                    <p class="card-text">Dulces y nutritivos, ideales para cualquier hora del día.</p>
                    <a href="#" class="btn btn-primary">Comprar</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>