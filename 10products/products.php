<?php

    // PHP creado por
    // Twitter: @javiersureda
    // Github: @javiersureda
    // Youtube: @javiersureda3

    require_once("../autoload.php");

    session_start(); // Inicia sesión

    $Error = "<h1>Permission denied</h1>";

    $Allowed = Functions::HasPermissions("A", "products.php");

    if (!$Allowed) {
        echo $Error;
        exit;
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

    $conn = database::LoadDatabase(); // Conexión a la base de datos

    // Obtener categorías desde la base de datos
    $stmt = $conn->prepare("SELECT cat_id, cat_description FROM pps_categories");
    $stmt->execute();
    $categories = $stmt->fetchAll();

    // Variables de paginación
    $productsPerPage = isset($_POST['products_per_page']) ? (int)$_POST['products_per_page'] : 8;
    $currentPage = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $offset = ($currentPage - 1) * $productsPerPage;

    // Variables de búsqueda
    $searchName = isset($_POST['search_name']) ? $_POST['search_name'] : '';
    $searchPrice = isset($_POST['search_price']) ? $_POST['search_price'] : '';
    $category = isset($_POST['category']) ? $_POST['category'] : '';

    // Construir la consulta principal con los filtros aplicados
    $sql = "SELECT p.*, IFNULL(AVG(r.rev_rating), 0) AS avg_rating 
            FROM pps_products p
            LEFT JOIN pps_reviews r ON p.prd_id = r.rev_product
            WHERE 1";
    $params = [];

    if (!empty($searchName)) {
        $sql .= " AND prd_name LIKE :search_name";
        $params['search_name'] = '%' . $searchName . '%';
    }
    if (!empty($searchPrice)) {
        $sql .= " AND prd_price = :search_price";
        $params['search_price'] = $searchPrice;
    }
    if (!empty($category) && is_numeric($category)) {
        $sql .= " AND prd_category = :category";
        $params['category'] = $category;
    }

    // Contar el total de productos para la paginación
    $countSql = str_replace("SELECT p.*, IFNULL(AVG(r.rev_rating), 0) AS avg_rating", "SELECT COUNT(*)", $sql);
    $stmt = $conn->prepare($countSql);
    $stmt->execute($params);
    $totalProducts = $stmt->fetchColumn();
    $totalPages = ceil($totalProducts / $productsPerPage);

    // Agregar límite y offset para la paginación
    $sql .= " GROUP BY p.prd_id LIMIT :limit OFFSET :offset";
    $stmt = $conn->prepare($sql);
    foreach ($params as $key => &$value) {
        $stmt->bindParam($key, $value);
    }
    $stmt->bindValue(':limit', $productsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll();

    // Consulta para verificar si hay productos en la base de datos
    $stmt = $conn->prepare("SELECT COUNT(*) FROM pps_products");
    $stmt->execute();
    $totalProductCount = $stmt->fetchColumn();
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
            <div class="jumbotron">
                <h1 class="display-4">¡Bienvenidos a Frutería del Barrio!</h1>
                <p class="lead">Las mejores frutas frescos directo de los agricultores de Valencia a tu mesa.</p>
                <hr class="my-4">
                <p>Visita nuestra sección de ofertas especiales.</p>
                <a class="btn btn-primary btn-lg mb-4" href="#" role="button">Ver Ofertas</a>
            </div>

            <!-- Formulario de búsqueda de productos -->
            <form id="searchForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="mb-4">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" placeholder="Buscar por nombre" name="search_name" value="<?php echo htmlspecialchars($searchName); ?>">
                    <input type="number" class="form-control" placeholder="Buscar por precio" name="search_price" step="0.01" value="<?php echo htmlspecialchars($searchPrice); ?>">
                    <select class="form-control" name="category">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($categories as $category) : ?>
                            <option value="<?php echo htmlspecialchars($category['cat_id']); ?>" <?php echo $category['cat_id'] == $category ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['cat_description']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select class="form-control" name="products_per_page" onchange="document.getElementById('searchForm').submit();">
                        <option value="10" <?php echo $productsPerPage == 10 ? 'selected' : ''; ?>>10 por página</option>
                        <option value="25" <?php echo $productsPerPage == 25 ? 'selected' : ''; ?>>25 por página</option>
                        <option value="50" <?php echo $productsPerPage == 50 ? 'selected' : ''; ?>>50 por página</option>
                        <option value="100" <?php echo $productsPerPage == 100 ? 'selected' : ''; ?>>100 por página</option>
                    </select>
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">Buscar</button>
                    </div>
                </div>
                <input type="hidden" name="page" value="<?php echo $currentPage; ?>">
            </form>

            <?php if ($totalProductCount > 0): ?>
                <?php if ($totalProducts > 0): ?>
                    <!-- Mostrar productos -->
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                        <?php foreach ($results as $row): ?>
                            <div class="col">
                                <div class="card h-100 shadow">
                                    <!-- Se utiliza htmlspecialchars para evitar XSS -->
                                    <div onclick="viewProductDetails(<?php echo htmlspecialchars($row['prd_id']); ?>)">
                                        <img src="<?php echo htmlspecialchars($row['prd_image']); ?>" class="card-img-top" style="height: 200px; width: auto; margin: auto; display: block;" alt="<?php echo htmlspecialchars($row['prd_name']); ?>">
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title"><?php echo htmlspecialchars($row['prd_name']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($row['prd_details']); ?></p>
                                        <p class="card-text"><span class="badge bg-success" style="font-size: 0.9rem;"><?php echo htmlspecialchars($row['prd_price']); ?>€</span></p>
                                        <!-- Mostrar la media de las estrellas -->
                                        <div class="mb-3">
                                            <p class="card-text"><small class="text-muted">Valoración media:</small></p>
                                            <div class="d-flex">
                                                <?php
                                                $rating = round($row['avg_rating'] * 2) / 2; // Redondear a 0.5 más cercano
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
                                        <!-- Formulario que da los detalles los productos -->
                                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="mt-auto" onsubmit="saveScrollPosition();">
                                            <input type="hidden" name="product_id" value="<?php echo $row['prd_id']; ?>">
                                            <div class="mb-3">
                                                <label for="quantity<?php echo $row['prd_id']; ?>" class="form-label">Cantidad:</label>
                                                <input type="number" class="form-control" id="quantity<?php echo $row['prd_id']; ?>" name="quantity" min="1" max="<?php echo $row['prd_stock']; ?>" value="1">
                                            </div>
                                            <p class="card-text"><small class="text-muted">En stock: <?php echo $row['prd_stock']; ?></small></p>
                                            <button type="submit" class="btn btn-primary">Añadir al carrito</button>
                                        </form>
                                        <button class="btn btn-secondary mt-3" onclick="viewProductDetails(<?php echo htmlspecialchars($row['prd_id']); ?>)">Ver más detalles</button>
                                    </div>
                                </div>
                            </div>
                            <!-- Incluir el formulario oculto y la función JavaScript para enviar el ID del producto por POST -->
                            <form id="productDetailsForm" action="product.php" method="post" style="display: none;">
                                <input type="hidden" name="prd_id" id="prd_id">
                            </form>
                            <script>
                                function viewProductDetails(productId) {
                                    document.getElementById("prd_id").value = productId;
                                    document.getElementById("productDetailsForm").submit();
                                }
                            </script>
                        <?php endforeach; ?>
                    </div>
                    <!-- Paginación -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mt-4">
                            <?php if ($currentPage > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="javascript:;" onclick="changePage(1)">&laquo;&laquo;</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="javascript:;" onclick="changePage(<?php echo $currentPage - 1; ?>)">&laquo;</a>
                                </li>
                            <?php endif; ?>
                            <?php for ($page = 1; $page <= $totalPages; $page++): ?>
                                <li class="page-item <?php echo $currentPage == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="javascript:;" onclick="changePage(<?php echo $page; ?>)"><?php echo $page; ?></a>
                                </li>
                            <?php endfor; ?>
                            <?php if ($currentPage < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="javascript:;" onclick="changePage(<?php echo $currentPage + 1; ?>)">&raquo;</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="javascript:;" onclick="changePage(<?php echo $totalPages; ?>)">&raquo;&raquo;</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php else: ?>
                    <!-- Cartel cuando no ha encontrado productos en la búsqueda -->
                    <div class="row row-cols-12 row-cols-md-12 g-4">
                        <div class="col-12 mt-4 d-flex align-items-center justify-content-center">
                            <div class="alert alert-warning" role="alert">
                                <h4 class="alert-heading">Producto no encontrado</h4>
                                <p>No hemos encontrado productos que coincidan con tu búsqueda. Por favor, intenta con otros términos o ajusta los filtros.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- Cartel de cuando no hay productos en la tienda -->
                <div class="row row-cols-12 row-cols-md-12 g-4">
                    <div class="col-12 mt-4 d-flex align-items-center justify-content-center">
                        <div class="alert alert-info" role="alert">
                            <h4 class="alert-heading">¡Ups! No hay productos disponibles.</h4>
                            <p>Actualmente no tenemos productos en stock. Por favor, vuelve más tarde o contacta con nosotros para más información.</p>
                            <hr>
                            <p class="mb-0">Mientras tanto, visita nuestras redes sociales o nuestra página de contacto para estar al día.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php include "../footer.php"; // Incluye el footer ?>

        <!-- Script para cambiar de página en la paginación -->
        <script>
            function changePage(page) {
                const form = document.getElementById('searchForm');
                const pageInput = document.createElement('input');
                pageInput.type = 'hidden';
                pageInput.name = 'page';
                pageInput.value = page;
                form.appendChild(pageInput);
                form.submit();
            }
        </script>

        <!-- Script para guardar la posición del usuario en la web al añadir un producto al carrito -->
        <script src="position.js"></script>
    </body>
</html>

<?php
// Se pone la conexión a NULL por seguridad y ahorrar memoria
$stmt = null;
?>
