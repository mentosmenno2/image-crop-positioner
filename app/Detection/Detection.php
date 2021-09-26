<?php
namespace Mentosmenno2\ImageCropPositioner\Detection;

use Mentosmenno2\ImageCropPositioner\Detection\FaceDetector\FaceDetector;
use Mentosmenno2\ImageCropPositioner\Detection\BaseDetector;

class Detection {

	/**
	 * Get available detectors
	 *
	 * @return BaseDetector[]
	 */
	public function get_available_detectors(): array {
		$classes         = array();
		$faces_available = FaceDetector::is_available();
		if ( $faces_available ) {
			$classes[] = FaceDetector::get_instance();
		}

		$keys = array_map(
			function( BaseDetector $detector ): string {
				return $detector->get_slug();
			}, $classes
		);

		return array_combine( $keys, $classes );
	}
}
