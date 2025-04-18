<?php

namespace Mentosmenno2\ImageCropPositioner\Migrators;

use Mentosmenno2\ImageCropPositioner\Migrators\BaseMigrator;

class Migrators {

	/**
	 * @return BaseMigrator[]
	 */
	public function get_migrators(): array {
		$classnames = array(
			RegenerateImages::class,
			FromMyEyesAreUpHere::class,
			ToMyEyesAreUpHere::class,
		);

		$classes = array_map(
			function ( string $classname ): BaseMigrator {
				return new $classname();
			}, $classnames
		);
		$keys    = array_map(
			function ( BaseMigrator $migrator ): string {
				return $migrator->get_slug();
			}, $classes
		);
		return array_combine( $keys, $classes );
	}
}
