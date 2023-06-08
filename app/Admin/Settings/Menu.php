<?php

namespace Mentosmenno2\ImageCropPositioner\Admin\Settings;

use Mentosmenno2\ImageCropPositioner\Templates;

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

		( new Templates() )->echo_template( 'admin/settings/settings' );
	}

	public function get_settings_menu(): array {
		$menu = array(
			array(
				'slug'  => 'settings',
				'title' => __( 'Settings', 'image-crop-positioner' ),
			),
			array(
				'slug'  => 'migrate',
				'title' => __( 'Migrate', 'image-crop-positioner' ),
			),
		);

		$keys = array_map(
			function( array $menu_item ): string {
				return $menu_item['slug'];
			}, $menu
		);
		return array_combine( $keys, $menu ) ?: array();
	}

	public function get_current_settings_menu_page(): array {
		$menu = $this->get_settings_menu();
		$page = filter_input( INPUT_GET, 'image-crop-positioner-settings-menu-page' );
		if ( ! array_key_exists( $page, $menu ) ) {
			return $menu['settings'];
		}
		return $menu[ $page ];
	}
}
