(function($) {

	Matrix.bind('sh_link', 'display', function(cell){
		var $field = $('.sh_link_wrap', this);

		if (! $field.length) {
			return;
		}

		Sh_link.init_field(this);
	});

})(jQuery);