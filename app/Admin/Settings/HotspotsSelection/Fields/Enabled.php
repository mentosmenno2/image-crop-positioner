<?php

namespace Mentosmenno2\ImageCropPositioner\Admin\Settings\HotspotsSelection\Fields;

use Mentosmenno2\ImageCropPositioner\Admin\Settings\BaseToggleField;

class Enabled extends BaseToggleField {

	protected const NAME = 'hotspots_selection_enabled';

	public function get_name(): string {
		return self::PREFIX . self::NAME;
	}

	public function get_label(): string {
		return __( 'Enabled', 'image-crop-positioner' );
	}

	public function get_checkbox_label(): string {
		return __( 'Enable hotspots selection.', 'image-crop-positioner' );
	}

	public function get_default_value(): bool {
		return true;
	}
}
