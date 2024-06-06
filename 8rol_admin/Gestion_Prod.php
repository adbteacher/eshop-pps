<?php
/*
	 Este código muestra los productos de la tienda a demas de tener la capacidad de editar
	 y eliminar dichos productos junto con la capacidad de exportar e importar CSV's
	 */
	require_once '../autoload.php';

	session_start();

	functions::checkAdminAccess();

	// Obtener una conexión a la base de datos
	$conexion = database::LoadDatabase();

	// Generar token anti-CSRF si no está definido
	if (empty($_SESSION['csrf_token']))
	{
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}

	// Función para mostrar la lista de productos
	function MostrarProductos($conexion): void
	{
		$query = "SELECT * FROM pps_products";
		$stmt  = $conexion->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if ($result)
		{
			echo '<div class="table-responsive">';
			echo '<h2 class="mt-4">Lista de Productos</h2>';
			echo '<table class="table table-bordered table-striped">';
			echo '<thead class="thead-dark"><tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Detalles</th><th>Precio</th><th>Stock</th><th>Imagen</th><th>En Oferta</th><th>Precio Oferta</th><th>Acciones</th></tr></thead>';
			echo '<tbody>';
			foreach ($result as $row)
			{
				echo '<tr>';
				echo '<td>' . htmlspecialchars($row['prd_id']) . '</td>';
				echo '<td>' . htmlspecialchars($row['prd_name']) . '</td>';
				echo '<td>' . htmlspecialchars($row['prd_category']) . '</td>';
				echo '<td>' . htmlspecialchars($row['prd_details']) . '</td>';
				echo '<td>' . htmlspecialchars($row['prd_price']) . '</td>';
				echo '<td>' . htmlspecialchars($row['prd_stock']) . '</td>';
				echo '<td><img src="' . htmlspecialchars($row['prd_image']) . '" alt="' . htmlspecialchars($row['prd_name']) . '" width="50" height="50"></td>';
				echo '<td>' . ($row['prd_on_offer'] ? 'Sí' : 'No') . '</td>';
				echo '<td>' . htmlspecialchars($row['prd_offer_price']) . '</td>';
				echo '<td>';
				echo '<form action="Mod_Prod.php" method="post" style="display:inline;">';
				echo '<input type="hidden" name="idProducto" value="' . htmlspecialchars($row['prd_id']) . '">';
				echo '<button type="submit" class="btn btn-warning btn-sm">Editar</button>';
				echo '</form> ';
				echo '<form method="post" style="display:inline;">';
				echo '<input type="hidden" name="idProducto" value="' . htmlspecialchars($row['prd_id']) . '">';
				echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
				echo '<button type="submit" name="eliminarProducto" class="btn btn-danger btn-sm">Eliminar</button>';
				echo '</form>';
				echo '</td>';
				echo '</tr>';
			}
			echo '</tbody>';
			echo '</table>';
			echo '</div>';
		}
		else
		{
			echo '<div class="alert alert-info">No se encontraron productos.</div>';
		}
	}

	function ObtenerCategorias($conexion)
	{
		$query = "SELECT cat_id, cat_description FROM pps_categories";
		$stmt  = $conexion->prepare($query);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	// Validar token anti-CSRF y manejar importación y eliminación de productos
	if ($_SERVER['REQUEST_METHOD'] === 'POST')
	{
		if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']))
		{
			echo "Error en la validación CSRF.";
		}
		else
		{
			if (isset($_POST['importarCSV']))
			{
				if (isset($_FILES['archivoCSV']) && $_FILES['archivoCSV']['error'] === UPLOAD_ERR_OK)
				{
					// Validar el tipo MIME del archivo subido
					$finfo = finfo_open(FILEINFO_MIME_TYPE);
					$mime = finfo_file($finfo, $_FILES['archivoCSV']['tmp_name']);
					finfo_close($finfo);
			
					// Tipos MIME aceptados para archivos CSV
					$mime_types = ['text/csv', 'application/vnd.ms-excel', 'text/plain'];
			
					if (in_array($mime, $mime_types))
					{
						$archivoTemporal = $_FILES['archivoCSV']['tmp_name'];
						$contenidoCSV    = file_get_contents($archivoTemporal);
						$filas           = array_map('str_getcsv', file($archivoTemporal));
			
						// Verificar si el archivo CSV tiene el formato correcto (al menos una fila y 8 columnas)
						$csvValido = true;
						$errorDetalles = '';
			
						// Verificar encabezado
						$encabezado = $filas[0];
						if (count($encabezado) !== 8)
						{
							$csvValido = false;
							$errorDetalles .= "Error en el formato de archivo.<br>";
						}
			
						// Verificar filas de datos
						for ($i = 1; $i < count($filas); $i++)
						{
							$fila = $filas[$i];
							if (count($fila) !== 8)
							{
								$csvValido = false;
								$errorDetalles .= "La fila " . ($i + 1) . " no tiene el formato adecuado.<br>";
							}
						}
			
						if ($csvValido)
						{
							$query_insert          = "INSERT INTO pps_products (prd_name, prd_category, prd_details, prd_price, prd_stock, prd_image, prd_on_offer, prd_offer_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
							$stmt_insert           = $conexion->prepare($query_insert);
							$categoria_inexistente = false;
			
							for ($i = 1; $i < count($filas); $i++)
							{
								$datos = $filas[$i];
								if (count($datos) === 8)
								{
									$nombre      = $datos[0];
									$categoria   = $datos[1];
									$detalles    = $datos[2];
									$precio      = $datos[3];
									$stock       = $datos[4];
									$imagen      = $datos[5];
									$on_offer    = strtolower($datos[6]) === 'sí' ? 1 : 0;
									$offer_price = $datos[7];
			
									$query_categoria = "SELECT cat_id FROM pps_categories WHERE cat_id = ?";
									$stmt_categoria  = $conexion->prepare($query_categoria);
									$stmt_categoria->execute([$categoria]);
									$categoria_existente = $stmt_categoria->fetchColumn();
			
									if ($categoria_existente)
									{
										$stmt_insert->execute([$nombre, $categoria, $detalles, $precio, $stock, $imagen, $on_offer, $offer_price]);
									}
									else
									{
										$categoria_inexistente = true;
									}
								}
							}
			
							if (!$categoria_inexistente)
							{
								echo '<div class="alert alert-success">Todos los productos del archivo CSV fueron importados exitosamente.</div>';
							}
						}
						else
						{
							echo '<div class="alert alert-danger">El archivo CSV no tiene el formato adecuado.<br>' . $errorDetalles . '</div>';
						}
					}
					else
					{
						echo '<div class="alert alert-danger">El archivo subido no es un archivo CSV válido.</div>';
					}
				}
				else
				{
					echo '<div class="alert alert-danger">Error al subir el archivo CSV. Por favor, inténtelo de nuevo.</div>';
				}
			}


			// Procesar eliminación de producto
			if (isset($_POST['eliminarProducto']))
			{
				if (!empty($_POST['idProducto']))
				{
					$idProducto = $_POST['idProducto'];
					$query      = "DELETE FROM pps_products WHERE prd_id = ?";
					$stmt       = $conexion->prepare($query);
					$exito      = $stmt->execute([$idProducto]);

					if ($exito)
					{
						header("Location: {$_SERVER['REQUEST_URI']}");
						exit();
					}
					else
					{
						echo '<div class="alert alert-danger">Error al eliminar el producto.</div>';
					}
				}
				else
				{
					echo '<div class="alert alert-warning">No se proporcionó un ID de producto válido.</div>';
				}
			}

			// Procesar formulario para agregar un nuevo producto
			if (isset($_POST['agregarProducto']))
			{
				$msg = array();

				// Validar campos 
				if (empty($_POST['nombre']))
				{
					$msg[] = 'El nombre del producto es obligatorio.';
				}
				else
				{
					$nombre = $_POST['nombre'];
				}

				if (empty($_POST['categoria']))
				{
					$msg[] = 'La categoría del producto es obligatoria.';
				}
				else
				{
					$categoria = $_POST['categoria'];
				}

				if (empty($_POST['detalles']))
				{
					$msg[] = 'Los detalles del producto son obligatorios.';
				}
				else
				{
					$detalles = $_POST['detalles'];
				}

				if (empty($_POST['precio']) || !is_numeric($_POST['precio']))
				{
					$msg[] = 'El precio del producto es obligatorio y debe ser un número.';
				}
				else
				{
					$precio = $_POST['precio'];
				}

				if (empty($_POST['stock']) || !is_numeric($_POST['stock']))
				{
					$msg[] = 'El stock es obligatorio y debe ser un número.';
				}
				else
				{
					$stock = $_POST['stock'];
				}

				if (empty($_POST['descripcion']))
				{
					$msg[] = 'La descripción del producto es obligatoria.';
				}
				else
				{
					$descripcion = $_POST['descripcion'];
				}

				if (!isset($_POST['on_offer']))
				{
					$msg[] = 'Indique si el producto está en oferta.';
				}
				else
				{
					$on_offer = $_POST['on_offer'];
				}

				if ($on_offer == 1)
				{
					if (empty($_POST['offer_price']) || !is_numeric($_POST['offer_price']))
					{
						$msg[] = 'El precio de oferta es obligatorio y debe ser un número si el producto está en oferta.';
					}
					else
					{
						$offer_price = $_POST['offer_price'];
					}
				}
				else
				{
					$offer_price = null; 
				}

				if (empty($_FILES['imagen']['name']))
				{
					$msg[] = 'Debes seleccionar una imagen para el producto.';
				}
				else
				{
					$file_info = $_FILES['imagen'];
					$file_name = $file_info['name'];
					$file_tmp  = $file_info['tmp_name'];
					$file_mime = mime_content_type($file_tmp);

					if (($file_mime == 'image/jpeg' || $file_mime == 'image/png') && exif_imagetype($file_tmp) != false)
					{
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

				// Si no hay errores, proceder con la inserción en la base de datos
				if (empty($msg))
				{
					$ruta_imagen_db = '../0images/' . $file_name; // Ruta a guardar en la base de datos
					$query_insert   = "INSERT INTO pps_products (prd_name, prd_category, prd_details, prd_price, prd_stock, prd_image, prd_on_offer, prd_offer_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
					$stmt_insert    = $conexion->prepare($query_insert);
					if ($stmt_insert->execute([$nombre, $categoria, $detalles, $precio, $stock, $ruta_imagen_db, $on_offer, $offer_price]))
					{
						echo '<div class="alert alert-success">Producto agregado exitosamente.</div>';
					}
					else
					{
						echo '<div class="alert alert-danger">Error al agregar el producto.</div>';
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
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/vendor/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Gestión de Productos</title>
</head>
<body>
<?php include "../nav.php"; ?>

<div class="container mt-5 mb-5">

    <!-- Formulario para importar CSV -->
    <h2>Importar/Exportar Productos desde CSV</h2>
    <form method="post" enctype="multipart/form-data" class="form-inline mb-3">
        <input type="file" name="archivoCSV" accept=".csv" class="form-control-file mr-2">
        <button type="submit" name="importarCSV" class="btn btn-primary">Importar CSV</button>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    </form>

    <!-- Botón para exportar productos a CSV -->
    <form method="post" action="Exportar.php" class="form-inline mb-4">
        <button type="submit" name="exportarCSV" class="btn btn-secondary">Exportar Productos a CSV</button>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    </form>

    <!-- Formulario para agregar un nuevo producto -->
    <h2>Agregar Nuevo Producto</h2>
    <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
        <div class="form-group">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="categoria">Categoría:</label>
            <select id="categoria" name="categoria" class="form-control" required>
				<?php
					$categorias = ObtenerCategorias($conexion);
					foreach ($categorias as $categoria)
					{
						echo "<option value='{$categoria['cat_id']}'>{$categoria['cat_description']}</option>";
					}
				?>
            </select>
        </div>
        <div class="form-group">
            <label for="detalles">Detalles:</label>
            <input type="text" id="detalles" name="detalles" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="precio">Precio:</label>
            <input type="number" id="precio" name="precio" step="0.01" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="stock">Stock:</label>
            <input type="number" id="stock" name="stock" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="on_offer">En Oferta:</label>
            <select id="on_offer" name="on_offer" class="form-control" required>
                <option value="1">Sí</option>
                <option value="0">No</option>
            </select>
        </div>
        <div class="form-group">
            <label for="offer_price">Precio de Oferta:</label>
            <input type="number" id="offer_price" name="offer_price" step="0.01" class="form-control">
        </div>
        <br>
        <div class="form-group">
            <label for="imagen">Imagen:</label>
            <input type="file" id="imagen" name="imagen" accept="image/*" class="form-control-file" required>
        </div>
        <br>
        <div class="form-group">
            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion" class="form-control" rows="3" required></textarea>
        </div>
        <button type="submit" name="agregarProducto" class="btn btn-success">Agregar Producto</button>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <br>
    </form>
    <button class="btn btn-secondary mt-3" onclick="window.location.href='Rol_Admin.php'">Ir a Rol-Admin</button>

    <!-- Mostrar lista de productos -->
	<?php MostrarProductos($conexion); ?>

</div>

<?php include "../footer.php"; ?>
</body>
</html>