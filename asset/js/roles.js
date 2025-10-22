$(document).ready(function() {

/* Role a resource. */

// Add the selected role to the edit panel.
	$('#role-selector .selector-child').click(function(event) {
		event.preventDefault();

		$('#role-resources').removeClass('empty');
		var roleName = $(this).data('child-search');

		if ($('#role-resources').find(`input[value="${roleName}"]`).length) {
			return;
		}

		var row = $($('#role-template').data('template'));
		row.children('td.role-name').text(roleName);
		row.find('td > input').val(roleName);
		$('#role-resources > tbody:last').append(row);
	});

// Remove a role from the edit panel.
	$('#role-resources').on('click', '.o-icon-delete', function(event) {
		event.preventDefault();

		var removeLink = $(this);
		var roleRow = $(this).closest('tr');
		var roleInput = removeLink.closest('td').find('input');
		roleInput.prop('disabled', true);

		// Undo remove role link.
		var undoRemoveLink = $('<a>', {
			href: '#',
			class: 'fa fa-undo',
			title: Omeka.jsTranslate('Undo remove role'),
			click: function(event) {
				event.preventDefault();
				roleRow.toggleClass('delete');
				roleInput.prop('disabled', false);
				removeLink.show();
				$(this).remove();
			},
		});

		roleRow.toggleClass('delete');
		undoRemoveLink.insertAfter(removeLink);
		removeLink.hide();
	});

	$('#permissions').on('change', '.permission-set-all', function(event) {

		var obj = $(this);
		var val = obj.val();
		var name = obj.attr('name');
		var arrname = name.split(/\[|\]/);
		var target = arrname[1];
		if(val !== 'set'){
			$("."+target).val([val]);
		}
		
	});

	$('#permissions').on('change', '.permission-set-specified', function(event) {

		var obj = $(this);
		var arrclass = obj.attr('class').split(" ");
		var target = arrclass[1];
		$("[name='permissions["+target+"]']").val(["set"]);

	});

    function addToProperties(action, state, term, label) {
        var id = state + '-' + term;
        if (document.getElementById(id)) {
            return;
        }
        var PropertyRow = $('<div class="' + state + ' row"></div>');
        PropertyRow.attr('id', id);
        PropertyRow.append($('<span>', {'class': 'property-label', 'text': label}));
        PropertyRow.append($('<ul class="actions"><li><a class="o-icon-delete remove-' + state + '" href="#"></a></li></ul>'));
        PropertyRow.append($('<input>', {'type': 'hidden', 'name': state + '[]', 'value': term}));
        $('#' + action + '-list').append(PropertyRow);
    }
    function actionProperty(action, state, propertySelectorChild) {
        var term = $(propertySelectorChild).data('propertyTerm');
        var label = $(propertySelectorChild).data('childSearch');
        addToProperties(action, state, term, label);
    }
    
	$('#filter-display-values #property-selector li.selector-child').on('click', function(e) {
		e.stopPropagation();
		actionProperty('filter-display-values', 'no-display-values', this);
	});
	$('#filter-display-values-list').on('click', '.remove-no-display-values', function (e) {
		e.preventDefault();
		$(this).closest('.no-display-values').remove();
	});
	$.each($('#filter-display-values-list').data('filterDisplayValues'), function(index, value) {
		var propertySelectorChild = $('#filter-display-values #property-selector li.selector-child[data-property-term="' + value + '"]');
		if (propertySelectorChild.length) {
			actionProperty('filter-display-values', 'no-display-values', propertySelectorChild);
		}
	});
	
	$('#hide-properties-in-item-form #property-selector li.selector-child').on('click', function(e) {
		e.stopPropagation();
		actionProperty('hide-properties-in-item-form', 'hidden-properties-in-item-form', this);
	});
	$('#hide-properties-in-item-form-list').on('click', '.remove-hidden-properties-in-item-form', function (e) {
		e.preventDefault();
		$(this).closest('.hidden-properties-in-item-form').remove();
	});
	$.each($('#hide-properties-in-item-form-list').data('hidePropertiesInItemForm'), function(index, value) {
		var propertySelectorChild = $('#hide-properties-in-item-form #property-selector li.selector-child[data-property-term="' + value + '"]');
		if (propertySelectorChild.length) {
			actionProperty('hide-properties-in-item-form', 'hidden-properties-in-item-form', propertySelectorChild);
		}
	});

});
