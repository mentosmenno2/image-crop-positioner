<?php

namespace Mentosmenno2\ImageCropPositioner\Admin\Settings\JSFacesDetection\Fields;

use Mentosmenno2\ImageCropPositioner\Admin\Settings\BaseToggleField;

class Enabled extends BaseToggleField {

	protected const NAME = 'js_faces_detection_enabled';

	public function get_name(): string {
		return self::PREFIX . self::NAME;
	}

	public function get_label(): string {
		return __( 'Enabled', 'image-crop-positioner' );
	}

	public function get_checkbox_label(): string {
		return __( 'Enable JS faces detection.', 'image-crop-positioner' );
	}

	public function get_default_value(): bool {
		return true;
	}
}
