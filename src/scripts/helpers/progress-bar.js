export default class ProgressBarHelper {
	constructor() {
		this.$ = jQuery;
	}

	setMinValue( $element, minValue ) {
		$element.attr( 'aria-valuemin', minValue );
	}

	setMaxValue( $element, maxValue ) {
		$element.attr( 'aria-valuemax', maxValue );
	}

	setCurrentValue( $element, currentValue ) {
		$element.attr( 'aria-valuenow', currentValue );
		const minValue = parseFloat( $element.attr( 'aria-valuemin' ) );
		const maxValue = parseFloat( $element.attr( 'aria-valuemax' ) );
		const type =  $element.data( 'type' );
		const percentage = ( currentValue - minValue ) / ( maxValue - minValue ) * 100;

		$element.find( '.image-crop-positioner-progress-bar__inner' ).css( 'width', percentage ? `${percentage}%` : 0 );
		if ( type === 'percentage' ) {
			$element.find( '.image-crop-positioner-progress-bar__text' ).text( `${percentage}%` );
		} else {
			$element.find( '.image-crop-positioner-progress-bar__text' ).text( `${currentValue} / ${maxValue}` );
		}
	}
}
