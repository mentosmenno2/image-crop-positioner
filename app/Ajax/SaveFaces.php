<?php

namespace Mentosmenno2\ImageCropPositioner\Ajax;

use Mentosmenno2\ImageCropPositioner\Assets;
use Mentosmenno2\ImageCropPositioner\Objects\Face;
use WP_Error;

class SaveFaces {

	protected const ACCURACY_THRESHHOLD = 50;

	public function register_hooks(): void {
		add_action( 'wp_ajax_image_crop_positioner_save_faces', array( $this, 'handle_request' ) );
	}

	/**
	 * Handle the request
	 */
	public function handle_request(): void {
		if ( ! check_ajax_referer( Assets::NONCE_ACTION, false, false ) ) {
			$error = new WP_Error(
				403, __( 'Invalid nonce.', 'image-crop-positioner' ), array(
					'status' => 403,
				)
			);
			wp_send_json_error( $error, 403 );
		}

		$attachment_id = (int) filter_input( INPUT_POST, 'attachment_id', FILTER_VALIDATE_INT );
		$post_type     = get_post_type( $attachment_id );
		if ( ! $attachment_id || $post_type !== 'attachment' ) {
			$error = new WP_Error(
				400, __( 'Invalid attachment ID.', 'image-crop-positioner' ), array(
					'status' => 400,
				)
			);
			wp_send_json_error( $error, 400 );
		}

		$faces = $_POST['faces'] ?? false;
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
	 * Detect faces from attachment, save it in the meta, and send them to the json response.
	 */
	protected function save_faces( int $attachment_id, array $faces ): void {
		$saveable_face_data = array_map(
			function( array $face_data ): array {
				return ( new Face( $face_data ) )->get_data_array();
			}, $faces
		);
		update_post_meta( $attachment_id, 'image_crop_positioner_faces', $saveable_face_data );

		$data = array(
			'faces' => $saveable_face_data,
		);
		wp_send_json_success( $data, 200 );
	}
}
