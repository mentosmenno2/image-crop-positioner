<?php

namespace Mentosmenno2\ImageCropPositioner;

use Mentosmenno2\ImageCropPositioner\Admin\Settings\Cache\Fields\BreakEnabled;
use Mentosmenno2\ImageCropPositioner\Helpers\AttachmentMeta;

class Cache {

	public function register_hooks(): void {
		add_action( 'updated_post_meta', array( $this, 'store_updated_date' ), 10, 3 );
		add_filter( 'wp_get_attachment_image_src', array( $this, 'change_attachment_image_src' ), 11, 2 );
		add_filter( 'wp_calculate_image_srcset', array( $this, 'change_attachment_image_srcset' ), 11, 5 );
	}

	/**
	 * When attachment metadata is generated for an image, set the updated date.
	 *
	 * @param integer $meta_id
	 * @param integer $attachment_id
	 * @param string $meta_key
	 * @return void
	 */
	public function store_updated_date( $meta_id, $attachment_id, $meta_key ) {
		if ( $meta_key !== '_wp_attachment_metadata' ) {
			return;
		}

		$attachment_id = (int) $attachment_id;
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
	 * @return array|false
	 */
	public function change_attachment_image_src( $image, $attachment_id ) {
		if ( ! ( new BreakEnabled() )->get_value() ) {
			return $image;
		}

		$attachment_id = (int) $attachment_id;
		if ( ! is_array( $image ) || ! isset( $image[0] ) || ! $attachment_id ) {
			return $image;
		}

		$image[0] = $this->change_attachment_image_url( $image[0], $attachment_id );
		return $image;
	}

	/**
	 * Change the sourceset of the attachment
	 *
	 * @param array<mixed> $sources
	 * @param array<int,int> $size_array
	 * @param string $image_src
	 * @param array<string,mixed> $image_meta
	 * @param int $attachment_id
	 * @return array<mixed>
	 */
	public function change_attachment_image_srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
		if ( ! ( new BreakEnabled() )->get_value() ) {
			return $sources;
		}

		foreach ( $sources as &$source ) {
			$source['url'] = $this->change_attachment_image_url( $source['url'], (int) $attachment_id );
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
