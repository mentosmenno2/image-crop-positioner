<?php

namespace Mentosmenno2\ImageCropPositioner\Admin\Settings\General\Fields;

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

	/**
	 * Get default value
	 *
	 * @return string
	 */
	protected function get_default_value() {
		return 'center';
	}

	public function render_field(): void {
		$setting = $this->get_value();
		?>

		<p><label for="<?php echo esc_attr( $this->get_name() ); ?>"><?php echo esc_html( $this->get_description() ); ?></label></p>

		<select id="<?php echo esc_attr( $this->get_name() ); ?>" name="<?php echo esc_attr( $this->get_name() ); ?>">
			<option value="center" <?php selected( $setting, 'center' ); ?> ><?php esc_html_e( 'Center of all spots', 'image-crop-positioner' ); ?></option>
			<option value="average" <?php selected( $setting, 'average' ); ?> ><?php esc_html_e( 'Average of all spots', 'image-crop-positioner' ); ?></option>
		</select>

		<?php
	}
}
