<?php
	require_once '../autoload.php'; // Incluye el archivo de conexión PDO
	require_once '../Functions.php';
	session_start();
	functions::checkAdminAccess();

	// Generar token anti-CSRF si no está definido
	if (empty($_SESSION['csrf_token']))
	{
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}

	// Obtener el ID del producto a modificar
	$idProducto = $_POST['idProducto'];

	// Obtener una conexión a la base de datos
	$conexion = database::LoadDatabase();

	try
	{
		// Preparar la consulta para obtener los datos del producto
		$query = "SELECT * FROM pps_products WHERE prd_id = ?";
		$stmt  = $conexion->prepare($query);
		$stmt->execute([$idProducto]);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$row)
		{
			echo "<div class='alert alert-danger'>Producto no encontrado.</div>";
			exit;
		}
	}
	catch (PDOException $e)
	{
		// Manejar cualquier excepción y mostrar un mensaje genérico
		echo "<div class='alert alert-danger'>Algo ha salido mal.</div>";
		exit;
	}

	// Consultar las categorías desde la base de datos
	$queryCategorias = "SELECT cat_id, cat_description FROM pps_categories";
	$stmtCategorias  = $conexion->query($queryCategorias);
	$categorias      = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);

	// Procesar el formulario si se ha enviado
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre']))
	{
		// Verificar el token CSRF
		if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']))
		{
			echo "<div class='alert alert-danger'>Error en la validación CSRF.</div>";
			exit;
		}

		$nombre      = $_POST['nombre'];
		$categoria   = $_POST['categoria'];
		$detalles    = $_POST['detalles'];
		$precio      = $_POST['precio'];
		$stock       = $_POST['stock'];
		$descripcion = $_POST['descripcion'];
		$on_offer    = $_POST['on_offer'];
		$offer_price = $_POST['offer_price'];
		$msg = [];

		// Validar si la categoría seleccionada existe
		$queryCategoria = "SELECT cat_id FROM pps_categories WHERE cat_id = ?";
		$stmtCategoria  = $conexion->prepare($queryCategoria);
		$stmtCategoria->execute([$categoria]);
		$categoriaExistente = $stmtCategoria->fetchColumn();

		if (!$categoriaExistente)
		{
			echo '<div class="alert alert-warning">La categoría seleccionada no existe.</div>';
		}
		else
		{
			// Procesar la imagen si se ha subido
			if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK)
			{
				$file_info = $_FILES['imagen'];
				$file_tmp  = $file_info['tmp_name'];
				$file_mime = mime_content_type($file_tmp);

				if (($file_mime == 'image/jpeg' || $file_mime == 'image/png') && exif_imagetype($file_tmp) != false)
				{
					// Modificar el nombre del archivo para incluir el nombre de la fruta
					$file_extension = pathinfo($file_info['name'], PATHINFO_EXTENSION);
					$file_name = $nombre . '.' . $file_extension;
					$ruta_imagen = '../0images/' . $file_name;
					if (!move_uploaded_file($file_tmp, $ruta_imagen))
					{
						$msg[] = 'Error al subir la imagen.';
					}
				}
				else
				{
					$msg[] = 'El archivo seleccionado no es una imagen válida.';
				}
			}
			else
			{
				// Si no se sube una nueva imagen, mantener la existente
				$ruta_imagen = $row['prd_image'];
			}

			// Si no hay errores, proceder con la actualización en la base de datos
			if (empty($msg))
			{
				$query = "UPDATE pps_products SET prd_name = ?, prd_category = ?, prd_details = ?, prd_price = ?, prd_stock = ?, prd_image = ?, prd_description = ?, prd_on_offer = ?, prd_offer_price = ? WHERE prd_id = ?";
				$stmt  = $conexion->prepare($query);
				$exito = $stmt->execute([$nombre, $categoria, $detalles, $precio, $stock, $ruta_imagen, $descripcion, $on_offer, $offer_price, $idProducto]);

				if ($exito)
				{
					header('Location: Gestion_Prod.php');
					exit;
				}
				else
				{
					echo '<div class="alert alert-danger">Error al modificar el producto.</div>';
				}
			}
			else
			{
				// Mostrar los mensajes de error
				foreach ($msg as $texto)
				{
					echo '<div class="alert alert-warning">' . htmlspecialchars($texto) . '</div>';
				}
			}
		}
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Producto</title>
    <link href="/vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include "../nav.php"; ?>

<div class="container mt-5 mb-5">
    <h1>Modificar Producto</h1>
    <form id="formModificarProducto" method="post" enctype="multipart/form-data">
        <input type="hidden" name="idProducto" value="<?php echo $idProducto; ?>">
        <!-- Campo oculto con el ID del producto -->
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <div class="form-group">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($row['prd_name']); ?>" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="categoria">Categoría:</label>
            <select id="categoria" name="categoria" class="form-control" required>
				<?php foreach ($categorias as $categoria): ?>
                    <option value="<?php echo $categoria['cat_id']; ?>" <?php if ($categoria['cat_id'] == $row['prd_category'])
						echo 'selected'; ?>>
						<?php echo htmlspecialchars($categoria['cat_description']); ?>
                    </option>
				<?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="precio">Precio:</label>
            <input type="number" step="0.01" id="precio" name="precio" value="<?php echo htmlspecialchars($row['prd_price']); ?>" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="stock">Stock:</label>
            <input type="number" id="stock" name="stock" value="<?php echo htmlspecialchars($row['prd_stock']); ?>" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="on_offer">En Oferta:</label>
            <select id="on_offer" name="on_offer" class="form-control" required>
                <option value="1" <?php if ($row['prd_on_offer'])
					echo 'selected'; ?>>Sí
                </option>
                <option value="0" <?php if (!$row['prd_on_offer'])
					echo 'selected'; ?>>No
                </option>
            </select>
        </div>
        <div class="form-group">
            <label for="offer_price">Precio de Oferta:</label>
            <input type="number" step="0.01" id="offer_price" name="offer_price" value="<?php echo htmlspecialchars($row['prd_offer_price']); ?>" class="form-control">
        </div>
        <br>
        <div class="form-group">
            <label for="imagen">Imagen:</label>
            <input type="file" id="imagen" name="imagen" accept="image/png, image/jpeg" class="form-control-file">
			<?php if (!empty($row['prd_image'])): ?>
                <img src="/0images/<?php echo htmlspecialchars(basename($row['prd_image'])); ?>" alt="Imagen del producto" width="100" class="mt-2">
			<?php endif; ?>
        </div>
        <div class="form-group">
            <label for="detalles">Detalles:</label>
            <textarea id="detalles" name="detalles" class="form-control" rows="3" required><?php echo htmlspecialchars($row['prd_details']); ?></textarea>
        </div>
        <br>
        <button type="submit" class="btn btn-primary">Modificar Producto</button>
        <button type="button" class="btn btn-secondary" onclick="window.location.href='Gestion_Prod.php'">Volver a
            Gestión de Productos
        </button>
    </form>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
	$(document).ready(function () {
		// AJAX para enviar el formulario de modificación de producto
		$("#formModificarProducto").on("submit", function (event) {
			event.preventDefault(); // Prevenir el envío del formulario
			var formData = new FormData(this); // Crear FormData con el formulario

			$.ajax({
				url: "procesar_modificacion_producto.php", // Ruta del archivo PHP que procesa el formulario
				type: "POST",
				data: formData, // Enviar los datos del formulario
				contentType: false, // No establecer el tipo de contenido
				processData: false, // No procesar los datos
				success: function (response) {
					var jsonResponse = JSON.parse(response);
					if (jsonResponse.status === 'success') {
						alert(jsonResponse.message); // Mostrar mensaje de éxito
						// Redireccionar a la página de administración de productos
						window.location.href = "Gestion_Prod.php";
					} else {
						alert(jsonResponse.message); // Mostrar mensaje de error
					}
				}
			});
		});
	});
</script>
<?php include "../footer.php"; ?>
</body>
</html>





