<?php

namespace Mentosmenno2\ImageCropPositioner\Migrators;

use Mentosmenno2\ImageCropPositioner\Regenerate;

class RegenerateImages extends BaseMigrator {

	public function get_slug(): string {
		return 'regenerate_images';
	}

	public function get_title(): string {
		return __( 'Regenerate all images', 'image-crop-positioner' );
	}

	public function get_description(): string {
		return __( 'Regenerate all image sizes for all images in the WordPress media library.', 'image-crop-positioner' );
	}

	public function get_default_batch_size(): int {
		return 10;
	}

	public function migrate_attachment( int $attachment_id ): int {
		$regenerated = ( new Regenerate() )->run( $attachment_id );
		return $regenerated ? self::STATUS_DONE : self::STATUS_SKIPPED;
	}
}
