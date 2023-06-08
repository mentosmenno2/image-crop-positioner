<?php

namespace Mentosmenno2\ImageCropPositioner;

class Conflicts {

	protected const CONFLICTING_PLUGINS = array(
		'my-eyes-are-up-here/my-eyes-are-up-here.php',
	);

	public function register_hooks(): void {
		$this->maybe_force_deactivate();
		add_action( 'admin_notices', array( $this, 'deactivation_admin_notice' ) );
	}

	/**
	 * Deactivate this plugin if a conflicting plugin is already active
	 */
	public function maybe_force_deactivate(): void {
		if ( apply_filters( 'image_crop_positioner_allow_conflicts', defined( 'IMAGE_CROP_POSITIONER_ALLOW_CONFLICTS' ) && constant( 'IMAGE_CROP_POSITIONER_ALLOW_CONFLICTS' ) ) ) {
			return;
		}

		foreach ( self::CONFLICTING_PLUGINS as $plugin ) {
			if ( ! is_plugin_active( $plugin ) ) {
				continue;
			}

			deactivate_plugins( plugin_basename( constant( 'IMAGE_CROP_POSITIONER_PLUGIN_FILE' ) ) );
			update_option( 'image_crop_positioner_conflict', true );
		}
	}

	/**
	 * Show an admin notice when the plugin is deactivated because of a conflict
	 */
	public function deactivation_admin_notice(): void {
		$conflict_occured = get_option( 'image_crop_positioner_conflict', false );
		if ( ! $conflict_occured ) {
			return;
		}
		delete_option( 'image_crop_positioner_conflict' );

		$active_conflicting_plugins = array_filter(
			self::CONFLICTING_PLUGINS, function( string $plugin ): bool {
				return is_plugin_active( $plugin );
			}
		);

		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		?>

		<div class="notice notice-error is-dismissible">
			<p>
				<strong><?php esc_html_e( 'Image Crop Positioner', 'image-crop-positioner' ); ?></strong>
				<?php esc_html_e( 'The plugin has been automatically disabled because the following conflicting plugins are active:', 'image-crop-positioner' ); ?></p>
			<ul>
				<?php

				foreach ( $active_conflicting_plugins as $plugin ) {
					$name = get_plugin_data( trailingslashit( constant( 'WP_PLUGIN_DIR' ) ) . $plugin, false )['Name'];
					if ( empty( $name ) ) {
						$name = get_plugin_data( trailingslashit( WPMU_PLUGIN_DIR ) . $plugin, false )['Name'] ?? $plugin;
					}
					?>
					<li><?php echo esc_html( $name ); ?></li>
				<?php } ?>
			</ul>
		</div>

		<?php
	}
}
