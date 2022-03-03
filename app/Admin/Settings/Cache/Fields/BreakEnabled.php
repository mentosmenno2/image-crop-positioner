<?php

namespace Mentosmenno2\ImageCropPositioner\Admin\Settings\Cache\Fields;

use Mentosmenno2\ImageCropPositioner\Admin\Settings\BaseToggleField;

class BreakEnabled extends BaseToggleField {

	protected const NAME = 'cache_break_enabled';

	public function get_name(): string {
		return self::PREFIX . self::NAME;
	}

	public function get_label(): string {
		return __( 'Break cache', 'image-crop-positioner' );
	}

	public function get_checkbox_label(): string {
		return __( 'Enable cache breaking.', 'image-crop-positioner' );
	}

	public function get_description(): string {
		return __( 'By enabling this option, an url parameter will be added to the image source to break cache after an image is cropped. Disabling this option might result in your changes to an image not beeing visible on your website, because they are cached by your web server or a plugin.', 'image-crop-positioner' );
	}

	public function get_default_value(): bool {
		return true;
	}
}
