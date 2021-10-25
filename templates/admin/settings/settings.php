<?php

use Mentosmenno2\ImageCropPositioner\Admin\Settings\Menu;
use Mentosmenno2\ImageCropPositioner\Templates;

if ( ! current_user_can( 'manage_options' ) ) {
	wp_safe_redirect( admin_url( 'admin.php' ) );
	exit;
}

settings_errors( Menu::NAME );

$menu_class   = new Menu();
$current_page = $menu_class->get_current_settings_menu_page();

?>

<div class="wrap">
	<?php
	( new Templates() )->echo_template(
		'admin/settings/menu', array(
			'current_item' => $current_page,
			'menu_items'   => $menu_class->get_settings_menu(),
		)
	);
	?>

	<h1>
		<?php echo esc_html( get_admin_page_title() ); ?> <?php echo esc_html( strtolower( $current_page['title'] ) ); ?>
	</h1>

	<?php ( new Templates() )->echo_template( 'admin/settings/pages/' . $current_page['slug'] ); ?>
</div>
