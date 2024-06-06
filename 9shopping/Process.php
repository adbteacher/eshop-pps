<?php
session_start();
require_once("../autoload.php");
require_once("../Mail_config.php");

// Añadir las líneas necesarias para PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
    if (isset($_POST['terms'])) {
        // Obtener el método de pago principal del usuario
        $user_id = $_SESSION['UserID'];
        $stmt = $conn->prepare("SELECT pmu_payment_method FROM pps_payment_methods_per_user WHERE pmu_user = ? AND pmu_is_main = 1");
        $stmt->execute([$user_id]);
        $main_method = $stmt->fetchColumn();

        if ($main_method) {
            // Inicializar el carrito
            $cart = $_SESSION['cart'] ?? [];

            if (!empty($cart)) {
                try {
                    // Iniciar transacción
                    $conn->beginTransaction();

                    // Obtener la dirección de envío principal del usuario
                    $stmt = $conn->prepare("SELECT adr_line1, adr_line2, adr_city, adr_state, adr_postal_code, adr_country FROM pps_addresses_per_user WHERE adr_user = ? AND adr_is_main = 1");
                    $stmt->execute([$user_id]);
                    $main_address = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$main_address) {
                        throw new Exception("No se encontró una dirección principal para el usuario.");
                    }

                    $shipping_address = htmlspecialchars("{$main_address['adr_line1']}, " .
                        ($main_address['adr_line2'] ? "{$main_address['adr_line2']}, " : '') .
                        "{$main_address['adr_city']}, {$main_address['adr_state']}, {$main_address['adr_postal_code']}, {$main_address['adr_country']}");

                    // Insertar orden en pps_orders
                    $stmt = $conn->prepare("INSERT INTO pps_orders (ord_user_id, ord_purchase_date, ord_order_status, ord_shipping_address) VALUES (?, NOW(), 'Creado', ?)");
                    $stmt->execute([$user_id, $shipping_address]);
                    $order_id = $conn->lastInsertId();

                    foreach ($cart as $productId => $quantity) {
                        // Verificar el stock actual
                        $stmt = $conn->prepare("SELECT prd_stock, prd_price, prd_offer_price FROM pps_products WHERE prd_id = ?");
                        $stmt->execute([$productId]);
                        $product = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($product && $product['prd_stock'] >= $quantity) {
                            // Actualizar el stock del producto
                            $newStock = $product['prd_stock'] - $quantity;
                            $stmt = $conn->prepare("UPDATE pps_products SET prd_stock = ? WHERE prd_id = ?");
                            $stmt->execute([$newStock, $productId]);

                            // Calcular el precio unitario y el subtotal
                            $unit_price = $product['prd_offer_price'] ?? $product['prd_price'];
                            $subtotal = $unit_price * $quantity;

                            // Insertar detalle de orden en pps_order_details
                            $stmt = $conn->prepare("INSERT INTO pps_order_details (ord_det_order_id, ord_det_prod_id, qty, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
                            $stmt->execute([$order_id, $productId, $quantity, $unit_price, $subtotal]);
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

                    // Enviar correo de confirmación
                    $stmt = $conn->prepare("SELECT usu_email FROM pps_users WHERE usu_id = ?");
                    $stmt->execute([$user_id]);
                    $user_email = $stmt->fetchColumn();

                    if ($user_email) {
                        $mail = getMailer();
                        $mail->addAddress($user_email); // Añadir destinatario

                        // Contenido del correo
                        $mail->isHTML(true); // Set email format to HTML
                        $mail->Subject = 'Confirmación de Compra';
                        $mail->Body    = '¡Gracias por tu compra! Tu pedido ha sido procesado exitosamente. Aquí están los detalles de tu pedido: ...';
                        $mail->AltBody = '¡Gracias por tu compra! Tu pedido ha sido procesado exitosamente. Aquí están los detalles de tu pedido: ...';

                        // Intentar enviar el correo
                        try {
                            $mail->send();
                        } catch (Exception $e) {
                            error_log("Error al enviar correo: " . $mail->ErrorInfo);
                            throw new Exception("No se pudo enviar el correo de confirmación.");
                        }
                    } else {
                        throw new Exception("No se encontró el correo electrónico del usuario.");
                    }

                } catch (Exception $e) {
                    if ($conn->inTransaction()) {
                        $conn->rollBack();
                    }
                    $error_message = "Error al procesar la compra: " . $e->getMessage();
                }
            } else {
                $error_message = "El carrito está vacío.";
            }
        } else {
            $error_message = "Debes seleccionar un método de pago.";
        }
    } else {
        $error_message = "Debes aceptar los términos y condiciones.";
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
    <link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../vendor/fortawesome/font-awesome/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include "../nav.php"; // Incluye el Navbar ?>
    <div class="container mt-5">
        <?php if ($purchase_success): ?>
            <h1>Compra Realizada Exitosamente</h1>
            <div class="alert alert-success">
                ¡Gracias por tu compra! Tu pedido ha sido procesado exitosamente.
            </div>
            <a href="../10products/products.php" class="btn btn-primary">Volver a Productos</a>
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
