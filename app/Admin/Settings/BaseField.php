<?php

namespace Mentosmenno2\ImageCropPositioner\Admin\Settings;

abstract class BaseField {
	public const PREFIX = 'image_crop_positioner_';

	abstract public function get_name(): string;

	abstract public function get_label(): string;

	public function get_description(): string {
		return '';
	}

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

	public function is_disabled(): bool {
		return false;
	}

	abstract public function render_field(): void;
}
