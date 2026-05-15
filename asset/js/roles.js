$(document).ready(function() {

	$(document).on('click', '#expand-menu', function(e) {
		e.preventDefault();
		var toggle = $(this);
		var target = $(this).attr('aria-target');
		toggle.toggleClass('collapsed').toggleClass('expanded');
		if (toggle.hasClass('expanded')) {
			toggle.attr('aria-expanded', 'true');
			$(target).css('display', 'block');
		} else {
			toggle.attr('aria-expanded', 'false');
			$(target).css('display', 'none');
		}
	});

});
