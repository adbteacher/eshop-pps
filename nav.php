<?php
session_start();

require_once(__DIR__ . "/autoload.php");

// Conexión a la base de datos
$conn = database::LoadDatabase();

// Consulta para verificar si el usuario tiene el rol "A"
$stmt = $conn->prepare("SELECT usu_rol FROM pps_users WHERE usu_id = ?");
$stmt->execute([$_SESSION["UserID"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

//TODO PENSAR SI SACAR A UNA FUNCION
if ($user['usu_rol'] === 'A') {
    $isAdmin = true; // Verificar si el usuario tiene el rol "A"
} else {
    $NameToDisplay = "Invitado";
    $isAdmin       = false; // Valor predeterminado para los usuarios no autenticados
}

if ($_SESSION["UserID"]) {
    $NameToDisplay = $_SESSION["UserName"];
} else {
    $NameToDisplay = "Invitado";
}

// Manejar la lógica de eliminación del carrito
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_product_id'])) {
    $removeProductId = $_POST['remove_product_id'];

    if (isset($_SESSION['cart'][$removeProductId])) {
        unset($_SESSION['cart'][$removeProductId]);
    }

    // Redirigir para evitar reenvío de formularios
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>
<style>
    /* Estilo para la imagen del perfil */
    .profile-image {
        width: 30px;
        /* Tamaño de la imagen */
        height: 30px;
        /* Altura de la imagen */
        border-radius: 50%;
        /* Hace que la imagen sea un círculo */
        margin-right: 5px;
        /* Espacio entre la imagen y el texto */
    }
        /* Estilo para la imagen del producto en el carrito */
        .cart-product-image {
        width: 40px;
        height: 40px;
        margin-right: 10px;
        display: block;
    }
</style>

<!-- Iconos de Bootstrap para el carrito -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="/index.php">
            <img src="/0images/favicon-32x32.png" alt="Logo" width="30" height="30" class="d-inline-block align-text-top">
            Frutería del Barrio
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/index.php">
                        <i class="bi bi-box-seam"></i> Productos
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto">
				<?php
					if (!empty($_SESSION["UserRol"])) {
						?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="/0images/default_user.png" alt="User" class="profile-image">
								<?php echo $NameToDisplay ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">

                            <?php if ($isAdmin) : ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="/8rol_admin/Rol_Admin.php"><i class="bi bi-shield-lock"></i> Panel de Administrador</a>
                                </li>
                            <?php endif; ?>

                                <li><a class="dropdown-item" href="/4profile/main_profile.php"><i class="bi bi-person-circle"></i> Perfil</a></li>
                                <li><a class="dropdown-item" href="/7rol_support/CreateTicket.php"><i class="bi bi-ticket-perforated"></i> Tickets</a></li>
                                <?php
									if ($_SESSION["UserRol"] == "S") {
										?>
                                        <li><a class="dropdown-item" href="/7rol_support/RolSupport.php"><i class="bi bi-tools"></i> Gestión de tickets</a></li>
										<?php
									}
								?>
                                <li><a class="dropdown-item" href="/logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a></li>
                            </ul>
                        </li>
						<?php
					} else {
						?>
                        <li class="nav-item">
                            <a class="nav-link" href="/3register/register.form.php">
                                <i class="bi bi-person-plus"></i> Registro
                            </a>
                        </li>
                        <li class="nav-item">
                             <a class="nav-link" href="/1login/login.php"><i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                             <a class="nav-link" href="/1login/login.php">
                                <img src="/0images/default_user.png" alt="User" class="profile-image">
								<?php echo $NameToDisplay ?>
                            </a>
                        </li>
						<?php
					}
				?>
            </ul>
             <!-- Carrito de compra -->
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle d-flex align-items-center" type="button" id="dropdownCartButton" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-cart-fill me-2"></i> Carrito
                    <span class="badge bg-secondary ms-2"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end p-3" aria-labelledby="dropdownCartButton" style="width: 350px;">
					<?php
						// Comprueba si hay productos en el carrito
						if (!empty($_SESSION['cart'])) :
							$productIds = array_keys($_SESSION['cart']);
							if (!empty($productIds)) {
								$placeholders = implode(',', array_fill(0, count($productIds), '?'));
								$stmt = $conn->prepare("SELECT prd_id, prd_name, prd_price, prd_image FROM pps_products WHERE prd_id IN ($placeholders)");
								$stmt->execute($productIds);
								$cartProducts = $stmt->fetchAll();
							}
							foreach ($cartProducts as $product) : // Muestra los productos en el carrito
					?>
                                <li class="dropdown-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo htmlspecialchars($product['prd_image']); ?>" alt="<?php echo htmlspecialchars($product['prd_name']); ?>" class="cart-product-image">
                                        <div class="d-flex flex-column">
                                            <span><?php echo htmlspecialchars($product['prd_name']); ?></span>
                                            <small class="text-muted"><?php echo number_format($product['prd_price'] * $_SESSION['cart'][$product['prd_id']], 2); ?> €</small>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-primary rounded-pill me-2"><?php echo $_SESSION['cart'][$product['prd_id']]; ?></span>
                                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                                            <input type="hidden" name="remove_product_id" value="<?php echo $product['prd_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger rounded-circle d-flex justify-content-center align-items-center p-0" style="width: 24px; height: 24px;">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    </div>
                                </li>
							<?php endforeach; ?>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-center" href="/9shopping/cart.php">Ver Carrito</a></li>
						<?php else : // Muestra cuando no hay productos en el carrito ?>
                            <li class="dropdown-item text-center">No hay productos en el carrito.</li>
						<?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</nav>

<script src="/vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>