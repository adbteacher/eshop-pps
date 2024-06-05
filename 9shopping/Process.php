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

$purchase_success = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_purchase'])) {
    if (isset($_POST['terms']) && isset($_POST['payment_method'])) {
        // Inicializar el carrito
        $cart = $_SESSION['cart'] ?? [];

        if (!empty($cart)) {
            try {
                // Iniciar transacción
                $conn->beginTransaction();

                foreach ($cart as $productId => $quantity) {
                    // Verificar el stock actual
                    $stmt = $conn->prepare("SELECT prd_stock FROM pps_products WHERE prd_id = ?");
                    $stmt->execute([$productId]);
                    $product = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($product && $product['prd_stock'] >= $quantity) {
                        // Actualizar el stock del producto
                        $newStock = $product['prd_stock'] - $quantity;
                        $stmt = $conn->prepare("UPDATE pps_products SET prd_stock = ? WHERE prd_id = ?");
                        $stmt->execute([$newStock, $productId]);
                    } else {
                        throw new Exception("Stock insuficiente para el producto ID: $productId");
                    }
                }

                // Confirmar transacción
                $conn->commit();

                // Limpiar el carrito
                unset($_SESSION['cart']);

                // Marcar compra como exitosa
                $purchase_success = true;
            } catch (Exception $e) {
                // Revertir transacción en caso de error
                $conn->rollBack();
                $error_message = "Error al procesar la compra: " . $e->getMessage();
            }
        } else {
            $error_message = "El carrito está vacío.";
        }
    } else {
        $error_message = "Donde vas Perro del Estado, esto no es comunismo. Debes aceptar los términos y condiciones y seleccionar un método de pago.";
    }
} else {
    header("Location: Checkout.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Compra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include "../nav.php"; // Incluye el Navbar ?>
    <div class="container mt-5">
        <?php if ($purchase_success): ?>
            <h1>Compra Realizada Exitosamente</h1>
            <div class="alert alert-success">
                ¡Gracias por tu compra! Tu pedido ha sido procesado exitosamente.
            </div>
            <a href="products.php" class="btn btn-primary">Volver a Productos</a>
        <?php else: ?>
            <h1>Error en la Confirmación de la Compra</h1>
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            <a href="Checkout.php" class="btn btn-primary">Volver a Intentar</a>
        <?php endif; ?>
    </div>
    <?php include "../footer.php"; // Incluye el footer ?>
</body>
</html>
