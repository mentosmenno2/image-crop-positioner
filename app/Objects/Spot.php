<?php

namespace Mentosmenno2\ImageCropPositioner\Objects;

use JsonSerializable;

/**
 * Represents a spot on an image
 */
class Spot implements JsonSerializable {

	protected float $x = 0;

	protected float $y = 0;

	protected float $width = 0;

	protected float $height = 0;

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

	/**
	 * @return static
	 */
	public function set_x( float $x ) {
		$this->x = $x;
		return $this;
	}

	public function get_y(): float {
		return $this->y;
	}

	/**
	 * @return static
	 */
	public function set_y( float $y ) {
		$this->y = $y;
		return $this;
	}

	public function get_width(): float {
		return $this->width;
	}

	/**
	 * @return static
	 */
	public function set_width( float $width ) {
		$this->width = $width;
		return $this;
	}

	public function get_height(): float {
		return $this->height;
	}

	/**
	 * @return static
	 */
	public function set_height( float $height ) {
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
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return $this->get_data_array();
	}
}
