<?php

namespace Mentosmenno2\ImageCropPositioner\Admin\Settings\JSFacesDetection\Fields;

use Mentosmenno2\ImageCropPositioner\Admin\Settings\BaseSelectField;
use Mentosmenno2\ImageCropPositioner\FaceDetection\FaceDetector;

class MinAccuracy extends BaseSelectField {

	protected const NAME = 'js_faces_detection_min_accuracy';

	public function get_name(): string {
		return self::PREFIX . self::NAME;
	}

	public function get_label(): string {
		return __( 'Minimum accuracy', 'image-crop-positioner' );
	}

	public function is_disabled(): bool {
		return ! FaceDetector::get_instance()->is_available();
	}

	public function get_description(): string {
		return __( 'Determines how accurate face detections must be to be used.', 'image-crop-positioner' );
	}

	public function get_options(): array {
		return array(
			10 => '10%',
			20 => '20%',
			30 => '30%',
			40 => '40%',
			50 => '50%',
			60 => '60%',
			70 => '70%',
			80 => '80%',
			90 => '90%',
		);
	}

	public function get_value(): int {
		return (int) parent::get_value();
	}

	public function get_default_value(): int {
		return 50;
	}
}
