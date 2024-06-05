<?php
session_start();
require_once("../autoload.php");

// Verificar si el usuario está autenticado
if (!isset($_SESSION['UserID'])) {
    header("Location: ../1login/login.php");
    exit();
}

	// Conexión a la base de datos
	$conn = database::LoadDatabase();

	// Calcular los costos de envío
	$shippingCost = 1.10; // Costo fijo de envío

// Calcular total del carrito
$total = 0;
$cartProduct = [];
if (!empty($_SESSION['cart'])) {
    $productIds = array_keys($_SESSION['cart']);
    if (!empty($productIds)) {
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $stmt = $conn->prepare("SELECT prd_id, prd_name, prd_price, prd_offer_price, prd_image FROM pps_products WHERE prd_id IN ($placeholders)");
        $stmt->execute($productIds);
        $cartProduct = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($cartProduct as $product) {
            $price = !is_null($product['prd_offer_price']) ? $product['prd_offer_price'] : $product['prd_price'];
            $productTotal = $price * $_SESSION['cart'][$product['prd_id']];
            $total += $productTotal;
        }
    }
}

	// Inicializar descuento del cupón
	$couponDiscount = 0;

// Manejar la aplicación del cupón
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apply_coupon'])) {
    $couponCode = filter_input(INPUT_POST, 'coupon_code', FILTER_SANITIZE_STRING);

    // Validar el formato del cupón
    if (preg_match('/^[A-Z0-9]{1,6}$/', $couponCode)) {
        $stmt = $conn->prepare("SELECT * FROM pps_coupons WHERE cou_code = ? AND cou_is_used = 'N'");
        $stmt->execute([$couponCode]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($coupon) {
            // Aplicar descuento como porcentaje
            $couponDiscount = $total * ($coupon['cou_discount'] / 100);
            $stmt = $conn->prepare("UPDATE pps_coupons SET cou_is_used = 'Y' WHERE cou_id = ?");
            $stmt->execute([$coupon['cou_id']]);
        } else {
            $couponMessage = "Cupón inválido o ya utilizado.";
        }
    } else {
        $couponMessage = "El código del cupón debe tener máximo 6 caracteres y contener solo letras mayúsculas y números.";
    }
}

	// Calcular el total final incluyendo costos de envío y descuento del cupón
	$grandTotal = $total + $shippingCost - $couponDiscount;

// Manejar la confirmación de la compra
if ($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_POST['confirm_purchase']) || isset($_POST['bank_transfer']))) {
    if (isset($_POST['terms'])) {
        // Aquí puedes añadir la lógica para guardar la orden en la base de datos y procesar el pago
        // ...

			// Limpiar el carrito
			unset($_SESSION['cart']);

        // Redirigir a la página de métodos de pago o transferencia bancaria
        if (isset($_POST['bank_transfer'])) {
            header("Location: Bank_Transfer.html");
        } else {
            header("Location: Payment_Methods.php");
        }
        exit();
    } else {
        $error_message = "Debes aceptar los términos y condiciones.";
    }
}

// Obtener los datos del usuario de la base de datos
$user_id = $_SESSION['UserID'];
$stmt = $conn->prepare("SELECT usu_name, usu_surnames, usu_email, usu_phone FROM pps_users WHERE usu_id = ?");
$stmt->execute([$user_id]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener los datos del usuario
$user_name = htmlspecialchars($user_data['usu_name'] ?? 'N/A');
$user_surnames = htmlspecialchars($user_data['usu_surnames'] ?? 'N/A');
$user_email = htmlspecialchars($user_data['usu_email'] ?? 'N/A');
$user_phone = htmlspecialchars($user_data['usu_phone'] ?? 'N/A');

// Obtener el método principal del usuario
$stmt = $conn->prepare("SELECT pmu_payment_method FROM pps_payment_methods_per_user WHERE pmu_user = ? AND pmu_is_main = 1");
$stmt->execute([$user_id]);
$main_method = $stmt->fetchColumn();

// Convertir el código del método a un texto legible
$main_method_text = ($main_method == 1) ? "Tarjeta de Crédito" : "PayPal";

// Obtener la dirección principal del usuario
$stmt = $conn->prepare("SELECT adr_line1, adr_line2, adr_city, adr_state, adr_postal_code, adr_country FROM pps_addresses_per_user WHERE adr_user = ? AND adr_is_main = 1");
$stmt->execute([$user_id]);
$main_address = $stmt->fetch(PDO::FETCH_ASSOC);

$main_address_text = $main_address ? 
    htmlspecialchars("{$main_address['adr_line1']}, " . 
    ($main_address['adr_line2'] ? "{$main_address['adr_line2']}, " : '') . 
    "{$main_address['adr_city']}, {$main_address['adr_state']}, {$main_address['adr_postal_code']}, {$main_address['adr_country']}") : 
    "No hay dirección principal asignada";

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Compra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .price-original {
            text-decoration: line-through;
        }
    </style>
</head>
<body>
    <?php include "../nav.php"; // Incluye el Navbar ?>
    <div class="container mt-5">
        <h1>Confirmar Compra</h1>
        <?php if (isset($couponMessage)): ?>
            <div class="alert alert-warning">
                <?php echo htmlspecialchars($couponMessage); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="container">
            <div class="row">
                <!-- Columna para la información del usuario -->
                <div class="col-md-6">
                    <h4>Datos del Usuario</h4>
                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($user_name); ?></p>
                    <p><strong>Apellidos:</strong> <?php echo htmlspecialchars($user_surnames); ?></p>
                    <p><strong>Correo Electrónico:</strong> <?php echo htmlspecialchars($user_email); ?></p>
                    <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($user_phone); ?></p>
                    <!-- Agrega más información del usuario aquí si es necesario -->
                </div>
                <!-- Columna para el método de pago principal y dirección -->
                <div class="col-md-6">
                    <h4>Método de Pago Principal</h4>
                    <p><?php echo htmlspecialchars($main_method_text); ?></p>
                    <a href="../4profile/payment_methods.php" class="btn btn-primary">Cambiar Método de Pago</a>
                    <!-- Dirección principal -->
                    <h4 class="mt-4">Dirección de Envío Principal</h4>
                    <p><?php echo htmlspecialchars($main_address_text); ?></p>
                    <a href="../4profile/usu_address.php" class="btn btn-primary">Cambiar Dirección de Envío</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <?php if (!empty($cartProduct)): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <br>
                                <th>Imagen</th>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Cantidad</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartProduct as $product): ?>
                                <tr>
                                    <td><img src="<?php echo htmlspecialchars($product['prd_image']); ?>" alt="<?php echo htmlspecialchars($product['prd_name']); ?>" style="width: 50px; height: 50px;"></td>
                                    <td><?php echo htmlspecialchars($product['prd_name']); ?></td>
                                    <td>
                                        <?php if (!is_null($product['prd_offer_price'])): ?>
                                            <span class="price-original"><?php echo htmlspecialchars(number_format($product['prd_price'], 2)); ?>€</span>
                                            <span class="price-discounted"><?php echo htmlspecialchars(number_format($product['prd_offer_price'], 2)); ?>€</span>
                                        <?php else: ?>
                                            <?php echo htmlspecialchars(number_format($product['prd_price'], 2)); ?>€
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($_SESSION['cart'][$product['prd_id']]); ?></td>
                                    <td><?php echo htmlspecialchars(number_format(($product['prd_offer_price'] ?? $product['prd_price']) * $_SESSION['cart'][$product['prd_id']], 2)); ?>€</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-between">
                        <h4>Subtotal: <?php echo htmlspecialchars(number_format($total, 2)); ?>€</h4>
                        <h4>Descuento: <?php echo htmlspecialchars(number_format($couponDiscount, 2)); ?>€</h4>
                        <h4>Costo de envío: <?php echo htmlspecialchars(number_format($shippingCost, 2)); ?>€</h4>
                        <h4>Total: <?php echo htmlspecialchars(number_format($grandTotal, 2)); ?>€</h4>
                    </div>
                <?php else: ?>
                    <p>No hay productos en el carrito.</p>
                <?php endif; ?>

                <form action="Process.php" method="post" class="mt-3">
    <div class="form-group">
        <label for="coupon_code">Código de Cupón:</label>
        <input type="text" class="form-control" id="coupon_code" name="coupon_code" maxlength="6" pattern="[A-Z0-9]{1,6}" title="Máximo 6 caracteres, solo letras mayúsculas y números.">
        <button type="submit" name="apply_coupon" class="btn btn-primary mt-2">Aplicar Cupón</button>
    </div>
    <div class="form-check mt-3">
        <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
        <label class="form-check-label" for="terms">He leído y acepto los <a href="Terms.php">términos y condiciones</a>.</label>
    </div>
    <button type="submit" name="confirm_purchase" class="btn btn-success mt-3">Confirmar Compra</button>
    <button type="submit" name="bank_transfer" class="btn btn-secondary mt-3">Transferencia Bancaria</button>
</form>

            </div>
        </div>
    </div>
    <?php include "../footer.php"; // Incluye el footer ?>
</body>
</html>
