<?php

namespace Mentosmenno2\ImageCropPositioner\Ajax;

use Mentosmenno2\ImageCropPositioner\Admin\Settings\Regeneration\Fields\UseCron;
use Mentosmenno2\ImageCropPositioner\Helpers\AttachmentMeta;
use Mentosmenno2\ImageCropPositioner\Objects\Face;
use Mentosmenno2\ImageCropPositioner\Regenerate;
use WP_Error;

class SaveFaces extends BaseAjaxCall {

	public function register_hooks(): void {
		add_action( 'wp_ajax_image_crop_positioner_save_faces', array( $this, 'handle_request' ) );
	}

	/**
	 * Handle the request
	 */
	public function handle_request(): void {
		$this->validate_nonce();
		$attachment_id = (int) filter_input( INPUT_POST, 'attachment_id', FILTER_VALIDATE_INT );
		$this->validate_is_attachment( $attachment_id );
		$this->validate_attachment_is_image( $attachment_id );

		$faces = $_POST['faces'] ?? false; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! is_array( $faces ) ) {
			$error = new WP_Error(
				400, __( 'Invalid faces.', 'image-crop-positioner' ), array(
					'status' => 400,
				)
			);
			wp_send_json_error( $error, 400 );
		}

		$this->save_faces( $attachment_id, $faces );
	}

	/**
	 * Save the faces in the attachment meta, and send the faces in the response
	 */
	protected function save_faces( int $attachment_id, array $faces_data ): void {
		$faces = array_map(
			function( array $face_data ): Face {
				return new Face( $face_data );
			}, $faces_data
		);
		( new AttachmentMeta() )->set_faces( $attachment_id, $faces );

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

		$return_faces_data = array_map(
			function( Face $face ): array {
				return $face->get_data_array();
			}, $faces
		);

		$data = array(
			'faces'                 => $return_faces_data,
			'regenerate_using_cron' => $regenerate_use_cron,
		);
		wp_send_json_success( $data, 200 );
	}
}
