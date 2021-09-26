<?php

namespace Mentosmenno2\ImageCropPositioner\CLI;

use Exception;
use Mentosmenno2\ImageCropPositioner\Detection\Detection as DetectionDetection;
use WP_CLI;
use Mentosmenno2\ImageCropPositioner\Detection\spotDetector\spotDetector;

class Detection {
	/**
	 * Get spots coordinates.
	 *
	 * @subcommand get-spots
	 *
	 * ## OPTIONS
	 *
	 * <detector>
	 * : Detector slug
	 *
	 * <input_file>
	 * : Path to an existing image file
	 *
	 * ## EXAMPLES
	 *
	 *     wp image-crop-positioner detection get-spots /path/to/input/image.jpg
	 */
	public function get_spots( array $args, array $assoc_args ): void {
		try {
			$detectors = ( new DetectionDetection() )->get_available_detectors();
			if ( ! array_key_exists( $args[0], $detectors ) ) {
				WP_CLI::error( 'Detector does not exist or is not active' );
				exit;
			}
			$spots = $detectors[ $args[0] ]->detect( $args[1] );
		} catch ( Exception $e ) {
			WP_CLI::error( $e->getMessage() );
			exit;
		}

		if ( empty( $spots ) ) {
			WP_CLI::warning( 'No spots found' );
		} else {
			foreach ( $spots as $spot_index => $spot ) {
				WP_CLI::log( sprintf( 'Spot %d of %d', $spot_index + 1, count( $spots ) ) );
				$spot_data = $spot->get_data_array();
				foreach ( $spot_data as $spot_data_key => $spot_data_item ) {
					WP_CLI::log( sprintf( '%s: %s', ucfirst( $spot_data_key ), (string) $spot_data_item ) );
				}
			}
		}
	}
}
