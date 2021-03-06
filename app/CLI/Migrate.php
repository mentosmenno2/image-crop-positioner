<?php

namespace Mentosmenno2\ImageCropPositioner\CLI;

use Mentosmenno2\ImageCropPositioner\Migrators\Migrators;
use Mentosmenno2\ImageCropPositioner\Migrators\MyEyesAreUpHere;
use WP_CLI;

class Migrate {

	protected const PROGRESS_DISPLAY_BATCH_SIZE = 20;

	/**
	 * Migrate attachments from a migrator to image-crop-positioner
	 *
	 * ## OPTIONS
	 *
	 * <migrator_slug>
	 * : Slug of a migrator
	 *
	 * [--per-page=<int>]
	 * : How many attachments are in a page. Use -1 for all attachments.
	 * ---
	 * default: -1
	 * ---
	 *
	 * [--page=<int>]
	 * : The page you want to process
	 * ---
	 * default: 1
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp image-crop-positioner migrate attachments my_eyes_are_up_here --per-page=100 --page=1
	 */
	public function attachments( array $args, array $assoc_args ): void {
		$migrator_slug = $args[0];
		$migrators     = ( new Migrators() )->get_migrators();
		if ( ! array_key_exists( $migrator_slug, $migrators ) ) {
			WP_CLI::error( sprintf( 'Migrator does not exist. Please select one of: %s', implode( ', ', array_keys( $migrators ) ) ) );
		}

		$per_page = (int) ( $assoc_args['per-page'] ?? -1 );
		$page     = (int) ( $assoc_args['page'] ?? 1 );

		WP_CLI::log( "Retrieving attachments (page: $page, per page: $per_page)" );

		$migrator    = new MyEyesAreUpHere();
		$wp_query    = $migrator->get_migratable_attachment_ids( $page, $per_page );
		$posts_count = $wp_query->post_count;

		/** @var int[] */
		$posts = $wp_query->posts;
		foreach ( $posts as $index => $post_id ) {
			WP_CLI::log( "Migrating attachment ID: $post_id" );
			$status = $migrator->migrate_attachment( $post_id );
			if ( $status === MyEyesAreUpHere::STATUS_DONE ) {
				WP_CLI::log( "Migrated attachment ID: $post_id" );
			} else {
				WP_CLI::log( "Skipped attachment ID: $post_id" );
			}

			$done = (int) $index + 1;
			if ( $done % self::PROGRESS_DISPLAY_BATCH_SIZE === 0 ) {
				WP_CLI::colorize( "%pMigrated $done of $posts_count attachments%n" );
			}
		}
		WP_CLI::success( "All $posts_count attachments have been migrated" );
	}
}
