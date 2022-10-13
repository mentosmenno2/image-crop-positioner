<?php

namespace Mentosmenno2\ImageCropPositioner\Ajax;

use Mentosmenno2\ImageCropPositioner\Migrators\BaseMigrator;
use Mentosmenno2\ImageCropPositioner\Migrators\Migrators;
use WP_Error;

class GetEncodedImage extends BaseAjaxCall {

	public function register_hooks(): void {
		add_action( 'wp_ajax_image_crop_positioner_get_encoded_image', array( $this, 'handle_request' ) );
	}

	/**
	 * Handle the request
	 */
	public function handle_request(): void {
		$this->validate_nonce();
		$attachment_id = (int) filter_input( INPUT_POST, 'attachment_id', FILTER_VALIDATE_INT );
		$this->validate_is_attachment( $attachment_id );
		$this->validate_attachment_is_image( $attachment_id );

		$this->get_base64_image( $attachment_id );
	}

	/**
	 * If image is hosted on external url (like an image bucket), convert it to a data image.
	 */
	protected function get_base64_image( int $attachment_id ): void {
		$image_src  = wp_get_attachment_image_src( $attachment_id, 'full' )[0] ?? '';
		$image_data = wp_remote_get( $image_src, array( 'timeout' => 5 ) ) ?: '';
		if ( ! $image_data instanceof WP_Error && ! empty( $image_data['body'] ) && ! empty( $image_data['headers']['content-type'] ) && strpos( $image_data['headers']['content-type'], 'image/' ) === 0 ) {
			$data = array(
				'image' => base64_encode( $image_data['body'] ), //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			);
			wp_send_json_success( $data, 200 );
			exit;
		}

		wp_send_json_error( 'invalid_image' );
	}
}
