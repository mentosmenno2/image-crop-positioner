export default class AdminNoticeHelper {
	constructor() {
		this.$ = jQuery;
	}

	getHtml( message, type = 'info', inline = true, isDismissable = false ) {
		const $newNoticeElement = this.$( `<div class="notice notice-${type} inline" >${message}</div>` );
		if ( inline ) {
			$newNoticeElement.addClass( 'inline' );
		}
		if ( isDismissable ) {
			$newNoticeElement.addClass( 'is-dismissable' );
		}
		return $newNoticeElement;
	}

	setToElementHtml( $element, message, type = 'info', inline = true, isDismissable = false ) {
		return $element.html( this.getHtml( message, type, inline, isDismissable ) );
	}

	appendToElement( $element, message, type = 'info', inline = true, isDismissable = false ) {
		return $element.append( this.getHtml( message, type, inline, isDismissable ) );
	}

	prependToElement( $element, message, type = 'info', inline = true, isDismissable = false ) {
		return $element.prepend( this.getHtml( message, type, inline, isDismissable ) );
	}

	removeFromElement( $element ) {
		return $element.find( '.notice' ).remove();
	}
}
