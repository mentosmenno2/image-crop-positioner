<?php
use Mentosmenno2\ImageCropPositioner\Migrators\Migrators;
use Mentosmenno2\ImageCropPositioner\Templates;

$migrators = ( new Migrators() )->get_migrators();

?>

<div>
	<?php
	foreach ( $migrators as $migrator ) {
		$data_config = wp_json_encode(
			array()
		) ?: '';
		?>
		<h2><?php echo esc_html( $migrator->get_title() ); ?></h2>

		<div class="migrator" data-image-crop-positioner-module="migrator" data-config="<?php echo esc_attr( $data_config ); ?>">
			<p><?php echo wp_kses_post( $migrator->get_description() ); ?></p>

			<div>
				<button type="button" class="button migrator__button-start"><?php esc_html_e( 'Start migration', 'image-crop-positioner' ); ?></button>
				<button type="button" class="button migrator__button-stop" disabled="disabled"><?php esc_html_e( 'Stop migration', 'image-crop-positioner' ); ?></button>
			</div>

			<div class="migrator__message" ></div>

			<table class="migrator__data-table wp-list-table widefat fixed striped" <?php ( new Templates() )->display_none( true ); ?>>
				<thead>
					<tr>
						<th><?php esc_html_e( 'Migrated', 'image-crop-positioner' ); ?></th>
						<th><?php esc_html_e( 'Skipped', 'image-crop-positioner' ); ?></th>
						<th><?php esc_html_e( 'Total processed', 'image-crop-positioner' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>0 / <?php esc_html_e( 'Unknown', 'image-crop-positioner' ); ?></td>
						<td>0 / <?php esc_html_e( 'Unknown', 'image-crop-positioner' ); ?></td>
						<td>0 / <?php esc_html_e( 'Unknown', 'image-crop-positioner' ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	<?php } ?>
</div>


