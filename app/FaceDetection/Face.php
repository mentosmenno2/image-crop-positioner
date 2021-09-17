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

class Face {

	/** @var float */
	protected $x = 0;

	/** @var float */
	protected $y = 0;

	/** @var float */
	protected $w = 0;

	/**
	 * @param array<string, float> $data
	 */
	public function __construct( array $data = array() ) {
		foreach ( $data as $key => $value ) {
			$this->$key = $value;
		}
	}

	public function get_x(): float {
		return $this->x;
	}

	public function set_x( float $x ): Face {
		$this->x = $x;
		return $this;
	}

	public function get_y(): float {
		return $this->y;
	}

	public function set_y( float $y ): Face {
		$this->y = $y;
		return $this;
	}

	public function get_w(): float {
		return $this->w;
	}

	public function set_w( float $w ): Face {
		$this->w = $w;
		return $this;
	}
}
