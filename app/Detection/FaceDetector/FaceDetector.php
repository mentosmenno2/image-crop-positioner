<?php
/**
 * PHP face detection
 *
 * This code has been reused from the softon/laravel-face-detect library.
 * @author Softon Technologies <powerupneo@gmail.com>
 *
 * The library is a Laravel version of mauricesvay/php-facedetection.
 * @author Maurice Svay <maurice@svay.com>
 * @package mauricesvay/php-facedetection
 * @see https://github.com/mauricesvay/php-facedetection
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 * That face detection library is a direct port of  a JavaScript face detection library by Karthik Tharavaad.
 * @author Karthik Tharavaad <karthik_tharavaad@yahoo.com>
 */

namespace Mentosmenno2\ImageCropPositioner\Detection\FaceDetector;

use Exception;
use GdImage;
use Mentosmenno2\ImageCropPositioner\Detection\BaseDetector;
use Mentosmenno2\ImageCropPositioner\Objects\Face;

class FaceDetector extends BaseDetector {

	/** @var int */
	protected const PADDING_WIDTH = 10;

	/** @var int */
	protected const PADDING_HEIGHT = 20;

	/** @var int */
	protected const ACCURACY_THRESHHOLD = 50;

	/** @var array */
	protected $detection_data = array();

	/**
	 * @inheritDoc
	 */
	protected function __construct() {
		if ( ! extension_loaded( 'gd' ) ) {
			throw new Exception( 'PHP GD extension is not loaded' );
		}

		$detection_file = dirname( __FILE__ ) . '/detection.dat';
		if ( ! is_file( $detection_file ) ) {
			throw new Exception( 'Detection data file does not exist' );
		}

		$contents = file_get_contents( $detection_file ); //phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( ! is_string( $contents ) ) {
			throw new Exception( 'Could not read file detection data' );
		}

		$detection_data = unserialize( $contents ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
		if ( ! is_array( $detection_data ) ) {
			throw new Exception( 'Detection data is invalid' );
		}

		$this->detection_data = $detection_data;
	}

	public function get_slug(): string {
		return 'faces';
	}

	/**
	 * @inheritDoc
	 */
	public function detect( $file ): array {
		$file = $this->file_to_resource( $file );
		/** @psalm-suppress PossiblyInvalidArgument */
		$im_width = imagesx( $file );
		/** @psalm-suppress PossiblyInvalidArgument */
		$im_height = imagesy( $file );
		if ( ! is_int( $im_width ) || ! is_int( $im_height ) ) {
			throw new Exception( 'Cannot determine dimensions' );
		}

		//Resample before detection?
		$ratio       = 0;
		$diff_width  = 320 - $im_width;
		$diff_height = 240 - $im_height;
		if ( $diff_width > $diff_height ) {
			$ratio = (float) ( $im_width / 320 );
		} else {
			$ratio = (float) ( $im_height / 240 );
		}

		if ( $ratio !== (float) 0 ) {
			$new_img_width  = (int) ( $im_width / $ratio );
			$new_img_height = (int) ( $im_height / $ratio );
			$reduced_canvas = imagecreatetruecolor( $new_img_width, $new_img_height );
			if ( ! $reduced_canvas ) {
				throw new Exception( 'Could not create new truecolor image' );
			}
			/** @psalm-suppress all */
			imagecopyresampled( $reduced_canvas, $file, 0, 0, 0, 0, $new_img_width, $new_img_height, $im_width, $im_height );

			$stats = $this->get_img_stats( $reduced_canvas );
			$face  = $this->do_detect_greedy_big_to_small( $stats['ii'], $stats['ii2'], $stats['width'], $stats['height'] );
			if ( $face instanceof Face && $face->get_width() > 0 ) {
				$face = $face
					->set_x( $face->get_x() * $ratio )
					->set_y( $face->get_y() * $ratio )
					->set_width( $face->get_width() * $ratio )
					->set_height( $face->get_height() * $ratio );
			}
		} else {
			/** @psalm-suppress all */
			$stats = $this->get_img_stats( $file );
			$face  = $this->do_detect_greedy_big_to_small( $stats['ii'], $stats['ii2'], $stats['width'], $stats['height'] );
		}
		if ( ! $face instanceof Face || $face->get_width() <= 0 ) {
			return array();
		}
		return array( $face );
	}

	/**
	 * @param resource|GdImage $canvas
	 *
	 * @return array
	 *
	 * @psalm-suppress UndefinedDocblockClass
	 */
	protected function get_img_stats( $canvas ) {
		/** @psalm-suppress PossiblyInvalidArgument */
		$image_width = imagesx( $canvas );
		/** @psalm-suppress PossiblyInvalidArgument */
		$image_height = imagesy( $canvas );
		if ( ! is_int( $image_width ) || ! is_int( $image_height ) ) {
			throw new Exception( 'Cannot determine dimensions of canvas' );
		}

		$iis = $this->compute_ii( $canvas, $image_width, $image_height );
		return array(
			'width'  => $image_width,
			'height' => $image_height,
			'ii'     => $iis['ii'],
			'ii2'    => $iis['ii2'],
		);
	}

	/**
	 * @param resource|GdImage $canvas
	 * @param int $image_width
	 * @param int $image_height
	 *
	 * @return array
	 *
	 * @psalm-suppress UndefinedDocblockClass
	 */
	protected function compute_ii( $canvas, $image_width, $image_height ) {
		$ii_w = $image_width + 1;
		$ii_h = $image_height + 1;
		$ii   = array();
		$ii2  = array();

		for ( $i = 0; $i < $ii_w; $i++ ) {
			$ii[ $i ]  = 0;
			$ii2[ $i ] = 0;
		}

		for ( $i = 1; $i < $ii_h - 1; $i++ ) {
			$ii[ $i * $ii_w ]  = 0;
			$ii2[ $i * $ii_w ] = 0;
			$rowsum            = 0;
			$rowsum2           = 0;
			for ( $j = 1; $j < $ii_w - 1; $j++ ) {
				/** @psalm-suppress PossiblyInvalidArgument */
				$rgb      = ImageColorAt( $canvas, $j, $i ) ?: 0;
				$red      = ( $rgb >> 16 ) & 0xFF;
				$green    = ( $rgb >> 8 ) & 0xFF;
				$blue     = $rgb & 0xFF;
				$grey     = ( 0.2989 * $red + 0.587 * $green + 0.114 * $blue ) >> 0;  // this is what matlab uses
				$rowsum  += $grey;
				$rowsum2 += $grey * $grey;

				$ii_above = ( $i - 1 ) * $ii_w + $j;
				$ii_this  = $i * $ii_w + $j;

				$ii[ $ii_this ]  = $ii[ $ii_above ] + $rowsum;
				$ii2[ $ii_this ] = $ii2[ $ii_above ] + $rowsum2;
			}
		}
		return array(
			'ii'  => $ii,
			'ii2' => $ii2,
		);
	}

	/**
	 * @param array $ii
	 * @param array $ii2
	 * @param int $width
	 * @param int $height
	 * @return Face|null
	 */
	protected function do_detect_greedy_big_to_small( $ii, $ii2, $width, $height ) {
		$s_w                = $width / 20.0;
		$s_h                = $height / 20.0;
		$start_scale        = $s_h < $s_w ? $s_h : $s_w;
		$scale_update       = 1 / 1.2;
		$loops              = 0;
		$max_loops          = 0;
		$face_data          = null;
		$detection_accuracy = null;
		for ( $scale = $start_scale; $scale > 1; $scale *= $scale_update ) {
			$max_loops++;
			if ( is_array( $face_data ) ) {
				continue;
			}

			$w        = ( 20 * $scale ) >> 0;
			$endx     = $width - $w - 1;
			$endy     = $height - $w - 1;
			$step     = max( $scale, 2 ) >> 0;
			$inv_area = 1 / ( $w * $w );
			for ( $y = 0; $y < $endy; $y += $step ) { //phpcs:ignore
				for ( $x = 0; $x < $endx; $x += $step ) {
					if ( is_array( $face_data ) ) {
						continue;
					}
					$detection_accuracy = $this->detect_on_sub_image( (int) $x, (int) $y, $scale, $ii, $ii2, (int) $w, $width + 1, $inv_area );
					if ( $detection_accuracy ) {
						$face_data = array(
							'x'      => $x,
							'y'      => $y,
							'width'  => $w,
							'height' => $w,
						);
					}
				} // end x
			} // end y
			$loops++;
		}  // end scale

		if ( ! is_array( $face_data ) ) {
			return null;
		}

		$scale_accuracy = ( $max_loops - ( $loops - 1 ) ) / $max_loops * 100;
		$accuracys      = array( $detection_accuracy, $scale_accuracy );
		$avg_accuracy   = array_sum( $accuracys ) / count( $accuracys );
		if ( $avg_accuracy < self::ACCURACY_THRESHHOLD ) {
			return null;
		}

		$face_data['accuracy'] = $avg_accuracy;
		return new Face(
			$face_data
		);
	}

	/**
	 * @param int $x
	 * @param int $y
	 * @param float $scale
	 * @param array $ii
	 * @param array $ii2
	 * @param int $w
	 * @param int $iiw
	 * @param float $inv_area
	 * @return float
	 */
	protected function detect_on_sub_image( $x, $y, $scale, $ii, $ii2, $w, $iiw, $inv_area ) {
		$mean             = ( $ii[ ( $y + $w ) * $iiw + $x + $w ] + $ii[ $y * $iiw + $x ] - $ii[ ( $y + $w ) * $iiw + $x ] - $ii[ $y * $iiw + $x + $w ] ) * $inv_area;
		$vnorm            = ( $ii2[ ( $y + $w ) * $iiw + $x + $w ] + $ii2[ $y * $iiw + $x ] - $ii2[ ( $y + $w ) * $iiw + $x ] - $ii2[ $y * $iiw + $x + $w ] ) * $inv_area - ( $mean * $mean );
		$vnorm            = $vnorm > 1 ? sqrt( $vnorm ) : 1;
		$largest_accuracy = 0;

		for ( $i_stage = 0; $i_stage < count( $this->detection_data ); $i_stage++ ) { // phpcs:ignore
			$stage = $this->detection_data[ $i_stage ];
			$trees = $stage[0];

			$stage_thresh = $stage[1];
			$stage_sum    = 0;

			for ( $i_tree = 0; $i_tree < count( $trees ); $i_tree++ ) { // phpcs:ignore
				$tree         = $trees[ $i_tree ];
				$current_node = $tree[0];
				$tree_sum     = 0;
				while ( $current_node !== null ) {
					$vals        = $current_node[0];
					$node_thresh = $vals[0];
					$leftval     = $vals[1];
					$rightval    = $vals[2];
					$leftidx     = $vals[3];
					$rightidx    = $vals[4];
					$rects       = $current_node[1];

					$rect_sum = 0;
					for ( $i_rect = 0; $i_rect < count( $rects ); $i_rect++ ) { // phpcs:ignore
						$s    = $scale;
						$rect = $rects[ $i_rect ];
						$rx   = ( $rect[0] * $s + $x ) >> 0;
						$ry   = ( $rect[1] * $s + $y ) >> 0;
						$rw   = ( $rect[2] * $s ) >> 0;
						$rh   = ( $rect[3] * $s ) >> 0;
						$wt   = $rect[4];

						$r_sum     = ( $ii[ ( $ry + $rh ) * $iiw + $rx + $rw ] + $ii[ $ry * $iiw + $rx ] - $ii[ ( $ry + $rh ) * $iiw + $rx ] - $ii[ $ry * $iiw + $rx + $rw ] ) * $wt;
						$rect_sum += $r_sum;
					}

					$rect_sum *= $inv_area;

					$current_node = null;
					if ( $rect_sum >= $node_thresh * $vnorm ) {
						if ( $rightidx === -1 ) {
							$tree_sum = $rightval;
						} else {
							$current_node = $tree[ $rightidx ];
						}
					} else {
						if ( $leftidx === -1 ) {
							$tree_sum = $leftval;
						} else {
							$current_node = $tree[ $leftidx ];
						}
					}
				}
				$stage_sum += $tree_sum;
			}
			$stage_accuracy   = $stage_sum - $stage_thresh;
			$largest_accuracy = max( $largest_accuracy, $stage_accuracy );
			if ( $stage_sum < $stage_thresh ) {
				return 0;
			}
		}

		$largest_accuracy = $largest_accuracy * 30;
		return min( $largest_accuracy, 100 );
	}
}
