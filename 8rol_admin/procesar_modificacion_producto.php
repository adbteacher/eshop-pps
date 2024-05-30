<?php
	require_once '../autoload.php'; // Incluye el archivo de conexión PDO
	session_start();

	$response['message'] = '';
	$response['status']  = 'error';

	// Verificar si la solicitud es mediante POST
	if ($_SERVER["REQUEST_METHOD"] == "POST")
	{
		// Obtener los datos del formulario
		$idProducto  = $_POST['idProducto'];
		$nombre      = $_POST['nombre'];
		$categoria   = $_POST['categoria'];
		$detalles    = $_POST['detalles'];
		$precio      = validarNumerico($_POST['precio']);
		$stock       = validarNumerico($_POST['stock']);
		$on_offer    = $_POST['on_offer'];
		$offer_price = validarNumerico($_POST['offer_price']);

		// Obtener una conexión a la base de datos
		$conexion = database::LoadDatabase();

		// Obtener los valores actuales del producto
		$query = "SELECT prd_name, prd_details, prd_image FROM pps_products WHERE prd_id = ?";
		$stmt  = $conexion->prepare($query);
		$stmt->execute([$idProducto]);
		$productoActual = $stmt->fetch(PDO::FETCH_ASSOC);

		// Validar campos de texto
		$nombreValido   = validarTexto($nombre);
		$detallesValido = validarTexto($detalles);

		// Usar los valores anteriores si los nuevos no son válidos
		if (!$nombreValido)
		{
			$nombre              = $productoActual['prd_name'];
			$response['message'] = "Error al actualizar producto, uso de caracteres no válidos en el nombre.";
			$response["status"]  = "Error";
		}

		if (!$detallesValido)
		{
			$detalles            = $productoActual['prd_details'];
			$response['message'] = "Error al actualizar producto, uso de caracteres no válidos en los detalles.";
			$response["status"]  = "Error";
		}

		// Validar precio de oferta si el producto está en oferta
		if ($on_offer == '1' && empty($offer_price))
		{
			$response['message'] = "Necesario precio de oferta.";
			$response["status"]  = "Error";
		}

		if ($on_offer == '0')
		{
			$offer_price = null; // Borrar el precio de oferta si el producto no está en oferta
		}

		if ($nombreValido && $detallesValido && ($on_offer == '0' || ($on_offer == '1' && !empty($offer_price))))
		{
			try
			{
				// Comprobar si se ha subido una nueva imagen
				if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK)
				{
					$file_info = $_FILES['imagen'];
					$file_tmp  = $file_info['tmp_name'];
					$file_mime = mime_content_type($file_tmp);

					// Validar el tipo MIME y la extensión del archivo
					$allowed_extensions = ['image/jpeg', 'image/png'];
					$file_extension     = pathinfo($file_info['name'], PATHINFO_EXTENSION);
					if (in_array($file_mime, $allowed_extensions) && in_array($file_extension, ['jpg', 'jpeg', 'png']))
					{
						$file_name   = $nombre . '.' . $file_extension;
						$ruta_imagen = '../0images/' . $file_name;
						if (move_uploaded_file($file_tmp, $ruta_imagen))
						{
							$imagen = $ruta_imagen;
						}
						else
						{
							$response['message'] = "Error al subir la nueva imagen.";
							$response["status"]  = "Error";

							echo json_encode($response);
							exit;
						}
					}
					else
					{
						$response['message'] = "El archivo seleccionado no es una imagen válida (solo JPG o PNG).";
						$response["status"]  = "Error";

						echo json_encode($response);
						exit;
					}
				}
				else
				{
					// Mantener la imagen existente si no se sube una nueva
					$imagen = $productoActual['prd_image'];
				}

				// Preparar la consulta para actualizar la información del producto
				$query = "UPDATE pps_products SET prd_name=?, prd_category=?, prd_details=?, prd_price=?, prd_stock=?, prd_image=?, prd_on_offer=?, prd_offer_price=? WHERE prd_id=?";
				$stmt  = $conexion->prepare($query);
				$stmt->execute([$nombre, $categoria, $detalles, $precio, $stock, $imagen, $on_offer, $offer_price, $idProducto]);

				if ($stmt->rowCount() > 0 || $stmt->errorCode() === '00000')
				{
					$response['status']  = 'success';
					$response['message'] = 'Producto modificado exitosamente.';
				}
				else
				{
					$response['message'] = 'No se realizaron cambios en el producto.';
				}
			}
			catch (PDOException $e)
			{
				// Manejar cualquier excepción y mostrar un mensaje genérico
				$response['message'] = "Error al modificar el producto: " . $e->getMessage();
			}
		}

		// Cerrar la conexión
		$conexion = null;
	}

	echo json_encode($response);

	// Función para validar campos de texto
	function validarTexto($texto): bool|int
	{
		// Verificar si el texto contiene solo letras y espacios
		return preg_match("/^[a-zA-Z\s,\.]+$/", $texto);
	}

	// Función para validar campos numéricos
	function validarNumerico($numero): array|string|null
	{
		// Permitir dígitos y un punto decimal opcional
		return preg_replace("/[^0-9.]/", "", $numero);
	}