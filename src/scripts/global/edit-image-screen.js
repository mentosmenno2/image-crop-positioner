( function( $, app ) {

	/**
	 * Handles the image edit screen.
	 * Cannot be initialized as a module, because WordPress replaces the instance everytime the window is re-opened.
	 */
	app.editImageScreen = function() {
		function initialize() {
			if ( wp.media ) {
				wp.media.view.Modal.prototype.on( 'open', function() { prepare(); } );
			}
		}

		function prepare() {
			addEventListeners();
			reloadImagePreviews();
		}

		function getRootElement() {
			return $( '.image-crop-positioner-attachment-edit-fields' );
		}

		function getChildElement( selector ) {
			return getRootElement().find( selector );
		}

		function getAttachmentId() {
			return parseInt( getRootElement().attr( 'data-attachment-id' ) );
		}

		// Event listeners need to be on the document, to prevent
		function addEventListeners() {
			getChildElement( '.add-faces' ).on( 'click', function() { addFaces(); } );
		}

		function addFaces() {
			reloadImagePreviews();
		}

		function reloadImagePreviews() {
			getChildElement( '.image-previews__images' ).html( '<div class="spinner is-active"></div>' );
			$.ajax( {
				url : window.image_crop_positioner_options.ajax_url,
				data : {
					_ajax_nonce: window.image_crop_positioner_options.nonce,
					action: 'image_crop_positioner_image_previews',
					attachment_id: getAttachmentId()
				},
				method : 'POST',
				dataType: "json",
				timeout: 30000,
			} )
				.done( function( data ) {
					getChildElement( '.image-previews__images' ).empty();
					data.data.forEach( imageSize => {
						getChildElement( '.image-previews__images' ).append( imageSize.html );
					} );
				} )
				.fail( function( jqXHR ) {
					window.alert( jqXHR.responseJSON.data[ 0 ].message ); // eslint-disable-line no-alert
				} );
		}

		initialize();
	};

}( jQuery, window.image_crop_positioner = window.image_crop_positioner || {} ) );
