import './global/edit-image-screen.js';
import 'jquery.faceDetection';

( function( app, $ ) {
	'use strict';

	app.instantiateElement = function( elem ) {
		const $this   = $( elem );
		const module  = $this.attr( 'data-image-crop-positioner-module' );
		if ( module === undefined ) {
			throw 'Image Crop Positioner module not defined (use data-image-crop-positioner-module="")';
		} else if ( module in app ) {
			new app[ module ]( elem );
			$this.attr( 'data-initialized', true );
		} else {
			throw 'Module \'' + module + '\' not found';
		}
	};

	app.instantiateModules = function() {
		$( '[data-image-crop-positioner-module]:not([data-initialized])' ).each( function() {
			app.instantiateElement( this );
		} );
	};

	app.bootstrap = function() {
		app.instantiateModules();

		new app.editImageScreen();

		// Define hooks when modules should also be instansiated
		if ( wp.media ) {
			wp.media.view.Modal.prototype.on( 'open', function() { app.instantiateModules(); } );
		}
	};

	app.bootstrap();
}( window.image_crop_positioner = window.image_crop_positioner || {}, jQuery ) );
