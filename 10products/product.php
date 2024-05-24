<?php

    // PHP creado por
    // Twitter: @javiersureda
    // Github: @javiersureda
    // Youtube: @javiersureda3

    session_start(); // Inicia sesión
    require_once("../autoload.php");

    // Conexión a la base de datos
    $conn = database::LoadDatabase();

    // Redirigir a la página principal si no se selecciona un producto
    if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['prd_id'])) {
        header("Location: ../index.php");
        exit();
    }

    $product_id = (int)$_POST['prd_id'];

    // Manejar la inserción de nuevas reseñas
    if (isset($_POST['rating']) && isset($_POST['review'])) {
        $rating = (int)$_POST['rating'];
        $review = trim($_POST['review']);
        $datetime = date('Y-m-d H:i:s');

        $stmt = $conn->prepare("INSERT INTO pps_reviews (rev_product, rev_rating, rev_message, rev_datetime) VALUES (?, ?, ?, ?)");
        $stmt->execute([$product_id, $rating, $review, $datetime]);

        // Redirigir para evitar reenvío de formularios
        header("Location: product.php");
        exit();
    }

    // Agregar al carrito
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id']) && isset($_POST['quantity']))
	{
		$productId = $_POST['product_id'];
		$quantity  = $_POST['quantity'];

		// Comprueba que el carrito esté inicializado
		if (!isset($_SESSION['cart']))
		{
			$_SESSION['cart'] = [];
		}

		// Agregar producto al carrito
		if (isset($_SESSION['cart'][$productId]))
		{
			$_SESSION['cart'][$productId] += $quantity;
		}
		else
		{
			$_SESSION['cart'][$productId] = $quantity;
		}

		// Redirigir para evitar reenvío de formularios
		header("Location: " . $_SERVER['PHP_SELF']);
		exit();
	}

    // Consulta para obtener las reseñas del producto
    $stmt = $conn->prepare("SELECT * FROM pps_reviews WHERE rev_product = ? ORDER BY rev_datetime DESC");
    $stmt->execute([$product_id]);
    $reviews = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <!-- Meta Etiquetas -->
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Página de detalles del producto en Frutería del Barrio">
        <meta name="keywords" content="detalles del producto, frutería, reseñas">
        <meta name="author" content="Javier Sureda">

        <!-- Título -->
        <title><?php echo htmlspecialchars($product['prd_name']); ?> - Frutería del Barrio</title>

        <!-- CSS / Hoja de estilos Bootstrap -->
        <link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

        <!-- Favicon -->
        <link rel="apple-touch-icon" sizes="180x180" href="/0images/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/0images/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/0images/favicon-16x16.png">
        <link rel="manifest" href="/0images/site.webmanifest">
    </head>
    <body>
        <?php 
            include "../nav.php"; // Incluye el Navbar 
        
            // Consulta para obtener los detalles del producto
            $stmt = $conn->prepare("SELECT * FROM pps_products WHERE prd_id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();

            if (!$product) {
                // Redirigir a la página principal si el producto no existe
                header("Location: ../index.php");
                exit();
    }
        ?>

        <div class="container mt-4">
            <div class="row">
                <div class="col-md-6">
                    <img src="<?php echo htmlspecialchars($product['prd_image']); ?>" class="img-fluid shadow border rounded" style="height: 450px; width: auto; margin: auto; display: block;" alt="<?php echo htmlspecialchars($product['prd_name']); ?>">
                </div>
                <div class="col-md-6">
                    <h1><?php echo htmlspecialchars($product['prd_name']); ?></h1>
                    <p class="lead"><?php echo htmlspecialchars($product['prd_details']); ?></p>
                    <p><strong>Precio:</strong> <?php echo htmlspecialchars($product['prd_price']); ?>€</p>
                    <p><strong>Stock:</strong> <?php echo htmlspecialchars($product['prd_stock']); ?></p>

                    <form action="product.php" method="post">
                        <input type="hidden" name="prd_id" value="<?php echo $product_id; ?>">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Cantidad:</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="1" max="<?php echo htmlspecialchars($product['prd_stock']); ?>" value="1">
                        </div>
                        <button type="submit" name="add_to_cart" class="btn btn-primary">Añadir al carrito</button>
                    </form>
                </div>
            </div>

            <hr>

            <h3>Reseñas</h3>
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">
                                <?php for ($i = 0; $i < 5; $i++): ?>
                                    <i class="fa<?php echo $i < $review['rev_rating'] ? 's' : 'r'; ?> fa-star"></i>
                                <?php endfor; ?>
                            </h5>
                            <p class="card-text"><?php echo htmlspecialchars($review['rev_message']); ?></p>
                            <p class="card-text"><small class="text-muted">Publicado el <?php echo date('d/m/Y H:i', strtotime($review['rev_datetime'])); ?></small></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay reseñas para este producto.</p>
            <?php endif; ?>

            <hr>

            <h3>Dejar una Reseña</h3>
            <form action="product.php" method="post">
                <input type="hidden" name="prd_id" value="<?php echo $product_id; ?>">
                <div class="mb-3">
                    <label for="rating" class="form-label">Calificación:</label>
                    <div id="rating" class="form-control">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <input type="radio" name="rating" value="<?php echo $i; ?>" id="star<?php echo $i; ?>" required>
                            <label for="star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                        <?php endfor; ?>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="review" class="form-label">Reseña:</label>
                    <textarea class="form-control" id="review" name="review" rows="3" maxlength="500" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Enviar Reseña</button>
            </form>
        </div>

        <?php include "../footer.php"; // Incluye el footer ?>

        <!-- Script para mostrar las estrellas de calificación -->
        <style>
            .fa-star {
                color: #ddd;
            }
            input[type="radio"]:checked ~ label .fa-star,
            label:hover ~ label .fa-star,
            label:hover .fa-star {
                color: #ffc107;
            }
            #rating {
                display: flex;
                flex-direction: row-reverse;
                justify-content: flex-end;
            }
            #rating input {
                display: none;
            }
            #rating label {
                cursor: pointer;
                width: 1em;
                font-size: 2rem;
                color: #ddd;
            }
            #rating input:checked ~ label,
            #rating label:hover,
            #rating label:hover ~ label {
                color: #ffc107;
            }
        </style>
    </body>
</html>
