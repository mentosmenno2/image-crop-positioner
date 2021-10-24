<?php

namespace Mentosmenno2\ImageCropPositioner\Admin\Settings\PHPFaceDetection\Fields;

use Mentosmenno2\ImageCropPositioner\FaceDetection\FaceDetector;

class Enabled extends BaseField {

	protected const NAME = 'enabled';

	public function get_name(): string {
		return self::PREFIX . self::NAME;
	}

	public function get_label(): string {
		return __( 'Enabled', 'image-crop-positioner' );
	}

	protected function is_disabled(): bool {
		return ! FaceDetector::get_instance()->is_available();
	}

	public function get_description(): string {
		if ( $this->is_disabled() ) {
			return __( 'Cannot enable PHP face detection because your system does not need the requirements.', 'image-crop-positioner' );
		}
		return __( 'Enable PHP face detection.', 'image-crop-positioner' );
	}

	public function get_value(): bool {
		if ( ! FaceDetector::get_instance()->is_available() ) {
			return false;
		}

		$default = $this->get_default_value();
		$value   = (bool) get_option( $this->get_name(), $default );
		return $value;
	}

	public function get_default_value(): bool {
		return FaceDetector::get_instance()->is_available();
	}

	public function render_field(): void {
		$disabled = $this->is_disabled();
		$value    = $this->get_value();
		?>

		<input type="checkbox" id="<?php echo esc_attr( $this->get_name() ); ?>" name="<?php echo esc_attr( $this->get_name() ); ?>" value="1" <?php checked( $value ); ?> <?php disabled( $disabled ); ?>/>
		<label for="<?php echo esc_attr( $this->get_name() ); ?>"><?php echo esc_html( $this->get_description() ); ?></label>

		<?php
	}
}
