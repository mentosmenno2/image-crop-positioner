<?php

namespace Mentosmenno2\ImageCropPositioner\Ajax;

use Mentosmenno2\ImageCropPositioner\Assets;
use WP_Error;

abstract class BaseAjaxCall {

	abstract public function register_hooks(): void;

	protected function validate_nonce(): void {
		if ( ! check_ajax_referer( Assets::NONCE_ACTION, false, false ) ) {
			$error = new WP_Error(
				403, __( 'Invalid nonce.', 'image-crop-positioner' ), array(
					'status' => 403,
				)
			);
			wp_send_json_error( $error, 403 );
		}
	}

	protected function validate_is_attachment( int $attachment_id ): void {
		$post_type = get_post_type( $attachment_id );
		if ( ! $attachment_id || $post_type !== 'attachment' ) {
			$error = new WP_Error(
				400, __( 'Invalid attachment ID.', 'image-crop-positioner' ), array(
					'status' => 400,
				)
			);
			wp_send_json_error( $error, 400 );
		}
	}

	protected function validate_attachment_is_image( int $attachment_id ): void {
		if ( ! wp_attachment_is_image( $attachment_id ) ) {
			$error = new WP_Error(
				400, __( 'Attachment is not an image.', 'image-crop-positioner' ), array(
					'status' => 400,
				)
			);
			wp_send_json_error( $error, 400 );
		}
	}
}
