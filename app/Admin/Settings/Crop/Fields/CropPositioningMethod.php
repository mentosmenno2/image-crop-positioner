<?php

namespace Mentosmenno2\ImageCropPositioner\Admin\Settings\Crop\Fields;

class CropPositioningMethod extends BaseField {

	protected const NAME = 'crop_positioning_method';

	public function get_name(): string {
		return self::PREFIX . self::NAME;
	}

	public function get_label(): string {
		return __( 'Crop positioning method', 'image-crop-positioner' );
	}

	public function get_description(): string {
		return __( 'The method used to determine the correct crop position.', 'image-crop-positioner' );
	}

	public function get_value(): ?string {
		$default = $this->get_default_value();
		$value   = (string) get_option( $this->get_name(), $default );
		if ( empty( $value ) ) {
			return $default;
		}
		return $value;
	}

	public function get_options(): array {
		return array(
			'center'  => __( 'Center of all spots', 'image-crop-positioner' ),
			'average' => __( 'Average of all spots', 'image-crop-positioner' ),
		);
	}

	/**
	 * Get default value
	 *
	 * @return string
	 */
	protected function get_default_value() {
		return 'center';
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
