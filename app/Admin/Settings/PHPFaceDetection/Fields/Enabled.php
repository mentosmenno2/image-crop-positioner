<?php

namespace Mentosmenno2\ImageCropPositioner\Admin\Settings\PHPFaceDetection\Fields;

use Mentosmenno2\ImageCropPositioner\Admin\Settings\BaseToggleField;
use Mentosmenno2\ImageCropPositioner\FaceDetection\FaceDetector;

class Enabled extends BaseToggleField {

	protected const NAME = 'php_face_detection_enabled';

	public function get_name(): string {
		return self::PREFIX . self::NAME;
	}

	public function get_label(): string {
		return __( 'Enabled', 'image-crop-positioner' );
	}

	public function get_checkbox_label(): string {
		return __( 'Enable server face detection.', 'image-crop-positioner' );
	}

	public function is_disabled(): bool {
		return ! FaceDetector::get_instance()->is_available();
	}

	public function get_description(): string {
		if ( $this->is_disabled() ) {
			return __( 'Cannot enable server face detection because your system does not need the requirements.', 'image-crop-positioner' );
		}
		
		return __( 'Server face detection is a heavy task for the webserver, which it might not be able to handle.', 'image-crop-positioner' );
	}

	public function get_value(): bool {
		if ( ! FaceDetector::get_instance()->is_available() ) {
			return false;
		}

		return parent::get_value();
	}

	public function get_default_value(): bool {
		return FaceDetector::get_instance()->is_available();
	}

	public function render_field(): void {
		$disabled    = $this->is_disabled();
		$value       = $this->get_value();
		$description = $this->get_description();
		?>

		<input type="checkbox" id="<?php echo esc_attr( $this->get_name() ); ?>" name="<?php echo esc_attr( $this->get_name() ); ?>" value="1" <?php checked( $value ); ?> <?php disabled( $disabled ); ?>/>
		<label for="<?php echo esc_attr( $this->get_name() ); ?>"><?php echo esc_html( $this->get_checkbox_label() ); ?></label>

		<?php if ( $description ) { ?>
			<p><?php echo esc_html( $description ); ?></p>
		<?php } ?>

		<?php
	}
}
