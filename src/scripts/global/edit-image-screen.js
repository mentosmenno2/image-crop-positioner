( function( $, app ) {

	/**
	 * Handles the image edit screen.
	 * Cannot be initialized as a module, because WordPress replaces the instance everytime the window is re-opened.
	 */
	app.editImageScreen = function() {

		let config = {};
		let tempHotspots = [];

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

			getChildElement( '.button__edit-hotspots' ).on( 'click', function() { editHotspots(); } );
			getChildElement( '.button__discard-hotspots' ).on( 'click', function() { discardHotspots(); } );
			getChildElement( '.button__save-hotspots' ).on( 'click', function() { saveHotspots(); } );
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
			showHotspots( config.hotspots );
		}

		function hideSpots() {
			getChildElement( '.image-spots-preview__spots' ).find( '.spot' ).remove();
		}

		function showSpot( spot, type ) {
			const $newFaceElement = $( `<div class="spot spot__${type}" data-x="${spot.x}" data-y="${spot.y}" data-width="${spot.width}" data-height="${spot.height}"></div>` );
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
		 * All detections
		 * ##########
		 */

		function disableAllDetections() {
			getFaceDetectionMessage().empty();
			getHotspotSelectionMessage().empty();
			getDetectFacesButton().prop( 'disabled', true );
			getRemoveFacesButton().prop( 'disabled', true );
			getEditHotspotsButton().prop( 'disabled', true );
		}

		function enableAllDetections() {
			getDetectFacesButton().prop( 'disabled', false );
			getRemoveFacesButton().prop( 'disabled', false );
			getEditHotspotsButton().prop( 'disabled', false );
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
			disableAllDetections();
			getDetectFacesButton().append( getSpinnerHtml() );

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
					getSaveFacesButton().attr( 'data-faces', JSON.stringify( data.data.faces ) );
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
					enableAllDetections();
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
			enableAllDetections();
		}

		function saveFaces() {
			getSaveFacesButton().append( getSpinnerHtml() );
			getSaveFacesButton().prop( 'disabled', true );
			getDiscardFacesButton().prop( 'disabled', true );
			getFaceDetectionMessage().empty();

			$.ajax( {
				url : window.image_crop_positioner_options.ajax_url,
				data : {
					_ajax_nonce: window.image_crop_positioner_options.nonce,
					action: 'image_crop_positioner_save_faces',
					attachment_id: config.attachment_id,
					faces: JSON.parse( getSaveFacesButton().attr( 'data-faces' ) ),
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
					enableAllDetections();
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
			disableAllDetections();
			getRemoveFacesButton().append( getSpinnerHtml() );

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
					enableAllDetections();
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
				} );
		}

		/**
		 * ##########
		 * Hotspot selection
		 * ##########
		 */

		function showHotspots( hotspotsList ) {
			getChildElement( '.image-spots-preview__spots' ).find( '.spot__hotspot' ).remove();
			hotspotsList.forEach( hotspotItem => {
				showSpot( hotspotItem, 'hotspot' );
			} );
		}

		function editHotspots() {
			tempHotspots = JSON.parse( JSON.stringify( config.hotspots ) );
			hideSpots();
			showHotspots( tempHotspots );
			disableAllDetections();
			getHotspotSelectionMessage().html( getAdminNoticeHtml( 'Please click on an empty area of the image to add hotspots, or on a hotspot to delete it.', 'info' ) );
			getEditHotspotsButton().hide();
			getSaveHotspotsButton().show();
			getDiscardHotspotsButton().show();

			// Add event listener stuffs
			addEditHotspotsEventListeners();
		}

		function discardHotspots() {
			removeEditHotspotsEventListeners();
			tempHotspots = [];
			loadSpots();
			getEditHotspotsButton().show();
			getSaveHotspotsButton().hide();
			getDiscardHotspotsButton().hide();
			enableAllDetections();
		}

		function saveHotspots() {
			getSaveHotspotsButton().append( getSpinnerHtml() );
			getSaveHotspotsButton().prop( 'disabled', true );
			getDiscardHotspotsButton().prop( 'disabled', true );
			getHotspotSelectionMessage().empty();
			removeEditHotspotsEventListeners();

			$.ajax( {
				url : window.image_crop_positioner_options.ajax_url,
				data : {
					_ajax_nonce: window.image_crop_positioner_options.nonce,
					action: 'image_crop_positioner_save_hotspots',
					attachment_id: config.attachment_id,
					hotspots: tempHotspots,
				},
				method : 'POST',
				dataType: "json",
				timeout: 30000,
			} )
				.done( function( data ) {
					config.hotspots = data.data.hotspots;
					loadSpots();
					reloadImagePreviews();
					getSaveHotspotsButton().hide();
					getDiscardHotspotsButton().hide();
					getEditHotspotsButton().show();
					enableAllDetections();
					getHotspotSelectionMessage().html( getAdminNoticeHtml( 'Hotspots are saved.', 'success' ) );
				} )
				.fail( function( jqXHR ) {
					let errorMessage = 'Error';
					if ( typeof jqXHR.responseJSON.data[ 0 ].message !== 'undefined' ) {
						errorMessage = jqXHR.responseJSON.data[ 0 ].message;
					}
					getHotspotSelectionMessage().html( getAdminNoticeHtml( errorMessage, 'error' ) );
					addEditHotspotsEventListeners();
				} )
				.always( function() {
					removeSpinnerHtml( getSaveHotspotsButton() );
					getSaveHotspotsButton().prop( 'disabled', false );
					getDiscardHotspotsButton().prop( 'disabled', false );
				} );
		}

		function addEditHotspotsEventListeners() {
			getChildElement( '.image-spots-preview' ).addClass( 'image-spots-preview--clickable' );
			getChildElement( '.image-spots-preview' ).on( 'click', onEditHotspotClick );
		}

		function removeEditHotspotsEventListeners() {
			getChildElement( '.image-spots-preview' ).removeClass( 'image-spots-preview--clickable' );
			getChildElement( '.image-spots-preview' ).off( 'click', onEditHotspotClick );
		}

		function onEditHotspotClick( e ) {
			const $clickedElement = $( e.target );

			// Run if hotspot was clicked
			if ( $clickedElement.hasClass( 'spot__hotspot' ) ) {
				tempHotspots.forEach( ( tempHotspot, tempHotspotIndex ) => {
					if ( parseFloat( tempHotspot.x ) !== parseFloat( $clickedElement.attr( 'data-x' ) ) ) { return; }
					if ( parseFloat( tempHotspot.y ) !== parseFloat( $clickedElement.attr( 'data-y' ) ) ) { return; }
					if ( parseFloat( tempHotspot.width ) !== parseFloat( $clickedElement.attr( 'data-width' ) ) ) { return; }
					if ( parseFloat( tempHotspot.height ) !== parseFloat( $clickedElement.attr( 'data-height' ) ) ) { return; }
					tempHotspots.splice( tempHotspotIndex, 1 );
					$clickedElement.remove();
				} );
				return;
			}

			// Image was clicked
			const hotspotDisplayWidth = $( this ).width() / 10;
			const hotspotDisplayX = e.offsetX - ( hotspotDisplayWidth / 2 );
			const hotspotDisplayY = e.offsetY - ( hotspotDisplayWidth / 2 );
			const ratio = config.attachment_metadata.width / $( this ).width();

			const hotspotRealWidth = hotspotDisplayWidth * ratio;
			const hotspotRealX = hotspotDisplayX * ratio;
			const hotspotRealY = hotspotDisplayY * ratio;

			tempHotspots.push( {
				x: hotspotRealX,
				y: hotspotRealY,
				width: hotspotRealWidth,
				height: hotspotRealWidth
			} );
			showHotspots( tempHotspots );
		}

		/**
		 * ##########
		 * Element getters
		 * ##########
		 */

		// Global
		function getRootElement() {
			return $( '.image-crop-positioner-attachment-edit-fields' );
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

		// Hotspot selection
		function getEditHotspotsButton() {
			return getChildElement( '.button__edit-hotspots' );
		}

		function getSaveHotspotsButton() {
			return getChildElement( '.button__save-hotspots' );
		}

		function getDiscardHotspotsButton() {
			return getChildElement( '.button__discard-hotspots' );
		}

		function getHotspotSelectionMessage() {
			return getChildElement( '.hotspot-selection__message' );
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
