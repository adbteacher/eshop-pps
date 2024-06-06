<?php
	require_once '../autoload.php';
	session_start();

	// Verificar si el usuario está autenticado
	functions::ActiveSession();

	//Comprobar permisos al programa
	functions::HasPermissions(basename(__FILE__));

	$tables_and_columns_names = array (
  'pps_order_details' => 
  array (
    'name' => '',
    'columns' => 
    array (
      'ord_det_id' => 
      array (
        'columndisplay' => 'idDetallePedido',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 1,
      ),
      'ord_det_order_id' => 
      array (
        'columndisplay' => 'idPedido',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 1,
        'primary' => 1,
        'auto' => 0,
      ),
      'ord_det_prod_id' => 
      array (
        'columndisplay' => 'idProducto',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 1,
        'primary' => 1,
        'auto' => 0,
      ),
      'qty' => 
      array (
        'columndisplay' => 'Cantidad',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'unit_price' => 
      array (
        'columndisplay' => 'Precio Unitario',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'subtotal' => 
      array (
        'columndisplay' => 'Subtotal',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
    ),
  ),
  'pps_orders' => 
  array (
    'name' => '',
    'columns' => 
    array (
      'ord_id' => 
      array (
        'columndisplay' => 'ord_id',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 1,
        'fk' => 0,
        'primary' => 1,
        'auto' => 1,
      ),
      'ord_user_id' => 
      array (
        'columndisplay' => 'ord_user_id',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 1,
        'fk' => 1,
        'primary' => 1,
        'auto' => 0,
      ),
      'ord_purchase_date' => 
      array (
        'columndisplay' => 'ord_purchase_date',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'ord_shipping_date' => 
      array (
        'columndisplay' => 'ord_shipping_date',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'ord_order_status' => 
      array (
        'columndisplay' => 'ord_order_status',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'ord_shipping_address' => 
      array (
        'columndisplay' => 'ord_shipping_address',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
    ),
  ),
  'pps_orders_history' => 
  array (
    'name' => '',
    'columns' => 
    array (
      'ord_hist_id' => 
      array (
        'columndisplay' => 'ord_hist_id',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 1,
      ),
      'ord_hist_order_id' => 
      array (
        'columndisplay' => 'ord_hist_order_id',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 1,
        'primary' => 1,
        'auto' => 0,
      ),
      'ord_hist_transaction_type' => 
      array (
        'columndisplay' => 'ord_hist_transaction_type',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'ord_hist_transaction_date' => 
      array (
        'columndisplay' => 'ord_hist_transaction_date',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'ord_hist_amount' => 
      array (
        'columndisplay' => 'ord_hist_amount',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
    ),
  ),
  'pps_products' => 
  array (
    'name' => '',
    'columns' => 
    array (
      'prd_id' => 
      array (
        'columndisplay' => 'prd_id',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 1,
        'fk' => 0,
        'primary' => 1,
        'auto' => 1,
      ),
      'prd_name' => 
      array (
        'columndisplay' => 'prd_name',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 1,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'prd_category' => 
      array (
        'columndisplay' => 'prd_category',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'prd_details' => 
      array (
        'columndisplay' => 'prd_details',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'prd_price' => 
      array (
        'columndisplay' => 'prd_price',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'prd_quantity_shop' => 
      array (
        'columndisplay' => 'prd_quantity_shop',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'prd_stock' => 
      array (
        'columndisplay' => 'prd_stock',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'prd_image' => 
      array (
        'columndisplay' => 'prd_image',
        'is_file' => 1,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'prd_description' => 
      array (
        'columndisplay' => 'prd_description',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
    ),
  ),
  'pps_users' => 
  array (
    'name' => '',
    'columns' => 
    array (
      'usu_id' => 
      array (
        'columndisplay' => 'usu_id',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 1,
        'fk' => 0,
        'primary' => 1,
        'auto' => 1,
      ),
      'usu_type' => 
      array (
        'columndisplay' => 'usu_type',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'usu_rol' => 
      array (
        'columndisplay' => 'usu_rol',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'usu_status' => 
      array (
        'columndisplay' => 'usu_status',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'usu_verification_code' => 
      array (
        'columndisplay' => 'usu_verification_code',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'usu_datetime' => 
      array (
        'columndisplay' => 'usu_datetime',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'usu_name' => 
      array (
        'columndisplay' => 'usu_name',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 1,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'usu_surnames' => 
      array (
        'columndisplay' => 'usu_surnames',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 1,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'usu_prefix' => 
      array (
        'columndisplay' => 'usu_prefix',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'usu_phone' => 
      array (
        'columndisplay' => 'usu_phone',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'usu_address' => 
      array (
        'columndisplay' => 'usu_address',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'usu_email' => 
      array (
        'columndisplay' => 'usu_email',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'usu_password' => 
      array (
        'columndisplay' => 'usu_password',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'usu_company' => 
      array (
        'columndisplay' => 'usu_company',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'usu_cif' => 
      array (
        'columndisplay' => 'usu_cif',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'usu_web' => 
      array (
        'columndisplay' => 'usu_web',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
      'usu_documents' => 
      array (
        'columndisplay' => 'usu_documents',
        'is_file' => 0,
        'columnvisible' => 1,
        'columninpreview' => 0,
        'fk' => 0,
        'primary' => 1,
        'auto' => 0,
      ),
    ),
  ),
);

?>