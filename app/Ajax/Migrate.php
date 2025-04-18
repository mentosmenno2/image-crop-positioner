<?php

namespace Mentosmenno2\ImageCropPositioner\Ajax;

use Mentosmenno2\ImageCropPositioner\Migrators\BaseMigrator;
use Mentosmenno2\ImageCropPositioner\Migrators\Migrators;
use WP_Error;

class Migrate extends BaseAjaxCall {

	public function register_hooks(): void {
		add_action( 'wp_ajax_image_crop_positioner_migrate', array( $this, 'handle_request' ) );
	}

	/**
	 * Handle the request
	 */
	public function handle_request(): void {
		$this->validate_nonce();

		$migrator = $this->get_and_validate_migrator();
		$page     = (int) ( filter_input( INPUT_POST, 'page', FILTER_VALIDATE_INT ) ?: 1 );
		$per_page = (int) ( filter_input( INPUT_POST, 'per_page', FILTER_VALIDATE_INT ) ?: 10 );
		$this->migrate( $migrator, $page, $per_page );
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
		}

		return $migrators[ $migrator_slug ];
	}

	protected function migrate( BaseMigrator $migrator, int $page, int $per_page ): void {
		$wp_query = $migrator->get_migratable_attachment_ids( $page, $per_page );
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
				'total_processed_posts' => ( $per_page * $page ) - $per_page + $wp_query->post_count,
				'total_posts'           => $wp_query->found_posts,
				'current_page'          => $page,
				'total_pages'           => $wp_query->max_num_pages,
			),
		);
		wp_send_json_success( $data, 200 );
	}
}
