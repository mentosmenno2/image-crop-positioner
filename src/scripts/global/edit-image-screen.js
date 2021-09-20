( function( $, app ) {

	/**
	 * Handles the image edit screen.
	 * Cannot be initialized as a module, because WordPress replaces the instance everytime the window is re-opened.
	 */
	app.editImageScreen = function() {

		let config = {};

		/**
		 * ##########
		 * Initialization
		 * ##########
		 */

		function initialize() {
			$( document ).on( 'imageCropPositionerEditFieldsReady', function() { prepare(); } );
		}

		function prepare() {
			getRootElement().removeData();
			config = getRootElement().data( 'config' );
			loadSpots();
			addEventListeners();
			reloadImagePreviews();
		}

		function addEventListeners() {
			getChildElement( '.button__detect-faces' ).on( 'click', function() { detectFaces(); } );
			getChildElement( '.button__discard-faces' ).on( 'click', function() { discardFaces(); } );
			getChildElement( '.button__save-faces' ).on( 'click', function() { saveFaces(); } );
			getChildElement( '.button__remove-faces' ).on( 'click', function() { removeFaces(); } );
		}

		/**
		 * ##########
		 * Image size previews
		 * ##########
		 */

		function reloadImagePreviews() {
			getChildElement( '.image-previews__images' ).html( getSpinnerHtml() );

			$.ajax( {
				url : window.image_crop_positioner_options.ajax_url,
				data : {
					_ajax_nonce: window.image_crop_positioner_options.nonce,
					action: 'image_crop_positioner_image_previews',
					attachment_id: config.attachment_id
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
					let errorMessage = 'Error';
					if ( typeof jqXHR.responseJSON.data[ 0 ].message !== 'undefined' ) {
						errorMessage = jqXHR.responseJSON.data[ 0 ].message;
					}
					getChildElement( '.image-previews__images' ).html( getAdminNoticeHtml( errorMessage, 'error' ) );
				} );
		}

		/**
		 * ##########
		 * Spots preview
		 * ##########
		 */

		function loadSpots() {
			hideSpots();
			showFaces( config.faces );
		}

		function hideSpots() {
			getChildElement( '.image-spots-preview__spots' ).find( '.spot' ).remove();
		}

		function showSpot( spot, type ) {
			const $newFaceElement = $( `<div class="spot spot__${type}" ></div>` );
			const leftPercent = spot.x / config.attachment_metadata.width * 100;
			const topPercent = spot.y / config.attachment_metadata.height * 100;
			const widthPercent = spot.width / config.attachment_metadata.width * 100;
			const heightPercent = spot.height / config.attachment_metadata.height * 100;
			$newFaceElement.css( {
				"top": `${topPercent}%`,
				"left": `${leftPercent}%`,
				"width": `${widthPercent}%`,
				"height": `${heightPercent}%`
			} );
			getChildElement( '.image-spots-preview__spots' ).append( $newFaceElement );
		}

		/**
		 * ##########
		 * Face detection
		 * ##########
		 */

		function showFaces( faceList ) {
			getChildElement( '.image-spots-preview__spots' ).find( '.spot__face' ).remove();
			faceList.forEach( faceItem => {
				showSpot( faceItem, 'face' );
			} );
		}

		function detectFaces() {
			getDetectFacesButton().append( getSpinnerHtml() );
			getDetectFacesButton().prop( 'disabled', true );
			getFaceDetectionMessage().empty();

			$.ajax( {
				url : window.image_crop_positioner_options.ajax_url,
				data : {
					_ajax_nonce: window.image_crop_positioner_options.nonce,
					action: 'image_crop_positioner_face_detection',
					attachment_id: config.attachment_id
				},
				method : 'POST',
				dataType: "json",
				timeout: 30000,
			} )
				.done( function( data ) {
					hideSpots();
					getSaveFacesButton().data( 'faces', data.data.faces );
					showFaces( data.data.faces );
					if ( data.data.faces.length > 0 ) {
						getFaceDetectionMessage().html( getAdminNoticeHtml( 'Please confirm that the found face is correct.', 'info' ) );
						getDetectFacesButton().hide();
						getSaveFacesButton().show();
						getDiscardFacesButton().show();
					} else {
						getFaceDetectionMessage().html( getAdminNoticeHtml( 'No faces found.', 'warning' ) );
					}
				} )
				.fail( function( jqXHR ) {
					let errorMessage = 'Error';
					if ( typeof jqXHR.responseJSON.data[ 0 ].message !== 'undefined' ) {
						errorMessage = jqXHR.responseJSON.data[ 0 ].message;
					}
					getFaceDetectionMessage().html( getAdminNoticeHtml( errorMessage, 'error' ) );
				} )
				.always( function() {
					removeSpinnerHtml( getDetectFacesButton() );
					getDetectFacesButton().prop( 'disabled', false );
				} );

		}

		function discardFaces() {
			loadSpots();
			getDetectFacesButton().show();
			getDiscardFacesButton().hide();
			getSaveFacesButton().hide();
			getFaceDetectionMessage().empty();
		}

		function saveFaces() {
			getSaveFacesButton().append( getSpinnerHtml() );
			getSaveFacesButton().prop( 'disabled', true );
			getDiscardFacesButton().prop( 'disabled', true );
			getFaceDetectionMessage().empty();
			getSaveFacesButton().removeData( 'faces' );

			$.ajax( {
				url : window.image_crop_positioner_options.ajax_url,
				data : {
					_ajax_nonce: window.image_crop_positioner_options.nonce,
					action: 'image_crop_positioner_save_faces',
					attachment_id: config.attachment_id,
					faces: getSaveFacesButton().data( 'faces' ),
				},
				method : 'POST',
				dataType: "json",
				timeout: 30000,
			} )
				.done( function( data ) {
					config.faces = data.data.faces;
					loadSpots();
					reloadImagePreviews();
					getSaveFacesButton().hide();
					getDiscardFacesButton().hide();
					getRemoveFacesButton().show();
					getFaceDetectionMessage().html( getAdminNoticeHtml( 'Faces are saved.', 'success' ) );
				} )
				.fail( function( jqXHR ) {
					let errorMessage = 'Error';
					if ( typeof jqXHR.responseJSON.data[ 0 ].message !== 'undefined' ) {
						errorMessage = jqXHR.responseJSON.data[ 0 ].message;
					}
					getFaceDetectionMessage().html( getAdminNoticeHtml( errorMessage, 'error' ) );
				} )
				.always( function() {
					removeSpinnerHtml( getSaveFacesButton() );
					getSaveFacesButton().prop( 'disabled', false );
					getDiscardFacesButton().prop( 'disabled', false );
				} );
		}

		function removeFaces() {
			getRemoveFacesButton().append( getSpinnerHtml() );
			getRemoveFacesButton().prop( 'disabled', true );
			getFaceDetectionMessage().empty();

			$.ajax( {
				url : window.image_crop_positioner_options.ajax_url,
				data : {
					_ajax_nonce: window.image_crop_positioner_options.nonce,
					action: 'image_crop_positioner_remove_faces',
					attachment_id: config.attachment_id,
				},
				method : 'POST',
				dataType: "json",
				timeout: 30000,
			} )
				.done( function( data ) {
					config.faces = data.data.faces;
					loadSpots();
					reloadImagePreviews();
					getRemoveFacesButton().hide();
					getDetectFacesButton().show();
					getFaceDetectionMessage().html( getAdminNoticeHtml( 'Faces are removed.', 'success' ) );
				} )
				.fail( function( jqXHR ) {
					let errorMessage = 'Error';
					if ( typeof jqXHR.responseJSON.data[ 0 ].message !== 'undefined' ) {
						errorMessage = jqXHR.responseJSON.data[ 0 ].message;
					}
					getFaceDetectionMessage().html( getAdminNoticeHtml( errorMessage, 'error' ) );
				} )
				.always( function() {
					removeSpinnerHtml( getRemoveFacesButton() );
					getRemoveFacesButton().prop( 'disabled', false );
					getRemoveFacesButton().prop( 'disabled', false );
				} );
		}

		/**
		 * ##########
		 * Element getters
		 * ##########
		 */

		// Global
		function getRootElement() {
			return $( document ).find( '.image-crop-positioner-attachment-edit-fields' );
		}

		function getChildElement( selector ) {
			return getRootElement().find( selector );
		}

		// Face detection
		function getDetectFacesButton() {
			return getChildElement( '.button__detect-faces' );
		}

		function getSaveFacesButton() {
			return getChildElement( '.button__save-faces' );
		}

		function getDiscardFacesButton() {
			return getChildElement( '.button__discard-faces' );
		}

		function getRemoveFacesButton() {
			return getChildElement( '.button__remove-faces' );
		}

		function getFaceDetectionMessage() {
			return getChildElement( '.face-detection__message' );
		}

		/**
		 * ##########
		 * HTML generators
		 * ##########
		 */
		function getSpinnerHtml() {
			return '<div class="spinner__wrapper"><div class="spinner is-active"></div></div>';
		}

		function getAdminNoticeHtml( message, type = 'info', isDismissable = false ) {
			const $newNoticeElement = $( `<div class="notice notice-${type} inline" >${message}</div>` );
			if ( isDismissable ) {
				$newNoticeElement.addClass( 'is-dismissable' );
			}
			return $newNoticeElement;
		}

		function removeSpinnerHtml( $element ) {
			return $element.find( '.spinner__wrapper' ).remove();
		}

		initialize();
	};

}( jQuery, window.image_crop_positioner = window.image_crop_positioner || {} ) );
