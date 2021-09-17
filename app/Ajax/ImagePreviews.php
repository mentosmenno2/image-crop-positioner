<?php

namespace Mentosmenno2\ImageCropPositioner\Ajax;

use Mentosmenno2\ImageCropPositioner\Assets;
use WP_Error;

class ImagePreviews {

	public function register_hooks(): void {
		add_action( 'wp_ajax_image_crop_positioner_image_previews', array( $this, 'handle_request' ) );
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
				403, __( 'Invalid attachment ID.', 'image-crop-positioner' ), array(
					'status' => 403,
				)
			);
			wp_send_json_error( $error, 403 );
		}

		$this->get_image_previews( $attachment_id );
	}

	/**
	 * Get the image previews and send them to the json response
	 */
	protected function get_image_previews( int $attachment_id ): void {
		$data  = array();
		$sizes = get_intermediate_image_sizes();
		foreach ( $sizes as $size ) {
			$image  = wp_get_attachment_image(
				$attachment_id, $size, false, array(
					'class' => 'image-previews__image',
				)
			);
			$data[] = array(
				'size' => $size,
				'html' => $image,
			);
		}

		wp_send_json_success( $data, 200 );
	}
}
