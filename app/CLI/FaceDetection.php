<?php

namespace Mentosmenno2\ImageCropPositioner\CLI;

use Exception;
use WP_CLI;
use Mentosmenno2\ImageCropPositioner\FaceDetection\FaceDetector;

class FaceDetection {
	/**
	 * Get face coordinates.
	 *
	 * @subcommand get-face
	 *
	 * ## OPTIONS
	 *
	 * <input_file>
	 * : Path to an existing image file
	 *
	 * ## EXAMPLES
	 *
	 *     wp image-crop-positioner face-detection get-face
	 */
	public function get_face( array $args, array $assoc_args ): void {
		try {
			$detector = ( new FaceDetector() )->extract( $args[0] );
		} catch ( Exception $e ) {
			WP_CLI::error( $e->getMessage() );
			exit;
		}

		if ( ! $detector->face_found ) {
			WP_CLI::warning( 'No face found' );
		} else {
			WP_CLI::log( 'Face found' );
			WP_CLI::log( 'X: ' . $detector->face['x'] );
			WP_CLI::log( 'Y: ' . $detector->face['y'] );
			WP_CLI::log( 'Size: ' . $detector->face['w'] . 'x' . $detector->face['w'] );
		}
	}

	/**
	 * Generate image with the face cropped out.
	 *
	 * @subcommand save-image
	 *
	 * ## OPTIONS
	 *
	 * <input_file>
	 * : Path to an existing image file
	 *
	 * <output_file>
	 * : Path to store the new image
	 *
	 * ## EXAMPLES
	 *
	 *     wp image-crop-positioner face-detection save-image
	 */
	public function save_image( array $args, array $assoc_args ): void {
		try {
			$saved = ( new FaceDetector() )->extract( $args[0] )->save( $args[1] );
		} catch ( Exception $e ) {
			WP_CLI::error( $e->getMessage() );
			exit;
		}

		if ( ! $saved ) {
			WP_CLI::error( 'Could not save image' );
		} else {
			WP_CLI::success( 'Image saved' );
		}
	}
}