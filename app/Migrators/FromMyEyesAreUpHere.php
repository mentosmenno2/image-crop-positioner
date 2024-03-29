<?php

namespace Mentosmenno2\ImageCropPositioner\Migrators;

use Mentosmenno2\ImageCropPositioner\Helpers\AttachmentMeta;
use Mentosmenno2\ImageCropPositioner\Objects\Face;
use Mentosmenno2\ImageCropPositioner\Objects\Hotspot;

class FromMyEyesAreUpHere extends BaseMigrator {

	public function get_slug(): string {
		return 'from_my_eyes_are_up_here';
	}

	public function get_title(): string {
		return __( 'From My Eyes Are Up Here', 'image-crop-positioner' );
	}

	public function get_description(): string {
		$url = 'https://wordpress.org/plugins/my-eyes-are-up-here/';
		return sprintf( __( 'Migrate faces and hotspots from the <a href="%s" target="_blank">My Eyes Are Up Here</a> plugin to Image Crop Positioner.', 'image-crop-positioner' ), $url );
	}

	public function get_default_batch_size(): int {
		return 50;
	}

	public function migrate_attachment( int $attachment_id ): int {
		$statuses = $this->get_migrations( $attachment_id );

		// If all statusses skipped, return skipped
		$all_skipped = count( array_unique( $statuses ) ) === 1 && end( $statuses ) === self::STATUS_SKIPPED;
		if ( $all_skipped ) {
			return self::STATUS_SKIPPED;
		}

		return self::STATUS_DONE;
	}

	protected function get_migrations( int $attachment_id ): array {
		return array(
			$this->migrate_faces( $attachment_id ),
			$this->migrate_hotspots( $attachment_id ),
		);
	}

	protected function has_faces( int $attachment_id ): bool {
		return metadata_exists( 'post', $attachment_id, AttachmentMeta::META_KEY_FACES );
	}

	/**
	 * Migrate faces from My Eyes Are Up Here to Image Crop Positioner
	 */
	protected function migrate_faces( int $attachment_id ): int {
		$old_faces = get_post_meta( $attachment_id, 'faces', true );
		if ( empty( $old_faces ) || ! is_array( $old_faces ) ) {
			return self::STATUS_SKIPPED;
		}

		if ( $this->has_faces( $attachment_id ) ) {
			return self::STATUS_SKIPPED;
		}

		$new_faces = array();
		foreach ( $old_faces as $old_face ) {
			$new_faces[] = new Face(
				array(
					'x'      => $old_face['x'],
					'y'      => $old_face['y'],
					'width'  => $old_face['width'],
					'height' => $old_face['height'] ?? $old_face['width'],
				)
			);
		}

		( new AttachmentMeta() )->set_faces( $attachment_id, $new_faces );
		return self::STATUS_DONE;
	}

	protected function has_hotspots( int $attachment_id ): bool {
		return metadata_exists( 'post', $attachment_id, AttachmentMeta::META_KEY_HOTSPOTS );
	}

	/**
	 * Migrate hotspots from My Eyes Are Up Here to Image Crop Positioner
	 */
	protected function migrate_hotspots( int $attachment_id ): int {
		$old_hotspots = get_post_meta( $attachment_id, 'hotspots', true );
		if ( empty( $old_hotspots ) || ! is_array( $old_hotspots ) ) {
			return self::STATUS_SKIPPED;
		}

		if ( $this->has_hotspots( $attachment_id ) ) {
			return self::STATUS_SKIPPED;
		}

		$new_hotspots = array();
		foreach ( $old_hotspots as $old_hotspot ) {
			$new_hotspots[] = new Hotspot(
				array(
					'x'      => $old_hotspot['x'],
					'y'      => $old_hotspot['y'],
					'width'  => $old_hotspot['width'],
					'height' => $old_hotspot['height'] ?? $old_hotspot['width'],
				)
			);
		}

		( new AttachmentMeta() )->set_hotspots( $attachment_id, $new_hotspots );
		return self::STATUS_DONE;
	}
}
