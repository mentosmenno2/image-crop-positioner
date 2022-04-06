<?php

namespace Mentosmenno2\ImageCropPositioner\Ajax;

use Exception;
use Mentosmenno2\ImageCropPositioner\Objects\Face;
use Mentosmenno2\ImageCropPositioner\FaceDetection\FaceDetector;
use WP_Error;

class FaceDetection extends BaseAjaxCall {
	public function register_hooks(): void {
		add_action( 'wp_ajax_image_crop_positioner_face_detection', array( $this, 'handle_request' ) );
	}

	/**
	 * Handle the request
	 */
	public function handle_request(): void {
		$this->validate_nonce();
		$attachment_id = (int) filter_input( INPUT_POST, 'attachment_id', FILTER_VALIDATE_INT );
		$this->validate_is_attachment( $attachment_id );
		$this->validate_attachment_is_image( $attachment_id );

		$this->detect_faces( $attachment_id );
	}

	/**
	 * Detect faces from attachment, save it in the meta, and send them to the json response.
	 */
	protected function detect_faces( int $attachment_id ): void {
		$file = get_attached_file( $attachment_id );
		if ( ! is_string( $file ) ) {
			$error = new WP_Error(
				400, __( 'Attachment has no file path.', 'image-crop-positioner' ), array(
					'status' => 400,
				)
			);
			wp_send_json_error( $error, 400 );
			exit;
		}

		try {
			$extraction = FaceDetector::get_instance()->extract( $file );
		} catch ( Exception $e ) {
			$error = new WP_Error(
				400, $e->getMessage(), array(
					'status' => 400,
				)
			);
			wp_send_json_error( $error, 400 );
			exit;
		}

		$data = array(
			'faces' => array(),
		);
		if ( $extraction->face instanceof Face ) {
			$data['faces'][] = $extraction->face->get_data_array();
		}
		wp_send_json_success( $data, 200 );
		exit;
	}
}
