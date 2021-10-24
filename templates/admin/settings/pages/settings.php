<?php

use Mentosmenno2\ImageCropPositioner\Admin\Settings\Menu;

?>

<div>
	<form action="options.php" method="post">
		<?php
			settings_fields( Menu::NAME );
			do_settings_sections( Menu::NAME );
			submit_button();
		?>
	</form>
</div>


