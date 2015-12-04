<div class="sh_link_wrap">
	<?php
		// "Link Type" Select
		echo form_dropdown($field_name.'[type]', 
				$options, 
				isset($data->type) ? $data->type : '',
				'class="sh_link_select"'
		);
	?>
	<?php
		// "Custom URL" Input
		if(array_key_exists('custom', $options))
		{
			echo form_input(array(
					'class' => 'sh_link_target sh_link_custom',
					'name' => $field_name.'[custom]',
					'value' => isset($data->custom) ? $data->custom : '',
					'placeholder' => lang('custom')
			));
		}
	?>
	<?php
		// "Email" Input
		if(array_key_exists('email', $options))
		{
			echo form_input(array(
		            'class' => 'sh_link_target sh_link_email',
		            'name' => $field_name.'[email]',
		            'value' => isset($data->email) ? $data->email : '',
		            'placeholder' => lang('email'),
		    ));
		}
	?>
	<?php
	    // "Page" Select
	    if(array_key_exists('page', $options)):
	?>
			<div class="sh_link_target sh_link_page">
				<?php 
					echo form_dropdown(
				        $field_name.'[page]',
				        $pages['titles'],
				        isset($data->page) ? $data->page : ''
				    );
				?>
			</div>
	<?php 
		endif; 
	?>
	<?php 
		// "File" Input UI
	    if(array_key_exists('file', $options)):
	?>
			<div class="sh_link_target sh_link_file publish_file">
				<?php $this->load->view('file_field'); ?>
			</div>

	<?php 
		endif; 
	?>
	<?php
		// "New Window" Checkbox
		if($new_window)
		{
			echo form_label(
				form_checkbox(
					$field_name.'[new_window]',
					'y', 
					isset($data->new_window) ? $data->new_window : ''
				).' '.lang('new_window')
			);
		}
	?>
</div><!-- /.sh_link_wrap -->