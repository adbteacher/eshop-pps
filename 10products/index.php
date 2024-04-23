<?php

    // PHP creado por
    // Twitter: @javiersureda
    // Github: @javiersureda
    // Youtube: @javiersureda3

    // Incluye el archivo de conexión a la base de datos
    include 'db.php'; 

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
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">Frutería del Barrio</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="#">Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Productos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Contacto</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container mt-4">
            <div class="jumbotron">
                <h1 class="display-4">¡Bienvenidos a Frutería del Barrio!</h1>
                <p class="lead">Las mejores frutas frescos directo de los agricultores de Valencia a tu mesa.</p>
                <hr class="my-4">
                <p>Visita nuestra sección de ofertas especiales.</p>
                <a class="btn btn-primary btn-lg" href="#" role="button">Ver Ofertas</a>
            </div>

            <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php
            $stmt = $conn->prepare("SELECT prd_name, prd_details, prd_image, prd_price, prd_stock, prd_id FROM pps_products");
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="col">';
                    echo '<div class="card">';
                    // Utiliza htmlspecialchars para evitar XSS
                    echo '<img src="' . htmlspecialchars($row["prd_image"]) . '" class="card-img-top" alt="' . htmlspecialchars($row["prd_name"]) . '">';
                    echo '<div class="card-body">';
                    echo '<h5 class="card-title">' . htmlspecialchars($row["prd_name"]) . '</h5>';
                    echo '<p class="card-text">' . htmlspecialchars($row["prd_details"]) . '</p>';
                    // Añadir el formulario con la cantidad máxima basada en el stock
                    echo '<form action="comprar.php" method="post">';
                    echo '<button type="submit" class="btn btn-primary">Comprar</button>';
                    echo '<input type="hidden" name="product_id" value="' . $row["prd_id"] . '">';
                    echo '<input type="number" style="margin-left: 10px;" name="quantity" min="1" max="' . $row["prd_stock"] . '" value="1">';
                    echo '<p>En stock: ' . $row["prd_stock"] . '</p>';
                    echo '</form>';
                    echo '</div></div></div>';
                }
            } else {
                // Mensaje de no resultados de manera visualmente atractiva
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
