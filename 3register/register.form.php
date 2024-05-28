<?php
/**
 * Josevi
 * CETI
 * PPS - Puesta en Producción Segura
 *
 */

session_start();

if (!empty($_SESSION['Errors']))
{
	echo 'Sesion: ' . var_dump($_SESSION);
	$Errors = $_SESSION['Errors'];
	// Borrar los errores de la sesión
	unset($_SESSION['Errors']);
}

$Fields = array(
	'CustomerName' => array(
		'label' => _('Nombre usuario'),
		'data-form' => 'cus',
		'attr' => array('class' => 'form-control mb-3', 'placeholder' => 'Nombre usuario', 'maxlength' => 100, 'type' => 'text', 'data-required' => 'true'),
		'error' => _('El nombre de usuario es obligatorio.'),
	),
	'CustomerSurNames' => array(
		'label' => _('Apellidos'),
		'data-form' => 'cus',
		'attr' => array('class' => 'form-control mb-3', 'placeholder' => 'Apellidos', 'maxlength' => 200, 'type' => 'text', 'data-required' => 'true'),
		'error' => _('Los apellidos son obligatorios.'),
	),
	'CompanyName' => array(
		'label' => _('Nombre empresa'),
		'data-form' => 'com',
		'attr' => array('class' => 'form-control mb-3', 'placeholder' => 'Nombre empresa', 'maxlength' => 100, 'type' => 'text', 'data-required' => 'true'),
		'error' => _('El nombre de empresa es obligatorio.'),
	),
	'Cif' => array(
		'label' => _('Cif'),
		'data-form' => 'com',
		'attr' => array('class' => 'form-control mb-3', 'placeholder' => 'Cif', 'maxlength' => 12, 'type' => 'text', 'data-required' => 'true'),
		'error' => _('El CIF es obligatorio.(1234567849A)'),
	),
	'Prefix' => array(
		'label' => _('Prefijo'),
		'data-form' => 'all',
		'attr' => array('class' => 'form-control mb-3', 'placeholder' => 'Prefijo', 'maxlength' => 5, 'type' => 'text', 'data-required' => 'true'),
		'error' => _('El prefijo es obligatorio. (+34)'),
	),
	'PhoneNumber' => array(
		'label' => _('Teléfono'),
		'data-form' => 'all',
		'attr' => array('class' => 'form-control mb-3', 'placeholder' => 'Teléfono', 'maxlength' => 11, 'type' => 'tel', 'data-required' => 'true'),
		'error' => _('El teléfono es obligatorio. (666999666)'),
	),
	'CompanyWeb' => array(
		'label' => _('Página Web'),
		'data-form' => 'com',
		'attr' => array('class' => 'form-control mb-3', 'placeholder' => 'Página Web', 'maxlength' => 50, 'type' => 'text', 'data-required' => 'true'),
		'error' => _('La web es obligatoria.'),
	),
	'Email' => array(
		'label' => _('Email'),
		'data-form' => 'all',
		'attr' => array('class' => 'form-control mb-3', 'placeholder' => 'Email', 'maxlength' => 200, 'type' => 'email', 'data-required' => 'true'),
		'error' => _('El correo es obligatorio.'),
	),
	'ConfirmEmail' => array(
		'label' => _('Confirmar email'),
		'data-form' => 'all',
		'attr' => array('class' => 'form-control mb-3', 'placeholder' => 'Confirmar email', 'maxlength' => 200, 'type' => 'email', 'data-required' => 'true'),
		'error' => _('Los correos no coinciden.'),
	),
	'Password' => array(
		'label' => _('Contraseña'),
		'data-form' => 'all',
		'attr' => array('class' => 'form-control mb-3', 'placeholder' => 'Contraseña', 'maxlength' => 300, 'type' => 'password', 'data-required' => 'true'),
		'error' => _('Has de introducir mayúsculas, minúsculas, números y caracteres especiales.'),
	),
	'ConfirmPassword' => array(
		'label' => _('Confirmar contraseña'),
		'data-form' => 'all',
		'attr' => array('class' => 'form-control mb-3', 'placeholder' => 'Confirmar contraseña', 'maxlength' => 300, 'type' => 'password', 'data-required' => 'true'),
		'error' => _('Las contraseñas no coinciden.'),
	),
	'CompanyDocuments' => array(
		'label' => _('Documento comercial o información fiscal. Máx 2 pdf.'),
		'data-form' => 'com', 'array' => '2',
		'attr' => array('class' => 'form-control mb-3', 'placeholder' => 'Documento comercial o información fiscal. Máx 2 pdf', 'type' => 'file',  'accept' => 'application/pdf', 'data-required' => 'true'),
		'error' => _('El documento es obligatorio.'),
	),
);

include "../nav.php";
?>

<meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">

<div class="centerSide">
	<form method="POST" action="register.php" enctype="multipart/form-data" class="needs-validation">
	    
		<div id="SelectUserType">
			<label><h2><?php echo _('Selecciona el tipo de usuario:'); ?></h2></label><br>
			<input type="radio" name="UserType" value="cus" id="UserType_Cus" checked><label for="UserType_Cus"><?php echo _('Cliente'); ?></label>
			<input type="radio" name="UserType" value="com" id="UserType_Com"><label for="UserType_Com"><?php echo _('Empresa'); ?></label>
		</div>
		<br>
		<div id="FormFields" class="position-relative">
			<h2 id="FormTitle"><?php echo _('Registro de Usuario'); ?></h2>
			<ul id="remove" >
				<?php
				foreach ($Fields as $Key => $Value)
				{
					$Class    = ($Value['data-form'] == 'com') ? 'hidden' : '';
					$Required = (isset($Value['attr']['data-required'])) ? ' required="required"' : '';
				?>
	                	<li data-form="<?php echo $Value['data-form']; ?>" class="<?php echo $Class; ?> mb-3">
					 
					<?php
					$items = 1;
					$is_array = '';
					if(isset($Value['array']) && $Value['array'] > 1 )
					{
						$items = $Value['array'];
						$is_array = '[]';
					}
					$count = 1;
					do
					{
					?>
					<input name="<?php echo $Key . $is_array; ?>" id="<?php echo $Key . ((!empty($is_array)) ? $count : ''); ?>" <?php
					foreach ($Value['attr'] as $vKey => $vValue)
					{
						echo $vKey . '="' . $vValue . '" ';
					}
					echo $Required;
					?>/>
					<?php
						$count++;
						if(isset($Value['error']))
						{
						?>
						<div class="data_error <?php echo $Key;?>_error hidden"><?php echo $Value['error'];?></div>
						<?php 
						} 
					}
					while($count <= $items);

					if (!empty($Errors)) {
						foreach ($Errors as $ErrorName => $ErrorMessage) {
						{
							if ("Error$Key" == $ErrorName)
							{
								echo '<div class="data_error">' . htmlspecialchars($ErrorMessage) . '</div>';
							}
						}
						}
					}
					?>
	                    </li>
				<?php
				}
				?>
			</ul>
			<input type="submit" class="btn btn-primary" name="register" value="<?php echo _('Enviar'); ?>"/>
		</div>
	</form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="./js/main.js"></script>

<style type="text/css">
.hidden{
	display: none;
}
.data_error{
	color: #c00;
	font-size: 0.8em
}
ul#remove {
	list-style-type: none;
	padding: 0;
}
.centerSide{
	width: 50%; /* Ajusta este valor según sea necesario */
	margin: 0 auto;
}
</style>