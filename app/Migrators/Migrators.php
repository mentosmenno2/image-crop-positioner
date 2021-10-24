<?php

namespace Mentosmenno2\ImageCropPositioner\Migrators;

use Mentosmenno2\ImageCropPositioner\Migrators\BaseMigrator;

class Migrators {

	/**
	 * @return BaseMigrator[]
	 */
	public function get_migrators(): array {
		$classnames = array(
			MyEyesAreUpHere::class,
		);

		return array_map(
			function( string $classname ): BaseMigrator {
				return new $classname();
			}, $classnames
		);
	}
}
