/**
 * Josevi
 * CETI
 * PPS - Puesta en Producción Segura
 *
 */

jQuery(document).ready(function ($) {
    $('input[name="UserType_U"]').attr('checked', 'checked');

    $('li.hidden > input').removeAttr('required');
    $('input[name="UserType"]').change(function () {
        var userType = $(this).val();
        $('li[data-form="U"]').each(function() {
            $(this).addClass('fadeOut');
            setTimeout(() => {
                $(this).addClass('hidden').removeClass('fadeOut').removeClass('fadeIn');
                if (userType === 'U') {
                    $(this).removeClass('hidden').addClass('fadeIn');
                }
                $(this).find('input').attr('required', userType === 'U');
            }, 500);
        });
        $('li[data-form="V"]').each(function() {
            $(this).addClass('fadeOut');
            setTimeout(() => {
                $(this).addClass('hidden').removeClass('fadeOut').removeClass('fadeIn');
                if (userType === 'V') {
                    $(this).removeClass('hidden').addClass('fadeIn');
                }
                $(this).find('input').attr('required', userType === 'V');
            }, 500);
        });
    });

    $('form').on('click', 'input[name="register"]', function (e) {
        var error = false;
        $('input[data-required="true"]').each(function () {
            var msgError = 'div.' + $(this).attr('id') + 'Error';
            if (!$(msgError).hasClass('hidden')) { $(msgError).addClass('hidden'); }
            if ($(this).val().trim() == '' && !$(this).parent().hasClass('hidden')) {
                $(msgError).removeClass('hidden')
                error = true;
            }
        });
        if (error) {
            e.preventDefault();
            return false;
        }
    });
});