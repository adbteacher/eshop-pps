<?php

require_once('config.php');
require_once('helpers.php');

include "../nav.php"; // Incluye el Navbar

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si existe el token CSRF en la sesión
if (!isset($_SESSION['csrf_token'])) {
    // Redirigir al usuario fuera de la página si no hay token CSRF
    header("Location: 1login/login.php"); // Cambia 'login.php' por la página que desees
    exit();
}

if (!isset($_SESSION["UserID"])) {
    // Si no se recibe el UserID de sesión, salir del script
    exit("No se ha recibido el ID de usuario. Saliendo...");
}

$UserID = $_SESSION["UserID"];

$UserRol = $_SESSION["UserRol"];

//$UserRol = "A";






// Check if it's an export request
$isCsvExport = isset($_GET['export']) && $_GET['export'] == 'csv';


//Get current URL and parameters for correct pagination
$script   = $_SERVER['SCRIPT_NAME'];
$parameters   = $_GET ? $_SERVER['QUERY_STRING'] : "" ;
$currenturl = $domain. $script . '?' . $parameters;

//Pagination
if (isset($_GET['pageno'])) {
    $pageno = $_GET['pageno'];
} else {
    $pageno = 1;
}

//idPedido
if (isset($_GET['ord_id'])) {
    $ord_id = $_GET['ord_id'];
} else {
    $ord_id = 0;
}

//echo "order_id" . $ord_id;
//$no_of_records_per_page is set on the index page. Default is 10.
$offset = ($pageno-1) * $no_of_records_per_page;

$total_pages_sql = "SELECT COUNT(*) FROM `pps_order_details`";
$result = mysqli_query($link,$total_pages_sql);
$total_rows = mysqli_fetch_array($result)[0];
$total_pages = ceil($total_rows / $no_of_records_per_page);

//Column sorting on column name
$columns = array('ord_det_id', 'ord_det_order_id', 'ord_det_prod_id', 'qty', 'unit_price', 'subtotal');
// Order by primary key on default
$order = 'ord_det_id';
if (isset($_GET['ord_id']) && in_array($_GET['ord_id'], $columns)) {
    $order = $_GET['ord_id'];
}

//Column sort order
$sortBy = array('asc', 'desc'); $sort = 'asc';
if (isset($_GET['sort']) && in_array($_GET['sort'], $sortBy)) {
        if($_GET['sort']=='asc') {
        $sort='asc';
        }
else {
    $sort='desc';
    }
}

//Generate WHERE statements for param
$where_columns = array_intersect_key($_GET, array_flip($columns));
$get_param = "";
//$where_statement = " WHERE 1=1 and `pps_order_details`.`ord_det_order_id` = ?";
$where_statement = " WHERE 1=1 and `pps_order_details`.`ord_det_order_id` = ?" . " AND ord_user_id =" . $UserID ;

foreach ( $where_columns as $key => $val ) {
    $where_statement .= " AND `$key` = '" . mysqli_real_escape_string($link, $val) . "' ";
    $get_param .= "&$key=$val";
    
    
}

if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($link, $_GET['search']);
    if (strpos('`pps_order_details`.`ord_det_id`, `pps_order_details`.`ord_det_order_id`, `pps_order_details`.`ord_det_prod_id`, `pps_order_details`.`qty`, `pps_order_details`.`unit_price`, `pps_order_details`.`subtotal`, `ord_det_order_idpps_orders`.`ord_id`, `ord_det_order_idpps_orders`.`ord_user_id`, `ord_det_prod_idpps_products`.`prd_id`, `ord_det_prod_idpps_products`.`prd_name`', ',')) {
        $where_statement .= " AND CONCAT_WS (`pps_order_details`.`ord_det_id`, `pps_order_details`.`ord_det_order_id`, `pps_order_details`.`ord_det_prod_id`, `pps_order_details`.`qty`, `pps_order_details`.`unit_price`, `pps_order_details`.`subtotal`, `ord_det_order_idpps_orders`.`ord_id`, `ord_det_order_idpps_orders`.`ord_user_id`, `ord_det_prod_idpps_products`.`prd_id`, `ord_det_prod_idpps_products`.`prd_name`) LIKE '%$search%'";
    } else {
        $where_statement .= " AND `pps_order_details`.`ord_det_id`, `pps_order_details`.`ord_det_order_id`, `pps_order_details`.`ord_det_prod_id`, `pps_order_details`.`qty`, `pps_order_details`.`unit_price`, `pps_order_details`.`subtotal`, `ord_det_order_idpps_orders`.`ord_id`, `ord_det_order_idpps_orders`.`ord_user_id`, `ord_det_prod_idpps_products`.`prd_id`, `ord_det_prod_idpps_products`.`prd_name` LIKE '%$search%'";
    }

} else {
    $search = "";
}

$order_clause = !empty($order) ? "ORDER BY `$order` $sort" : '';
$group_clause = !empty($order) && $order == 'ord_det_id' ? "GROUP BY `pps_order_details`.`$order`" : '';

// Prepare SQL queries
$sql = "SELECT `pps_order_details`.* 
			, CONCAT_WS(' | ',`ord_det_order_idpps_orders`.`ord_id`, `ord_det_order_idpps_orders`.`ord_user_id`) AS `ord_det_order_idpps_ordersord_id`
			, CONCAT_WS(' | ',`ord_det_prod_idpps_products`.`prd_id`, `ord_det_prod_idpps_products`.`prd_name`) AS `ord_det_prod_idpps_productsprd_id`
        FROM `pps_order_details` 
			LEFT JOIN `pps_orders` AS `ord_det_order_idpps_orders` ON `ord_det_order_idpps_orders`.`ord_id` = `pps_order_details`.`ord_det_order_id`
			LEFT JOIN `pps_products` AS `ord_det_prod_idpps_products` ON `ord_det_prod_idpps_products`.`prd_id` = `pps_order_details`.`ord_det_prod_id`
        $where_statement  
        $group_clause
        $order_clause";


//echo "sql:" . $sql;

// Add pagination only if it's not a CSV export
if (!$isCsvExport) {
    $sql .= " LIMIT $offset, $no_of_records_per_page";
}

// Execute the main query
$result = mysqli_query($link, $sql);

$stmt = $link->prepare("$sql");
try {
            $stmt->bind_param("i", $ord_id);

            $stmt->execute();
            $result = $stmt->get_result();
            //echo $result;
} catch (Exception $e) {
            error_log($e->getMessage());
            $error = $e->getMessage();
}



$count_pages = "SELECT COUNT(*) AS count
                FROM `pps_order_details` 
			LEFT JOIN `pps_orders` AS `ord_det_order_idpps_orders` ON `ord_det_order_idpps_orders`.`ord_id` = `pps_order_details`.`ord_det_order_id`
			LEFT JOIN `pps_products` AS `ord_det_prod_idpps_products` ON `ord_det_prod_idpps_products`.`prd_id` = `pps_order_details`.`ord_det_prod_id`
                $where_statement";

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>eshop-pps</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <script src="https://kit.fontawesome.com/6b773fe9e4.js" crossorigin="anonymous"></script>
    <style type="text/css">
        .page-header h2{
            margin-top: 0;
        }
        table tr td:last-child a{
            margin-right: 5px;
        }
        body {
            font-size: 14px;
        }
    </style>
</head>
<body>
    <?//php require_once('navbar.php'); ?>
    <section class="pt-5">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="page-header clearfix">
                        <?php
                        // Prevent crash if $str contains single quotes
                        $str = <<<'EOD'
                        pps_order_details
                        EOD;
                        ?>
                        <h2 class="float-left"><?php translate('%s Details', true, $str) ?></h2>
                        <!--
                        <a href="pps_order_details-create.php" class="btn btn-success float-right"><?php translate('Add New Record') ?></a>
                        
    -->
                        <a href="pps_order_details-index.php" class="btn btn-info float-right mr-2"><?php translate('Reset View') ?></a>
                        
                        <a href="javascript:history.back()" class="btn btn-secondary float-right mr-2"><?php translate('Back') ?></a>
                    </div>

                    <div class="form-row">
                        <form action="pps_order_details-index.php" method="get">
                            <div class="col"> <input type="text" class="form-control" placeholder="<?php translate('Search this table') ?>" name="search"></div>
                        </form>
                        <br>


                        <?php
                        if($result) :
                            if(mysqli_num_rows($result) > 0) :
                                //cho $count_pages;
                                //$number_of_results = mysqli_fetch_assoc(mysqli_query($link, $count_pages))['count'];
                                $stmt_count = $link->prepare($count_pages);
                               
                                try {
                                    $stmt_count->bind_param("i", $ord_id);
                        
                                    $stmt_count->execute();
                                    $result_count = $stmt_count->get_result();
                                    //echo $result;
                                } catch (Exception $e) {
                                    error_log($e->getMessage());
                                    $error = $e->getMessage();
                                }
                                $number_of_results = $result_count->fetch_assoc()['count'];


                                $total_pages = ceil($number_of_results / $no_of_records_per_page);
                                translate('total_results', true, $number_of_results, $pageno, $total_pages);
                                ?>

                                <table class='table table-bordered table-striped'>
                                    <thead class='thead-light'>
                                        <tr>
                                            <?php 									$columnname = "ord_det_id";
									$sort_link = isset($_GET["order"]) && $_GET["order"] == $columnname && $_GET["sort"] == "asc" ? "desc" : "asc";
									$sort_link = isset($_GET["order"]) && $_GET["order"] == $columnname && $_GET["sort"] == "desc" ? "asc" : $sort_link;
									echo "<th><a href=?search=$search&order=ord_det_id&sort=".$sort_link.">idDetallePedido</a></th>";
									$columnname = "ord_det_order_id";
									$sort_link = isset($_GET["order"]) && $_GET["order"] == $columnname && $_GET["sort"] == "asc" ? "desc" : "asc";
									$sort_link = isset($_GET["order"]) && $_GET["order"] == $columnname && $_GET["sort"] == "desc" ? "asc" : $sort_link;
									echo "<th><a href=?search=$search&order=ord_det_order_id&sort=".$sort_link.">idPedido</a></th>";
									$columnname = "ord_det_prod_id";
									$sort_link = isset($_GET["order"]) && $_GET["order"] == $columnname && $_GET["sort"] == "asc" ? "desc" : "asc";
									$sort_link = isset($_GET["order"]) && $_GET["order"] == $columnname && $_GET["sort"] == "desc" ? "asc" : $sort_link;
									echo "<th><a href=?search=$search&order=ord_det_prod_id&sort=".$sort_link.">idProducto</a></th>";
									$columnname = "qty";
									$sort_link = isset($_GET["order"]) && $_GET["order"] == $columnname && $_GET["sort"] == "asc" ? "desc" : "asc";
									$sort_link = isset($_GET["order"]) && $_GET["order"] == $columnname && $_GET["sort"] == "desc" ? "asc" : $sort_link;
									echo "<th><a href=?search=$search&order=qty&sort=".$sort_link.">Cantidad</a></th>";
									$columnname = "unit_price";
									$sort_link = isset($_GET["order"]) && $_GET["order"] == $columnname && $_GET["sort"] == "asc" ? "desc" : "asc";
									$sort_link = isset($_GET["order"]) && $_GET["order"] == $columnname && $_GET["sort"] == "desc" ? "asc" : $sort_link;
									echo "<th><a href=?search=$search&order=unit_price&sort=".$sort_link.">Precio Unitario</a></th>";
									$columnname = "subtotal";
									$sort_link = isset($_GET["order"]) && $_GET["order"] == $columnname && $_GET["sort"] == "asc" ? "desc" : "asc";
									$sort_link = isset($_GET["order"]) && $_GET["order"] == $columnname && $_GET["sort"] == "desc" ? "asc" : $sort_link;
									echo "<th><a href=?search=$search&order=subtotal&sort=".$sort_link.">Subtotal</a></th>";
 ?>
                                            <th><?php translate('Actions'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = mysqli_fetch_array($result)): ?>
                                            <tr>
                                                <?php echo "<td>" . htmlspecialchars($row['ord_det_id'] ?? "") . "</td>";
										echo "<td>" . get_fk_url($row["ord_det_order_id"], "pps_orders", "ord_id", $row["ord_det_order_idpps_ordersord_id"], 1, true) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['ord_det_prod_id'] ?? "") . "</td>";
											//echo "<td>" . get_fk_url($row["ord_det_prod_id"], "pps_products", "prd_id", $row["ord_det_prod_idpps_productsprd_id"], 1, true) . "</td>";
											echo "<td>" . htmlspecialchars($row['qty'] ?? "") . "</td>";
										echo "<td>" . htmlspecialchars($row['unit_price'] ?? "") . "</td>";
										echo "<td>" . htmlspecialchars($row['subtotal'] ?? "") . "</td>";
										 ?>
                                                <td>
                                                    <?php
                                                    $column_id = 'ord_det_id';
                                                    if (!empty($column_id)): ?>
                                                    <!--
                                                        <a id='read-<?php echo $row['ord_det_id']; ?>' href='pps_order_details-read.php?ord_det_id=<?php echo $row['ord_det_id']; ?>' title='<?php echo addslashes(translate('View Record', false)); ?>' data-toggle='tooltip' class='btn btn-sm btn-info'><i class='far fa-eye'></i></a>

                                                        -->
                                                        <?php if ($UserRol == "A"): ?>
                                                            <!--
                                                                <a id='update-<?php echo $row['ord_det_id']; ?>' href='pps_order_details-update.php?ord_det_id=<?php echo $row['ord_det_id']; ?>' title='<?php echo addslashes(translate('Update Record', false)); ?>' data-toggle='tooltip' class='btn btn-sm btn-warning'><i class='far fa-edit'></i></a>
                                                                <a id='delete-<?php echo $row['ord_det_id']; ?>' href='pps_order_details-delete.php?ord_det_id=<?php echo $row['ord_det_id']; ?>' title='<?php echo addslashes(translate('Delete Record', false)); ?>' data-toggle='tooltip' class='btn btn-sm btn-danger'><i class='far fa-trash-alt'></i></a>
                                                            -->    
                                                   <?php endif; ?>  
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
                                    <li class="page-item"><a class="page-link" href="<?php echo $new_url .'&pageno=1' ?>"><?php translate('First') ?></a></li>
                                    <li class="page-item <?php if($pageno <= 1){ echo 'disabled'; } ?>">
                                        <a class="page-link" href="<?php if($pageno <= 1){ echo '#'; } else { echo $new_url ."&pageno=".($pageno - 1); } ?>"><?php translate('Prev') ?></a>
                                    </li>
                                    <li class="page-item <?php if($pageno >= $total_pages){ echo 'disabled'; } ?>">
                                        <a class="page-link" href="<?php if($pageno >= $total_pages){ echo '#'; } else { echo $new_url . "&pageno=".($pageno + 1); } ?>"><?php translate('Next') ?></a>
                                    </li>
                                    <li class="page-item <?php if($pageno >= $total_pages){ echo 'disabled'; } ?>">
                                        <a class="page-item"><a class="page-link" href="<?php echo $new_url .'&pageno=' . $total_pages; ?>"><?php translate('Last') ?></a>
                                    </li>
                                </ul>

                                <?php mysqli_free_result($result); ?>
                            <?php else: ?>
                            <p class='lead'><em><?php translate('No records were found.') ?></em></p>
                        <?php endif ?>

                    <?php else: ?>
                        <div class="alert alert-danger" role="alert">
                            ERROR: Could not able to execute <?php echo $sql. " " . mysqli_error($link); ?>
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
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</body>
</html>
