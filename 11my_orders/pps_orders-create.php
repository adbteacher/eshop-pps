<?php
	require_once '../autoload.php';
	require_once('config.php');
    require_once('helpers.php');

	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}

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

        $ord_user_id = $_POST["ord_user_id"] == "" ? null : trim($_POST["ord_user_id"]);
		$ord_purchase_date = $_POST["ord_purchase_date"] == "" ? null : trim($_POST["ord_purchase_date"]);
		$ord_shipping_date = $_POST["ord_shipping_date"] == "" ? null : trim($_POST["ord_shipping_date"]);
		$ord_order_status = trim($_POST["ord_order_status"]);
		$ord_shipping_address = trim($_POST["ord_shipping_address"]);
		


        $stmt = $link->prepare("INSERT INTO `pps_orders` (`ord_user_id`, `ord_purchase_date`, `ord_shipping_date`, `ord_order_status`, `ord_shipping_address`) VALUES (?, ?, ?, ?, ?)");


        // obtener una array con los atributos
        $atributos = explode(',', str_replace(' ', '',str_replace('`', '', "`ord_user_id`, `ord_purchase_date`, `ord_shipping_date`, `ord_order_status`, `ord_shipping_address`")));

        // Contar los elementos del array
        $cantidad_atributos = count($atributos);
       
        // Generar una cadena con tantas "s" como atributos tenga
        $cadena_s = str_repeat('s', $cantidad_atributos);

        try {
            $stmt->bind_param($cadena_s,$ord_user_id, $ord_purchase_date, $ord_shipping_date , $ord_order_status, $ord_shipping_address);
           // $stmt->bind_param($cadena_s,$prd_name, $prd_category, $prd_details, $prd_price, $prd_quantity_shop, $prd_stock, $prd_image, $prd_description);

            $stmt->execute();
        } catch (Exception $e) {
            error_log($e->getMessage());
            $error = $e->getMessage();
        }

        if (!isset($error)){
            $new_id = mysqli_insert_id($link);
            header("location: pps_orders-read.php?ord_id=$new_id");
        } else {
            /*
            $uploaded_files = array();
            foreach ($upload_results as $result) {
                if (isset($result['success'])) {
                    // Delete the uploaded files if there were any error while saving postdata in DB
                    unlink($upload_target_dir . $result['success']);
                }
            }
            */
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
                                            <label for="ord_user_id">ord_user_id</label>
                                            <select class="form-control" id="ord_user_id" name="ord_user_id"><option value="">Null</option> <?php
                                    $sql = "SELECT `usu_id`, `usu_name`, `usu_surnames`, `usu_id` FROM `pps_users` ORDER BY `usu_id`, `usu_name`, `usu_surnames`";
                                    $result = mysqli_query($link, $sql);
                                    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                                        $duprow = $row;
                                        unset($duprow["usu_id"]);
                                        $value = implode(" | ", $duprow);
                                        $ord_user_id = isset($ord_user_id) ? $ord_user_id : null;
                                        if ($row["usu_id"] == $ord_user_id){
                                            echo '<option value="' . $row["usu_id"] . '"selected="selected">' . $value . '</option>';
                                        } else {
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
							 $enum_ord_order_status = array('Creado','PendienteEnvio','Enviado','Pendiente Devolución','Reembolsado');
                                                foreach ($enum_ord_order_status as  $val){
                                                    if ($val == $ord_order_status){
                                                        echo '<option value="' . $val . '" selected="selected">' . $val . '</option>';
                                                    } else
                                                    echo '<option value="' . $val . '">' . $val . '</option>';
                                                }
                                                ?></select>
                                        </div>
						<div class="form-group">
                                            <label for="ord_shipping_address">ord_shipping_address*</label>
                                            <input type="text" name="ord_shipping_address" id="ord_shipping_address" maxlength="255" class="form-control" value="<?php echo @$ord_shipping_address; ?>">
                                        </div>

                        <input type="submit" class="btn btn-primary" value="<?php translate('Create') ?>">
                        <a href="pps_orders-index.php" class="btn btn-secondary"><?php translate('Cancel') ?></a>
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