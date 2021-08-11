import SessionHelper from '../helpers/session-helper';

export default class APIHelper {
	constructor() {
		this.$ = jQuery;
		this.sessionHelper = new SessionHelper();
	}

	getCart() {
		return this.$.ajax( {
			method: "GET",
			url: this.getCartUrl(),
			dataType: 'json',
			timeout: 5000,
		} );
	}

	generateUrl( urlPath ) {
		if ( ! window.place_that_face_options.api.client_name ) {
			return null;
		}

		return window.place_that_face_options.api.base_url + encodeURIComponent( window.place_that_face_options.api.client_name ) + '/' + urlPath.replace( /^\/+|\/+$/g, '' );
	}

	getCartUrl() {
		let urlPath = '/cart/';
		const sessionId = this.sessionHelper.getId();
		if ( sessionId ) {
			urlPath += sessionId;
		}
		return this.generateUrl( urlPath );
	}
}
