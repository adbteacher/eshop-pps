<!DOCTYPE html>
<html>
<head>
    <title>Editar Producto</title>
  <link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include "../nav.php"; // Incluye el Navbar
?>
    <h1>Editar Producto</h1>
    <?php
    session_start();
    require_once '../autoload.php';
    require_once 'biblioteca.php';
    
    //AddSecurityHeaders();

    function redireccionar($mensaje, $url) {
        echo "<p>$mensaje</p>";
        echo "<p><a href='$url'>Volver</a></p>";
        exit();
    }

    if (isset($_GET['id'])) {
        $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST['Volver'])) {
                header('Location: mainpage.php');
                exit();
            }

            $nombre = filter_var($_POST['nombre'], FILTER_SANITIZE_STRING);
            $categoria = filter_var($_POST['categoria'], FILTER_SANITIZE_NUMBER_INT);
            $detalles = filter_var($_POST['detalles'], FILTER_SANITIZE_STRING);
            $precio = filter_var($_POST['precio'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $cantidad = filter_var($_POST['cantidad'], FILTER_SANITIZE_NUMBER_INT);
            $stock = filter_var($_POST['stock'], FILTER_SANITIZE_NUMBER_INT);
            
            $campos_vacios = [];
            if (empty($nombre)) $campos_vacios[] = "nombre";
            if (empty($categoria)) $campos_vacios[] = "categoria";
            if (empty($detalles)) $campos_vacios[] = "detalles";
            if (empty($precio)) $campos_vacios[] = "precio";
            if (empty($cantidad)) $campos_vacios[] = "cantidad";
            if (empty($stock)) $campos_vacios[] = "stock";

            if (!empty($campos_vacios)) {
                echo "<p class='error'>Por favor, complete los siguientes campos: " . htmlspecialchars(implode(', ', $campos_vacios)) . "</p>";
            } else {
                $conn = GetDatabaseConnection();
                $consultaSQL = "UPDATE pps_products SET prd_name = :nombre, prd_category = :categoria, prd_details = :detalles, prd_price = :precio, prd_quantity_shop = :cantidad, prd_stock = :stock WHERE prd_id = :id";
                $stmt = $conn->prepare($consultaSQL);
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':categoria', $categoria);
                $stmt->bindParam(':detalles', $detalles);
                $stmt->bindParam(':precio', $precio);
                $stmt->bindParam(':cantidad', $cantidad);
                $stmt->bindParam(':stock', $stock);
                $stmt->bindParam(':id', $id);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    redireccionar("La información ha sido actualizada.", "mainpage.php");
                } else {
                    echo "<p>No se pudo actualizar la información.</p>";
                }
            }
        }

        $conn = GetDatabaseConnection();
        $consultaSQL = "SELECT * FROM pps_products WHERE prd_id = :id";
        $stmt = $conn->prepare($consultaSQL);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($producto) {
    ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . htmlspecialchars($id); ?>" method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($producto['prd_id']); ?>">
                <label for="nombre">Nombre:</label>
                <input type="text" name="nombre" value="<?php echo htmlspecialchars($producto['prd_name']); ?>">
                <label for="categoria">Categoría:</label>
                <select name="categoria">
                    <option value=""></option>
                    <option value="1" <?php if ($producto['prd_category'] == 1) echo "selected"; ?>>Fruta</option>
                    <option value="2" <?php if ($producto['prd_category'] == 2) echo "selected"; ?>>Verdura</option>
                </select>
                <label for="detalles">Detalles:</label>
                <input type="text" name="detalles" value="<?php echo htmlspecialchars($producto['prd_details']); ?>">
                <label for="precio">Precio:</label>
                <input type="text" name="precio" value="<?php echo htmlspecialchars($producto['prd_price']); ?>">
                <label for="cantidad">Cantidad en Tienda:</label>
                <input type="text" name="cantidad" value="<?php echo htmlspecialchars($producto['prd_quantity_shop']); ?>">
                <label for="stock">Stock:</label>
                <input type="text" name="stock" value="<?php echo htmlspecialchars($producto['prd_stock']); ?>">
                <input type="submit" value="Actualizar">
                <input type="submit" name="Volver" value="Volver" class="boton">
            </form>
    <?php
        } else {
            echo "<p>No se encontró el producto.</p>";
        }
    } else {
        echo "<p>No se proporcionó un ID de producto.</p>";
    }
    ?>
<?php include "../footer.php"; // Incluye el footer
?>
</body>
</html>
