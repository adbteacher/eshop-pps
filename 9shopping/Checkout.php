<?php
session_start();
require_once("../autoload.php");

// Conexión a la base de datos
$conn = database::LoadDatabase();

// Calcular los costos de envío
$shippingCost = 1.10; // Costo fijo de envío

// Calcular total del carrito
$total = 0;
$productIds = array_keys($_SESSION['cart']);
if (!empty($productIds)) {
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $stmt = $conn->prepare("SELECT prd_id, prd_name, prd_price, prd_image FROM pps_products WHERE prd_id IN ($placeholders)");
    $stmt->execute($productIds);
    $cartProducts = $stmt->fetchAll();

    foreach ($cartProducts as $product) {
        $productTotal = $product['prd_price'] * $_SESSION['cart'][$product['prd_id']];
        $total += $productTotal;
    }
}

// Inicializar descuento del cupón
$couponDiscount = 0;

// Manejar la aplicación del cupón
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apply_coupon'])) {
    $couponCode = $_POST['coupon_code'];

    $stmt = $conn->prepare("SELECT * FROM pps_coupons WHERE cou_code = ? AND cou_is_used = 'N'");
    $stmt->execute([$couponCode]);
    $coupon = $stmt->fetch();

    if ($coupon) {
        // Aplicar descuento como porcentaje
        $couponDiscount = $total * ($coupon['cou_discount'] / 100);
        $stmt = $conn->prepare("UPDATE pps_coupons SET cou_is_used = 'Y' WHERE cou_id = ?");
        $stmt->execute([$coupon['cou_id']]);
    } else {
        $couponMessage = "Cupón inválido o ya utilizado.";
    }
}

// Calcular el total final incluyendo costos de envío y descuento del cupón
$grandTotal = $total + $shippingCost - $couponDiscount;

// Manejar la confirmación de la compra
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_purchase'])) {
    if (isset($_POST['terms'])) {
        // Aquí puedes añadir la lógica para guardar la orden en la base de datos y procesar el pago
        // ...

        // Limpiar el carrito
        unset($_SESSION['cart']);

        // Redirigir a la página de métodos de pago
        header("Location: Payment_Methods.php");
        exit();
    } else {
        $error_message = "Debes aceptar los términos y condiciones.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Compra</title>
    <link href="../autoload.php" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Confirmar Compra</h1>
    <div class="row">
        <div class="col-md-12">
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
            <p>
            <table class="table">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartProducts as $product): ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars($product['prd_image']); ?>" alt="<?php echo htmlspecialchars($product['prd_name']); ?>" style="width: 50px; height: 50px;"></td>
                            <td><?php echo htmlspecialchars($product['prd_name']); ?></td>
                            <td><?php echo number_format($product['prd_price'], 2); ?>€</td>
                            <td><?php echo $_SESSION['cart'][$product['prd_id']]; ?></td>
                            <td><?php echo number_format($product['prd_price'] * $_SESSION['cart'][$product['prd_id']], 2); ?>€</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="d-flex justify-content-between">
                <h4>Subtotal: <?php echo number_format($total, 2); ?>€</h4>
                <h4>Descuento: <?php echo number_format($couponDiscount, 2); ?>€</h4>
                <h4>Costo de envío: <?php echo number_format($shippingCost, 2); ?>€</h4>
                <h4>Total: <?php echo number_format($grandTotal, 2); ?>€</h4>
            </div>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="mt-3">
                <div class="form-group">
                    <label for="coupon_code">Código de Cupón:</label>
                    <input type="text" class="form-control" id="coupon_code" name="coupon_code">
                    <button type="submit" name="apply_coupon" class="btn btn-primary mt-2">Aplicar Cupón</button>
                </div>
                <p>
                <div class="form-check mt-3">
                    <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                    <label class="form-check-label" for="terms">He leído y acepto los <a href="Terms.php">términos y condiciones</a>.</label>
                </div>
                <p>
                <button type="submit" name="confirm_purchase" class="btn btn-success mt-3">Confirmar Compra</button>
                <a href="Bank_Transfer.html" class="btn btn-secondary">Transferencia Bancaria</a>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
