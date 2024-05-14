<?php
require_once "../Functions.php";

// Conexión a la base de datos
$conn = database::LoadDatabase();
?>
<style>
    /* Estilo para la imagen del perfil */
    .profile-image {
        width: 40px; /* Tamaño de la imagen */
        height: 40px; /* Altura de la imagen */
        border-radius: 50%; /* Hace que la imagen sea un círculo */
        margin-right: 5px; /* Espacio entre la imagen y el texto */
    }
</style>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Frutería del Barrio</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="#">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Productos</a>
                </li>
                <li class="nav-item, profile-user">
                    <a href="../4profile/usu_info.php">
                        <img src="../0images/default_user.png" alt="User" class="profile-image">
                        Perfil
                    </a>
                </li>
            </ul>
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownCartButton" data-bs-toggle="dropdown" aria-expanded="false">
                    Carrito de Compras
                    <span class="badge bg-secondary"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownCartButton">
                    <?php if (!empty($_SESSION['cart'])): ?>
                        <?php
                        $productIds = array_keys($_SESSION['cart']);
                        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
                        $stmt = $conn->prepare("SELECT prd_id, prd_name, prd_price FROM pps_products WHERE prd_id IN ($placeholders)");
                        $stmt->execute($productIds);
                        $cartProducts = $stmt->fetchAll();
                        ?>
                        <?php foreach ($cartProducts as $product): ?>
                            <li class="dropdown-item d-flex justify-content-between align-items-center">
                                <?php echo htmlspecialchars($product['prd_name']); ?>
                                <span class="badge bg-primary rounded-pill"><?php echo $_SESSION['cart'][$product['prd_id']]; ?></span>
                                <span class="text-muted"><?php echo $product['prd_price']; ?>€</span>
                            </li>
                        <?php endforeach; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="cart.php">Ver Carrito</a></li>
                    <?php else: ?>
                        <li class="dropdown-item">No hay productos en el carrito.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</nav>

<script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.js"></script>
