<?php
	require_once '../autoload.php';

	require_once('config.php');
	require_once('helpers.php');
	require_once('config-tables-columns.php');

	include "../nav.php"; // Incluye el Navbar

	if (session_status() == PHP_SESSION_NONE)
	{
		session_start();
	}

	// Verificar si el usuario está autenticado
	functions::ActiveSession();

	//Comprobar permisos al programa
	functions::HasPermissions(basename(__FILE__));

	// Verificar si existe el token CSRF en la sesión
	if (!isset($_SESSION['csrf_token']))
	{
		// Redirigir al usuario fuera de la página si no hay token CSRF
		//header("Location: ../1login/login.php"); // Cambia 'login.php' por la página que desees
		exit("No se ha recibido token. Saliendo...");
	}

	if (!isset($_SESSION["UserID"]))
	{
		// Si no se recibe el UserID de sesión, salir del script
		exit("No se ha recibido el ID de usuario. Saliendo...");
	}

	$UserID  = $_SESSION["UserID"];
	$UserRol = $_SESSION["UserRol"];

	// Check existence of id parameter before processing further
	$_GET["ord_id"] = trim($_GET["ord_id"]);
	if (isset($_GET["ord_id"]) && !empty($_GET["ord_id"]))
	{
		// Prepare a select statement
		$sql = "SELECT `pps_orders`.* 
			, CONCAT_WS(' | ',`ord_user_idpps_users`.`usu_id`, `ord_user_idpps_users`.`usu_name`, `ord_user_idpps_users`.`usu_surnames`) AS `ord_user_idpps_usersusu_id`
            FROM `pps_orders` 
			LEFT JOIN `pps_users` AS `ord_user_idpps_users` ON `ord_user_idpps_users`.`usu_id` = `pps_orders`.`ord_user_id`
            WHERE `pps_orders`.`ord_id` = ?   AND ord_user_id =" . $UserID
			. " GROUP BY `pps_orders`.`ord_id`;";

		if ($stmt = mysqli_prepare($link, $sql))
		{
			// Set parameters
			$param_id = trim($_GET["ord_id"]);

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
				}
				else
				{
					// URL doesn't contain valid id parameter. Redirect to error page
					header("location: error.php");
					exit();
				}

			}
			else
			{
				echo translate('stmt_error') . "<br>" . $stmt->error;
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
    <title><?php translate('View Record') ?></title>
<!--    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">-->
    <link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <link href="../vendor/fortawesome/font-awesome/css/all.min.css" rel="stylesheet">

</head>
<?php //require_once('navbar.php'); ?>
<body>
<section class="pt-5">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="page-header">
                    <h1><?php translate('View Record') ?></h1>
                </div>

				<?php
					// Check if the column is file upload
					// echo '<pre>';
					// print_r($tables_and_columns_names['pps_orders']["columns"]['ord_user_id']);
					// echo '</pre>';
					$has_link_file = isset($tables_and_columns_names['pps_orders']["columns"]['ord_user_id']['is_file']) ? true : false;
					if ($has_link_file)
					{
						$is_file       = $tables_and_columns_names['pps_orders']["columns"]['ord_user_id']['is_file'];
						$link_file     = $is_file ? '<a href="uploads/' . htmlspecialchars($row['ord_user_id']) . '" target="_blank" class="uploaded_file" id="link_ord_user_id">' : '';
						$end_link_file = $is_file ? "</a>" : "";
					}
				?>

                <div class="form-group">
                    <!--
									    <h4>ord_user_id</h4>
									    <?php if ($has_link_file): ?>
									        <p class="form-control-static"><?php echo $link_file ?><?php echo get_fk_url($row["ord_user_id"], "pps_users", "usu_id", $row["ord_user_idpps_usersusu_id"], 1, false); ?><?php echo $end_link_file ?></p>
									
										-->
					<?php endif ?>
                </div> <?php
					// Check if the column is file upload
					// echo '<pre>';
					// print_r($tables_and_columns_names['pps_orders']["columns"]['ord_purchase_date']);
					// echo '</pre>';
					$has_link_file = isset($tables_and_columns_names['pps_orders']["columns"]['ord_purchase_date']['is_file']) ? true : false;
					if ($has_link_file)
					{
						$is_file       = $tables_and_columns_names['pps_orders']["columns"]['ord_purchase_date']['is_file'];
						$link_file     = $is_file ? '<a href="uploads/' . htmlspecialchars($row['ord_purchase_date']) . '" target="_blank" class="uploaded_file" id="link_ord_purchase_date">' : '';
						$end_link_file = $is_file ? "</a>" : "";
					}
				?>
                <div class="form-group">
                    <h4>Fecha de compra</h4>
					<?php if ($has_link_file): ?>
                        <p class="form-control-static"><?php echo $link_file ?><?php echo convert_date($row["ord_purchase_date"]); ?><?php echo $end_link_file ?></p>
					<?php endif ?>
                </div> <?php
					// Check if the column is file upload
					// echo '<pre>';
					// print_r($tables_and_columns_names['pps_orders']["columns"]['ord_shipping_date']);
					// echo '</pre>';
					$has_link_file = isset($tables_and_columns_names['pps_orders']["columns"]['ord_shipping_date']['is_file']) ? true : false;
					if ($has_link_file)
					{
						$is_file       = $tables_and_columns_names['pps_orders']["columns"]['ord_shipping_date']['is_file'];
						$link_file     = $is_file ? '<a href="uploads/' . htmlspecialchars($row['ord_shipping_date']) . '" target="_blank" class="uploaded_file" id="link_ord_shipping_date">' : '';
						$end_link_file = $is_file ? "</a>" : "";
					}
				?>
                <div class="form-group">
                    <h4>Fecha de envío</h4>
					<?php if ($has_link_file): ?>
                        <p class="form-control-static"><?php echo $link_file ?><?php echo convert_date($row["ord_shipping_date"]); ?><?php echo $end_link_file ?></p>
					<?php endif ?>
                </div> <?php
					// Check if the column is file upload
					// echo '<pre>';
					// print_r($tables_and_columns_names['pps_orders']["columns"]['ord_order_status']);
					// echo '</pre>';
					$has_link_file = isset($tables_and_columns_names['pps_orders']["columns"]['ord_order_status']['is_file']) ? true : false;
					if ($has_link_file)
					{
						$is_file       = $tables_and_columns_names['pps_orders']["columns"]['ord_order_status']['is_file'];
						$link_file     = $is_file ? '<a href="uploads/' . htmlspecialchars($row['ord_order_status']) . '" target="_blank" class="uploaded_file" id="link_ord_order_status">' : '';
						$end_link_file = $is_file ? "</a>" : "";
					}
				?>
                <div class="form-group">
                    <h4>Estado*</h4>
					<?php echo "status" . htmlspecialchars($row['ord_order_status']) ?>
					<?php if ($has_link_file): ?>
                        <p class="form-control-static"><?php echo $link_file ?><?php echo htmlspecialchars($row["ord_order_status"] ?? ""); ?><?php echo $end_link_file ?></p>
					<?php endif ?>
                </div> <?php
					// Check if the column is file upload
					// echo '<pre>';
					// print_r($tables_and_columns_names['pps_orders']["columns"]['ord_shipping_address']);
					// echo '</pre>';
					$has_link_file = isset($tables_and_columns_names['pps_orders']["columns"]['ord_shipping_address']['is_file']) ? true : false;
					if ($has_link_file)
					{
						$is_file       = $tables_and_columns_names['pps_orders']["columns"]['ord_shipping_address']['is_file'];
						$link_file     = $is_file ? '<a href="uploads/' . htmlspecialchars($row['ord_shipping_address']) . '" target="_blank" class="uploaded_file" id="link_ord_shipping_address">' : '';
						$end_link_file = $is_file ? "</a>" : "";
					}
				?>
                <div class="form-group">
                    <h4>Dirección de envío</h4>
					<?php if ($has_link_file): ?>
                        <p class="form-control-static"><?php echo $link_file ?><?php echo htmlspecialchars($row["ord_shipping_address"] ?? ""); ?><?php echo $end_link_file ?></p>
					<?php endif ?>
                </div>
                <hr>
                <p>
                    <!--
                        <a href="pps_orders-update.php?ord_id=<?php echo $_GET["ord_id"]; ?>" class="btn btn-warning"><?php translate('Update Record') ?></a>
                        <a href="pps_orders-delete.php?ord_id=<?php echo $_GET["ord_id"]; ?>" class="btn btn-danger"><?php translate('Delete Record') ?></a>
                        <a href="pps_orders-create.php" class="btn btn-success"><?php translate('Add New Record') ?></a>
								-->
                    <a href="pps_orders-index.php" class="btn btn-primary"><?php translate('Back to List') ?></a>
                </p>
				<?php
					$html           = "";
					$id             = is_numeric($row["ord_id"]) ? $row["ord_id"] : "'" . $row["ord_id"] . "'";
					$sql            = "SELECT COUNT(*) AS count FROM `pps_orders_history` WHERE `ord_hist_order_id` = " . $id . ";";
					$number_of_refs = mysqli_fetch_assoc(mysqli_query($link, $sql))["count"];
					if ($number_of_refs > 0)
					{
						$html .= '<p><a href="pps_orders_history-index.php?ord_hist_order_id=' . $row["ord_id"] . '" class="btn btn-info">' . translate("references_view_btn", false, $number_of_refs, "pps_orders_history", "ord_hist_order_id", $row["ord_id"]) . '</a></p></p>';
					}
					$id             = is_numeric($row["ord_id"]) ? $row["ord_id"] : "'" . $row["ord_id"] . "'";
					$sql            = "SELECT COUNT(*) AS count FROM `pps_order_details` WHERE `ord_det_order_id` = " . $id . ";";
					$number_of_refs = mysqli_fetch_assoc(mysqli_query($link, $sql))["count"];
					if ($number_of_refs > 0)
					{
						$html .= '<p><a href="pps_order_details-index.php?ord_id=' . $row["ord_id"] . '" class="btn btn-info">' . translate("references_view_btn", false, $number_of_refs, "pps_order_details", "ord_det_order_id", $row["ord_id"]) . '</a></p></p>';
					}
					if ($html != "")
					{
						echo "<h3>" . translate("references_tables", false, "pps_orders") . "</h3>" . $html;
					}

					// Close connection
					mysqli_close($link);
				?>
            </div>
        </div>
    </div>
</section>
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