<?php

namespace Mentosmenno2\ImageCropPositioner;

use Mentosmenno2\ImageCropPositioner\Helpers\AttachmentMeta;

class Cache {

	public function register_hooks(): void {
		add_action( 'updated_post_meta', array( $this, 'store_updated_date' ), 10, 3 );
		add_filter( 'wp_get_attachment_image_src', array( $this, 'change_attachment_image_src' ), 11, 2 );
		add_filter( 'wp_calculate_image_srcset', array( $this, 'change_attachment_image_srcset' ), 11, 5 );
	}

	public function store_updated_date( int $meta_id, int $attachment_id, string $meta_key ): void {
		if ( $meta_key !== '_wp_attachment_metadata' ) {
			return;
		}

		if ( ! wp_attachment_is_image( $attachment_id ) ) {
			return;
		}

		( new AttachmentMeta() )->set_updated_timestamp( $attachment_id, time() );
	}

	/**
	 * Change the source of the attachment
	 *
	 * @param array|false $image
	 * @param int|string $attachment_id
	 * @param string $size
	 * @return array|false
	 */
	public function change_attachment_image_src( $image, $attachment_id ) {
		$attachment_id = (int) $attachment_id;
		if ( ! is_array( $image ) || ! isset( $image[0] ) || ! $attachment_id ) {
			return $image;
		}

		$image[0] = $this->change_attachment_image_url( $image[0], $attachment_id );
		return $image;
	}

	/**
	 * Change the sourceset of the attachment
	 */
	public function change_attachment_image_srcset( array $sources, array $size_array, string $image_src, array $image_meta, int $attachment_id ): array {
		foreach ( $sources as &$source ) {
			$source['url'] = $this->change_attachment_image_url( $source['url'], $attachment_id );
		}

		return $sources;
	}

	/**
	 * Add the updated timestamp to the url of an image
	 */
	protected function change_attachment_image_url( string $url, int $attachment_id ): string {
		$updated_date = ( new AttachmentMeta() )->get_updated_timestamp( $attachment_id );
		if ( ! $updated_date ) {
			return $url;
		}

		$url = add_query_arg( 'image-crop-positioner-ts', $updated_date, $url );
		return $url;
	}
}
