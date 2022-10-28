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
	 * [--batch-size=<int>]
	 * : How many attachments are processed in a batch. Use -1 for all attachments.
	 * ---
	 * default: 100
	 * ---
	 *
	 * [--start-batch-number=<int>]
	 * : The batch number you want to start processing from
	 * ---
	 * default: 1
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp image-crop-positioner migrate attachments my_eyes_are_up_here --batch-size=100 --start-batch-number=1
	 */
	public function attachments( array $args, array $assoc_args ): void {
		$migrator_slug = $args[0];
		$migrators     = ( new Migrators() )->get_migrators();
		if ( ! array_key_exists( $migrator_slug, $migrators ) ) {
			WP_CLI::error( sprintf( 'Migrator does not exist. Please select one of: %s', implode( ', ', array_keys( $migrators ) ) ) );
		}

		$migrator     = $migrators[ $migrator_slug ];
		$batch_size   = (int) ( $assoc_args['batch-size'] ?? 100 );
		$batch_number = (int) ( $assoc_args['start-batch-number'] ?? 1 );
		$posts_count  = 0;
		$total_count  = 0;
		$first_run    = true;

		while ( $first_run || $posts_count ) {
			$first_run = false;

			WP_CLI::log( "Getting new batch (batch number: $batch_number, batch size: $batch_size)" );
			$wp_query    = $migrator->get_migratable_attachment_ids( $batch_number, $batch_size );
			$posts_count = $wp_query->post_count;
			if ( $posts_count === 0 ) {
				WP_CLI::log( 'No more items remaining' );
				continue;
			}

			$total_count += $posts_count;
			WP_CLI::log( "Processing batch (batch number: $batch_number, batch size: $batch_size, items in batch: $posts_count)" );

			/** @var int[] */
			$posts = $wp_query->posts;
			foreach ( $posts as $post_id ) {
				WP_CLI::log( "Migrating attachment ID: $post_id" );
				$status = $migrator->migrate_attachment( $post_id );
				if ( $status === MyEyesAreUpHere::STATUS_DONE ) {
					WP_CLI::log( "Migrated attachment ID: $post_id" );
				} else {
					WP_CLI::log( "Skipped attachment ID: $post_id" );
				}
			}

			WP_CLI::log( "Processed batch (batch number: $batch_number, batch size: $batch_size, items in batch: $posts_count, total items processed: $total_count)" );

			$batch_number++;
		}

		WP_CLI::success( "All $total_count attachments have been migrated" );
	}
}
