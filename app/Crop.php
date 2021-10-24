<?php

namespace Mentosmenno2\ImageCropPositioner;

use Mentosmenno2\ImageCropPositioner\Admin\Settings\Crop\Fields\CropPositioningMethod;
use Mentosmenno2\ImageCropPositioner\Helpers\AttachmentMeta;
use Mentosmenno2\ImageCropPositioner\Objects\Spot;

class Crop {

	/** @var int */
	protected $attachment_id = 0;

	public function register_hooks(): void {
		add_filter( 'get_attached_file', array( $this, 'set_attachment_id' ), 10, 2 );
		add_filter( 'update_attached_file', array( $this, 'set_attachment_id' ), 10, 2 );

		add_filter( 'image_resize_dimensions', array( $this, 'crop' ), 12, 6 );
	}

	/**
	 * Hacky use of attached_file filters to get current attachment ID being resized
	 */
	public function set_attachment_id( string $file, int $attachment_id ): string {
		$this->attachment_id = $attachment_id;
		return $file;
	}

	/**
	 * Alters the crop location of the GD image editor class.
	 * It gets the center of all spots, and attempts to set the crop on the original image as close in the center of the spots center.
	 *
	 * @param mixed $payload Whether to preempt output of the resize dimensions.
	 * @param int $orig_w Original width in pixels.
	 * @param int $orig_h Original height in pixels.
	 * @param int $dest_w New width in pixels.
	 * @param int $dest_h New height in pixels.
	 * @param bool|array $crop Whether to crop image to specified width and height or resize. An array can specify positioning of the crop area. Default false.
	 *
	 * @return mixed
	 */
	public function crop( $payload, int $orig_w, int $orig_h, int $dest_w, int $dest_h, $crop ) {
		$attachment_meta_helper = new AttachmentMeta();

		/** @var Spot[] */
		$spots = array_merge(
			$attachment_meta_helper->get_faces( $this->attachment_id ),
			$attachment_meta_helper->get_hotspots( $this->attachment_id )
		);

		// Exit if not cropping or no spots
		if ( ! $crop || empty( $spots ) ) {
			return $payload;
		}

		// Calculate the size of the crop from the original image
		$crop_w       = $orig_w;
		$width_factor = $orig_w / $dest_w;
		$crop_h       = $dest_h * $width_factor;
		if ( $crop_h > $orig_h ) { // Original image has a wider aspect ratio than dest image
			$crop_h        = $orig_h;
			$height_factor = $orig_h / $dest_h;
			$crop_w        = $dest_w * $height_factor;
		}

		// Get the spots focus coordinates
		if ( ( new CropPositioningMethod() )->get_value() === 'average' ) {
			$spots_focus_coords = $this->get_spots_average( $spots );
		} else {
			$spots_focus_coords = $this->get_spots_center( $spots );
		}

		// Place the crop in the center of the spots
		$crop_x = $spots_focus_coords['x'] - ( $crop_w / 2 );
		$crop_y = $spots_focus_coords['y'] - ( $crop_h / 2 );

		// If crop on x gets over border of original image, set x to closest position to border possible
		if ( $crop_x < 0 ) {
			$crop_x = 0;
		} elseif ( $crop_x + $crop_w > $orig_w ) {
			$crop_x = $orig_w - $crop_w;
		}

		// If crop on y get over border of original image, set x to closest position to border possible
		if ( $crop_y < 0 ) {
			$crop_y = 0;
		} elseif ( $crop_y + $crop_h > $orig_h ) {
			$crop_y = $orig_h - $crop_h;
		}

		return array( 0, 0, (int) $crop_x, (int) $crop_y, $dest_w, $dest_h, (int) $crop_w, (int) $crop_h );
	}

	/**
	 * @param Spot[] $spots
	 * @return array
	 */
	protected function get_spots_average( array $spots ): array {
		$x_total     = 0;
		$y_total     = 0;
		$spots_count = count( $spots );
		foreach ( $spots as $spot ) {
			$x_total += $spot->get_x() + ( $spot->get_width() / 2 );
			$y_total += $spot->get_y() + ( $spot->get_height() / 2 );
		}
		return array(
			'x' => (int) ( $x_total / $spots_count ),
			'y' => (int) ( $y_total / $spots_count ),
		);
	}

	/**
	 * @param Spot[] $spots
	 * @return array
	 */
	protected function get_spots_center( array $spots ): array {
		// Get bounding box
		$bounding_box = array(
			'x_min' => PHP_INT_MAX,
			'x_max' => 0,
			'y_min' => PHP_INT_MAX,
			'y_max' => 0,
		);
		foreach ( $spots as $spot ) {
			$bounding_box['x_min'] = (int) min( $bounding_box['x_min'], $spot->get_x() );
			$bounding_box['x_max'] = (int) max( $bounding_box['x_max'], $spot->get_x() + $spot->get_width() );
			$bounding_box['y_min'] = (int) min( $bounding_box['y_min'], $spot->get_y() );
			$bounding_box['y_max'] = (int) max( $bounding_box['y_max'], $spot->get_y() + $spot->get_height() );
		}

		// Get center of bounding box
		$x_diff = $bounding_box['x_max'] - $bounding_box['x_min'];
		$y_diff = $bounding_box['y_max'] - $bounding_box['y_min'];
		return array(
			'x' => (int) ( $bounding_box['x_min'] + ( $x_diff / 2 ) ),
			'y' => (int) ( $bounding_box['y_min'] + ( $y_diff / 2 ) ),
		);
	}
}
