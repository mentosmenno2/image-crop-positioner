import AdminNoticeHelper from "../helpers/admin-notice";
import ProgressBarHelper from "../helpers/progress-bar";
import SpinnerHelper from "../helpers/spinner";

( function( $, app ) {

	app.migrator = function( element ) {

		const spinnerHelper = new SpinnerHelper();
		const adminNoticeHelper = new AdminNoticeHelper();
		const progressbarHelper = new ProgressBarHelper();

		const $element = $( element );
		const $buttonStart = $element.find( '.image-crop-positioner-migrator__button-start' );
		const $buttonStop = $element.find( '.image-crop-positioner-migrator__button-stop' );
		const $message = $element.find( '.image-crop-positioner-migrator__message' );
		const $log = $element.find( '.image-crop-positioner-migrator__log' );
		const $logContent = $log.find( '.image-crop-positioner-migrator__log-content' );
		const $buttonToggleLog = $log.find( '.image-crop-positioner-migrator__log-button' );
		const $progressBar = $element.find( '.image-crop-positioner-progress-bar' );
		const config = $element.data( 'config' );

		let isStopQueued = false;
		let currentProcessingPage = 1;

		// Initialize an instance
		function initialize() {
			addEventListeners();
		}

		function addEventListeners() {
			$buttonStart.on( 'click', startMigration );
			$buttonStop.on( 'click', queueStopMigration );
			$buttonToggleLog.on( 'click', toggleDisplayLogContent );
		}

		function startMigration() {
			spinnerHelper.appendToElement( $buttonStart );
			$buttonStart.attr( 'disabled', true );
			adminNoticeHelper.setToElementHtml( $message, 'Migration in progress, please wait...', 'info' );
			$progressBar.show();
			updateProgressbar( 0, 0 );
			$log.show();
			$logContent.empty();
			$buttonStop.attr( 'disabled', false );
			isStopQueued = false;
			currentProcessingPage = 1;

			sendApiRequest();
		}

		function queueStopMigration() {
			spinnerHelper.appendToElement( $buttonStop );
			$buttonStop.attr( 'disabled', true );
			adminNoticeHelper.setToElementHtml( $message, 'Migration stopping, please wait...', 'info' );
			isStopQueued = true;
		}

		function sendApiRequest() {
			$.ajax( {
				url : window.image_crop_positioner_options.ajax_url,
				data : {
					_ajax_nonce: window.image_crop_positioner_options.nonce,
					action: 'image_crop_positioner_migrate',
					page: currentProcessingPage,
					migrator: config.migrator_slug,
				},
				method : 'POST',
				dataType: "json",
				timeout: 30000,
			} )
				.done( function( data ) {
					updateProgressbar( data.data.pagination.total_processed_posts, data.data.pagination.total_posts );
					appendProcessedItemsToLog( data.data.log );

					if ( data.data.pagination.current_page === data.data.pagination.total_pages ) {
						completeMigration();
						return;
					}

					if ( isStopQueued ) {
						stopMigration( false );
						return;
					}

					currentProcessingPage++;
					sendApiRequest();
				} )
				.fail( function( jqXHR ) {
					isStopQueued = true;

					let errorMessage = 'Error';
					if ( typeof jqXHR.responseJSON !== 'undefined' && typeof jqXHR.responseJSON.data[ 0 ].message !== 'undefined' ) {
						errorMessage = jqXHR.responseJSON.data[ 0 ].message;
					}
					adminNoticeHelper.setToElementHtml( $message, errorMessage, 'error' );
					$logContent.append( `Migration error: ${errorMessage}` );
					stopMigration( true );
				} );
		}

		function completeMigration() {
			spinnerHelper.removeFromElement( $buttonStart );
			spinnerHelper.removeFromElement( $buttonStop );
			$buttonStart.attr( 'disabled', false );
			$buttonStop.attr( 'disabled', true );
			adminNoticeHelper.setToElementHtml( $message, 'Migration completed', 'success' );
			$logContent.append( 'Migration completed' );
		}

		function stopMigration( dontSetMessage ) {
			spinnerHelper.removeFromElement( $buttonStart );
			spinnerHelper.removeFromElement( $buttonStop );
			$buttonStart.attr( 'disabled', false );
			$buttonStop.attr( 'disabled', true );
			if ( ! dontSetMessage ) {
				adminNoticeHelper.setToElementHtml( $message, 'Migration stopped', 'error' );
				$logContent.append( 'Migration stopped' );
			}
		}

		function updateProgressbar( current, total ) {
			progressbarHelper.setMaxValue( $progressBar, total );
			progressbarHelper.setCurrentValue( $progressBar, current );
		}

		function appendProcessedItemsToLog( processedItems ) {
			processedItems.forEach( processedItem => {
				$logContent.append( `Attachment ID ${processedItem.attachment_id}: ${processedItem.status}.\n` );
			} );
		}

		function toggleDisplayLogContent() {
			$logContent.toggle();
		}

		initialize();
	};

}( jQuery, window.image_crop_positioner = window.image_crop_positioner || {} ) );
