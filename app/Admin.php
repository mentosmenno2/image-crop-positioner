<?php

namespace Mentosmenno2\ImageCropPositioner;

use WP_Post;

class Admin {

	public function register_hooks(): void {
		add_filter( 'attachment_fields_to_edit', array( $this, 'edit_fields' ), 10, 2 );
	}

	/**
	 * Add custom fields to attachment edit screen
	 */
	public function edit_fields( array $form_fields, WP_Post $attachment ): array {
		if ( ! wp_attachment_is_image( $attachment->ID ) ) {
			return $form_fields;
		}

		$html = ( new Templates() )->get_template(
			'admin/attachment-edit-fields', array(
				'attachment' => $attachment,
			)
		);

		$form_fields['image_crop_positioner'] = array(
			'label' => __( 'Image crop position', 'image-crop-positioner' ),
			'input' => 'html',
			'html'  => $html,
		);

		return $form_fields;
	}

}
