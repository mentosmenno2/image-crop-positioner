<?php

namespace Mentosmenno2\ImageCropPositioner\Migrators;

use Mentosmenno2\ImageCropPositioner\Helpers\AttachmentMeta;
use Mentosmenno2\ImageCropPositioner\Objects\Face;
use Mentosmenno2\ImageCropPositioner\Objects\Hotspot;
use Mentosmenno2\ImageCropPositioner\Objects\Spot;

class ToMyEyesAreUpHere extends BaseMigrator {

	public function get_slug(): string {
		return 'to_my_eyes_are_up_here';
	}

	public function get_title(): string {
		return __( 'To My Eyes Are Up Here', 'image-crop-positioner' );
	}

	public function get_description(): string {
		$url = 'https://wordpress.org/plugins/my-eyes-are-up-here/';
		return sprintf( __( 'Migrate faces and hotspots from Image Crop Positioner to the <a href="%s" target="_blank">My Eyes Are Up Here</a> plugin. We\'re sorry to see you go, but we also know we can\'t stop you from doing so. But at least, we can make the process easier.', 'image-crop-positioner' ), $url );
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
		return metadata_exists( 'post', $attachment_id, 'faces' );
	}

	/**
	 * Migrate faces from Image Crop Positioner to My Eyes Are Up Here
	 */
	protected function migrate_faces( int $attachment_id ): int {
		$old_faces = ( new AttachmentMeta() )->get_faces( $attachment_id );
		if ( empty( $old_faces ) ) {
			return self::STATUS_SKIPPED;
		}

		if ( $this->has_faces( $attachment_id ) ) {
			return self::STATUS_SKIPPED;
		}

		$new_faces = array();
		foreach ( $old_faces as $old_face ) {
			$new_faces[] = $this->convert_face( $old_face );
		}

		update_post_meta( $attachment_id, 'faces', $new_faces );
		return self::STATUS_DONE;
	}

	protected function has_hotspots( int $attachment_id ): bool {
		return metadata_exists( 'post', $attachment_id, 'hotspots' );
	}

	/**
	 * Migrate hotspots from Image Crop Positioner to My Eyes Are Up Here
	 */
	protected function migrate_hotspots( int $attachment_id ): int {
		$old_hotspots = ( new AttachmentMeta() )->get_hotspots( $attachment_id );
		if ( empty( $old_hotspots ) ) {
			return self::STATUS_SKIPPED;
		}

		if ( $this->has_hotspots( $attachment_id ) ) {
			return self::STATUS_SKIPPED;
		}

		$new_hotspots = array();
		foreach ( $old_hotspots as $old_hotspot ) {
			$new_hotspots[] = $this->convert_hotspot( $old_hotspot );
		}

		update_post_meta( $attachment_id, 'hotspots', $new_hotspots );
		return self::STATUS_DONE;
	}

	protected function convert_face( Face $face ): array {
		return array(
			'x'      => (string) $face->get_x(),
			'y'      => (string) $face->get_y(),
			'width'  => (string) $face->get_width(),
			'height' => (string) $face->get_height(),
		);
	}

	protected function convert_hotspot( Hotspot $hotspot ):array {
		return array(
			'x'     => (string) $hotspot->get_x(),
			'y'     => (string) $hotspot->get_y(),
			'width' => (string) $hotspot->get_width(),
		);
	}
}
