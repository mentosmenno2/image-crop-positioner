<?php

namespace Mentosmenno2\ImageCropPositioner\Ajax;

use Mentosmenno2\ImageCropPositioner\Migrators\BaseMigrator;
use Mentosmenno2\ImageCropPositioner\Migrators\Migrators;
use WP_Error;

class Migrate extends BaseAjaxCall {

	protected const BATCH_SIZE = 50;

	public function register_hooks(): void {
		add_action( 'wp_ajax_image_crop_positioner_migrate', array( $this, 'handle_request' ) );
	}

	/**
	 * Handle the request
	 */
	public function handle_request(): void {
		$this->validate_nonce();

		$migrator = $this->get_and_validate_migrator();
		$page     = (int) filter_input( INPUT_POST, 'page', FILTER_VALIDATE_INT ) ?: 1;
		$this->migrate( $migrator, $page );
	}

	protected function get_and_validate_migrator(): BaseMigrator {
		$migrator_slug = (string) filter_input( INPUT_POST, 'migrator' );
		$migrators     = ( new Migrators() )->get_migrators();
		if ( ! array_key_exists( $migrator_slug, $migrators ) ) {
			$error = new WP_Error(
				403, __( 'Invalid validator slug', 'image-crop-positioner' ), array(
					'status' => 403,
				)
			);
			wp_send_json_error( $error, 403 );
			exit;
		}

		return $migrators[ $migrator_slug ];
	}

	protected function migrate( BaseMigrator $migrator, int $page ): void {
		$wp_query = $migrator->get_migratable_attachment_ids( $page, self::BATCH_SIZE );
		$log      = array();

		/** @var int[] */
		$posts = $wp_query->posts;
		foreach ( $posts as $post_id ) {
			$status      = $migrator->migrate_attachment( $post_id );
			$status_text = $status === BaseMigrator::STATUS_DONE ? __( 'Migrated', 'image-crop-positioner' ) : __( 'Skipped', 'image-crop-positioner' );
			$log[]       = array(
				'attachment_id' => $post_id,
				'status'        => $status_text,
			);
		}

		$data = array(
			'log'        => $log,
			'pagination' => array(
				'now_processed_posts'   => $wp_query->post_count,
				'total_processed_posts' => ( self::BATCH_SIZE * $page ),
				'total_posts'           => $wp_query->found_posts,
				'current_page'          => $page,
				'total_pages'           => $wp_query->max_num_pages,
			),
		);
		wp_send_json_success( $data, 200 );
		exit;
	}
}
