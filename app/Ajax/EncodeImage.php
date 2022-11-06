<?php

namespace Mentosmenno2\ImageCropPositioner\Ajax;

use WP_Error;

class EncodeImage extends BaseAjaxCall {

	public function register_hooks(): void {
		add_action( 'wp_ajax_image_crop_positioner_encode_image', array( $this, 'handle_request' ) );
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
	 * Get the image source converted to base64 if possible.
	 * Otherwise get the original source.
	 */
	protected function get_base64_image( int $attachment_id ): void {

		// Attempt with local file
		$file_path    = get_attached_file( $attachment_id ) ?: '';
		$mime_type    = mime_content_type( $file_path );
		$file_content = file_get_contents( $file_path ) ?: ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( $file_path && $mime_type && $file_content ) {
			$base64 = 'data:' . $mime_type . ';base64,' . base64_encode( $file_content ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			$this->return_success_response( $base64 );
			exit;
		}

		// Attempt with remote get
		$image_src  = wp_get_attachment_image_src( $attachment_id, 'full' )[0] ?? '';
		$image_data = wp_remote_get(
			$image_src,
			array(
				'timeout' => 10,
			)
		);
		if ( ! $image_data instanceof WP_Error && ! empty( $image_data['body'] ) && ! empty( $image_data['headers']['content-type'] ) && strpos( $image_data['headers']['content-type'], 'image/' ) === 0 ) {
			$image_src = 'data:' . $image_data['headers']['content-type'] . ';base64,' . base64_encode( $image_data['body'] ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			$this->return_success_response( $image_src );
			exit;
		}

		// If both fail, return the original source
		$this->return_success_response(
			$image_src,
			new WP_Error(
				400, __( 'Could not get base64 data for image.', 'image-crop-positioner' ), array(
					'status' => 400,
				)
			)
		);
		exit;
	}

	protected function return_success_response( string $src, ?WP_Error $download_debug_data = null ): void {
		wp_send_json_success(
			array(
				'src'                 => $src,
				'download_debug_data' => $download_debug_data,
			), 200
		);
		exit;
	}
}
