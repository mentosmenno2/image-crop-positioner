<?php

namespace Mentosmenno2\ImageCropPositioner\Ajax;

use Mentosmenno2\ImageCropPositioner\Assets;
use Mentosmenno2\ImageCropPositioner\Helpers\AttachmentMeta;
use Mentosmenno2\ImageCropPositioner\Regenerate;
use WP_Error;

class RemoveFaces {

	protected const ACCURACY_THRESHHOLD = 50;

	public function register_hooks(): void {
		add_action( 'wp_ajax_image_crop_positioner_remove_faces', array( $this, 'handle_request' ) );
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

		$this->remove_faces( $attachment_id );
	}

	/**
	 * Empty the faces meta from the attachment, and send the faces in the response
	 */
	protected function remove_faces( int $attachment_id ): void {
		$faces = array();
		( new AttachmentMeta() )->set_faces( $attachment_id, $faces );

		$regenerated = ( new Regenerate() )->run( $attachment_id );
		if ( ! $regenerated ) {
			$error = new WP_Error(
				500, __( 'Could not regenerate images.', 'image-crop-positioner' ), array(
					'status' => 500,
				)
			);
			wp_send_json_error( $error, 500 );
			exit;
		}

		$data = array(
			'faces' => $faces,
		);
		wp_send_json_success( $data, 200 );
		exit;
	}
}
