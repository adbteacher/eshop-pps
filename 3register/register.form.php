<?php
/**
 * Josevi
 * CETI
 * PPS - Puesta en Producción Segura
 *
 */

if(session_status() != PHP_SESSION_ACTIVE) session_start();

$Errors = isset($_SESSION['Errors']) ? $_SESSION['Errors'] : array();
if (!empty($_SESSION['Errors']))
{
	$Errors = $_SESSION['Errors'];
	// Borrar los errores de la sesión
	unset($_SESSION['Errors']);
}

$Fields = array(
	'CustomerName' => array(
		'label' => _('Nombre usuario'),
		'data-form' => 'U',
		'attr' => array('class' => 'form-control mb-3', 'placeholder' => 'Nombre usuario',
			'maxlength' => 100, 'type' => 'text', 'data-required' => 'true',
			'value' => isset($formValues['CustomerName']) ? $formValues['CustomerName'] : ''),
		'error' => _('El nombre de usuario es obligatorio.'),
	),
	'CustomerSurNames' => array(
		'label' => _('Apellidos'),
		'data-form' => 'U',
		'attr' => array('class' => 'form-control mb-3', 'placeholder' => 'Apellidos',
			'maxlength' => 200, 'type' => 'text', 'data-required' => 'true',
			'value' => isset($formValues['CustomerSurNames']) ? $formValues['CustomerSurNames'] : ''),
		'error' => _('Los apellidos son obligatorios.'),
	),
	'CompanyName' => array(
		'label' => _('Nombre empresa'),
		'data-form' => 'V',
		'attr' => array('class' => 'form-control mb-3', 'placeholder' => 'Nombre empresa',
			'maxlength' => 100, 'type' => 'text', 'data-required' => 'true',
			'value' => isset($formValues['CompanyName']) ? $formValues['CompanyName'] : ''),
		'error' => _('El nombre de empresa es obligatorio.'),
	),
	'Cif' => array(
		'label' => _('Cif'),
		'data-form' => 'V',
		'attr' => array('class' => 'form-control mb-3', 'placeholder' => 'Cif',
			'maxlength' => 12, 'type' => 'text', 'data-required' => 'true',
			'value' => isset($formValues['Cif']) ? $formValues['Cif'] : ''),
		'error' => _('El CIF es obligatorio.(1234567849A)'),
	),
	'Prefix' => array(
		'label' => _('Prefijo'),
		'data-form' => 'all',
		'attr' => array('class' => 'form-control mb-3', 'placeholder' => 'Prefijo',
			'maxlength' => 5, 'type' => 'text', 'data-required' => 'true',
			'value' => isset($formValues['Prefix']) ? $formValues['Prefix'] : ''),
		'error' => _('El prefijo es obligatorio. (+34)'),
	),
	'PhoneNumber' => array(
		'label' => _('Teléfono'),
		'data-form' => 'all',
		'attr' => array('class' => 'form-control mb-3', 'placeholder' => 'Teléfono',
			'maxlength' => 11, 'type' => 'tel', 'data-required' => 'true',
			'value' => isset($formValues['PhoneNumber']) ? $formValues['PhoneNumber'] : ''),
		'error' => _('El teléfono es obligatorio. (666999666)'),
	),
	'CompanyWeb' => array(
		'label' => _('Página Web'),
		'data-form' => 'V',
		'attr' => array('class' => 'form-control mb-3', 'placeholder' => 'Página Web',
			'maxlength' => 50, 'type' => 'text', 'data-required' => 'true',
			'value' => isset($formValues['CompanyWeb']) ? $formValues['CompanyWeb'] : ''),
		'error' => _('La web es obligatoria.'),
	),
	'Email' => array(
		'label' => _('Email'),
		'data-form' => 'all',
		'attr' => array('class' => 'form-control mb-3', 'placeholder' => 'Email',
			'maxlength' => 200, 'type' => 'email', 'data-required' => 'true',
			'value' => isset($formValues['Email']) ? $formValues['Email'] : ''),
		'error' => _('El correo es obligatorio.'),
	),
	'ConfirmEmail' => array(
		'label' => _('Confirmar email'),
		'data-form' => 'all',
		'attr' => array('class' => 'form-control mb-3', 'placeholder' => 'Confirmar email',
			'maxlength' => 200, 'type' => 'email', 'data-required' => 'true',
			'value' => isset($formValues['ConfirmEmail']) ? $formValues['ConfirmEmail'] : ''),
		'error' => _('Los correos no coinciden.'),
	),
	'Password' => array(
		'label' => _('Contraseña'),
		'data-form' => 'all',
		'attr' => array('class' => 'form-control mb-3', 'placeholder' => 'Contraseña',
			'maxlength' => 300, 'type' => 'password', 'data-required' => 'true',
			'value' => isset($formValues['Password']) ? $formValues['Password'] : ''),
		'error' => _('Has de introducir mayúsculas, minúsculas, números y carácteres especiales.'),
	),
	'ConfirmPassword' => array(
		'label' => _('Confirmar contraseña'),
		'data-form' => 'all',
		'attr' => array('class' => 'form-control mb-3', 'placeholder' => 'Confirmar contraseña',
			'maxlength' => 300, 'type' => 'password', 'data-required' => 'true',
			'value' => isset($formValues['ConfirmPassword']) ? $formValues['ConfirmPassword'] : ''),
		'error' => _('Las contraseñas no coinciden.'),
	),
	'CompanyDocuments' => array(
		'label' => _('Documento comercial o información fiscal. Máx 2 pdf.'),
		'data-form' => 'V', 'array' => '2',
		'attr' => array('class' => 'form-control mb-3', 'placeholder' => 'Documento comercial o i
				formación fiscal. Máx 2 pdf', 'type' => 'file', 'accept' => 'application/pdf', 'data-required' => 'true',
				'value' => isset($formValues['CompanyDocuments']) ? $formValues['CompanyDocuments'] : ''),
		'error' => _('El documento es obligatorio.'),
	),
	'UserExist' => array(
		'error' => _('El usuario ya existe.'),
	),
	'SendMail' => array(
		'error' => _('Error con el código de verificación. Póngase en contacto con un administrador para poder registrarse correctamente o vuelva a intentarlo.'),
	),
);

?>
<meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">

<?php 
include "../nav.php";
?>


<div class="centerSide">
	<form method="POST" action="register.php" enctype="multipart/form-data" class="needs-validation">
	    
		<div id="SelectUserType">
			<label><h2><?php echo _('Selecciona el tipo de usuario:'); ?></h2></label><br>
			<input type="radio" name="UserType" value="U" id="UserType_U" checked><label for="UserType_U"><?php echo _('Cliente'); ?></label>
			<input type="radio" name="UserType" value="V" id="UserType_V"><label for="UserType_V"><?php echo _('Empresa'); ?></label>
		</div>
		<br>
		<div id="FormFields" class="position-relative">
			<h2 id="FormTitle"><?php echo _('Registro de Usuario'); ?></h2>
			<ul class="remove" >
				<?php
				foreach ($Fields as $Key => $Value)
				{
					if (isset($Value['data-form']))
					{
						$Class    = ($Value['data-form'] == 'V') ? 'hidden' : '';
						$Required = (isset($Value['attr']['data-required'])) ? ' required="required"' : '';
					}
				?>
	                	<li data-form="<?php if (isset($Value['data-form']))
	                	{
		                	echo $Value['data-form'];} ?>" class="<?php echo $Class; ?> mb-3">
					 
                	<?php
					$items    = 1;
					$is_array = '';
					if(isset($Value['array']) && $Value['array'] > 1 )
					{
						$items    = $Value['array'];
						$is_array = '[]'; // Si es un array se carga por separado
					}
					$count = 1;
					do
					{
					
					if (isset($Value['attr'])) {
						?>
						<!-- Se muestran y cargan los formularios y sus atributos -->
						<input name="<?php echo $Key . $is_array; ?>" id="<?php echo $Key . ((!empty($is_array)) ? $count : ''); ?>" <?php
						foreach ($Value['attr'] as $vKey => $vValue)
							{
								echo $vKey . '="' . $vValue . '" ';
							}
						echo $Required;
						?>/>
					
					<?php
						}
						
						$count++;

						// Muestra de errores del lado del servidor
						if(isset($Value['error']))
						{
						?>
						<div class="dataError <?php echo $Key;?>Error<?php echo in_array($Key, $Errors) ? '' : ' hidden';?>"><?php echo $Value['error'];?></div>

						<?php 
						} 
					}
					while($count <= $items);
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
.hidden
{
	display: none;
}
.dataError
{
	color: #c00;
	font-size: 0.8em
}
ul.remove
{
	list-style-type: none;
	padding: 0;
}
.centerSide
{
	width: 50%;
	margin: 0 auto;
}
</style>