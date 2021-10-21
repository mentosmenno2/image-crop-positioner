<?php

namespace Mentosmenno2\ImageCropPositioner\Admin\Settings;

abstract class BaseField {
	abstract public function get_name(): string;

	abstract public function get_label(): string;

	abstract public function get_description(): string;

	/**
	 * Get field value
	 *
	 * @return mixed
	 */
	abstract public function get_value();

	/**
	 * Get field value
	 *
	 * @return mixed
	 */
	abstract protected function get_default_value();

	abstract public function render_field(): void;
}
