<?php
/**
 * Plugin Name: Image Crop Positioner
 * Version:     0.0.1
 * Description: Facial recognition and hotspot selection for cropping images in WordPress.
 * Author:      mentosmenno2
 *
 * Text Domain: image-crop-positioner
 * Domain Path: /languages/
 */

// Initialize the plugin
add_action(
	'plugins_loaded',
	function() {
		// Set plugin variables
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$plugin_path = plugin_dir_path( __FILE__ );
		$plugin_data = get_plugin_data( __FILE__ );
		$plugin_url  = plugin_dir_url( __FILE__ );
		define( 'IMAGE_CROP_POSITIONER_PLUGIN_VERSION', isset( $plugin_data['Version'] ) ? $plugin_data['Version'] : '' );
		define( 'IMAGE_CROP_POSITIONER_PLUGIN_FILE', __FILE__ );
		define( 'IMAGE_CROP_POSITIONER_PLUGIN_PATH', $plugin_path );
		define( 'IMAGE_CROP_POSITIONER_PLUGIN_URL', $plugin_url );
		define( 'IMAGE_CROP_POSITIONER_PLUGIN_NAMESPACE', 'Mentosmenno2\\ImageCropPositioner\\' );

		// Autoload files
		$autoload_file = $plugin_path . 'vendor/autoload.php';
		if ( file_exists( $autoload_file ) ) {
			require_once $autoload_file;
		} else {
			/**
			 * Autoload using spl_autoload_register
			 * @see https://www.php.net/manual/en/language.oop5.autoload.php#120258
			 */
			$autoload_dir = IMAGE_CROP_POSITIONER_PLUGIN_PATH . 'app' . DIRECTORY_SEPARATOR;
			spl_autoload_register(
				function ( string $class ) use ( $autoload_dir ) {
					$no_plugin_ns_class = str_replace( IMAGE_CROP_POSITIONER_PLUGIN_NAMESPACE, '', $class );
					if ( $no_plugin_ns_class === $class ) {
						return false; // Class not in plugin namespace, skip autoloading
					}

					$file = str_replace( '\\', DIRECTORY_SEPARATOR, $no_plugin_ns_class ) . '.php';
					$file = $autoload_dir . $file;
					if ( ! file_exists( $file ) ) {
						throw new Exception( 'Class ' . $class . 'not found' );
					}

					// Require the file
					require_once $file;
					return true;
				}
			);
		}

		// Register hooks
		( new Mentosmenno2\ImageCropPositioner\Conflicts() )->register_hooks();
		( new Mentosmenno2\ImageCropPositioner\Assets() )->register_hooks();
		( new Mentosmenno2\ImageCropPositioner\Admin() )->register_hooks();
		( new Mentosmenno2\ImageCropPositioner\Crop() )->register_hooks();
		( new Mentosmenno2\ImageCropPositioner\Ajax\ImagePreviews() )->register_hooks();
		( new Mentosmenno2\ImageCropPositioner\Ajax\Detection() )->register_hooks();
		( new Mentosmenno2\ImageCropPositioner\Ajax\SaveFaces() )->register_hooks();
		( new Mentosmenno2\ImageCropPositioner\Ajax\RemoveFaces() )->register_hooks();

		// Register CLI commands
		if ( defined( 'WP_CLI' ) && constant( 'WP_CLI' ) ) {
			\WP_CLI::add_command( 'image-crop-positioner detection', \Mentosmenno2\ImageCropPositioner\CLI\Detection::class );
			\WP_CLI::add_command( 'image-crop-positioner migrate', \Mentosmenno2\ImageCropPositioner\CLI\Migrate::class );
		}

		// Load textdomain
		load_plugin_textdomain( 'image-crop-positioner', false, basename( dirname( __FILE__ ) ) . '/languages/' );
	}
);
