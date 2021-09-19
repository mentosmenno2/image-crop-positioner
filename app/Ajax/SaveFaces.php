<?php

namespace Mentosmenno2\ImageCropPositioner\Ajax;

use Mentosmenno2\ImageCropPositioner\Assets;
use Mentosmenno2\ImageCropPositioner\Helpers\AttachmentMeta;
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
			exit;
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
			exit;
		}

		$faces = $_POST['faces'] ?? false;
		if ( ! is_array( $faces ) ) {
			$error = new WP_Error(
				400, __( 'Invalid faces.', 'image-crop-positioner' ), array(
					'status' => 400,
				)
			);
			wp_send_json_error( $error, 400 );
			exit;
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

		$return_faces_data = array_map(
			function( Face $face ): array {
				return $face->get_data_array();
			}, $faces
		);

		$data = array(
			'faces' => $return_faces_data,
		);
		wp_send_json_success( $data, 200 );
		exit;
	}
}
