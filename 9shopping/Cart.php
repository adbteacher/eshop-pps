<?php
session_start();
require_once("../autoload.php");

$conn = database::LoadDatabase();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Eliminar producto del carrito
    if (isset($_POST['remove_product_id'])) {
        $removeProductId = $_POST['remove_product_id'];
        if (isset($_SESSION['cart'][$removeProductId])) {
            unset($_SESSION['cart'][$removeProductId]);
        }
    }

    // Actualizar cantidad del producto
    if (isset($_POST['update_product_id']) && isset($_POST['quantity'])) {
        $updateProductId = $_POST['update_product_id'];
        $quantity = $_POST['quantity'];
        if (isset($_SESSION['cart'][$updateProductId])) {
            $_SESSION['cart'][$updateProductId] = $quantity;
        }
    }

    // Redirigir para evitar reenvío de formularios
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
    <link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<?php include "../nav.php"; ?>

<div class="container mt-4">
    <h1>Carrito de Compras</h1>
    <?php if (!empty($_SESSION['cart'])): ?>
        <?php
        $productIds = array_keys($_SESSION['cart']);
        if (!empty($productIds)) {
            $placeholders = implode(',', array_fill(0, count($productIds), '?'));
            $stmt = $conn->prepare("SELECT prd_id, prd_name, prd_price, prd_image FROM pps_products WHERE prd_id IN ($placeholders)");
            $stmt->execute($productIds);
            $cartProducts = $stmt->fetchAll();
        }
        ?>
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
                <?php foreach ($cartProducts as $product): ?>
                    <tr>
                        <td>
                            <img src="<?php echo htmlspecialchars($product['prd_image']); ?>" alt="<?php echo htmlspecialchars($product['prd_name']); ?>" style="width: 50px; height: 50px;">
                        </td>
                        <td><?php echo htmlspecialchars($product['prd_name']); ?></td>
                        <td><?php echo number_format($product['prd_price'], 2); ?>€</td>
                        <td>
                            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="d-inline">
                                <input type="number" name="quantity" value="<?php echo $_SESSION['cart'][$product['prd_id']]; ?>" min="1" class="form-control d-inline-block w-auto">
                                <input type="hidden" name="update_product_id" value="<?php echo $product['prd_id']; ?>">
                                <button type="submit" class="btn btn-primary btn-sm">Actualizar</button>
                            </form>
                        </td>
                        <td><?php echo number_format($product['prd_price'] * $_SESSION['cart'][$product['prd_id']], 2); ?>€</td>
                        <td>
                            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="d-inline">
                                <input type="hidden" name="remove_product_id" value="<?php echo $product['prd_id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="text-end">
            <h4>Total: 
                <?php
                $total = 0;
                foreach ($cartProducts as $product) {
                    $total += $product['prd_price'] * $_SESSION['cart'][$product['prd_id']];
                }
                echo number_format($total, 2);
                ?>€
            </h4>
            <a href="checkout.php" class="btn btn-success">Proceder al Pago</a>
        </div>
    <?php else: ?>
        <p>No hay productos en el carrito.</p>
    <?php endif; ?>
</div>

<script src="../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
