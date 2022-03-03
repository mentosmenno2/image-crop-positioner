<?php

namespace Mentosmenno2\ImageCropPositioner\Admin\Settings\Cache;

use Mentosmenno2\ImageCropPositioner\Admin\Settings\Menu;
use Mentosmenno2\ImageCropPositioner\Admin\Settings\Cache\Fields\BreakEnabled;

class Settings {
	public const FIELDS = array(
		BreakEnabled::class,
	);

	public function register_hooks(): void {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_init', array( $this, 'register_fields' ) );

		foreach ( self::FIELDS as $setting_field_class ) {
			$setting_field = new $setting_field_class();
			if ( is_callable( array( $setting_field, 'register_hooks' ) ) ) {
				$setting_field->register_hooks();
			}
		}
	}

	public function register_settings(): void {
		foreach ( self::FIELDS as $setting_field_class ) {
			$setting_field = new $setting_field_class();
			$args          = array(
				'description'  => $setting_field->get_description(),
				'show_in_rest' => false,
			);
			register_setting( Menu::NAME, $setting_field->get_name(), $args );
		}
	}

	public function register_fields(): void {
		foreach ( self::FIELDS as $setting_field_class ) {
			$setting_field = new $setting_field_class();
			add_settings_field(
				$setting_field->get_name(),
				$setting_field->get_label(),
				array( $setting_field, 'render_field' ),
				Menu::NAME,
				Section::NAME,
				array(
					'label_for' => $setting_field->get_name(),
					'class'     => $setting_field->get_name() . '_field',
				)
			);
		}
	}
}
