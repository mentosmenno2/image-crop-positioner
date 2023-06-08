<?php

namespace Mentosmenno2\ImageCropPositioner\Admin\Settings;

abstract class BaseSelectField extends BaseField {
	/**
	 * Get field options
	 *
	 * @return array<string|int,string>
	 */
	abstract public function get_options(): array;

	/**
	 * Get field value
	 *
	 * @return string|int
	 */
	public function get_value() {
		$default = $this->get_default_value();
		$value   = (string) get_option( $this->get_name(), $default );
		return $value;
	}

	/**
	 * Get default value
	 *
	 * @return string|int
	 */
	public function get_default_value() {
		return '';
	}

	public function render_field(): void {
		$options     = $this->get_options();
		$value       = $this->get_value();
		$description = $this->get_description();
		?>

		<select id="<?php echo esc_attr( $this->get_name() ); ?>" name="<?php echo esc_attr( $this->get_name() ); ?>" <?php disabled( $this->is_disabled() ); ?>>
			<?php foreach ( $options as $option_value => $option_label ) { ?>
				<option value="<?php echo esc_attr( (string) $option_value ); ?>" <?php selected( $option_value, $value, true ); ?>>
					<?php echo esc_html( $option_label ); ?>
				</option>
			<?php } ?>
		</select>

		<?php if ( $description ) { ?>
			<p><?php echo wp_kses_post( $description ); ?></p>
		<?php } ?>

		<?php
	}
}
