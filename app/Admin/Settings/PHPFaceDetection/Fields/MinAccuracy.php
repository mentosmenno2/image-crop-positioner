<?php

namespace Mentosmenno2\ImageCropPositioner\Admin\Settings\PHPFaceDetection\Fields;

use Mentosmenno2\ImageCropPositioner\FaceDetection\FaceDetector;

class MinAccuracy extends BaseField {

	protected const NAME = 'min_accuracy';

	public function get_name(): string {
		return self::PREFIX . self::NAME;
	}

	public function get_label(): string {
		return __( 'Minimum accuracy', 'image-crop-positioner' );
	}

	protected function is_disabled(): bool {
		return ! FaceDetector::get_instance()->is_available();
	}

	public function get_description(): string {
		return __( 'Please select how accurate face detections must be to be used.', 'image-crop-positioner' );
	}

	public function get_value(): int {
		$default = $this->get_default_value();
		$value   = (int) get_option( $this->get_name(), $default );
		if ( empty( $value ) ) {
			return $default;
		}
		return $value;
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

	/**
	 * Get default value
	 *
	 * @return int
	 */
	protected function get_default_value() {
		return 50;
	}

	public function render_field(): void {
		$options = $this->get_options();
		$value   = $this->get_value();
		?>

		<select id="<?php echo esc_attr( $this->get_name() ); ?>" name="<?php echo esc_attr( $this->get_name() ); ?>">
			<?php foreach ( $options as $option_name => $option_label ) { ?>
				<option value="<?php echo esc_attr( $option_name ); ?>" <?php selected( $option_name, $value, true ); ?>>
					<?php echo esc_html( $option_label ); ?>
				</option>
			<?php } ?>
		</select>

		<p><label for="<?php echo esc_attr( $this->get_name() ); ?>"><?php echo esc_html( $this->get_description() ); ?></label></p>

		<?php
	}
}
