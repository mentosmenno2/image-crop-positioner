import AdminNoticeHelper from "../helpers/admin-notice";
import SpinnerHelper from "../helpers/spinner";

( function( $, app ) {

	app.migrator = function( element ) {

		const spinnerHelper = new SpinnerHelper();
		const adminNoticeHelper = new AdminNoticeHelper();

		const $element = $( element );
		const $buttonStart = $element.find( '.migrator__button-start' );
		const $buttonStop = $element.find( '.migrator__button-stop' );
		const $message = $element.find( '.migrator__message' );
		const $dataTable = $element.find( '.migrator__data-table' );

		let isStopQueued = false;

		// Initialize an instance
		function initialize() {
			addEventListeners();
		}

		function addEventListeners() {
			$buttonStart.on( 'click', startMigration );
			$buttonStop.on( 'click', stopMigration );
		}

		function startMigration() {
			spinnerHelper.appendToElement( $buttonStart );
			$buttonStart.attr( 'disabled', true );
			adminNoticeHelper.setToElementHtml( $message, 'Migration in progress, please wait...', 'info' );
			$dataTable.show();
			$buttonStop.attr( 'disabled', false );
			isStopQueued = false;
		}

		function stopMigration() {
			spinnerHelper.appendToElement( $buttonStop );
			$buttonStop.attr( 'disabled', true );
			adminNoticeHelper.setToElementHtml( $message, 'Migration stopping, please wait...', 'info' );
			isStopQueued = true;
		}

		initialize();
	};

}( jQuery, window.image_crop_positioner = window.image_crop_positioner || {} ) );
