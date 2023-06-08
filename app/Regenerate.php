<?php

namespace Mentosmenno2\ImageCropPositioner;

class Regenerate {

	public const ACTION = 'image_crop_positioner_regenerate_image';

	public function register_hooks(): void {
		add_action( self::ACTION, array( $this, 'cron_run' ), 10, 1 );
	}

	public function execute( int $attachment_id, bool $schedule ): bool {
		if ( $schedule ) {
			if ( wp_next_scheduled( self::ACTION, array( $attachment_id ) ) ) {
				return true;
			}

			wp_schedule_single_event( time(), self::ACTION, array( $attachment_id ) );
			return true;
		}

		return $this->run( $attachment_id );
	}

	public function cron_run( int $attachment_id ): void {
		$this->run( $attachment_id );
	}

	/**
	 * Regenerate attachment image sizes
	 */
	public function run( int $attachment_id ): bool {
		$this->set_execution_time_limit();

		$file = get_attached_file( $attachment_id );
		if ( ! is_string( $file ) ) {
			return false;
		}

		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$metadata = wp_generate_attachment_metadata( $attachment_id, $file );
		/**
		 * @psalm-suppress DocblockTypeContradiction
		 * The docblock states $metadata can only be an array, but it can also be a WP_Error
		 */
		if ( ! is_array( $metadata ) || empty( $metadata ) ) {
			return false;
		}

		wp_update_attachment_metadata( $attachment_id, $metadata );
		return true;
	}

	/**
	 * If the max execution time is lower than 5 minutes, change it to 5 minutes
	 */
	protected function set_execution_time_limit(): void {
		$minimal_time_limit = 5 * MINUTE_IN_SECONDS;
		$current_time_limit = ini_get( 'max_execution_time' );
		if ( false !== $current_time_limit && ( (int) $current_time_limit === 0 || (int) $current_time_limit > $minimal_time_limit ) ) {
			return;
		}
		@set_time_limit( $minimal_time_limit ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}
}
