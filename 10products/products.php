<?php

    // PHP creado por
    // Twitter: @javiersureda
    // Github: @javiersureda
    // Youtube: @javiersureda3

    require_once "../Functions.php";

    $Error = "<h1>Permission denied</h1>";

	$Allowed = Functions::HasPermissions("A", "products.php");

    if (!$Allowed)
	{
        echo $Error;
    }

?>
<!DOCTYPE html>
    <!--
			
			Página web creada por
			Twitter: @javiersureda
            Github: @javiersureda
            Youtube: @javiersureda3

    -->
<html lang="es">
    <head>

        <!-- Meta Etiquetas -->
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Página web para PPS en CIPFP Mislata, por javiersureda">
		<meta name="keywords" content="javiersureda, pps, mislata, cipfpmislata">
		<meta name="author" content="Javier Sureda">

        <!-- Titulo -->
        <title>Frutería del Barrio</title>

        <!-- CSS / Hoja de estilos Bootstrap -->
        <link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>

    <body>
    <?php
        include "../nav.php";
    ?>

        <div class="container mt-4">
            <div class="jumbotron">
                <h1 class="display-4">¡Bienvenidos a Frutería del Barrio!</h1>
                <p class="lead">Las mejores frutas frescos directo de los agricultores de Valencia a tu mesa.</p>
                <hr class="my-4">
                <p>Visita nuestra sección de ofertas especiales.</p>
                <a class="btn btn-primary btn-lg mb-4" href="#" role="button">Ver Ofertas</a>
            </div>

            <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php
            $stmt = $Query->prepare("SELECT prd_name, prd_details, prd_image, prd_price, prd_stock, prd_id FROM pps_products");
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="col">';
                    echo '<div class="card h-100">';

                    // Se utiliza htmlspecialchars para evitar XSS
                    echo '<img src="' . htmlspecialchars($row["prd_image"]) . '" class="card-img-top" alt="' . htmlspecialchars($row["prd_name"]) . '">';
                    echo '<div class="card-body d-flex flex-column">';
                    echo '<h5 class="card-title">' . htmlspecialchars($row["prd_name"]) . '</h5>';
                    echo '<p class="card-text">' . htmlspecialchars($row["prd_details"]) . '</p>';
                    
                    // Formulario que da los detalles de stock y permite añadir al carrito
                    echo '<form action="comprar.php" method="post" class="mt-auto">';
                    echo '<input type="hidden" name="product_id" value="' . $row["prd_id"] . '">';
                    echo '<div class="mb-3">';
                    echo '<label for="quantity' . $row['prd_id'] . '" class="form-label">Cantidad:</label>';
                    echo '<input type="number" class="form-control" id="quantity' . $row['prd_id'] . '" name="quantity" min="1" max="' . $row["prd_stock"] . '" value="1">';
                    echo '</div>';
                    echo '<p class="card-text"><small class="text-muted">En stock: ' . $row["prd_stock"] . '</small></p>';
                    echo '<button type="submit" class="btn btn-primary">Añadir al carrito</button>';
                    echo '</form>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                // Cartel de cuando no hay productos
                echo '<div class="col-12">';
                echo '<div class="alert alert-info" role="alert">';
                echo '<h4 class="alert-heading">¡Ups! No hay productos disponibles.</h4>';
                echo '<p>Actualmente no tenemos productos en stock. Por favor, vuelve más tarde o contacta con nosotros para más información.</p>';
                echo '<hr>';
                echo '<p class="mb-0">Mientras tanto, visita nuestras redes sociales o nuestra página de contacto para estar al día.</p>';
                echo '</div>';
                echo '</div>';
            }
                $stmt->close();
            ?>
            </div>
        </div>

        <!-- Script de Bootstrap -->
        <script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
