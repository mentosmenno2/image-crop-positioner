<?php

namespace Mentosmenno2\ImageCropPositioner\Admin\Settings\General\Fields;

class ClientName extends BaseField {

	protected const NAME = 'client_name';

	public function get_name(): string {
		return self::PREFIX . self::NAME;
	}

	public function get_label(): string {
		return __( 'Client name', 'image-crop-positioner' );
	}

	public function get_description(): string {
		return __( 'The client name is required for the plugin to work.', 'image-crop-positioner' );
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
	 * @return null
	 */
	protected function get_default_value() {
		return null;
	}

	public function render_field(): void {
		$setting = $this->get_value();
		?>

		<p><label for="<?php echo esc_attr( $this->get_name() ); ?>"><?php echo esc_html( $this->get_description() ); ?></label></p>

		<input type="text" id="<?php echo esc_attr( $this->get_name() ); ?>" name="<?php echo esc_attr( $this->get_name() ); ?>" class="regular-text" value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>">

		<?php
	}
}
