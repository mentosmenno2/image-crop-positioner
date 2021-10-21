<?php

namespace Mentosmenno2\ImageCropPositioner\Admin\Settings\General\Fields;

use Mentosmenno2\ImageCropPositioner\Sync\EventsSync;
use Mentosmenno2\ImageCropPositioner\Sync\EventTypesSync;
use Mentosmenno2\ImageCropPositioner\Sync\LocationsSync;

class SyncEventsInterval extends BaseField {

	protected const NAME = 'sync_events_interval';

	public function register_hooks(): void {
		add_action( 'update_option_' . $this->get_name(), array( $this, 'maybe_reschedule_syncs' ), 10, 2 );
	}

	public function get_name(): string {
		return self::PREFIX . self::NAME;
	}

	public function get_label(): string {
		return __( 'Sync events interval', 'image-crop-positioner' );
	}

	public function get_description(): string {
		return __( 'Select how often events should be synced.', 'image-crop-positioner' );
	}

	public function get_value(): string {
		$default = $this->get_default_value();
		$value   = get_option( $this->get_name(), $default );
		$options = array_keys( $this->get_options() );
		if ( ! in_array( $value, $options, true ) ) {
			return $default;
		}
		return $value;
	}

	public function get_default_value(): string {
		return 'hourly';
	}

	/**
	 * Get available options for post type selections
	 *
	 * @return array
	 */
	public function get_options(): array {
		$schedules = wp_get_schedules();

		$options = array();
		foreach ( $schedules as $schedule_name => $schedule_args ) {
			$options[ $schedule_name ] = $schedule_args['display'] ?? $schedule_name;
		}

		return $options;
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

	/**
	 * Reschedule events if value has changed
	 *
	 * @param mixed $old_value
	 * @param mixed $value
	 * @return void
	 */
	public function maybe_reschedule_syncs( $old_value, $value ): void {
		if ( $old_value === $value ) {
			return;
		}
		( new LocationsSync() )->reschedule_sync();
		( new EventTypesSync() )->reschedule_sync();
		( new EventsSync() )->reschedule_sync();
	}
}
