<!DOCTYPE html>
<html>
<head>
    <title>Editar Producto</title>
    <link rel="stylesheet" href="estilos/style.css?v=0.0.1">
    <style>
        .error {
            color: red;
        }
        form {
            display: flex;
            flex-direction: column;
            max-width: 300px;
        }
        form label {
            margin-bottom: 5px;
        }
        form input[type="text"],
        form select {
            margin-bottom: 10px;
            padding: 5px;
        }
        form input[type="submit"] {
            margin-top: 10px;
            padding: 5px 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Editar Producto</h1>

    <?php
    session_start();

    // Función para imprimir un mensaje de éxito y redirigir
    function redireccionar($mensaje, $url) {
        echo "<script>alert('$mensaje'); window.location.href='" . htmlspecialchars($url) . "';</script>";
        exit();
    }

    // Verificar si se ha proporcionado un ID de producto
    if (isset($_GET['id'])) {
        // Obtener el ID del producto de la URL y sanitizarlo
        $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

        // Establecemos una conexión a la base de datos
        include "biblioteca.php";
        $conn = connection();

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
            // Verificar que no haya campos vacíos
            $nombre = filter_var($_POST['nombre'], FILTER_SANITIZE_STRING);
            $categoria = filter_var($_POST['categoria'], FILTER_SANITIZE_NUMBER_INT);
            $detalles = filter_var($_POST['detalles'], FILTER_SANITIZE_STRING);
            $precio = filter_var($_POST['precio'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $cantidad = filter_var($_POST['cantidad'], FILTER_SANITIZE_NUMBER_INT);
            $stock = filter_var($_POST['stock'], FILTER_SANITIZE_NUMBER_INT);
            
            $campos_vacios = [];
            if (empty($nombre)) {
                $campos_vacios[] = "nombre";
            }
            if (empty($categoria)) {
                $campos_vacios[] = "categoria";
            }
            if (empty($detalles)) {
                $campos_vacios[] = "detalles";
            }
            if (empty($precio)) {
                $campos_vacios[] = "precio";
            }
            if (empty($cantidad)) {
                $campos_vacios[] = "cantidad";
            }
            if (empty($stock)) {
                $campos_vacios[] = "stock";
            }

            if (!empty($campos_vacios)) {
                echo "<p class='error'>Por favor, complete los siguientes campos: " . htmlspecialchars(implode(', ', $campos_vacios)) . "</p>";
            } else {
                // Actualizar los datos en la base de datos
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

                // Verificar si se actualizó algún registro
                if ($stmt->rowCount() > 0) {
                    // Mostrar mensaje de éxito y redirigir a mainpage.php
                    redireccionar("La información ha sido actualizada.", "mainpage.php");
                } else {
                    echo "<p>No se pudo actualizar la información.</p>";
                }
            }
        }

        // Realizamos una consulta para obtener los detalles del producto
        $consultaSQL = "SELECT * FROM pps_products WHERE prd_id = :id";
        $stmt = $conn->prepare($consultaSQL);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($producto) {
    ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . htmlspecialchars($id); ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
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
                <input type="submit" name="Volver" value="Volver" formaction="mainpage.php" class="boton">
            </form>
    <?php
        } else {
            echo "<p>No se encontró el producto.</p>";
        }
    } else {
        echo "<p>No se proporcionó un ID de producto.</p>";
    }

    // Cerramos la conexión a la base de datos
    cerrar_conexion($conn);

    // Generar un nuevo token CSRF para la próxima solicitud
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    ?>
</body>
</html>
