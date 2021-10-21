<?php

namespace Mentosmenno2\ImageCropPositioner\Admin\Settings\Display\Fields;

class TicketSalesScreenType extends BaseField {

	protected const NAME = 'ticket_sales_screen_type';

	public function get_name(): string {
		return self::PREFIX . self::NAME;
	}

	public function get_label(): string {
		return __( 'Ticket sales screen type', 'image-crop-positioner' );
	}

	public function get_description(): string {
		return __( 'Choose how the ticket sales are shown on your site. With a popup overlay, or on the side of the screen.', 'image-crop-positioner' );
	}

	public function get_value(): ?string {
		$default = $this->get_default_value();
		$value   = (string) get_option( $this->get_name(), $default );
		if ( empty( $value ) ) {
			return $default;
		}
		return $value;
	}

	public function get_default_value(): string {
		return 'popup';
	}

	public function get_options(): array {
		return array(
			'popup' => __( 'Popup', 'image-crop-positioner' ),
			'side'  => __( 'Side', 'image-crop-positioner' ),
		);
	}

	public function render_field(): void {
		$options = $this->get_options();

		$setting = $this->get_value();
		?>

		<p><label for="<?php echo esc_attr( $this->get_name() ); ?>"><?php echo esc_html( $this->get_description() ); ?></label></p>

		<select id="<?php echo esc_attr( $this->get_name() ); ?>" name="<?php echo esc_attr( $this->get_name() ); ?>">
			<?php foreach ( $options as $option_name => $option_label ) { ?>
				<option value="<?php echo esc_attr( $option_name ); ?>" <?php selected( $option_name, $setting, true ); ?>>
					<?php echo esc_html( $option_label ); ?>
				</option>
			<?php } ?>
		</select>

		<?php
	}
}
