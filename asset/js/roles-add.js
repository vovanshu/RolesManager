$(document).ready(function() {

	var TypesAddAction = 'input[name="typeAddAction"]';

	intiTypesAddAction($(TypesAddAction));

	$(document).on('change', TypesAddAction, function() {
		intiTypesAddAction($(this));
	});

	function intiTypesAddAction(toggler) {
        var type = toggler.val();
		// console.log(type);
		if (type === 'changeNative') {
			$('#changeNative_field').show();
			$('#roleAStpl_field').hide();
			$('#parentRole_field').hide();
			$('#name_field').hide().removeClass('required');
			$('#name').removeAttr('required');
			$('#name').val($('#changeNative').val());
		} else if (type === 'roleAStpl') {
			$('#changeNative_field').hide();
			$('#roleAStpl_field').show();
			$('#parentRole_field').hide();
			$('#name_field').show().addClass('required');
			$('#name').attr('required', 'required');
			$('#name').val('');
		} else {
			$('#changeNative_field').hide();
			$('#roleAStpl_field').hide();
			$('#parentRole_field').show();
			$('#name_field').show().addClass('required');
			$('#name').attr('required', 'required');
			$('#name').val('');
		}

    }

});
