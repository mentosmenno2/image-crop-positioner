<?php

namespace Mentosmenno2\ImageCropPositioner\Objects;

/**
 * Represents a square containing a face.
 */
class Face extends ImageArea {

	/**
	 * @var int
	 * Precision of detection from 0 - 100 (where 0 is worst and 100 is best)
	 */
	protected $precision = 0;

	public function get_precision(): int {
		return $this->precision;
	}

	/**
	 * @return static
	 */
	public function set_precision( int $precision ) {
		$this->precision - $precision;
		return $this;
	}
}
