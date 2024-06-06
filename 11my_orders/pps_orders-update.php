<?php
	require_once '../autoload.php';

	require_once('config.php');
	require_once('helpers.php');
	require_once('config-tables-columns.php');

	if (session_status() == PHP_SESSION_NONE)
	{
		session_start();
	}

	// Verificar si el usuario está autenticado
	functions::ActiveSession();

	//Comprobar permisos al programa
	functions::HasPermissions(basename(__FILE__));

	//echo "tokden recibido " . $_SESSION['csrf_token'];
	//$_SESSION["UserRol"] ='A';
	// Verificar si existe el token CSRF en la sesión
	if (!isset($_SESSION['csrf_token']) or $_SESSION["UserRol"] != 'A')
	{
		// Redirigir al usuario fuera de la página si no hay token CSRF
		//header("Location: ../1login/login.php"); // Cambia 'login.php' por la página que desees
		//exit();
		exit("No puedes actualizar elementos");
	}

	if (!isset($_SESSION["UserID"]))
	{
		// Si no se recibe el UserID de sesión, salir del script
		exit("No se ha recibido el ID de usuario. Saliendo...");
	}


	// Processing form data when form is submitted
	if (isset($_POST["ord_id"]) && !empty($_POST["ord_id"]))
	{
		// Get hidden input value
		$ord_id = $_POST["ord_id"];

		// Checking for upload fields
		$upload_results = array();
		$upload_errors  = array();


		$upload_errors = array();

		// Check for regular fields
		if (!in_array(true, array_column($upload_results, 'error')))
		{

			$ord_user_id       = $_POST["ord_user_id"] == "" ? null : trim($_POST["ord_user_id"]);
			$ord_purchase_date = $_POST["ord_purchase_date"] == "" ? null : trim($_POST["ord_purchase_date"]);
			$ord_shipping_date = $_POST["ord_shipping_date"] == "" ? null : trim($_POST["ord_shipping_date"]);
			$ord_order_status  = trim($_POST["ord_order_status"]);
			echo "orderstatus" . $ord_order_status;
			$ord_shipping_address = trim($_POST["ord_shipping_address"]);


			// Prepare an update statement

			$stmt = $link->prepare("UPDATE `pps_orders` SET `ord_user_id`=?,`ord_purchase_date`=?,`ord_shipping_date`=?,`ord_order_status`=?,`ord_shipping_address`=? WHERE `ord_id`=?");

			try
			{

				$stmt->bind_param('issssi', $ord_user_id, $ord_purchase_date, $ord_shipping_date, $ord_order_status, $ord_shipping_address, $ord_id);
				$stmt->execute();
			}
			catch (Exception $e)
			{
				error_log($e->getMessage());
				$error = $e->getMessage();
			}


			$fechaActual = date('Y-m-d H:i:s');
			echo "fecha" . $fechaActual;
			$stmt_hist = $link->prepare("INSERT INTO `pps_orders_history` (ord_hist_order_id, ord_hist_transaction_type,ord_hist_transaction_date) VALUES (?,?,?)");

			try
			{
				echo "orderid" . $ord_user_id . "cstatus " . $ord_order_status . " fecha: " . $fechaActual;
				$stmt_hist->bind_param('sss', $ord_id, $ord_order_status, $fechaActual);

				$stmt_hist->execute();
			}
			catch (Exception $e)
			{
				error_log($e->getMessage());
				$error = $e->getMessage();
				echo $e;
			}

			if (!isset($error))
			{
				header("location: pps_orders-read.php?ord_id=$ord_id");
			}
			else
			{
				$uploaded_files = array();
				foreach ($upload_results as $result)
				{
					if (isset($result['success']))
					{
						// Delete the uploaded files if there were any error while saving postdata in DB
						unlink($upload_target_dir . $result['success']);
					}
				}
			}

		}
	}
	// Check existence of id parameter before processing further
	$_GET["ord_id"] = trim($_GET["ord_id"]);
	if (isset($_GET["ord_id"]) && !empty($_GET["ord_id"]))
	{
		// Get URL parameter
		$ord_id = trim($_GET["ord_id"]);

		// Prepare a select statement
		$sql = "SELECT * FROM `pps_orders` WHERE `ord_id` = ?";
		if ($stmt = mysqli_prepare($link, $sql))
		{
			// Set parameters
			$param_id = $ord_id;

			// Bind variables to the prepared statement as parameters
			if (is_int($param_id))
			{
				$__vartype = "i";
			}
            elseif (is_string($param_id))
				$__vartype = "s";
            elseif (is_numeric($param_id))
				$__vartype = "d";
			else $__vartype = "b"; // blob
			mysqli_stmt_bind_param($stmt, $__vartype, $param_id);

			// Attempt to execute the prepared statement
			if (mysqli_stmt_execute($stmt))
			{
				$result = mysqli_stmt_get_result($stmt);

				if (mysqli_num_rows($result) == 1)
				{
					/* Fetch result row as an associative array. Since the result set
					contains only one row, we don't need to use while loop */
					$row = mysqli_fetch_array($result, MYSQLI_ASSOC);

					// Retrieve individual field value

					$ord_user_id          = htmlspecialchars($row["ord_user_id"] ?? "");
					$ord_purchase_date    = htmlspecialchars($row["ord_purchase_date"] ?? "");
					$ord_shipping_date    = htmlspecialchars($row["ord_shipping_date"] ?? "");
					$ord_order_status     = htmlspecialchars($row["ord_order_status"] ?? "");
					$ord_shipping_address = htmlspecialchars($row["ord_shipping_address"] ?? "");


				}
				else
				{
					// URL doesn't contain valid id. Redirect to error page
					header("location: error.php");
					exit();
				}

			}
			else
			{
				translate('stmt_error') . "<br>" . $stmt->error;
			}
		}

		// Close statement
		mysqli_stmt_close($stmt);

	}
	else
	{
		// URL doesn't contain id parameter. Redirect to error page
		header("location: error.php");
		exit();
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php translate('Update Record') ?></title>
    <link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <link href="../vendor/fortawesome/font-awesome/css/all.min.css" rel="stylesheet">

</head>
<body>
<?php include('../nav.php'); ?>
<section class="pt-5">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="page-header">
                    <h2><?php translate('Update Record') ?></h2>
                </div>
				<?php print_error_if_exists(@$upload_errors); ?>
				<?php print_error_if_exists(@$error); ?>
                <p><?php translate('update_record_instructions') ?></p>
                <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post" enctype="multipart/form-data">

                    <div class="form-group">
                        <label for="ord_user_id">ord_user_id</label>
                        <select class="form-control" id="ord_user_id" name="ord_user_id">
                            <option value="">Null</option> <?php
								$sql    = "SELECT `usu_id`, `usu_name`, `usu_surnames`, `usu_id` FROM `pps_users` ORDER BY `usu_id`, `usu_name`, `usu_surnames`";
								$result = mysqli_query($link, $sql);
								while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
								{
									$duprow = $row;
									unset($duprow["usu_id"]);
									$value       = implode(" | ", $duprow);
									$ord_user_id = isset($ord_user_id) ? $ord_user_id : null;
									if ($row["usu_id"] == $ord_user_id)
									{
										echo '<option value="' . $row["usu_id"] . '"selected="selected">' . $value . '</option>';
									}
									else
									{
										echo '<option value="' . $row["usu_id"] . '">' . $value . '</option>';
									}
								}
							?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="ord_purchase_date">ord_purchase_date</label>
                        <input type="date" name="ord_purchase_date" id="ord_purchase_date" class="form-control" value="<?php echo @$ord_purchase_date; ?>">
                    </div>
                    <div class="form-group">
                        <label for="ord_shipping_date">ord_shipping_date</label>
                        <input type="date" name="ord_shipping_date" id="ord_shipping_date" class="form-control" value="<?php echo @$ord_shipping_date; ?>">
                    </div>
                    <div class="form-group">
                        <label for="ord_order_status">ord_order_status*</label>
                        <select name="ord_order_status" class="form-control" id="ord_order_status"><?php
								$enum_ord_order_status = array('Creado', 'PendienteEnvio', 'Enviado', 'Pendiente Devolución', 'Reembolsado');
								foreach ($enum_ord_order_status as $val)
								{
									if ($val == $ord_order_status)
									{
										echo '<option value="' . $val . '" selected="selected">' . $val . '</option>';
									}
									else
									{
										echo '<option value="' . $val . '">' . $val . '</option>';
									}
								}
							?></select>
                    </div>
                    <div class="form-group">
                        <label for="ord_shipping_address">ord_shipping_address*</label>
                        <input type="text" name="ord_shipping_address" id="ord_shipping_address" maxlength="255" class="form-control" value="<?php echo @$ord_shipping_address; ?>">
                    </div>

                    <input type="hidden" name="ord_id" value="<?php echo $ord_id; ?>"/>
                    <p>
                        <input type="submit" class="btn btn-primary" value="Actualizar ">
                        <a href="javascript:history.back()" class="btn btn-secondary">Cancelar</a>
                    </p>
                    <hr>
                    <p>

                        <a href="pps_orders-read.php?ord_id=<?php echo $_GET["ord_id"]; ?>" class="btn btn-info"><?php translate('View Record') ?></a>

                        <a href="pps_orders-delete.php?ord_id=<?php echo $_GET["ord_id"]; ?>" class="btn btn-danger"><?php translate('Delete Record') ?></a>

                        <a href="pps_orders-index.php" class="btn btn-primary"><?php translate('Back to List') ?></a>

                    </p>
                    <p><?php translate('required_fiels_instructions') ?></p>
                </form>
            </div>
        </div>
    </div>
</section>
</body>
<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
<script type="text/javascript">
	$(document).ready(function () {
		$('[data-toggle="tooltip"]').tooltip();
	});
</script>
</body>
</html>