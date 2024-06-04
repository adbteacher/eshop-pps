<?php
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}

	require_once(__DIR__ . "/autoload.php");

	// Conexión a la base de datos
	$conn = database::LoadDatabase();

	// Consulta para obtener el rol del usuario
	$stmt = $conn->prepare("SELECT usu_rol FROM pps_users WHERE usu_id = ?");
	$stmt->execute([$_SESSION["UserID"]]);
	$user = $stmt->fetch(PDO::FETCH_ASSOC);

	$isAdmin = false;
	$isVendor = false;

	if ($user) {
		if ($user['usu_rol'] === 'A') {
			$isAdmin = true; // Verificar si el usuario tiene el rol "A"
		}
		if ($user['usu_rol'] === 'V') {
			$isVendor = true; // Verificar si el usuario tiene el rol "V"
		}
	}

	if ($_SESSION["UserID"]) {
		$NameToDisplay = $_SESSION["UserName"];
	} else {
		$NameToDisplay = "Invitado";
	}

	// Manejar la lógica de eliminación del carrito
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_product_id'])) {
		if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
			die("Error: Invalid CSRF token");
		}

		$removeProductId = (int)$_POST['remove_product_id'];

		if (isset($_SESSION['cart'][$removeProductId])) {
			unset($_SESSION['cart'][$removeProductId]);
		}

		// Redirigir para evitar reenvío de formularios
		header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']));
		exit();
	}
?>
<style>
    /* Estilo para la imagen del perfil */
    .profile-image {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        margin-right: 5px;
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
<link href="/vendor/twbs/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="/vendor/fortawesome/font-awesome/css/all.min.css" rel="stylesheet">

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
                <?php if ($isVendor): ?>
                <li class="nav-item">
                    <a class="nav-link" href="/5rol_vendor/mainpage.php">
                        <i class="bi bi-box-seam"></i> Manage
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav ms-auto">
				<?php if (!empty($_SESSION["UserRol"])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="/0images/default_user.png" alt="User" class="profile-image">
						<?php echo $NameToDisplay ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown" style="width: 230px;">
                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
							<?php if ($isAdmin): ?>
                            <li>
                                <a class="dropdown-item" href="/8rol_admin/Rol_Admin.php"><i class="bi bi-shield-lock"></i> Panel de Administrador</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
							<?php endif; ?>
							<?php if ($_SESSION["UserRol"] == "S"): ?>
                            <li>
                                <a class="dropdown-item" href="/7rol_support/RolSupport.php"><i class="bi bi-tools"></i> Gestión de tickets</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
							<?php endif; ?>
                            <li>
                                <a class="dropdown-item" href="/4profile/main_profile.php"><i class="bi bi-person-circle"></i> Perfil</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/7rol_support/CreateTicket.php"><i class="bi bi-ticket-perforated"></i> Tickets</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="/logout.php"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
                            </li>
                        </form>
                    </ul>
                </li>
				<?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="/3register/register.form.php">
                        <i class="bi bi-person-plus"></i> Registro
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/1login/login.php"><i class="bi bi-box-arrow-in-right"></i> Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/4profile/main_profile.php">
                        <img src="/0images/default_user.png" alt="User" class="profile-image">
						<?php echo $NameToDisplay ?>
                    </a>
                </li>
				<?php endif; ?>
            </ul>
            <!-- Carrito de compra -->
            <div class="dropdown ms-2">
                <button class="btn btn-secondary dropdown-toggle d-flex align-items-center" type="button" id="dropdownCartButton" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-cart-fill me-2"></i> Carrito
                    <span class="badge bg-secondary ms-2"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end p-3" aria-labelledby="dropdownCartButton" style="width: auto;">
					<?php
					// Comprueba si hay productos en el carrito
					if (!empty($_SESSION['cart'])) :
						$productIds = array_keys($_SESSION['cart']);
						if (!empty($productIds)) {
							$placeholders = implode(',', array_fill(0, count($productIds), '?'));
							$stmt         = $conn->prepare("SELECT prd_id, prd_name, prd_price, prd_image FROM pps_products WHERE prd_id IN ($placeholders)");
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
                                <div class="d-flex align-items-center ms-3 mb-3">
                                    <span class="badge bg-primary rounded-pill me-2"><?php echo $_SESSION['cart'][$product['prd_id']]; ?></span>
                                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                                        <input type="hidden" name="remove_product_id" value="<?php echo $product['prd_id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
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
