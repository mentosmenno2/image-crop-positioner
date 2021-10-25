<?php

namespace Mentosmenno2\ImageCropPositioner\Migrators;

use WP_Query;

abstract class BaseMigrator {

	public const STATUS_SKIPPED = 0;
	public const STATUS_DONE    = 1;

	/**
	 * Unique slug for the migrator
	 */
	abstract public function get_slug(): string;

	/**
	 * Title of the migrator
	 */
	abstract public function get_title(): string;

	/**
	 * Description of the migrator. May contain HTML characters.
	 */
	abstract public function get_description(): string;

	/**
	 * Migrate an attachment
	 *
	 * @param integer $attachment_id
	 * @return self::STATUS_*
	 */
	abstract public function migrate_attachment( int $attachment_id ): int;

	public function get_migratable_attachment_ids( int $page = 1, int $per_page = -1 ): WP_Query {
		$image_mimes = array_filter(
			get_allowed_mime_types(), function( string $mime ): bool {
				return strpos( $mime, 'image/' ) === 0;
			}
		);

		$args = array(
			'post_type'      => 'attachment',
			'post_status'    => 'any',
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'fields'         => 'ids',
			'order'          => 'ASC',
			'orderby'        => 'ID',
			'post_mime_type' => $image_mimes,
		);

		$wp_query = new WP_Query( $args );
		return $wp_query;
	}
}
