<?php

namespace Mentosmenno2\ImageCropPositioner\Ajax;

use Exception;
use Mentosmenno2\ImageCropPositioner\Assets;
use Mentosmenno2\ImageCropPositioner\Detection\BaseDetector;
use Mentosmenno2\ImageCropPositioner\Detection\Detection as DetectionDetection;
use Mentosmenno2\ImageCropPositioner\Objects\Face;
use Mentosmenno2\ImageCropPositioner\Detection\FaceDetector\FaceDetector;
use Mentosmenno2\ImageCropPositioner\Objects\Spot;
use WP_Error;

class Detection {

	public function register_hooks(): void {
		add_action( 'wp_ajax_image_crop_positioner_detection', array( $this, 'handle_request' ) );
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

		$detector  = (string) filter_input( INPUT_POST, 'detector' );
		$detectors = ( new DetectionDetection() )->get_available_detectors();
		if ( array_key_exists( $detector, $detectors ) ) {
			$error = new WP_Error(
				400, __( 'Detector does not exist or is not available.', 'image-crop-positioner' ), array(
					'status' => 400,
				)
			);
			wp_send_json_error( $error, 400 );
			exit;
		}

		$this->detect_spots( $attachment_id, $detectors[ $detector ] );
	}

	/**
	 * Detect spots from attachment, save it in the meta, and send them to the json response.
	 */
	protected function detect_spots( int $attachment_id, BaseDetector $detector ): void {
		$file = wp_get_original_image_path( $attachment_id );
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
			$spots = $detector->detect( $file );
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
			'spots' => array(),
		);

		$data['spots'] = array_map(
			function( Spot $spot ): array {
				return $spot->get_data_array();
			}, $spots
		);
		wp_send_json_success( $data, 200 );
		exit;
	}
}
