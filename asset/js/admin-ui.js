var RolesManager = {

    filterSelectorSimple : function() {
        var filter = $(this).val().toLowerCase();
        var selector = $(this).closest('.selector');
        var totalCount = 0;
        selector.find('li.selector-child').each(function() {
			var child = $(this);
			var label = child.data('child-search').toLowerCase();
			if ((label.indexOf(filter) < 0) || (child.hasClass('added'))) {
				// Label doesn't contain the filter string. Hide the child.
				child.addClass('filter-hidden');
			} else {
				// Label contains the filter string. Show the child.
				child.removeClass('filter-hidden');
				totalCount++;
			}
        });
        if (filter == '') {
            selector.find('li.selector-child').removeClass('show');
            $('.filter-match').removeClass('filter-match');
        }
        selector.find('span.selector-total-count').text(totalCount);
    }   

};

$(document).ready(function() {

	$('.simple-selector-filter').on('keyup', (function() {
		var timer = 0;
		return function() {
			clearTimeout(timer);
			timer = setTimeout(RolesManager.filterSelectorSimple.bind(this), 400);
		}
	})());

});
