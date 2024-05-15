<?php

    // PHP creado por
    // Twitter: @javiersureda
    // Github: @javiersureda
    // Youtube: @javiersureda3

    //session_start(); // Inicia sesión
    require_once("autoload.php");
	require_once("vendor/autoload.php");
    require_once("Functions.php");

    //require_once "db.php"; // DB ANTIGUA
    
    $Error = "<h1>Permission denied</h1>";
/*
    $Allowed = Functions::HasPermissions("A", "products.php");

	if (!$Allowed)
	{
        echo $Error;
        exit;
    }
*/

    // Agregar al carrito
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id']) && isset($_POST['quantity'])) {
        $productId = $_POST['product_id'];
        $quantity = $_POST['quantity'];

        // Comprueba que el carrito esté inicializado
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    
        // Agregar producto al carrito
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }
    
        // Redirigir para evitar reenvío de formularios
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    $conn = database::LoadDatabase(); // Conexión a la base de datos

    // Obtener categorías desde la base de datos
    $stmt = $conn->prepare("SELECT cat_id, cat_description FROM pps_categories");
    $stmt->execute();
    $categories = $stmt->fetchAll();
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
    <?php include "nav.php"; // Incluye el Navbar ?>

        <div class="container mt-4 mb-4">
            <div class="jumbotron">
                <h1 class="display-4">¡Bienvenidos a Frutería del Barrio!</h1>
                <p class="lead">Las mejores frutas frescos directo de los agricultores de Valencia a tu mesa.</p>
                <hr class="my-4">
                <p>Visita nuestra sección de ofertas especiales.</p>
                <a class="btn btn-primary btn-lg mb-4" href="#" role="button">Ver Ofertas</a>
            </div>

            <!-- Formulario de búsqueda de productos -->
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="mb-4">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" placeholder="Buscar por nombre" name="search_name">
                    <input type="number" class="form-control" placeholder="Buscar por precio" name="search_price" step="0.01">
                    <select class="form-control" name="category">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category['cat_id']); ?>"><?php echo htmlspecialchars($category['cat_description']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">Buscar</button>
                    </div>
                </div>
            </form>

            <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php
            // Se comprueba que hay productos
            $stmt = $conn->prepare("SELECT COUNT(*) FROM pps_products");
            $stmt->execute();
            $count = $stmt->fetchColumn();

            /*  
                Si no hay productos en la DB, va directamente a la alerta de que no hay productos,
                si hay productos, se realiza la consulta principal o de busqueda
            */
            if ($count > 0) {  
                $sql = "SELECT prd_id, prd_name, prd_category, prd_details, prd_price, prd_image, prd_stock FROM pps_products WHERE 1=1";
                $params = [];
                
                // Barra de búsqueda
                if (!empty($_POST['search_name'])) {
                    $sql .= " AND prd_name LIKE :search_name";
                    $params['search_name'] = '%' . $_POST['search_name'] . '%';
                }
                if (!empty($_POST['search_price'])) {
                    $sql .= " AND prd_price = :search_price";
                    $params['search_price'] = $_POST['search_price'];
                }
                if (!empty($_POST['category']) && is_numeric($_POST['category'])) {
                    $sql .= " AND prd_category = :category";
                    $params['category'] = $_POST['category'];
                }
        
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
                $results = $stmt->fetchAll();
            
            // Comprueba que hay productos y los muestra en tarjetas
            if (!empty($results)) {
                foreach ($results as $row) {
                    echo '<div class="col">';
                    echo '<div class="card h-100 shadow">';

                    // Se utiliza htmlspecialchars para evitar XSS
                    echo '<img src="' . htmlspecialchars($row["prd_image"]) . '" class="card-img-top" style="height: 370px; width: auto; margin: auto;" alt="' . htmlspecialchars($row["prd_name"]) . '">';
                    echo '<div class="card-body d-flex flex-column">';
                    echo '<h5 class="card-title">' . htmlspecialchars($row["prd_name"]) . '</h5>';
                    echo '<p class="card-text">' . htmlspecialchars($row["prd_details"]) . '</p>';
                    echo '<p class="card-text"><span class="badge bg-success" style="font-size: 0.9rem;">' . htmlspecialchars($row["prd_price"]) . '€</span></p>';
                    
                    // Formulario que da los detalles los productos
                    echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '" method="post" class="mt-auto" onsubmit="saveScrollPosition();">';
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
                // Cartel cuando no ha encontrado productos en la busqueda
                echo '<div class="col-12">';
                echo '<div class="alert alert-warning" role="alert">';
                echo '<h4 class="alert-heading">Producto no encontrado</h4>';
                echo '<p>No hemos encontrado productos que coincidan con tu búsqueda. Por favor, intenta con otros términos o ajusta los filtros.</p>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            // Cartel de cuando no hay productos en la tienda
            echo '<div class="col-12">';
            echo '<div class="alert alert-info" role="alert">';
            echo '<h4 class="alert-heading">¡Ups! No hay productos disponibles.</h4>';
            echo '<p>Actualmente no tenemos productos en stock. Por favor, vuelve más tarde o contacta con nosotros para más información.</p>';
            echo '<hr>';
            echo '<p class="mb-0">Mientras tanto, visita nuestras redes sociales o nuestra página de contacto para estar al día.</p>';
            echo '</div>';
            echo '</div>';
        }
            // Se pone la conexión a NULL por seguridad y ahorrar memoria
            $stmt = null;
            ?>
            </div>
        </div>
        <?php include "footer.php"; // Incluye el footer ?>
        
        <!-- Script para guardar la posición del usuario
             en la web al añadir un producto al carrito   -->
        <script src="10products/position.js"></script>
    </body>
</html>
