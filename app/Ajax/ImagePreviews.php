<?php

namespace Mentosmenno2\ImageCropPositioner\Ajax;

class ImagePreviews extends BaseAjaxCall {

	public function register_hooks(): void {
		add_action( 'wp_ajax_image_crop_positioner_image_previews', array( $this, 'handle_request' ) );
	}

	/**
	 * Handle the request
	 */
	public function handle_request(): void {
		$this->validate_nonce();
		$attachment_id = (int) filter_input( INPUT_POST, 'attachment_id', FILTER_VALIDATE_INT );
		$this->validate_is_attachment( $attachment_id );
		$this->validate_attachment_is_image( $attachment_id );

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
		exit;
	}
}
