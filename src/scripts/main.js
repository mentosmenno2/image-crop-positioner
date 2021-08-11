( function( app, $ ) {
	'use strict';

	app.instantiate = function( elem ) {
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

	$( '[data-image-crop-positioner-module]' ).each( function() {
		app.instantiate( this );
	} );

}( window.image_crop_positioner = window.image_crop_positioner || {}, jQuery ) );
