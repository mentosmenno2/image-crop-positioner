export default class SpinnerHelper {
	constructor() {
		this.$ = jQuery;
	}

	getHtml() {
		return '<div class="image-crop-positioner-spinner__wrapper"><div class="spinner image-crop-positioner-spinner is-active"></div></div>';
	}

	setToElementHtml( $element ) {
		return $element.html( this.getHtml() );
	}

	appendToElement( $element ) {
		return $element.append( this.getHtml() );
	}

	prependToElement( $element ) {
		return $element.prepend( this.getHtml() );
	}

	removeFromElement( $element ) {
		return $element.find( '.image-crop-positioner-spinner__wrapper' ).remove();
	}
}
