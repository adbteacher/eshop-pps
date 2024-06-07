<?php
require_once '../autoload.php';

require_once('config.php');
require_once('helpers.php');

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

$UserID = $_SESSION["UserID"];
$UserRol = $_SESSION["UserRol"];

//$UserRol = "A";

//echo "tokden recibido " . $_SESSION['csrf_token'] ;
// Aquí puedes continuar con el resto de tu código si $_SESSION["UserID"] está definido
//echo "ID de usuario recibido: " . $UserID;

//echo "ID de usuario recibido: " . $_SESSION["UserRol"] ;




// Check if it's an export request
$isCsvExport = isset($_GET['export']) && $_GET['export'] == 'csv';


//Get current URL and parameters for correct pagination
$script = $_SERVER['SCRIPT_NAME'];
$parameters = $_GET ? $_SERVER['QUERY_STRING'] : "";
$currenturl = $domain . $script . '?' . $parameters;

//Pagination
if (isset($_GET['pageno']))
{
	$pageno = $_GET['pageno'];
}
else
{
	$pageno = 1;
}

//$no_of_records_per_page is set on the index page. Default is 10.
$offset = ($pageno - 1) * $no_of_records_per_page;

$total_pages_sql = "SELECT COUNT(*) FROM `pps_orders`";
$result = mysqli_query($link, $total_pages_sql);
$total_rows = mysqli_fetch_array($result)[0];
$total_pages = ceil($total_rows / $no_of_records_per_page);

//Column sorting on column name
$columns = array('ord_id', 'ord_user_id', 'ord_purchase_date', 'ord_shipping_date', 'ord_order_status', 'ord_shipping_address');
//$columns = array('ID', 'Usuario', 'Fecha de compra', 'Fecha de envío', 'Estado', 'Dirección de envío');

// Order by primary key on default
$order = 'ord_id';
if (isset($_GET['order']) && in_array($_GET['order'], $columns))
{
	$order = $_GET['order'];
}

//Column sort order
$sortBy = array('asc', 'desc'); $sort = 'asc';
if (isset($_GET['sort']) && in_array($_GET['sort'], $sortBy))
{
	if ($_GET['sort'] == 'asc')
	{
		$sort = 'asc';
	}
	else
	{
		$sort = 'desc';
	}
}

//Generate WHERE statements for param
$where_columns = array_intersect_key($_GET, array_flip($columns));
$get_param = "";
$where_statement = " WHERE 1=1 " . "AND ord_user_id =" . $UserID;
foreach ($where_columns as $key => $val)
{
	$where_statement .= " AND `$key` = '" . mysqli_real_escape_string($link, $val) . "' ";
	$get_param       .= "&$key=$val";
}

if (!empty($_GET['search']))
{
	$search = mysqli_real_escape_string($link, $_GET['search']);
	if (strpos('`pps_orders`.`ord_id`, `pps_orders`.`ord_user_id`, `pps_orders`.`ord_purchase_date`, `pps_orders`.`ord_shipping_date`, `pps_orders`.`ord_order_status`, `pps_orders`.`ord_shipping_address`, `ord_user_idpps_users`.`usu_id`, `ord_user_idpps_users`.`usu_name`, `ord_user_idpps_users`.`usu_surnames`', ','))
	{
		$where_statement .= " AND CONCAT_WS (`pps_orders`.`ord_id`, `pps_orders`.`ord_user_id`, `pps_orders`.`ord_purchase_date`, `pps_orders`.`ord_shipping_date`, `pps_orders`.`ord_order_status`, `pps_orders`.`ord_shipping_address`, `ord_user_idpps_users`.`usu_id`, `ord_user_idpps_users`.`usu_name`, `ord_user_idpps_users`.`usu_surnames`) LIKE '%$search%'";
	}
	else
	{
		$where_statement .= " AND `pps_orders`.`ord_id`, `pps_orders`.`ord_user_id`, `pps_orders`.`ord_purchase_date`, `pps_orders`.`ord_shipping_date`, `pps_orders`.`ord_order_status`, `pps_orders`.`ord_shipping_address`, `ord_user_idpps_users`.`usu_id`, `ord_user_idpps_users`.`usu_name`, `ord_user_idpps_users`.`usu_surnames` LIKE '%$search%'";
	}

}
else
{
	$search = "";
}

$order_clause = !empty($order) ? "ORDER BY `$order` $sort" : '';
$group_clause = !empty($order) && $order == 'ord_id' ? "GROUP BY `pps_orders`.`$order`" : '';

// Prepare SQL queries
$sql = "SELECT `pps_orders`.* 
			, CONCAT_WS(' | ',`ord_user_idpps_users`.`usu_id`, `ord_user_idpps_users`.`usu_name`, `ord_user_idpps_users`.`usu_surnames`) AS `ord_user_idpps_usersusu_id`
        FROM `pps_orders` 
			LEFT JOIN `pps_users` AS `ord_user_idpps_users` ON `ord_user_idpps_users`.`usu_id` = `pps_orders`.`ord_user_id`
        $where_statement
        $group_clause
        $order_clause";



// Execute the main query
$result = mysqli_query($link, $sql);


$count_pages = "SELECT COUNT(*) AS count
                FROM `pps_orders` 
			LEFT JOIN `pps_users` AS `ord_user_idpps_users` ON `ord_user_idpps_users`.`usu_id` = `pps_orders`.`ord_user_id`
                $where_statement";

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>eshop-pps</title>
    <link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <link href="../vendor/fortawesome/font-awesome/css/all.min.css" rel="stylesheet">

    <style type="text/css">
        .page-header h2 {
            margin-top: 0;
        }

        table tr td:last-child a {
            margin-right: 5px;
        }

        body {
            font-size: 14px;
        }
    </style>
</head>
<body>
<?php include "../nav.php"; // Incluye el Navbar?>

<section class="pt-5">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="page-header clearfix">
					<?php
						// Prevent crash if $str contains single quotes
						$str = <<<'EOD'
                        pps_orders
                        EOD;
					?>
                    <h2 class="float-left"><?php translate('%s Details', true, $str) ?></h2>

                    <a href="pps_orders-index.php" class="btn btn-info float-right mr-2"><?php translate('Reset View') ?></a>

                    <a href="javascript:history.back()" class="btn btn-secondary float-right mr-2"><?php translate('Back') ?></a>
                </div>

                <div class="form-row">
                    <form action="pps_orders-index.php" method="get">
                        <div class="col">
                            <input type="text" class="form-control" placeholder="<?php translate('Search this table') ?>" name="search">
                        </div>
                    </form>
                    <br>


					<?php
						if ($result) :
							if (mysqli_num_rows($result) > 0) :
								$number_of_results = mysqli_fetch_assoc(mysqli_query($link, $count_pages))['count'];
								$total_pages = ceil($number_of_results / $no_of_records_per_page);
								translate('total_results', true, $number_of_results, $pageno, $total_pages);
								?>

                                <table class='table table-bordered table-striped'>
                                    <thead class='thead-light'>
                                    <tr>
										<?php $columnname = "ord_id";
											$sort_link    = isset($_GET["order"]) && $_GET["order"] == $columnname && $_GET["sort"] == "asc" ? "desc" : "asc";
											$sort_link    = isset($_GET["order"]) && $_GET["order"] == $columnname && $_GET["sort"] == "desc" ? "asc" : $sort_link;
											echo "<th><a href=?search=$search&order=ord_id&sort=" . $sort_link . ">ID</a></th>";
											$columnname = "ord_user_id";
											$sort_link  = isset($_GET["order"]) && $_GET["order"] == $columnname && $_GET["sort"] == "asc" ? "desc" : "asc";
											$sort_link  = isset($_GET["order"]) && $_GET["order"] == $columnname && $_GET["sort"] == "desc" ? "asc" : $sort_link;
											echo "<th><a href=?search=$search&order=ord_user_id&sort=" . $sort_link . ">Usuario</a></th>";
											$columnname = "ord_purchase_date";
											$sort_link  = isset($_GET["order"]) && $_GET["order"] == $columnname && $_GET["sort"] == "asc" ? "desc" : "asc";
											$sort_link  = isset($_GET["order"]) && $_GET["order"] == $columnname && $_GET["sort"] == "desc" ? "asc" : $sort_link;
											echo "<th><a href=?search=$search&order=ord_purchase_date&sort=" . $sort_link . ">Fecha de compra</a></th>";
											$columnname = "ord_shipping_date";
											$sort_link  = isset($_GET["order"]) && $_GET["order"] == $columnname && $_GET["sort"] == "asc" ? "desc" : "asc";
											$sort_link  = isset($_GET["order"]) && $_GET["order"] == $columnname && $_GET["sort"] == "desc" ? "asc" : $sort_link;
											echo "<th><a href=?search=$search&order=ord_shipping_date&sort=" . $sort_link . ">Fecha de envío</a></th>";
											$columnname = "ord_order_status";
											$sort_link  = isset($_GET["order"]) && $_GET["order"] == $columnname && $_GET["sort"] == "asc" ? "desc" : "asc";
											$sort_link  = isset($_GET["order"]) && $_GET["order"] == $columnname && $_GET["sort"] == "desc" ? "asc" : $sort_link;
											echo "<th><a href=?search=$search&order=ord_order_status&sort=" . $sort_link . ">Estado</a></th>";
											$columnname = "ord_shipping_address";
											$sort_link  = isset($_GET["order"]) && $_GET["order"] == $columnname && $_GET["sort"] == "asc" ? "desc" : "asc";
											$sort_link  = isset($_GET["order"]) && $_GET["order"] == $columnname && $_GET["sort"] == "desc" ? "asc" : $sort_link;
											echo "<th><a href=?search=$search&order=ord_shipping_address&sort=" . $sort_link . ">Dirección de envío</a></th>";
										?>
                                        <th><?php translate('Actions'); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
									<?php while ($row = mysqli_fetch_array($result)): ?>
                                        <tr>
											<?php ?>
											<?php //echo "<td><a href='http://localhost/roberto/core/eshop-pps/pps_order_details-index.php?ord_id=" . urlencode($row['ord_id']) . "'>" . htmlspecialchars($row['ord_id'] ?? "") . "</a></td>";

												echo "<td><a href=" . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/pps_order_details-index.php?ord_id=' . urlencode($row['ord_id']) . "'>" . htmlspecialchars($row['ord_id'] ?? "") . "</a></td>";

												echo "<td>" . htmlspecialchars($row['ord_user_id'] ?? "") . "</td>";
												//echo "<td>" . get_fk_url($row["ord_user_id"], "pps_users", "usu_id", $row["ord_user_idpps_usersusu_id"], 1, true) . "</td>";
												echo "<td>" . convert_date($row['ord_purchase_date']) . "</td>";
												echo "<td>" . convert_date($row['ord_shipping_date']) . "</td>";
												echo "<td>" . htmlspecialchars($row['ord_order_status'] ?? "") . "</td>";
												echo "<td>";
												// Check if the column is file upload
												// echo '<pre>';
												// print_r($tables_and_columns_names['pps_orders']["columns"]['ord_shipping_address']);
												// echo '</pre>';
												$has_link_file = isset($tables_and_columns_names['pps_orders']["columns"]['ord_shipping_address']['is_file']) ? true : false;
												if ($has_link_file)
												{
													$is_file   = $tables_and_columns_names['pps_orders']["columns"]['ord_shipping_address']['is_file'];
													$link_file = $is_file ? '<a href="uploads/' . htmlspecialchars($row['ord_shipping_address']) . '" target="_blank" class="uploaded_file" id="link_ord_shipping_address">' : '';
													echo $link_file;
												}
												echo nl2br(htmlspecialchars($row['ord_shipping_address'] ?? ""));
												if ($has_link_file)
												{
													echo $is_file ? "</a>" : "";
												}
												echo "</td>" . "\n\t\t\t\t\t\t\t\t\t\t\t\t"; ?>
                                            <td>
												<?php
													$column_id = 'ord_id';
													if (!empty($column_id)): ?>
                                                        <a id='read-<?php echo $row['ord_id']; ?>' href='pps_orders-read.php?ord_id=<?php echo $row['ord_id']; ?>' title='<?php echo addslashes(translate('View Record', false)); ?>' data-toggle='tooltip' class='btn btn-sm btn-info'><i class='far fa-eye'></i></a>


														<?php if ($UserRol == "A"): ?>
                                                            <a id='update-<?php echo $row['ord_id']; ?>' href='pps_orders-update.php?ord_id=<?php echo $row['ord_id']; ?>' title='<?php echo addslashes(translate('Update Record', false)); ?>' data-toggle='tooltip' class='btn btn-sm btn-warning'><i class='far fa-edit'></i></a>
                                                            <a id='delete-<?php echo $row['ord_id']; ?>' href='pps_orders-delete.php?ord_id=<?php echo $row['ord_id']; ?>' title='<?php echo addslashes(translate('Delete Record', false)); ?>' data-toggle='tooltip' class='btn btn-sm btn-danger'><i class='far fa-trash-alt'></i></a>
														<?php endif; ?>
                                                        <!--
                                                        <a id='update-<?php echo $row['ord_id']; ?>' href='pps_orders-update.php?ord_id=<?php echo $row['ord_id']; ?>' title='<?php echo addslashes(translate('Update Record', false)); ?>' data-toggle='tooltip' class='btn btn-sm btn-warning'><i class='far fa-edit'></i></a>
                                                        <a id='delete-<?php echo $row['ord_id']; ?>' href='pps_orders-delete.php?ord_id=<?php echo $row['ord_id']; ?>' title='<?php echo addslashes(translate('Delete Record', false)); ?>' data-toggle='tooltip' class='btn btn-sm btn-danger'><i class='far fa-trash-alt'></i></a>
                                                    -->
													<?php else: ?>
														<?php echo addslashes(translate('unsupported_no_pk')); ?>
													<?php endif; ?>
                                            </td>
                                        </tr>
									<?php endwhile; ?>
                                    </tbody>
                                </table>


                                <ul class="pagination" align-right>
									<?php
										$new_url = preg_replace('/&?pageno=[^&]*/', '', $currenturl);
									?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo $new_url . '&pageno=1' ?>"><?php translate('First') ?></a>
                                    </li>
                                    <li class="page-item <?php if ($pageno <= 1)
									{
										echo 'disabled';
									} ?>">
                                        <a class="page-link" href="<?php if ($pageno <= 1)
										{
											echo '#';
										}
										else
										{
											echo $new_url . "&pageno=" . ($pageno - 1);
										} ?>"><?php translate('Prev') ?></a>
                                    </li>
                                    <li class="page-item <?php if ($pageno >= $total_pages)
									{
										echo 'disabled';
									} ?>">
                                        <a class="page-link" href="<?php if ($pageno >= $total_pages)
										{
											echo '#';
										}
										else
										{
											echo $new_url . "&pageno=" . ($pageno + 1);
										} ?>"><?php translate('Next') ?></a>
                                    </li>
                                    <li class="page-item <?php if ($pageno >= $total_pages)
									{
										echo 'disabled';
									} ?>">
                                        <a class="page-item"><a class="page-link" href="<?php echo $new_url . '&pageno=' . $total_pages; ?>"><?php translate('Last') ?></a>
                                    </li>
                                </ul>

								<?php mysqli_free_result($result); ?>
							<?php else: ?>
                                <p class='lead'><em><?php translate('No records were found.') ?></em></p>
							<?php endif ?>

						<?php else: ?>
                            <div class="alert alert-danger" role="alert">
                                ERROR: Could not able to execute <?php echo $sql . " " . mysqli_error($link); ?>
                            </div>
						<?php endif ?>

					<?php mysqli_close($link) ?>
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
