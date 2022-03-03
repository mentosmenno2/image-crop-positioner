<?php

namespace Mentosmenno2\ImageCropPositioner\Admin\Settings\Crop\Fields;

use Mentosmenno2\ImageCropPositioner\Admin\Settings\BaseSelectField;

class PositioningMethod extends BaseSelectField {

	protected const NAME = 'crop_positioning_method';

	public function get_name(): string {
		return self::PREFIX . self::NAME;
	}

	public function get_label(): string {
		return __( 'Positioning method', 'image-crop-positioner' );
	}

	public function get_description(): string {
		return __( 'The method used to determine the correct crop position.', 'image-crop-positioner' );
	}

	public function get_options(): array {
		return array(
			'center'  => __( 'Center of all spots', 'image-crop-positioner' ),
			'average' => __( 'Average of all spots', 'image-crop-positioner' ),
		);
	}

	public function get_default_value(): string {
		return 'center';
	}
}
