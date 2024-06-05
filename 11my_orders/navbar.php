<?php require_once('config-tables-columns.php'); ?>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand nav-link" href="index.php">eshop-pps</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <?php translate('Select Page') ?>
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
        <a href="pps_order_details-index.php" class="dropdown-item">pps_order_details</a> 
	
        	<a class="dropdown-item" href="pps_orders-index.php"><?php echo (!empty($tables_and_columns_names["pps_orders"]["name"])) ? $tables_and_columns_names["pps_orders"]["name"] : "pps_orders" ?></a>
		<a class="dropdown-item" href="pps_orders_history-index.php"><?php echo (!empty($tables_and_columns_names["pps_orders_history"]["name"])) ? $tables_and_columns_names["pps_orders_history"]["name"] : "pps_orders_history" ?></a>
		
    <!--
    <a class="dropdown-item" href="pps_products-index.php"><?php echo (!empty($tables_and_columns_names["pps_products"]["name"])) ? $tables_and_columns_names["pps_products"]["name"] : "pps_products" ?></a>
		<a class="dropdown-item" href="pps_users-index.php"><?php echo (!empty($tables_and_columns_names["pps_users"]["name"])) ? $tables_and_columns_names["pps_users"]["name"] : "pps_users" ?></a>
-->
  
    <!-- TABLE_BUTTONS -->
        </div>
      </li>
    </ul>
  </div>
</nav>