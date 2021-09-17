<?php

namespace Mentosmenno2\ImageCropPositioner\Migrators;

use Mentosmenno2\ImageCropPositioner\Objects\Face;
use Mentosmenno2\ImageCropPositioner\Objects\Hotspot;

class MyEyesAreUpHere {

	public const STATUS_SKIPPED = 1;
	public const STATUS_SUCCESS = 2;
	public const STATUS_FAILED  = 3;

	public function migrate_attachment( int $attachment_id ): int {
		$faces_success    = $this->migrate_faces( $attachment_id );
		$hotspots_success = $this->migrate_hotspots( $attachment_id );

		return max( $faces_success, $hotspots_success );
	}

	protected function migrate_faces( int $attachment_id ): int {
		$old_faces = get_post_meta( $attachment_id, 'faces', true );
		if ( empty( $old_faces ) || ! is_array( $old_faces ) ) {
			return self::STATUS_SKIPPED;
		}

		$new_faces = get_post_meta( $attachment_id, 'image_crop_positioner_faces', true );
		if ( ! empty( $new_faces ) && is_array( $new_faces ) ) {
			return self::STATUS_SKIPPED;
		}

		$new_faces = array();
		foreach ( $old_faces as $old_face ) {
			$face_object = new Face(
				array(
					'x'      => $old_face['x'],
					'y'      => $old_face['y'],
					'width'  => $old_face['width'],
					'height' => $old_face['height'] ?? $old_face['width'],
				)
			);
			$new_faces[] = $face_object->get_data_array();
		}

		$updated = (bool) update_post_meta( $attachment_id, 'image_crop_positioner_faces', $new_faces );
		if ( ! $updated ) {
			return self::STATUS_FAILED;
		}
		return self::STATUS_SUCCESS;
	}

	protected function migrate_hotspots( int $attachment_id ): int {
		$old_hotspots = get_post_meta( $attachment_id, 'hotspots', true );
		if ( empty( $old_hotspots ) || ! is_array( $old_hotspots ) ) {
			return self::STATUS_SKIPPED;
		}

		$new_hotspots = get_post_meta( $attachment_id, 'image_crop_positioner_hotspots', true );
		if ( ! empty( $new_hotspots ) && is_array( $new_hotspots ) ) {
			return self::STATUS_SKIPPED;
		}

		$new_hotspots = array();
		foreach ( $old_hotspots as $old_hotspot ) {
			$hotspot_object = new Hotspot(
				array(
					'x'      => $old_hotspot['x'],
					'y'      => $old_hotspot['y'],
					'width'  => $old_hotspot['width'],
					'height' => $old_hotspot['height'] ?? $old_hotspot['width'],
				)
			);
			$new_hotspots[] = $hotspot_object->get_data_array();
		}

		$updated = (bool) update_post_meta( $attachment_id, 'image_crop_positioner_hotspots', $new_hotspots );
		if ( ! $updated ) {
			return self::STATUS_FAILED;
		}
		return self::STATUS_SUCCESS;
	}
}
