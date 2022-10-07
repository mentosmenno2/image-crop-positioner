import devTools from "devtools-detect";
import AdminNoticeHelper from "../helpers/admin-notice";
import SpinnerHelper from "../helpers/spinner";

( function( $, app ) {

	/**
	 * Handles the image edit screen.
	 * Cannot be initialized as a module, because WordPress replaces the instance everytime the window is re-opened.
	 */
	app.editImageScreen = function() {

		const spinnerHelper = new SpinnerHelper();
		const adminNoticeHelper = new AdminNoticeHelper();

		let config = {};
		let tempHotspots = [];

		/**
		 * ##########
		 * Initialization
		 * ##########
		 */

		function initialize() {
			const $rootElement = getRootElement();

			// If element already exists, prepare directly
			if ( $rootElement.length > 0 ) {
				prepare();
			}

			// Prepare after element is ready
			$( document ).on( 'imageCropPositionerEditFieldsReady', function() { prepare(); } );
		}

		function prepare() {
			getRootElement().removeData();
			config = getRootElement().data( 'config' );
			loadSpots();
			addEventListeners();
			loadPreviewImage();
			reloadImagePreviews();
		}

		function addEventListeners() {
			getDetectFacesPhpButton().on( 'click', function() { detectFacesPhp(); } );
			getDetectFacesJsButton().on( 'click', function() { detectFacesJs(); } );
			getDiscardFacesButton().on( 'click', function() { discardFaces(); } );
			getSaveFacesButton().on( 'click', function() { saveFaces(); } );
			getRemoveFacesButton().on( 'click', function() { removeFaces(); } );

			getEditHotspotsButton().on( 'click', function() { editHotspots(); } );
			getDiscardHotspotsButton().on( 'click', function() { discardHotspots(); } );
			getSaveHotspotsButton().on( 'click', function() { saveHotspots(); } );

			getPreviewImage().on( 'load', function() { previewImageLoaded(); } );
		}

		function previewImageLoaded() {
			getDetectFacesJsButton().attr( 'disabled', false );
			spinnerHelper.removeFromElement( getChildElement( '.image-spots-preview' ) );
			spinnerHelper.removeFromElement( getDetectFacesJsButton() );
		}

		function loadPreviewImage() {
			getPreviewImage().attr( 'src', config.image_src );
		}

		/**
		 * ##########
		 * Image size previews
		 * ##########
		 */

		function reloadImagePreviews() {
			spinnerHelper.setToElementHtml( getChildElement( '.image-previews__images' ) );

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
					if ( typeof jqXHR.responseJSON !== 'undefined' && typeof jqXHR.responseJSON.data[ 0 ].message !== 'undefined' ) {
						errorMessage = jqXHR.responseJSON.data[ 0 ].message;
					}
					adminNoticeHelper.setToElementHtml( getChildElement( '.image-previews__images' ), errorMessage, 'error' );
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
			adminNoticeHelper.removeFromElement( getFaceDetectionMessage() );
			adminNoticeHelper.removeFromElement( getHotspotSelectionMessage() );
			getDetectFacesPhpButton().prop( 'disabled', true );
			getDetectFacesJsButton().prop( 'disabled', true );
			getRemoveFacesButton().prop( 'disabled', true );
			getEditHotspotsButton().prop( 'disabled', true );
		}

		function enableAllDetections() {
			getDetectFacesPhpButton().prop( 'disabled', false );
			getDetectFacesJsButton().prop( 'disabled', false );
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

		function detectFacesPhp() {
			disableAllDetections();
			spinnerHelper.appendToElement( getDetectFacesPhpButton() );

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
						adminNoticeHelper.setToElementHtml( getFaceDetectionMessage(), 'Please confirm that the found face is correct.', 'info' );
						getDetectFacesPhpButton().hide();
						getDetectFacesJsButton().hide();
						getSaveFacesButton().show();
						getDiscardFacesButton().show();
					} else {
						enableAllDetections();
						adminNoticeHelper.setToElementHtml( getFaceDetectionMessage(), 'No face found.', 'warning' );
					}
				} )
				.fail( function( jqXHR ) {
					let errorMessage = 'Error';
					if ( typeof jqXHR.responseJSON !== 'undefined' && typeof jqXHR.responseJSON.data[ 0 ].message !== 'undefined' ) {
						errorMessage = jqXHR.responseJSON.data[ 0 ].message;
					}
					enableAllDetections();
					adminNoticeHelper.setToElementHtml( getFaceDetectionMessage(), errorMessage, 'error' );
				} )
				.always( function() {
					spinnerHelper.removeFromElement( getDetectFacesPhpButton() );
					getDetectFacesPhpButton().prop( 'disabled', false );
				} );
		}

		function detectFacesJs() {
			if ( devTools.isOpen ) {
				adminNoticeHelper.setToElementHtml( getFaceDetectionMessage(), 'Face detection via JavaScript does not work when devtools is open. Please close your devtools and try again.', 'error' );
				return;
			}

			disableAllDetections();
			spinnerHelper.appendToElement( getDetectFacesJsButton() );

			// Put it in a setTimeout so JavaScript will run it a little bit later, making sure the spinner works.
			setTimeout( () => {
				detectFacesJsBackgroundTask();
			}, 0 );
		}

		function detectFacesJsBackgroundTask() {
			try {
				getPreviewImage().faceDetection( {
					complete( foundFaces ) {
						if ( ! Array.isArray( foundFaces ) ) {
							return;
						}

						foundFaces = foundFaces.map( face => {
							return {
								x: face.x,
								y: face.y,
								width: face.width,
								height: face.height,
								accuracy: Math.min( Math.max( face.confidence, 0 ), 10 ) * 10,
							};
						} );

						foundFaces = foundFaces.filter( face => {
							return face.accuracy >= config.js_faces_detection.min_accuracy;
						} );

						hideSpots();
						getSaveFacesButton().attr( 'data-faces', JSON.stringify( foundFaces ) );
						showFaces( foundFaces );
						if ( foundFaces.length > 0 ) {
							adminNoticeHelper.setToElementHtml( getFaceDetectionMessage(), 'Please confirm that the found faces are correct.', 'info' );
							getDetectFacesPhpButton().hide();
							getDetectFacesJsButton().hide();
							getSaveFacesButton().show();
							getDiscardFacesButton().show();
						} else {
							enableAllDetections();
							adminNoticeHelper.setToElementHtml( getFaceDetectionMessage(), 'No faces found.', 'warning' );
						}

						spinnerHelper.removeFromElement( getDetectFacesJsButton() );
						getDetectFacesJsButton().prop( 'disabled', false );
					},
					error ( code, errorMessage ) {
						enableAllDetections();
						adminNoticeHelper.setToElementHtml( getFaceDetectionMessage(), `${code}: ${errorMessage}`, 'error' );

						spinnerHelper.removeFromElement( getDetectFacesJsButton() );
						getDetectFacesJsButton().prop( 'disabled', false );
					}
				} );
			} catch ( error ) {
				enableAllDetections();
				adminNoticeHelper.setToElementHtml( getFaceDetectionMessage(), 'JavaScript faces detection failed for this picture.', 'error' );
				spinnerHelper.removeFromElement( getDetectFacesJsButton() );
				getDetectFacesJsButton().prop( 'disabled', false );
			}
		}

		function discardFaces() {
			loadSpots();
			getDetectFacesPhpButton().show();
			getDetectFacesJsButton().show();
			getDiscardFacesButton().hide();
			getSaveFacesButton().hide();
			enableAllDetections();
			adminNoticeHelper.removeFromElement( getFaceDetectionMessage() );
		}

		function saveFaces() {
			spinnerHelper.appendToElement( getSaveFacesButton() );
			getSaveFacesButton().prop( 'disabled', true );
			getDiscardFacesButton().prop( 'disabled', true );
			adminNoticeHelper.removeFromElement( getFaceDetectionMessage() );

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
					adminNoticeHelper.setToElementHtml( getFaceDetectionMessage(), 'Faces are saved.', 'success' );
				} )
				.fail( function( jqXHR ) {
					let errorMessage = 'Error';
					if ( typeof jqXHR.responseJSON !== 'undefined' && typeof jqXHR.responseJSON.data[ 0 ].message !== 'undefined' ) {
						errorMessage = jqXHR.responseJSON.data[ 0 ].message;
					}
					adminNoticeHelper.setToElementHtml( getFaceDetectionMessage(), errorMessage, 'error' );
				} )
				.always( function() {
					spinnerHelper.removeFromElement( getSaveFacesButton() );
					getSaveFacesButton().prop( 'disabled', false );
					getDiscardFacesButton().prop( 'disabled', false );
				} );
		}

		function removeFaces() {
			disableAllDetections();
			spinnerHelper.appendToElement( getRemoveFacesButton() );

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
					getDetectFacesPhpButton().show();
					getDetectFacesJsButton().show();
					enableAllDetections();
					adminNoticeHelper.setToElementHtml( getFaceDetectionMessage(), 'Faces are removed.', 'success' );
				} )
				.fail( function( jqXHR ) {
					let errorMessage = 'Error';
					if ( typeof jqXHR.responseJSON !== 'undefined' && typeof jqXHR.responseJSON.data[ 0 ].message !== 'undefined' ) {
						errorMessage = jqXHR.responseJSON.data[ 0 ].message;
					}
					adminNoticeHelper.setToElementHtml( getFaceDetectionMessage(), errorMessage, 'error' );
				} )
				.always( function() {
					spinnerHelper.removeFromElement( getRemoveFacesButton() );
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
			adminNoticeHelper.setToElementHtml( getHotspotSelectionMessage(), 'Please click on an empty area of the image to add hotspots, or on a hotspot to delete it.', 'info' );
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
			adminNoticeHelper.removeFromElement( getFaceDetectionMessage() );
		}

		function saveHotspots() {
			spinnerHelper.appendToElement( getSaveHotspotsButton() );
			getSaveHotspotsButton().prop( 'disabled', true );
			getDiscardHotspotsButton().prop( 'disabled', true );
			adminNoticeHelper.removeFromElement( getHotspotSelectionMessage() );
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
					adminNoticeHelper.setToElementHtml( getHotspotSelectionMessage(), 'Hotspots are saved.', 'success' );
				} )
				.fail( function( jqXHR ) {
					let errorMessage = 'Error';
					if ( typeof jqXHR.responseJSON !== 'undefined' && typeof jqXHR.responseJSON.data[ 0 ].message !== 'undefined' ) {
						errorMessage = jqXHR.responseJSON.data[ 0 ].message;
					}
					adminNoticeHelper.setToElementHtml( getHotspotSelectionMessage(), errorMessage, 'error' );
					addEditHotspotsEventListeners();
				} )
				.always( function() {
					spinnerHelper.removeFromElement( getSaveHotspotsButton() );
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

		function getPreviewImage() {
			return getChildElement( '#image-crop-positioner-image-spots-preview-image' );
		}

		// Face detection
		function getDetectFacesPhpButton() {
			return getChildElement( '.button__detect-faces-php' );
		}

		function getDetectFacesJsButton() {
			return getChildElement( '.button__detect-faces-js' );
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

		initialize();
	};

}( jQuery, window.image_crop_positioner = window.image_crop_positioner || {} ) );
