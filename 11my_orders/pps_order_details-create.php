<?php
	require_once '../autoload.php';
	require_once('config.php');
    require_once('helpers.php');

	session_start();

	// Verificar si el usuario está autenticado
	functions::ActiveSession();

	//Comprobar permisos al programa
	functions::HasPermissions(basename(__FILE__));

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Checking for upload fields
    $upload_results = array();
    

    $upload_errors = array();
    
    // Check for regular fields
    if (!in_array(true, array_column($upload_results, 'error'))) {

        $ord_det_order_id = $_POST["ord_det_order_id"] == "" ? null : trim($_POST["ord_det_order_id"]);
		$ord_det_prod_id = $_POST["ord_det_prod_id"] == "" ? null : trim($_POST["ord_det_prod_id"]);
		$qty = trim($_POST["qty"]);
		$unit_price = trim($_POST["unit_price"]);
		$subtotal = trim($_POST["subtotal"]);
		
        

        $stmt = $link->prepare("INSERT INTO `pps_order_details` (`ord_det_order_id`, `ord_det_prod_id`, `qty`, `unit_price`, `subtotal`) VALUES (?, ?, ?, ?, ?)");


        // obtener una array con los atributos
        $atributos = explode(',', str_replace(' ', '',str_replace('`', '', "`ord_det_order_id`, `ord_det_prod_id`, `qty`, `unit_price`, `subtotal`")));

        // Contar los elementos del array
        $cantidad_atributos = count($atributos);
       
        // Generar una cadena con tantas "s" como atributos tenga
        $cadena_s = str_repeat('s', $cantidad_atributos);

        try {
            $stmt->bind_param($cadena_s,$ord_det_order_id, $ord_det_prod_id, $qty, $unit_price, $subtotal);

            $stmt->execute();
        } catch (Exception $e) {
            error_log($e->getMessage());
            $error = $e->getMessage();
        }

        if (!isset($error)){
            $new_id = mysqli_insert_id($link);
            header("location: pps_order_details-read.php?ord_det_id=$new_id");
        } else {
            $uploaded_files = array();
            foreach ($upload_results as $result) {
                if (isset($result['success'])) {
                    // Delete the uploaded files if there were any error while saving postdata in DB
                    unlink($upload_target_dir . $result['success']);
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
    <title><?php translate('Add New Record') ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
</head>
<?php require_once('navbar.php'); ?>
<body>
    <section class="pt-5">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6 mx-auto">
                    <div class="page-header">
                        <h2><?php translate('Add New Record') ?></h2>
                    </div>
                    <?php print_error_if_exists(@$upload_errors); ?>
                    <?php print_error_if_exists(@$error); ?>
                    <p><?php translate('add_new_record_instructions') ?></p>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">

                        <div class="form-group">
                                            <label for="ord_det_order_id">idPedido</label>
                                            <select class="form-control" id="ord_det_order_id" name="ord_det_order_id"><option value="">Null</option> <?php
                                    //$sql = "SELECT `ord_id`, `ord_user_id`, `ord_id` FROM `pps_orders` ORDER BY `ord_id`, `ord_user_id`";
                                    $sql = "SELECT  `ord_id`, `ord_user_id`  FROM `pps_orders` ORDER BY `ord_id`, `ord_user_id`";
                                    //$sql = "SELECT distinct `ord_id` FROM `pps_orders` ORDER BY `ord_id`, `ord_user_id`";
                                    $result = mysqli_query($link, $sql);
                                    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                                        $duprow = $row;
                                        unset($duprow["ord_id"]);
                                        $value = implode(" | ", $duprow);
                                        
                                        $ord_det_order_id = isset($ord_det_order_id) ? $ord_det_order_id : null;
                                        if ($row["ord_id"] == $ord_det_order_id){
                                            echo '<option value="' . $row["ord_id"] . '"selected="selected">' . $row["ord_id"]  . '</option>';
                                        } else {
                                            echo '<option value="' . $row["ord_id"] . '">' . $row["ord_id"]. '</option>';
                                        }
                                    }
                                    ?>
                                    </select>
                                        </div>
						<div class="form-group">
                                            <label for="ord_det_prod_id">idProducto</label>
                                            <select class="form-control" id="ord_det_prod_id" name="ord_det_prod_id"><option value="">Null</option> <?php
                                    $sql = "SELECT `prd_id`, `prd_name`, `prd_id` FROM `pps_products` ORDER BY `prd_id`, `prd_name`";
                                    $result = mysqli_query($link, $sql);
                                    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                                        $duprow = $row;
                                        unset($duprow["prd_id"]);
                                        $value = implode(" | ", $duprow);
                                        $ord_det_prod_id = isset($ord_det_prod_id) ? $ord_det_prod_id : null;
                                        if ($row["prd_id"] == $ord_det_prod_id){
                                            echo '<option value="' . $row["prd_id"] . '"selected="selected">' . $value . '</option>';
                                        } else {
                                            echo '<option value="' . $row["prd_id"] . '">' . $value . '</option>';
                                        }
                                    }
                                    ?>
                                    </select>
                                        </div>
						<div class="form-group">
                                            <label for="qty">Cantidad*</label>
                                            <input type="number" name="qty" id="qty" class="form-control" value="<?php echo @$qty; ?>">
                                        </div>
						<div class="form-group">
                                            <label for="unit_price">Precio Unitario*</label>
                                            <input type="number" name="unit_price" id="unit_price" class="form-control" value="<?php echo @$unit_price; ?>" step="any">
                                        </div>
						<div class="form-group">
                                            <label for="subtotal">Subtotal*</label>
                                            <input type="number" name="subtotal" id="subtotal" class="form-control" value="<?php echo @$subtotal; ?>" step="any">
                                        </div>

                        <input type="submit" class="btn btn-primary" value="<?php translate('Create') ?>">
                        <a href="pps_order_details-index.php" class="btn btn-secondary"><?php translate('Cancel') ?></a>
                    </form>
                    <p><small><?php translate('required_fiels_instructions') ?></small></p>
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