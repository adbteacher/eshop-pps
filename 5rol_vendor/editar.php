<!DOCTYPE html>
<html>
<head>
    <title>Editar Producto</title>
    <link href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include "../nav.php"; // Incluye el Navbar ?>
<h1>Editar Producto</h1>
<?php
	if (session_status() == PHP_SESSION_NONE)
	{
		session_start();
	}

	require_once '../autoload.php';
	require_once 'biblioteca.php';

	// Verificar si el usuario está autenticado
	functions::ActiveSession();

	//Comprobar permisos al programa
	functions::HasPermissions(basename(__FILE__));

	// Generar y almacenar el token CSRF si no existe
	if (empty($_SESSION['csrf_token']))
	{
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}

	// Función para redireccionar con un mensaje
	function redireccionar($mensaje, $url)
	{
		echo "<p>$mensaje</p>";
		echo "<p><a href='$url'>Volver</a></p>";
		exit();
		exit();
	}

	if (isset($_GET['id']))
	{
		$id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

		if ($_SERVER["REQUEST_METHOD"] == "POST")
		{
			// Validar el token CSRF
			if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']))
			{
				echo "<p class='error'>Error, vuelva a intentarlo más tarde.</p>";
				error_log("Error en la validación CSRF.");
			}
			else
			{
				if (isset($_POST['Volver']))
				{
					header('Location: mainpage.php');
					exit();
				}

				$nombre    = filter_var($_POST['nombre'], FILTER_UNSAFE_RAW);
				$categoria = filter_var($_POST['categoria'], FILTER_SANITIZE_NUMBER_INT);
				$detalles  = filter_var($_POST['detalles'], FILTER_UNSAFE_RAW);
				$precio    = filter_var($_POST['precio'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
				$stock     = filter_var($_POST['stock'], FILTER_SANITIZE_NUMBER_INT);

				$campos_vacios = [];
				if (empty($nombre))
				{
					$campos_vacios[] = "nombre";
				}
				if (empty($categoria))
				{
					$campos_vacios[] = "categoria";
				}
				if (empty($detalles))
				{
					$campos_vacios[] = "detalles";
				}
				if (empty($precio))
				{
					$campos_vacios[] = "precio";
				}
				if (empty($stock))
				{
					$campos_vacios[] = "stock";
				}

				if (!empty($campos_vacios))
				{
					echo "<p class='error'>Por favor, complete los siguientes campos: " . htmlspecialchars(implode(', ', $campos_vacios)) . "</p>";
				}
				else
				{
					$conn        = GetDatabaseConnection();
					$consultaSQL = "UPDATE pps_products SET prd_name = :nombre, prd_category = :categoria, prd_details = :detalles, prd_price = :precio, prd_stock = :stock WHERE prd_id = :id";
					$stmt        = $conn->prepare($consultaSQL);
					$stmt->bindParam(':nombre', $nombre);
					$stmt->bindParam(':categoria', $categoria);
					$stmt->bindParam(':detalles', $detalles);
					$stmt->bindParam(':precio', $precio);
					$stmt->bindParam(':stock', $stock);
					$stmt->bindParam(':id', $id);
					$stmt->execute();

					if ($stmt->rowCount() > 0)
					{
						redireccionar("La información ha sido actualizada.", "mainpage.php");
					}
					else
					{
						echo "<p>No se pudo actualizar la información.</p>";
					}
				}
			}
		}

		$conn        = GetDatabaseConnection();
		$consultaSQL = "SELECT * FROM pps_products WHERE prd_id = :id";
		$stmt        = $conn->prepare($consultaSQL);
		$stmt->bindParam(':id', $id);
		$stmt->execute();
		$producto = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($producto)
		{
			?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . htmlspecialchars($id); ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($producto['prd_id']); ?>">
                <label for="nombre">Nombre:</label>

                <label>
                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($producto['prd_name']); ?>">
                </label>
                <br>
                <label for="categoria">Categoría:</label>

                <label>
                    <select class="form-select" name="categoria">
                        <option value="0" <?php if (!empty($_POST['categoria']) && $_POST['categoria'] == '0')
                        {
                            echo 'selected';
                        } ?>></option>
                        <?php
                            $stmt = $conn->query("SELECT cat_id, cat_description FROM pps_categories");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
                            {
                                echo "<option value='" . htmlspecialchars($row['cat_id']) . "'";
                                if (!empty($_POST['categoria']) && $_POST['categoria'] == $row['cat_id'])
                                {
                                    echo ' selected';
                                }
                                if ($producto['prd_category'] == $row['cat_id'])
                                {
                                    echo ' selected';
                                }
                                echo ">" . htmlspecialchars($row['cat_description']) . "</option>";
                            }
                        ?>
                    </select>
                </label>
                <br>
                <label for="detalles">Detalles:</label>
                <input type="text" name="detalles" value="<?php echo htmlspecialchars($producto['prd_details']); ?>">
                <br>
                <label for="precio">Precio:</label>
                <input type="text" name="precio" value="<?php echo htmlspecialchars($producto['prd_price']); ?>">
                <br>
                <label for="stock">Stock:</label>
                <input type="text" name="stock" value="<?php echo htmlspecialchars($producto['prd_stock']); ?>">
                <br>
                <input type="submit" value="Actualizar">
                <input type="submit" name="Volver" value="Volver" class="boton">
            </form>
			<?php
		}
		else
		{
			echo "<p>No se encontró el producto.</p>";
		}
	}
	else
	{
		echo "<p>No se proporcionó un ID de producto.</p>";
	}
?>
<?php include "../footer.php"; // Incluye el footer ?>
</body>
</html>
