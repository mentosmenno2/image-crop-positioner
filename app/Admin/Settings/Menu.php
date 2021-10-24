<?php

namespace Mentosmenno2\ImageCropPositioner\Admin\Settings;

class Menu {

	public const NAME = 'image_crop_positioner_options';

	public function register_hooks(): void {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
	}

	public function add_menu_page(): void {
		add_options_page(
			__( 'Image Crop Positioner', 'image-crop-positioner' ),
			__( 'Image Crop Positioner', 'image-crop-positioner' ),
			'manage_options',
			self::NAME,
			array( $this, 'render_settings_page' )
		);
	}

	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		require_once IMAGE_CROP_POSITIONER_PLUGIN_PATH . 'templates/admin/settings-page.php';
	}
}
