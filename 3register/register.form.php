<?php
/**
 * Josevi
 * CETI
 * PPS - Puesta en Producción Segura
 *
 */

//if (!defined('SI_NO_EXISTE_PETA'))
//{
//	die('No me seas cabrón y sal de aquí');
//}

$Fields = array(
	'CustomerName' => array(
		'label' => _('Nombre usuario'),
		'data-form' => 'cus',
		'attr' => array('maxlength' => 100, 'type' => 'text', 'data-required' => 'true'),
	),
	'CustomerSurNames' => array(
		'label' => _('Apellidos'),
		'data-form' => 'cus',
		'attr' => array('maxlength' => 200, 'type' => 'text', 'data-required' => 'true'),
	),
	'CompanyName' => array(
		'label' => _('Nombre empresa'),
		'data-form' => 'com',
		'attr' => array('maxlength' => 100, 'type' => 'text', 'data-required' => 'true'),
	),
	'Cif' => array(
		'label' => _('Cif'),
		'data-form' => 'com',
		'attr' => array('maxlength' => 12, 'type' => 'text', 'data-required' => 'true'),
	),
	'Prefix' => array(
		'label' => _('Prefijo'),
		'data-form' => 'all',
		'attr' => array('maxlength' => 5, 'type' => 'text', 'data-required' => 'true'),
	),
	'PhoneNumber' => array(
		'label' => _('Teléfono'),
		'data-form' => 'all',
		'attr' => array('maxlength' => 11, 'type' => 'tel', 'data-required' => 'true'),
	),
	'Address' => array(
		'label' => _('Dirección'),
		'data-form' => 'all',
		'attr' => array('maxlength' => 200, 'type' => 'text', 'data-required' => 'true'),
	),
	'CompanyWeb' => array(
		'label' => _('Página Web'),
		'data-form' => 'com',
		'attr' => array('maxlength' => 50, 'type' => 'text'),
	),
	'Email' => array(
		'label' => _('Email'),
		'data-form' => 'all',
		'attr' => array('maxlength' => 200, 'type' => 'email', 'data-required' => 'true'),
	),
	'ConfirmEmail' => array(
		'label' => _('Confirmar email'),
		'data-form' => 'all',
		'attr' => array('maxlength' => 200, 'type' => 'email', 'data-required' => 'true'),
	),
	'Password' => array(
		'label' => _('Contraseña'),
		'data-form' => 'all',
		'attr' => array('maxlength' => 300, 'type' => 'password', 'data-required' => 'true'),
	),
	'ConfirmPassword' => array(
		'label' => _('Confirmar contraseña'),
		'data-form' => 'all',
		'attr' => array('maxlength' => 300, 'type' => 'password', 'data-required' => 'true'),
	),
	'CompanyDocuments' => array(
		'label' => _('Documento comercial o información fiscal. Máx 2 pdf.'),
		'data-form' => 'com',
		'attr' => array('maxlength' => 200, 'type' => 'file', 'data-required' => 'true', 'accept' => '.pdf', 'multiple' => 'multiple'),
	),
);

?>
<script type="text/javascript">
	radiobtn = document.getElementById("UserType_Cus");
	radiobtn.checked = true;
</script>

<?php
	include "../nav.php";
?>

<div class="RegisterForm">
	<!--
	<form method="POST" action="register.php">
	-->
	<form method="POST" action="register.php" enctype="multipart/form-data">
	    <!--
	    <input type="hidden" name="action" value="register"/>
		-->
	    
	    <div id="SelectUserType">
	        <label><?php echo _('Selecciona el tipo de usuario:'); ?></label>
	        <input type="radio" name="UserType" value="cus" id="UserType_Cus" checked><label for="UserType_Cus"><?php echo _('Cliente'); ?></label>
	        <input type="radio" name="UserType" value="com" id="UserType_Com"><label for="UserType_Com"><?php echo _('Empresa'); ?></label>
	    </div>
	    
	    <div id="FormFields">
	        <h2 id="FormTitle"><?php echo _('Registro de Usuario'); ?></h2>
	        <ul>
				<?php
					foreach ($Fields as $Key => $Value)
					{
						$Class    = ($Value['data-form'] == 'com') ? 'hidden' : '';
						$Required = (isset($Value['attr']['data-required'])) ? ' required="required"' : '';
						?>
	                    <li data-form="<?php echo $Value['data-form']; ?>" class="<?php echo $Class; ?>">
	                        <label for="<?php echo $Key; ?>"><?php echo $Value['label']; ?></label>
	                        <input name="<?php echo $Key; ?>" id="<?php echo $Key; ?>" <?php
								foreach ($Value['attr'] as $vKey => $vValue)
								{
									echo $vKey . '="' . $vValue . '" ';
								}
								echo $Required;
							?>/>
	                    </li>
						<?php
					}
				?>
	        </ul>
	        <input type="submit" name="register" value="<?php echo _('Enviar'); ?>"/>
	    </div>
	</form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script type="text/javascript">
jQuery(document).ready(function ($) {
	$('li.hidden > input').removeAttr('required');
	$('input[name="UserType"').change(function () {
		$('li[data-form="cus"').toggleClass('hidden');
		$('li[data-form="com"').toggleClass('hidden');
		$('input[data-required="true"]').attr('required', 'true');
		$('li.hidden > input').removeAttr('required');
	});
});
</script>