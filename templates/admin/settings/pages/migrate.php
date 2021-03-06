<?php
use Mentosmenno2\ImageCropPositioner\Migrators\Migrators;
use Mentosmenno2\ImageCropPositioner\Templates;

$migrators = ( new Migrators() )->get_migrators();

?>

<div>
	<?php
	foreach ( $migrators as $migrator ) {
		$data_config = wp_json_encode(
			array(
				'migrator_slug' => $migrator->get_slug(),
			)
		) ?: '';
		?>
		<h2><?php echo esc_html( $migrator->get_title() ); ?></h2>

		<div class="image-crop-positioner-migrator" data-image-crop-positioner-module="migrator" data-config="<?php echo esc_attr( $data_config ); ?>">
			<p><?php echo wp_kses_post( $migrator->get_description() ); ?></p>

			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row">
							<label for="image-crop-positioner-migrator-<?php echo esc_attr( $migrator->get_slug() ); ?>__input-batch-size">
								<?php esc_html_e( 'Batch size: ', 'image-crop-positioner' ); ?>
							</label>
						</th>
						<td>
							<input
								type="number"
								id="image-crop-positioner-migrator-<?php echo esc_attr( $migrator->get_slug() ); ?>__input-batch-size"
								class="image-crop-positioner-migrator__input-batch-size"
								min="1"
								max="100"\
								value="<?php echo esc_attr( (string) $migrator->get_default_batch_size() ); ?>"
							/>
							<label for="image-crop-positioner-migrator-<?php echo esc_attr( $migrator->get_slug() ); ?>__input-batch-size">
								<?php esc_html_e( 'Images per batch', 'image-crop-positioner' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'Higher batch sizes speed up the migration, but increase the chance of server timeouts. If you experience server timeouts, please lower the batch size or use WP-CLI.', 'image-crop-positioner' ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>

			<div>
				<button type="button" class="button image-crop-positioner-migrator__button-start"><?php esc_html_e( 'Start migration', 'image-crop-positioner' ); ?></button>
				<button type="button" class="button image-crop-positioner-migrator__button-stop" disabled="disabled"><?php esc_html_e( 'Stop migration', 'image-crop-positioner' ); ?></button>
			</div>

			<div class="image-crop-positioner-migrator__message" ></div>

			<?php
			( new Templates() )->echo_template(
				'partials/progress-bar', array(
					'min_value'     => 0,
					'max_value'     => 0,
					'current_value' => 0,
					'display_type'  => 'progress',
					'attributes'    => array(
						'style' => 'display: none;',
					),
				)
			);
			?>

			<div class="image-crop-positioner-migrator__log" >
				<button type="button" class="button image-crop-positioner-migrator__log-button"><?php esc_html_e( 'Show/hide log', 'image-crop-positioner' ); ?></button>
				<pre class="image-crop-positioner-migrator__log-content" ></pre>
			</div>
		</div>

		<hr>
	<?php } ?>
</div>


