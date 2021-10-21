<?php

namespace Mentosmenno2\ImageCropPositioner\FaceDetection;

use Exception;
use Mentosmenno2\ImageCropPositioner\Helpers\AttachmentMeta;
use Mentosmenno2\ImageCropPositioner\Objects\Face;

class AutoDetect {

	protected const ACCURACY_THRESHHOLD = 50;

	public function register_hooks(): void {
		add_filter( 'add_attachment', array( $this, 'auto_detect_faces' ) );
	}

	/**
	 * Auto detect faces in an image
	 */
	public function auto_detect_faces( int $attachment_id ): void {
		if ( ! wp_attachment_is_image( $attachment_id ) ) {
			return;
		}

		$file = wp_get_original_image_path( $attachment_id );
		if ( ! is_string( $file ) ) {
			return;
		}

		try {
			$extraction = FaceDetector::get_instance()->extract( $file );
		} catch ( Exception $e ) {
			return;
		}

		if ( ! $extraction->face instanceof Face || $extraction->face->get_accuracy() < self::ACCURACY_THRESHHOLD ) {
			return;
		}

		$faces_data = array( $extraction->face->get_data_array() );
		$faces      = array_map(
			function( array $face_data ): Face {
				return new Face( $face_data );
			}, $faces_data
		);
		( new AttachmentMeta() )->set_faces( $attachment_id, $faces );
	}
}
