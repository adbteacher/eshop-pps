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

// Inicializar variables para mensajes de error
$errorMessages = [];

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

// Manejar la lógica de actualización de cantidad
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product_id']) && isset($_POST['update_quantity'])) {
    $updateProductId = $_POST['update_product_id'];
    $updateQuantity = intval($_POST['update_quantity']);

    // Obtener la cantidad disponible en stock para el producto
    $stmt = $conn->prepare("SELECT prd_stock FROM pps_products WHERE prd_id = ?");
    $stmt->execute([$updateProductId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product && $updateQuantity > $product['prd_stock']) {
        // Si la cantidad solicitada excede el stock, agregar un mensaje de error
        $errorMessages[] = "La cantidad solicitada para el producto con ID $updateProductId excede el stock disponible.";
    } else {
        if (isset($_SESSION['cart'][$updateProductId])) {
            $_SESSION['cart'][$updateProductId] = $updateQuantity;
        }

        // Redirigir para evitar reenvío de formularios
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
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
        <h1>Carrito de Compras</h1>
        <?php if (!empty($errorMessages)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errorMessages as $message): ?>
                    <p><?php echo htmlspecialchars($message); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col-md-12">
                <?php if (!empty($_SESSION['cart'])): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Imagen</th>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Cantidad</th>
                                <th>Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total = 0;
                            $productIds = array_keys($_SESSION['cart']);
                            if (!empty($productIds)) {
                                $placeholders = implode(',', array_fill(0, count($productIds), '?'));
                                $stmt = $conn->prepare("SELECT prd_id, prd_name, prd_price, prd_offer_price, prd_image, prd_stock FROM pps_products WHERE prd_id IN ($placeholders)");
                                $stmt->execute($productIds);
                                $cartProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                foreach ($cartProducts as $product) {
                                    $isOnOffer = !is_null($product['prd_offer_price']);
                                    $price = $isOnOffer ? $product['prd_offer_price'] : $product['prd_price'];
                                    $productTotal = $price * $_SESSION['cart'][$product['prd_id']];
                                    $total += $productTotal;
                                    ?>
                                    <tr>
                                        <td><img src="<?php echo htmlspecialchars($product['prd_image']); ?>" alt="<?php echo htmlspecialchars($product['prd_name']); ?>" style="width: 50px; height: 50px;"></td>
                                        <td><?php echo htmlspecialchars($product['prd_name']); ?></td>
                                        <td>
                                            <?php if ($isOnOffer): ?>
                                                <span class="price-original"><?php echo number_format($product['prd_price'], 2); ?>€</span>
                                                <span><?php echo number_format($product['prd_offer_price'], 2); ?>€</span>
                                            <?php else: ?>
                                                <span><?php echo number_format($product['prd_price'], 2); ?>€</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="d-inline-block">
                                                <input type="number" name="update_quantity" value="<?php echo $_SESSION['cart'][$product['prd_id']]; ?>" min="1" max="<?php echo $product['prd_stock']; ?>" class="form-control form-control-sm" inputmode="numeric" pattern="[0-9]*">
                                                <input type="hidden" name="update_product_id" value="<?php echo $product['prd_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-primary mt-1">Actualizar</button>
                                            </form>
                                        </td>
                                        <td><?php echo number_format($productTotal, 2); ?>€</td>
                                        <td>
                                            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="d-inline-block">
                                                <input type="hidden" name="remove_product_id" value="<?php echo $product['prd_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } ?>
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-end">
                        <h4>Total: <?php echo number_format($total, 2); ?>€</h4>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <a href="Checkout.php" class="btn btn-success">Proceder al pago</a>
                    </div>
                <?php else: ?>
                    <p>No hay productos en el carrito.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include "../footer.php"; // Incluye el footer ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
