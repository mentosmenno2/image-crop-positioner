<?php

namespace Mentosmenno2\ImageCropPositioner\Admin\Settings\PHPFaceDetection\Fields;

use Mentosmenno2\ImageCropPositioner\Admin\Settings\BaseToggleField;
use Mentosmenno2\ImageCropPositioner\FaceDetection\FaceDetector;

class AutoDetectOnUpload extends BaseToggleField {

	protected const NAME = 'php_face_detection_auto_detect_on_upload';

	public function get_name(): string {
		return self::PREFIX . self::NAME;
	}

	public function get_label(): string {
		return __( 'Auto detect and crop', 'image-crop-positioner' );
	}

	public function get_checkbox_label(): string {
		return __( 'Automatically detect faces and crop the image.', 'image-crop-positioner' );
	}

	public function is_disabled(): bool {
		return ! FaceDetector::get_instance()->is_available();
	}

	public function get_description(): string {
		return __( 'After uploading an image, automatically attempt to detect a face and crop the image.', 'image-crop-positioner' );
	}

	public function get_default_value(): bool {
		return false;
	}
}
