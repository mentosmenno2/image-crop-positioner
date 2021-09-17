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

namespace Mentosmenno2\ImageCropPositioner\FaceDetection;

use Exception;
use GdImage;

class FaceDetector {

	/** @var int */
	protected const PADDING_WIDTH = 10;

	/** @var int */
	protected const PADDING_HEIGHT = 20;

	/** @var array */
	protected $detection_data = array();

	/** @var self|null*/
	protected static $instance = null;

	/**
	 * @var null|resource|GdImage
	 *
	 * @psalm-suppress UndefinedDocblockClass
	 */
	public $canvas;

	/** @var Face|null */
	public $face;

	/** @var bool */
	public $face_found = false;

	/**
	 * Create a new face detector class
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

	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function is_available(): bool {
		try {
			new self();
		} catch ( Exception $e ) {
			return false;
		}
		return true;
	}

	/**
	 * @param resource|GdImage|string $file
	 *
	 * @return $this
	 *
	 * @psalm-suppress UndefinedDocblockClass
	 */
	public function extract( $file ) {

		/** @psalm-suppress UndefinedClass */
		if ( is_resource( $file ) || $file instanceof GdImage ) {
			$this->canvas = $file;
		} elseif ( is_file( $file ) ) {
			/** @var string */
			$extension     = pathinfo( $file, PATHINFO_EXTENSION );
			$extension     = $this->map_file_extension( strtolower( $extension ) );
			$function_name = 'imagecreatefrom' . $extension;
			if ( ! function_exists( $function_name ) ) {
				throw new Exception( "File extension of $file is not supported for reading data" );
			}

			/** @var resource|GdImage|false */
			$result = $function_name( $file );
			if ( $result === false ) {
				throw new Exception( "Cannot load $file" );
			}

			$this->canvas = $result;
		} else {
			throw new Exception( 'Provided file is not a file' );
		}

		/** @psalm-suppress all */
		if ( is_null( $this->canvas ) ) {
			throw new Exception( 'Could not create canvas' );
		}

		/** @psalm-suppress PossiblyInvalidArgument */
		$im_width = imagesx( $this->canvas );
		/** @psalm-suppress PossiblyInvalidArgument */
		$im_height = imagesy( $this->canvas );
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
			imagecopyresampled( $reduced_canvas, $this->canvas, 0, 0, 0, 0, $new_img_width, $new_img_height, $im_width, $im_height );

			$stats      = $this->get_img_stats( $reduced_canvas );
			$this->face = $this->do_detect_greedy_big_to_small( $stats['ii'], $stats['ii2'], $stats['width'], $stats['height'] );
			if ( $this->face instanceof Face && $this->face->get_width() > 0 ) {
				$this->face = $this->face
					->set_x( $this->face->get_x() * $ratio )
					->set_y( $this->face->get_y() * $ratio )
					->set_width( $this->face->get_width() * $ratio )
					->set_height( $this->face->get_height() * $ratio );
			}
		} else {
			/** @psalm-suppress all */
			$stats      = $this->get_img_stats( $this->canvas );
			$this->face = $this->do_detect_greedy_big_to_small( $stats['ii'], $stats['ii2'], $stats['width'], $stats['height'] );
		}
		if ( $this->face instanceof Face && $this->face->get_width() > 0 ) {
			$this->face_found = true;
		}
		return $this;
	}

	protected function map_file_extension( string $extension ): string {
		$map = array(
			'jpg' => 'jpeg',
		);
		if ( ! isset( $map[ $extension ] ) ) {
			return $extension;
		}
		return $map[ $extension ];
	}

	/**
	 * @param string $file_name
	 * @param bool $overwrite
	 * @return bool
	 */
	public function save( $file_name, $overwrite = true ) {
		if ( ! $overwrite && file_exists( $file_name ) ) {
			throw new Exception( "Save File Already Exists ($file_name)" );
		}

		if ( ! $this->face instanceof Face ) {
			throw new Exception( 'Cannot save file because no faces are detected' );
		}

		/** @psalm-suppress UndefinedClass */
		if ( ! is_resource( $this->canvas ) && ! $this->canvas instanceof GdImage ) {
			throw new Exception( 'Cannot save file because no canvas' );
		}

		$to_crop = array(
			'x'      => $this->face->get_x() - ( self::PADDING_WIDTH / 2 ),
			'y'      => $this->face->get_y() - ( self::PADDING_HEIGHT / 2 ),
			'width'  => $this->face->get_width() + self::PADDING_WIDTH,
			'height' => $this->face->get_height() + self::PADDING_HEIGHT,
		);

		/** @psalm-suppress PossiblyInvalidArgument */
		$cropped_canvas = imagecrop( $this->canvas, $to_crop );

		/** @var string */
		$extension     = pathinfo( $file_name, PATHINFO_EXTENSION );
		$extension     = $this->map_file_extension( strtolower( $extension ) );
		$function_name = 'image' . $extension;
		if ( ! function_exists( $function_name ) ) {
			throw new Exception( 'File extension is not supported for saving' );
		}

		/** @var bool */
		$result = $function_name( $cropped_canvas, $file_name, 100 );
		return $result;
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
		$s_w          = $width / 20.0;
		$s_h          = $height / 20.0;
		$start_scale  = $s_h < $s_w ? $s_h : $s_w;
		$scale_update = 1 / 1.2;
		for ( $scale = $start_scale; $scale > 1; $scale *= $scale_update ) {
			$w        = ( 20 * $scale ) >> 0;
			$endx     = $width - $w - 1;
			$endy     = $height - $w - 1;
			$step     = max( $scale, 2 ) >> 0;
			$inv_area = 1 / ( $w * $w );
			for ( $y = 0; $y < $endy; $y += $step ) { //phpcs:ignore
				for ( $x = 0; $x < $endx; $x += $step ) {
					$passed = $this->detect_on_sub_image( (int) $x, (int) $y, $scale, $ii, $ii2, (int) $w, $width + 1, $inv_area );
					if ( $passed ) {
						return new Face(
							array(
								'x'      => $x,
								'y'      => $y,
								'width'  => $w,
								'height' => $w,
							)
						);
					}
				} // end x
			} // end y
		}  // end scale
		return null;
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
	 * @return bool
	 */
	protected function detect_on_sub_image( $x, $y, $scale, $ii, $ii2, $w, $iiw, $inv_area ) {
		$mean  = ( $ii[ ( $y + $w ) * $iiw + $x + $w ] + $ii[ $y * $iiw + $x ] - $ii[ ( $y + $w ) * $iiw + $x ] - $ii[ $y * $iiw + $x + $w ] ) * $inv_area;
		$vnorm = ( $ii2[ ( $y + $w ) * $iiw + $x + $w ] + $ii2[ $y * $iiw + $x ] - $ii2[ ( $y + $w ) * $iiw + $x ] - $ii2[ $y * $iiw + $x + $w ] ) * $inv_area - ( $mean * $mean );
		$vnorm = $vnorm > 1 ? sqrt( $vnorm ) : 1;

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
			if ( $stage_sum < $stage_thresh ) {
				return false;
			}
		}
		return true;
	}
}
