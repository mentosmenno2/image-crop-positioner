<?php

namespace Mentosmenno2\ImageCropPositioner\Migrators;

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
}
