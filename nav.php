<?php
	session_start();

	require_once(__DIR__ . "/autoload.php");

	if ($_SESSION["UserID"])
	{
		$NameToDisplay = $_SESSION["UserName"];
	}
	else
	{
		$NameToDisplay = "Invitado";
	}


	// Conexión a la base de datos
	$conn = database::LoadDatabase();

	// Manejar la lógica de eliminación del carrito
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_product_id']))
	{
		$removeProductId = $_POST['remove_product_id'];

		if (isset($_SESSION['cart'][$removeProductId]))
		{
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
        width: 40px;
        /* Tamaño de la imagen */
        height: 40px;
        /* Altura de la imagen */
        border-radius: 50%;
        /* Hace que la imagen sea un círculo */
        margin-right: 5px;
        /* Espacio entre la imagen y el texto */
    }
</style>

<!-- Iconos de Bootstrap para el carrito -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="/index.php">Frutería del Barrio</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="#">Categorias</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Productos</a>
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
                                <li><a class="dropdown-item" href="/4profile/main_profile.php">Perfil</a></li>
                                <li><a class="dropdown-item" href="/7rol_support/CreateTicket.php">Tickets</a></li>
                                <?php
									if ($_SESSION["UserRol"] == "S") {
										?>
                                        <li><a class="dropdown-item" href="/7rol_support/RolSupport.php">Gestión de tickets</a></li>
										<?php
									}
								?>
                                <li><a class="dropdown-item" href="/logout.php">Cerrar sesión</a></li>
                            </ul>
                        </li>
						<?php
					} else {
						?>
                        <li class="nav-item">
                            <a class="nav-link" href="/1login/login.php">Login
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
							if (!empty($productIds))
							{
								$placeholders = implode(',', array_fill(0, count($productIds), '?'));
								$stmt         = $conn->prepare("SELECT prd_id, prd_name, prd_price FROM pps_products WHERE prd_id IN ($placeholders)");
								$stmt->execute($productIds);
								$cartProducts = $stmt->fetchAll();
							}
							foreach ($cartProducts as $product) : // Muestra los productos en el carrito
								?>
                                <li class="dropdown-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex flex-column">
                                        <span><?php echo htmlspecialchars($product['prd_name']); ?></span>
                                        <small class="text-muted"><?php echo number_format($product['prd_price'] * $_SESSION['cart'][$product['prd_id']], 2); ?>
                                            €</small>
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
                            <li><a class="dropdown-item text-center" href="cart.php">Ver Carrito</a></li>
						<?php else : // Muestra cuando no hay productos en el carrito
							?>
                            <li class="dropdown-item text-center">No hay productos en el carrito.</li>
						<?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</nav>

<script src="/vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>