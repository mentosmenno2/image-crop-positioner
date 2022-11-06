<?php

/**
 * @var WP_Post
 */

use Mentosmenno2\ImageCropPositioner\Admin\Settings\JSFacesDetection\Fields\MinAccuracy as JSFacesDetectionMinAccuracy;
use Mentosmenno2\ImageCropPositioner\Admin\Settings\PHPFaceDetection\Fields\Enabled as PHPFaceDetectionEnabled;
use Mentosmenno2\ImageCropPositioner\Admin\Settings\JSFacesDetection\Fields\Enabled as JSFacesDetectionEnabled;
use Mentosmenno2\ImageCropPositioner\Admin\Settings\HotspotsSelection\Fields\Enabled as HotspotsSelectionEnabled;
use Mentosmenno2\ImageCropPositioner\Helpers\AttachmentMeta;
use Mentosmenno2\ImageCropPositioner\Templates;

$attachment = $template->get_arg( 'attachment' );

if ( ! wp_attachment_is_image( $attachment ) ) {
	return;
}

$attachmentmeta      = new AttachmentMeta();
$faces               = $attachmentmeta->get_faces( $attachment->ID );
$hotspots            = $attachmentmeta->get_hotspots( $attachment->ID );
$image_src           = wp_get_attachment_image_src( $attachment->ID, 'full' )[0] ?? '';
$attachment_metadata = wp_get_attachment_metadata( $attachment->ID ) ?: array();
$is_external         = strpos( $image_src, home_url() ) !== 0;

$data_config = wp_json_encode(
	array(
		'attachment_id'       => $attachment->ID,
		'image_src'           => $image_src,
		'attachment_metadata' => $attachment_metadata,
		'faces'               => $faces,
		'hotspots'            => $hotspots,
		'is_external'         => $is_external,
		'js_faces_detection'  => array(
			'min_accuracy' => ( new JSFacesDetectionMinAccuracy() )->get_value(),
		),
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
		<?php ( new Templates() )->echo_template( 'partials/spinner' ); ?>
		<img
			class="image-spots-preview__image"
			id="image-crop-positioner-image-spots-preview-image"
			src="data:"
			loading="lazy"
			width="<?php echo esc_attr( (string) ( $attachment_metadata['width'] ?? '' ) ); ?>"
			height="<?php echo esc_attr( (string) ( $attachment_metadata['height'] ?? '' ) ); ?>"
		>
		<div class="image-spots-preview__spots"></div>
	</div>

	<!-- Face detection buttons -->
	<?php
	$php_face_detection_enabled = ( new PHPFaceDetectionEnabled() )->get_value();
	$js_faces_detection_enabled = ( new JSFacesDetectionEnabled() )->get_value();
	if ( $php_face_detection_enabled || $js_faces_detection_enabled ) {
		?>
		<div class="face-detection">
			<p><strong><?php esc_html_e( 'Face detection', 'image-crop-positioner' ); ?></strong></p>
			<p><?php esc_html_e( "Please note this is very basic face detection and won't find everything. Use hotspots to highlight any that were missed.", 'image-crop-positioner' ); ?></p>
			<?php if ( $php_face_detection_enabled ) { ?>
				<button type="button" class="button button__detect-faces-php" <?php ( new Templates() )->display_none( ! empty( $faces ) ); ?>><?php esc_html_e( 'Detect face via PHP', 'image-crop-positioner' ); ?></button>
			<?php } ?>
			<?php if ( $js_faces_detection_enabled ) { ?>
				<button type="button" class="button button__detect-faces-js" disabled="disabled" <?php ( new Templates() )->display_none( ! empty( $faces ) ); ?>>
					<?php esc_html_e( 'Detect faces via JavaScript', 'image-crop-positioner' ); ?>
					<?php ( new Templates() )->echo_template( 'partials/spinner' ); ?>
				</button>
			<?php } ?>
			<button type="button" class="button button__save-faces" <?php ( new Templates() )->display_none( true ); ?>><?php esc_html_e( 'Save faces', 'image-crop-positioner' ); ?></button>
			<button type="button" class="button button__discard-faces" <?php ( new Templates() )->display_none( true ); ?>><?php esc_html_e( 'Discard faces', 'image-crop-positioner' ); ?></button>
			<button type="button" class="button button__remove-faces" <?php ( new Templates() )->display_none( empty( $faces ) ); ?>><?php esc_html_e( 'Remove faces', 'image-crop-positioner' ); ?></button>
			<span class="face-detection__message" ></span>
		</div>
	<?php } ?>

	<!-- Hotspot buttons -->
	<?php if ( ( new HotspotsSelectionEnabled() )->get_value() ) { ?>
	<div class="hotspots-selection">
		<p><strong><?php esc_html_e( 'Hotspot selection', 'image-crop-positioner' ); ?></strong></p>
		<button type="button" class="button button__edit-hotspots"><?php esc_html_e( 'Edit hotspots', 'image-crop-positioner' ); ?></button>
		<button type="button" class="button button__save-hotspots" <?php ( new Templates() )->display_none( true ); ?>><?php esc_html_e( 'Save hotspots', 'image-crop-positioner' ); ?></button>
		<button type="button" class="button button__discard-hotspots" <?php ( new Templates() )->display_none( true ); ?>><?php esc_html_e( 'Discard changes', 'image-crop-positioner' ); ?></button>
		<span class="hotspot-selection__message" ></span>
	</div>
	<?php } ?>

	<div class="crop-preview"></div>
</div>

<script>
	jQuery( document ).trigger( 'imageCropPositionerEditFieldsReady' );
</script>
