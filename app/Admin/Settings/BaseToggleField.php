<?php

namespace Mentosmenno2\ImageCropPositioner\Admin\Settings;

abstract class BaseToggleField extends BaseField {
	abstract public function get_checkbox_label(): string;

	public function get_value(): bool {
		$default = $this->get_default_value();
		$value   = (bool) get_option( $this->get_name(), $default );
		return $value;
	}

	public function get_default_value(): bool {
		return false;
	}

	public function render_field(): void {
		$value       = $this->get_value();
		$description = $this->get_description();
		?>

		<input type="checkbox" id="<?php echo esc_attr( $this->get_name() ); ?>" name="<?php echo esc_attr( $this->get_name() ); ?>" value="1" <?php checked( $value ); ?> <?php disabled( $this->is_disabled() ); ?>/>
		<label for="<?php echo esc_attr( $this->get_name() ); ?>"><?php echo esc_html( $this->get_checkbox_label() ); ?></label>

		<?php if ( $description ) { ?>
			<p><?php echo wp_kses_post( $description ); ?></p>
		<?php } ?>

		<?php
	}
}
