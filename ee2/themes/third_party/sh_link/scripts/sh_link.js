var Sh_link = window.Sh_link || {};

(function($) {

	Sh_link.init = function() {
		$('.sh_link_wrap')
			.not('.grid_field .sh_link_wrap, .matrix .sh_link_wrap')
			.each(function(){
				Sh_link.init_field(this);
			});
	};

	Sh_link.init_field = function(container) {
		var $container = $(container);
		var $select = $container.find('.sh_link_select');
		var $file_field = $container.find('.sh_link_file_field')
		
		$select.on('change', function(){
			Sh_link.toggle_selection($select);
		});
		
		Sh_link.toggle_selection($select);

		if($file_field.length) {
			Sh_link.init_file_field($file_field);
		}
	};

	Sh_link.toggle_selection = function(select){
		var $select = $(select)
		var $field_group = $select.parent();
		var selected_type = $select.val();

		$field_group.find('.sh_link_target').hide();
		$field_group.find('.sh_link_target.sh_link_'+selected_type).show();
	};

	Sh_link.init_file_field = function(container) {
		var $container = $(container);
		var $remove = $container.find('.sh_link_remove');
		var $choose = $container.find('.sh_link_choose');
		var $thumb  = $container.find('.sh_link_file_thumb');
		var $file_field = $container.find('input[type="hidden"][name$="[file][filename]"]');
		var $dir_field  = $container.find('input[type="hidden"][name$="[file][directory]"]');

		$remove.on('click', function(e){
			e.preventDefault();
			$thumb.html('').addClass('sh_link_hide');
			$remove.addClass('sh_link_hide');
			$choose.removeClass('sh_link_hide');
			$file_field.val('');
			$dir_field.val('');
		});

		$.ee_filebrowser.add_trigger($choose, {
			content_type: 'any',
			directory:    'all'
		}, function(file, field) {
			var file_thumb = Sh_link.file_thumb(file.thumb, file.file_name);
			$thumb.html(file_thumb).removeClass('sh_link_hide');

			if(file.is_image) {
				$thumb.removeClass('no_image');
			} else {
				$thumb.addClass('no_image');
			}

			$remove.removeClass('sh_link_hide');
			$choose.addClass('sh_link_hide');

			$file_field.val(file.file_name);
			$dir_field.val(file.upload_location_id);
		});
	};

	Sh_link.file_thumb = function(url, filename) {
		return '<img src="'+url+'" title="'+filename+'">'
				+ '<p>'+filename+'</p>';
	};


	Sh_link.init();

})(jQuery);