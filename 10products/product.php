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
    if (isset($_POST['rating']) && isset($_POST['review']) && isset($_SESSION['UserID'])) {
        $rating = (int)$_POST['rating'];
        $review = trim($_POST['review']);
        $datetime = date('Y-m-d H:i:s');
        $user_id = $_SESSION['UserID'];

        $stmt = $conn->prepare("INSERT INTO pps_reviews (rev_product, rev_rating, rev_message, rev_datetime, rev_user_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$product_id, $rating, $review, $datetime, $user_id]);

        // Redirigir para evitar reenvío de formularios
        header("Location: product.php");
        exit();
    }

    // Manejar la eliminación de reseñas
    if (isset($_POST['delete_review_id']) && isset($_SESSION['UserID'])) {
        $review_id = (int)$_POST['delete_review_id'];
        $user_id = $_SESSION['UserID'];

        $stmt = $conn->prepare("DELETE FROM pps_reviews WHERE rev_id = ? AND rev_user_id = ?");
        $stmt->execute([$review_id, $user_id]);

        // Redirigir para evitar reenvío de formularios
        header("Location: product.php");
        exit();
    }

    // Manejar la edición de reseñas
    if (isset($_POST['edit_review_id']) && isset($_POST['edit_rating']) && isset($_POST['edit_review']) && isset($_SESSION['UserID'])) {
        $review_id = (int)$_POST['edit_review_id'];
        $rating = (int)$_POST['edit_rating'];
        $review = trim($_POST['edit_review']);
        $user_id = $_SESSION['UserID'];

        $stmt = $conn->prepare("UPDATE pps_reviews SET rev_rating = ?, rev_message = ? WHERE rev_id = ? AND rev_user_id = ?");
        $stmt->execute([$rating, $review, $review_id, $user_id]);

        // Redirigir para evitar reenvío de formularios
        header("Location: product.php");
        exit();
    }

    // Agregar al carrito
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id']) && isset($_POST['quantity'])) {
        $productId = $_POST['product_id'];
        $quantity  = $_POST['quantity'];

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

    // Consulta para obtener las reseñas del producto
    $stmt = $conn->prepare("SELECT r.*, u.usu_name FROM pps_reviews r LEFT JOIN pps_users u ON r.rev_user_id = u.usu_id WHERE r.rev_product = ? ORDER BY r.rev_datetime DESC");
    $stmt->execute([$product_id]);
    $reviews = $stmt->fetchAll();

    // Consulta para obtener los detalles del producto
    $stmt = $conn->prepare("SELECT p.*, IFNULL(AVG(r.rev_rating), 0) AS avg_rating FROM pps_products p LEFT JOIN pps_reviews r ON p.prd_id = r.rev_product WHERE p.prd_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        // Redirigir a la página principal si el producto no existe
        header("Location: ../index.php");
        exit();
    }
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
        <?php include "../nav.php"; // Incluye el Navbar ?>

        <div class="container mt-4 mb-4">
            <div class="row">
                <div class="col-md-6 shadow border rounded mb-4">
                    <img src="<?php echo htmlspecialchars($product['prd_image']); ?>" class="img-fluid" style="height: 450px; width: auto; margin: auto; display: block;" alt="<?php echo htmlspecialchars($product['prd_name']); ?>">
                </div>
                <div class="col-md-6">
                    <h1><?php echo htmlspecialchars($product['prd_name']); ?></h1>
                    <p class="lead"><?php echo htmlspecialchars($product['prd_details']); ?></p>
                    <?php if ($product['prd_on_offer']): ?>
                        <p><strong>Precio:</strong> <span class="text-muted text-decoration-line-through"><?php echo htmlspecialchars($product['prd_price']); ?>€</span> <span class="badge bg-success" style="font-size: 1rem;"><?php echo htmlspecialchars($product['prd_offer_price']); ?>€</span></p>
                        <div class="badge bg-danger text-white" style="font-size: 1rem;">Oferta</div>
                    <?php else: ?>
                        <p><strong>Precio:</strong> <span class="badge bg-success" style="font-size: 1rem;"><?php echo htmlspecialchars($product['prd_price']); ?>€</span></p>
                    <?php endif; ?>
                    <p><strong>Stock:</strong> <?php echo htmlspecialchars($product['prd_stock']); ?></p>

                    <form action="product.php" method="post">
                        <input type="hidden" name="prd_id" value="<?php echo $product_id; ?>">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <div class="mb-3" style="max-width: 120px;">
                            <label for="quantity" class="form-label">Cantidad:</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="1" max="<?php echo htmlspecialchars($product['prd_stock']); ?>" value="1">
                        </div>
                        <button type="submit" name="add_to_cart" class="btn btn-primary">Añadir al carrito</button>
                    </form>

                    <!-- Mostrar la media de las estrellas -->
                    <div class="mt-3">
                        <p class="card-text"><small class="text-muted">Valoración media:</small></p>
                        <div class="d-flex">
                            <?php
                            $rating = round($product['avg_rating'] * 2) / 2; // Redondear a 0.5 más cercano
                            for ($i = 0; $i < 5; $i++) {
                                if ($i < floor($rating)) {
                                    echo '<i class="fas fa-star" style="color: #ffc107;"></i>';
                                } elseif ($i < ceil($rating)) {
                                    echo '<i class="fas fa-star-half-alt" style="color: #ffc107;"></i>';
                                } else {
                                    echo '<i class="far fa-star" style="color: #ffc107;"></i>';
                                }
                            }
                            ?>
                        </div>
                    </div>
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
                                    <i class="fa<?php echo $i < $review['rev_rating'] ? 's' : 'r'; ?> fa-star" style="color: #ffc107;"></i>
                                <?php endfor; ?>
                            </h5>
                            <p class="card-text"><?php echo htmlspecialchars($review['rev_message']); ?></p>
                            <p class="card-text"><small class="text-muted">Publicado por <strong><?php echo htmlspecialchars($review['usu_name']); ?></strong> el <?php echo date('d/m/Y H:i', strtotime($review['rev_datetime'])); ?></small></p>
                            <?php if (isset($_SESSION['UserID']) && $_SESSION['UserID'] == $review['rev_user_id']): ?>
                                <form action="product.php" method="post" style="display:inline;">
                                    <input type="hidden" name="prd_id" value="<?php echo $product_id; ?>">
                                    <input type="hidden" name="delete_review_id" value="<?php echo $review['rev_id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> Eliminar</button>
                                </form>
                                <button class="btn btn-secondary btn-sm" onclick="editReview('<?php echo $review['rev_id']; ?>', '<?php echo $review['rev_rating']; ?>', '<?php echo htmlspecialchars(addslashes($review['rev_message'])); ?>')"><i class="fas fa-edit"></i> Editar</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay reseñas para este producto.</p>
            <?php endif; ?>

            <hr>

            <h3>Dejar una Reseña</h3>
            <?php
                // Redirigir a la página de inicio de sesión si el usuario no está autenticado
                if (!isset($_SESSION['UserID'])) {
                    echo '<h5 class="alert alert-warning col-md-2 mt-4 mb-4">Inicia sesión para escribir una reseña</h5>';
                    echo '<a class="btn btn-primary" href="/1login/login.php">Iniciar sesión</a>';
                } else {
            ?>
            <form id="new_review_form" action="product.php" method="post">
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
            <?php } ?>
        </div>

        <div id="edit_review_form_container" class="container mt-4 mb-4" style="display:none;">
            <h3>Editar Reseña</h3>
            <form id="edit_review_form" action="product.php" method="post">
                <input type="hidden" name="prd_id" value="<?php echo $product_id; ?>">
                <input type="hidden" name="edit_review_id" id="edit_review_id">
                <div class="mb-3">
                    <label for="edit_rating" class="form-label">Calificación:</label>
                    <div id="edit_rating" class="form-control">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <input type="radio" name="edit_rating" value="<?php echo $i; ?>" id="edit_star<?php echo $i; ?>" required>
                            <label for="edit_star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                        <?php endfor; ?>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="edit_review" class="form-label">Reseña:</label>
                    <textarea class="form-control" id="edit_review" name="edit_review" rows="3" maxlength="500" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <button type="button" class="btn btn-secondary" onclick="cancelEdit()">Cancelar</button>
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
            #rating, #edit_rating {
                display: flex;
                flex-direction: row-reverse;
                justify-content: flex-end;
            }
            #rating input, #edit_rating input {
                display: none;
            }
            #rating label, #edit_rating label {
                cursor: pointer;
                width: 1em;
                font-size: 2rem;
                color: #ddd;
            }
            #rating input:checked ~ label, #rating label:hover, #rating label:hover ~ label,
            #edit_rating input:checked ~ label, #edit_rating label:hover, #edit_rating label:hover ~ label {
                color: #ffc107;
            }
        </style>

        <script>
            function editReview(reviewId, rating, message) {
                document.getElementById('edit_review_id').value = reviewId;
                document.getElementById('edit_review').value = message;
                const stars = document.querySelectorAll('#edit_rating input');
                stars.forEach(star => {
                    if (star.value == rating) {
                        star.checked = true;
                    }
                });
                document.getElementById('edit_review_form_container').style.display = 'block';
                document.getElementById('new_review_form').style.display = 'none';
            }

            function cancelEdit() {
                document.getElementById('edit_review_form_container').style.display = 'none';
                document.getElementById('new_review_form').style.display = 'block';
            }
        </script>

    </body>
</html>
