( function( app, $ ) {
	'use strict';

	app.instantiate = function( elem ) {
		const $this   = $( elem );
		const module  = $this.attr( 'data-place-that-face-module' );
		if ( module === undefined ) {
			throw 'Place That Face module not defined (use data-place-that-face-module="")';
		} else if ( module in app ) {
			new app[ module ]( elem );
			$this.attr( 'data-initialized', true );
		} else {
			throw 'Module \'' + module + '\' not found';
		}
	};

	$( '[data-place-that-face-module]' ).each( function() {
		app.instantiate( this );
	} );

}( window.place_that_face = window.place_that_face || {}, jQuery ) );
