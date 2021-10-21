<?php

namespace Mentosmenno2\ImageCropPositioner\Admin\Settings\General\Fields;

use WP_Post_Type;

class PostTypes extends BaseField {

	protected const NAME = 'post_types';

	public function get_name(): string {
		return self::PREFIX . self::NAME;
	}

	public function get_label(): string {
		return __( 'Post types', 'image-crop-positioner' );
	}

	public function get_description(): string {
		return __( 'Select the post types that can have a Image Crop Positioner event.', 'image-crop-positioner' );
	}

	public function get_value(): array {
		$default = $this->get_default_value();
		$value   = get_option( $this->get_name(), $default );
		if ( ! is_array( $value ) ) {
			return $default;
		}
		return $value;
	}

	public function get_default_value(): array {
		return array();
	}

	/**
	 * Get available options for post type selections
	 *
	 * @return array
	 */
	public function get_options(): array {
		$post_types = get_post_types( array( 'public' => true ), 'objects' );

		$options = array();
		foreach ( $post_types as $post_type ) {
			if ( ! $post_type instanceof WP_Post_Type ) {
				continue;
			}
			$options[ $post_type->name ] = $post_type->label ?: $post_type->name;
		}

		return $options;
	}

	public function render_field(): void {
		$setting = $this->get_value();
		$options = $this->get_options();

		?>

		<p><label for="<?php echo esc_attr( $this->get_name() ); ?>"><?php echo esc_html( $this->get_description() ); ?></label></p>

		<fieldset>

			<legend class="screen-reader-text"><?php echo esc_html( $this->get_label() ); ?></legend>

			<?php foreach ( $options as $option_name => $option_label ) { ?>

				<div>
					<label for="<?php echo esc_attr( $this->get_name() ); ?>_<?php echo esc_attr( $option_name ); ?>">
						<input
							type="checkbox"
							id="<?php echo esc_attr( $this->get_name() ); ?>_<?php echo esc_attr( $option_name ); ?>"
							name="<?php echo esc_attr( $this->get_name() ); ?>[]"
							value="<?php echo esc_attr( $option_name ); ?>"
							<?php checked( in_array( $option_name, $setting, true ), true, true ); ?>
						>
						<?php echo esc_html( $option_label ); ?>
					</label>
				</div>

			<?php } ?>

		</fieldset>

		<?php
	}
}
