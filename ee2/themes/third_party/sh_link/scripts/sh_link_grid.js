(function($) {

	Grid.bind('sh_link', 'display', function(cell){
		var $field = cell.find('.sh_link_wrap');

		if (! $field.length) {
			return;
		}

		Sh_link.init_field(cell);
	});

})(jQuery);