<?php

namespace Mentosmenno2\ImageCropPositioner\Objects;

/**
 * Represents a square containing a face.
 */
class Face extends Spot {

	/**
	 * Accuracy of detection from 0 - 100 (where 0 is worst and 100 is best)
	 */
	protected float $accuracy = 0;

	public function get_accuracy(): float {
		return $this->accuracy;
	}

	/**
	 * @return static
	 */
	public function set_accuracy( float $accuracy ) {
		$this->accuracy = $accuracy;
		return $this;
	}

	public function get_data_array(): array {
		$data             = parent::get_data_array();
		$data['accuracy'] = $this->accuracy;
		return $data;
	}
}
