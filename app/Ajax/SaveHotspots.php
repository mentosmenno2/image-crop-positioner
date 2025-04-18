<?php

namespace Mentosmenno2\ImageCropPositioner\Ajax;

use Mentosmenno2\ImageCropPositioner\Admin\Settings\Regeneration\Fields\UseCron;
use Mentosmenno2\ImageCropPositioner\Helpers\AttachmentMeta;
use Mentosmenno2\ImageCropPositioner\Objects\Hotspot;
use Mentosmenno2\ImageCropPositioner\Regenerate;
use WP_Error;

class SaveHotspots extends BaseAjaxCall {

	public function register_hooks(): void {
		add_action( 'wp_ajax_image_crop_positioner_save_hotspots', array( $this, 'handle_request' ) );
	}

	/**
	 * Handle the request
	 */
	public function handle_request(): void {
		$this->validate_nonce();
		$attachment_id = (int) filter_input( INPUT_POST, 'attachment_id', FILTER_VALIDATE_INT );
		$this->validate_is_attachment( $attachment_id );
		$this->validate_attachment_is_image( $attachment_id );

		$hotspots = $_POST['hotspots'] ?? array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! is_array( $hotspots ) ) {
			$error = new WP_Error(
				400, __( 'Invalid hotspots.', 'image-crop-positioner' ), array(
					'status' => 400,
				)
			);
			wp_send_json_error( $error, 400 );
			exit;
		}

		$this->save_hotspots( $attachment_id, $hotspots );
	}

	/**
	 * Save the hotspots in the attachment meta, and send the hotspots in the response
	 */
	protected function save_hotspots( int $attachment_id, array $hotspots_data ): void {
		$hotspots = array_map(
			function ( array $hotspot_data ): Hotspot {
				return new Hotspot( $hotspot_data );
			}, $hotspots_data
		);
		( new AttachmentMeta() )->set_hotspots( $attachment_id, $hotspots );

		$regenerate_use_cron = ( new UseCron() )->get_value();
		$regenerated         = ( new Regenerate() )->execute( $attachment_id, $regenerate_use_cron );
		if ( ! $regenerated ) {
			$error = new WP_Error(
				500, __( 'Could not regenerate images.', 'image-crop-positioner' ), array(
					'status' => 500,
				)
			);
			wp_send_json_error( $error, 500 );
		}

		$return_hotspots_data = array_map(
			function ( Hotspot $hotspot ): array {
				return $hotspot->get_data_array();
			}, $hotspots
		);

		$data = array(
			'hotspots'              => $return_hotspots_data,
			'regenerate_using_cron' => $regenerate_use_cron,
		);
		wp_send_json_success( $data, 200 );
	}
}
