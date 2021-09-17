<?php

namespace Mentosmenno2\ImageCropPositioner\FaceDetection;

use JsonSerializable;

/**
 * Represents a square containing a face.
 */
class Face implements JsonSerializable {

	/** @var float */
	protected $x = 0;

	/** @var float */
	protected $y = 0;

	/** @var float */
	protected $width = 0;

	/** @var float */
	protected $height = 0;

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

	public function get_width(): float {
		return $this->width;
	}

	public function set_width( float $width ): Face {
		$this->width = $width;
		return $this;
	}

	public function get_height(): float {
		return $this->height;
	}

	public function set_height( float $height ): Face {
		$this->height = $height;
		return $this;
	}

	public function get_data_array(): array {
		return array(
			'x'      => $this->x,
			'y'      => $this->y,
			'width'  => $this->width,
			'height' => $this->height,
		);
	}

	/**
	 * @return array
	 */
	public function jsonSerialize() {
		return $this->get_data_array();
	}
}
