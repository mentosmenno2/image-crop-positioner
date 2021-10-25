<?php

/**
 * @global Templates $template
 */

/** @var float */
$min_value = $template->get_arg( 'min_value' ) ?: 0;
/** @var float */
$max_value = $template->get_arg( 'max_value' ) ?: 0;
/** @var float */
$current_value = $template->get_arg( 'current_value' ) ?: 0;
/** @var string */
$display_type = $template->get_arg( 'display_type' ) ?: 'percentage';
/** @var array */
$attributes = $template->get_arg( 'attributes' ) ?: array();
$percentage = 0;
if ( $max_value ) {
	$percentage = ( $current_value - $min_value ) / ( $max_value - $min_value ) * 100;
}

?>

<div>
	<div
		class="image-crop-positioner-progress-bar"
		role="progressbar"
		aria-valuenow="<?php echo esc_attr( (string) $current_value ); ?>"
		aria-valuemin="<?php echo esc_attr( (string) $min_value ); ?>"
		aria-valuemax="<?php echo esc_attr( (string) ( $max_value ? $max_value : 100 ) ); ?>"
		data-type="<?php echo esc_attr( $display_type ); ?>"
		<?php foreach ( $attributes as $attribute_name => $attribute_value ) { ?>
			<?php echo esc_attr( $attribute_name ); ?>="<?php echo esc_attr( $attribute_value ); ?>"
		<?php } ?>
	>
		<div class="image-crop-positioner-progress-bar__inner" style="width: <?php echo esc_attr( $percentage ? $percentage . '%' : '0' ); ?>;"></div>
		<div class="image-crop-positioner-progress-bar__text" >
			<?php if ( $display_type === 'percentage' ) { ?>
				<?php echo esc_html( round( $current_value / $max_value * 100 ) . '%' ); ?>
			<?php } else { ?>
				<?php echo esc_html( $current_value . ' / ' . $max_value ); ?>
			<?php } ?>
		</div>
	</div>
</div>
