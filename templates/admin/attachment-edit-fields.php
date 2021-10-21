<?php

/**
 * @var WP_Post
 */

use Mentosmenno2\ImageCropPositioner\Helpers\AttachmentMeta;

$attachment = $template->get_arg( 'attachment' );

if ( ! wp_attachment_is_image( $attachment ) ) {
	return;
}

$attachmentmeta = new AttachmentMeta();
$faces          = $attachmentmeta->get_faces( $attachment->ID );
$hotspots       = $attachmentmeta->get_hotspots( $attachment->ID );

if ( ! function_exists( 'display_none' ) ) {
	/**
	 * @param mixed $display_none
	 * @param mixed $compare
	 * @param bool $echo
	 * @return void|string
	 */
	function display_none( $display_none, $compare = true, $echo = true ) {
		__checked_selected_helper( $display_none, $compare, $echo, 'style="display: none;"' );
	}
}

$data_config = wp_json_encode(
	array(
		'attachment_id'       => $attachment->ID,
		'attachment_metadata' => wp_get_attachment_metadata( $attachment->ID ) ?: array(),
		'faces'               => $faces,
		'hotspots'            => $hotspots,
	)
) ?: '';

?>

<div class="image-crop-positioner-attachment-edit-fields" data-config="<?php echo esc_attr( $data_config ); ?>">
	<!-- Sizes previews -->
	<div class="image-previews" >
		<p class="image-previews__text"><strong><?php esc_html_e( 'Image size previews', 'image-crop-positioner' ); ?></strong></p>
		<div class="image-previews__images"></div>
	</div>

	<!-- Spots preview -->
	<p><strong><?php esc_html_e( 'The important spots on the image', 'image-crop-positioner' ); ?></strong></p>
	<div class="image-spots-preview" >
		<?php
		echo wp_get_attachment_image(
			$attachment->ID, 'full', false, array(
				'class' => 'image-spots-preview__image',
				'id'    => 'image-crop-positioner-image-spots-preview-image',
			)
		);
		?>
		<div class="image-spots-preview__spots"></div>
	</div>

	<!-- Face detection buttons -->
	<div class="face-detection">
		<p><strong><?php esc_html_e( 'Face detection', 'image-crop-positioner' ); ?></strong></p>
		<p><?php esc_html_e( "Please note this is very basic face detection and won't find everything. Use hotspots to highlight any that were missed.", 'image-crop-positioner' ); ?></p>
		<button type="button" class="button button__detect-faces-php" <?php display_none( ! empty( $faces ) ); ?>><?php esc_html_e( 'Detect faces via PHP', 'image-crop-positioner' ); ?></button>
		<button type="button" class="button button__detect-faces-js" <?php display_none( ! empty( $faces ) ); ?>><?php esc_html_e( 'Detect faces via JavaScript', 'image-crop-positioner' ); ?></button>
		<button type="button" class="button button__save-faces" <?php display_none( true ); ?>><?php esc_html_e( 'Save faces', 'image-crop-positioner' ); ?></button>
		<button type="button" class="button button__discard-faces" <?php display_none( true ); ?>><?php esc_html_e( 'Discard faces', 'image-crop-positioner' ); ?></button>
		<button type="button" class="button button__remove-faces" <?php display_none( empty( $faces ) ); ?>><?php esc_html_e( 'Remove faces', 'image-crop-positioner' ); ?></button>
		<span class="face-detection__message" ></span>
	</div>

	<!-- Hotspot buttons -->
	<div class="hotspots-selection">
		<p><strong><?php esc_html_e( 'Hotspot selection', 'image-crop-positioner' ); ?></strong></p>
		<button type="button" class="button button__edit-hotspots"><?php esc_html_e( 'Edit hotspots', 'image-crop-positioner' ); ?></button>
		<button type="button" class="button button__save-hotspots" <?php display_none( true ); ?>><?php esc_html_e( 'Save hotspots', 'image-crop-positioner' ); ?></button>
		<button type="button" class="button button__discard-hotspots" <?php display_none( true ); ?>><?php esc_html_e( 'Discard changes', 'image-crop-positioner' ); ?></button>
		<span class="hotspot-selection__message" ></span>
	</div>

	<div class="crop-preview"></div>
</div>

<script>
	jQuery( document ).trigger( 'imageCropPositionerEditFieldsReady' );
</script>
