<?php

use Mentosmenno2\ImageCropPositioner\Admin\Settings\Menu;
use Mentosmenno2\ImageCropPositioner\Templates;

/**
 * @global Templates $template
 */

$current_page = $template->get_arg( 'current_item' );
$menu_items   = $template->get_arg( 'menu_items' );
?>

<nav class="wp-filter" >
	<ul class="filter-links">
		<?php
		foreach ( $menu_items as $menu_item ) {
			$is_current = $current_page['slug'] === $menu_item['slug'];
			$url        = add_query_arg(
				array(
					'page' => rawurlencode( Menu::NAME ),
					'image-crop-positioner-settings-menu-page' => rawurlencode( $menu_item['slug'] ),
				), admin_url( 'options-general.php' )
			);
			?>
			<li>
				<a
					href="<?php echo esc_url( $url ); ?>"
					class="<?php echo $is_current ? 'current' : ''; ?>"
					<?php echo $is_current ? 'aria-current="page"' : ''; ?>
				>
					<?php echo esc_html( $menu_item['title'] ); ?>
				</a>
			</li>
		<?php } ?>
	</ul>
</nav>
