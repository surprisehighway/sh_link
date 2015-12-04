<div class="sh_link_file_field">
	<div class="sh_link_file_thumb <?=$thumb_class?><?php if(empty($filename)):?> sh_link_hide<?php endif; ?>">
		<img src="<?=$thumb?>" title="<?=$filename?>">
		<p><?=$filename?></p>
	</div>
	<div class="sh_link_file_actions">
		<a href="#" class="sh_link_btn sh_link_remove<?php if(empty($filename)):?> sh_link_hide<?php endif; ?>">Remove file</a>
		<a href="#" class="sh_link_btn sh_link_choose<?php if(!empty($filename)):?> sh_link_hide<?php endif; ?>">Add file</a>
	</div>
	<input type="hidden" name="<?=$field_name?>[file][filename]" value="<?=$filename?>">
	<input type="hidden" name="<?=$field_name?>[file][directory]" value="<?=$directory?>">
</div>