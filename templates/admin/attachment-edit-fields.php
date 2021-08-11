<div class="image-crop-positioner-attachment-edit-fields">
	<div>
		<p><strong><?php esc_html_e( 'Image previews', 'image-crop-positioner' ); ?></strong></p>
		<div class="image-previews"></div>
	</div>

	<div>
		<button type="button" class="button add-faces"><?php esc_html_e( 'Detect faces', 'image-crop-positioner' ); ?></button>
		<button type="button" class="button remove-faces"><?php esc_html_e( 'Forget faces', 'image-crop-positioner' ); ?></button>
		<p><?php esc_html_e( "Please note this is basic face detection and won't find everything. Use hotspots to highlight any that were missed.", 'image-crop-positioner' ); ?></p>
	</div>

	<div>
		<button type="button" class="button add-hotspots"><?php esc_html_e( 'Add hotspots', 'image-crop-positioner' ); ?></button>
		<button type="button" class="button remove-hotspots"><?php esc_html_e( 'Remove hotspots', 'image-crop-positioner' ); ?></button>
	</div>

	<div class="crop-preview"></div>
</div>
