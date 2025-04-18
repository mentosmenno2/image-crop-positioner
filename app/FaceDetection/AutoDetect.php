<?php

namespace Mentosmenno2\ImageCropPositioner\FaceDetection;

use Exception;
use Mentosmenno2\ImageCropPositioner\Admin\Settings\PHPFaceDetection\Fields\AutoDetectOnUpload as AutoDetectOnUploadSetting;
use Mentosmenno2\ImageCropPositioner\Admin\Settings\PHPFaceDetection\Fields\Enabled as PHPFaceDetectionEnabledSetting;
use Mentosmenno2\ImageCropPositioner\Helpers\AttachmentMeta;
use Mentosmenno2\ImageCropPositioner\Objects\Face;

class AutoDetect {

	public function register_hooks(): void {
		add_action( 'add_attachment', array( $this, 'set_added_with_plugin_enabled' ) );
		add_action( 'add_attachment', array( $this, 'auto_detect_faces' ) );
		add_filter( 'updated_postmeta', array( $this, 'auto_detect_faces_after_saving_imagemeta' ), 10, 3 );
	}

	/**
	 * Hijack the updated_postmeta filter to detect faces directly after generating attachment metadata
	 */
	public function auto_detect_faces_after_saving_imagemeta( int $meta_id, int $attachment_id, string $meta_key ): int {
		if ( $meta_key !== '_wp_attachment_metadata' ) {
			return $meta_id;
		}

		$this->auto_detect_faces( $attachment_id );
		return $meta_id;
	}

	public function set_added_with_plugin_enabled( int $attachment_id ): void {
		( new AttachmentMeta() )->set_plugin_enabled_on_upload( $attachment_id, true );
	}

	/**
	 * Auto detect faces in an image
	 */
	public function auto_detect_faces( int $attachment_id ): void {
		$attachment_meta = new AttachmentMeta();

		// If already autodetected, skip.
		if ( $attachment_meta->get_faces_autodetected( $attachment_id ) ) {
			return;
		}

		// If this plugin was not enabled when this attachment was uploaded, skip.
		if ( ! $attachment_meta->get_plugin_enabled_on_upload( $attachment_id ) ) {
			return;
		}

		// If not an image, skip.
		if ( ! wp_attachment_is_image( $attachment_id ) ) {
			return;
		}

		// If no metadata is present, we cannot autodetect faces, because large images havn't been downscaled yet. Skip.
		if ( ! wp_get_attachment_metadata( $attachment_id ) ) {
			return;
		}

		// Always mark as autodetected to never try again in the future.
		$attachment_meta->set_faces_autodetected( $attachment_id, true );

		// If PHP face detection is not enabled, skip.
		if ( ! ( new PHPFaceDetectionEnabledSetting() )->get_value() ) {
			return;
		}

		// If autodetect faces setting is not enabled, skip.
		if ( ! ( new AutoDetectOnUploadSetting() )->get_value() ) {
			return;
		}

		// If already has faces, skip.
		if ( $attachment_meta->get_faces( $attachment_id ) ) {
			return;
		}

		$file = get_attached_file( $attachment_id );
		if ( ! is_string( $file ) ) {
			return;
		}

		try {
			$extraction = FaceDetector::get_instance()->extract( $file );
		} catch ( Exception $e ) {
			return;
		}

		if ( ! $extraction->face instanceof Face ) {
			return;
		}

		$faces_data = array( $extraction->face->get_data_array() );
		$faces      = array_map(
			function ( array $face_data ): Face {
				return new Face( $face_data );
			}, $faces_data
		);
		$attachment_meta->set_faces( $attachment_id, $faces );
	}
}
