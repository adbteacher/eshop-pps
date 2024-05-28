/**
 * Josevi
 * CETI
 * PPS - Puesta en Producción Segura
 *
 */

jQuery(document).ready(function ($)
{

	$('input[name="UserType_Cus"]').attr('checked', 'checked');

	$('li.hidden > input').removeAttr('required');
	$('input[name="UserType"').change(function () {
		$('li[data-form="cus"').toggleClass('hidden');
		$('li[data-form="com"').toggleClass('hidden');
		$('input[data-required="true"]').attr('required', 'true');
		$('li.hidden > input').removeAttr('required');
	});

	$('form').on('click', 'input[name="register"]', function(e){
		var error = false;
		$('input[data-required="true"]').each(function(){
			var msgError = 'div.' + $(this).attr('id') + '_error';
			if(!$(msgError).hasClass('hidden')){ $(msgError).addClass('hidden'); }
			if($(this).val().trim() == '' && !$(this).parent().hasClass('hidden')){
				$(msgError).removeClass('hidden')
				error = true;
			}
		});
		if(error){
			e.preventDefault();
			return false;
		}
	});
});
