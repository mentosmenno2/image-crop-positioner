<?php
	/**
	 * @var WP_Post
	 */
	$attachment = $template->get_arg( 'attachment' );
?>

<div class="image-crop-positioner-attachment-edit-fields" data-image-crop-positioner-module="editImage" data-attachment-id="<?php echo esc_attr( (string) $attachment->ID ); ?>">
	<div class="image-previews" >
		<p class="image-previews__text"><strong><?php esc_html_e( 'Image previews', 'image-crop-positioner' ); ?></strong></p>
		<div class="image-previews__images"></div>
	</div>

	<div class="face-detection">
		<button type="button" class="button add-faces"><?php esc_html_e( 'Detect faces', 'image-crop-positioner' ); ?></button>
		<button type="button" class="button remove-faces"><?php esc_html_e( 'Forget faces', 'image-crop-positioner' ); ?></button>
		<p><?php esc_html_e( "Please note this is basic face detection and won't find everything. Use hotspots to highlight any that were missed.", 'image-crop-positioner' ); ?></p>
	</div>

	<div class="hotspots-selection">
		<button type="button" class="button add-hotspots"><?php esc_html_e( 'Add hotspots', 'image-crop-positioner' ); ?></button>
		<button type="button" class="button remove-hotspots"><?php esc_html_e( 'Remove hotspots', 'image-crop-positioner' ); ?></button>
	</div>

	<div class="crop-preview"></div>
</div>
