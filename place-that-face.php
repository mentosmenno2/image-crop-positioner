<?php
/**
 * Plugin Name:  Place That Face
 * Version:      0.4.1
 * Description:  Facial recognition and hotspot selection for cropping images in WordPress.
 * Author:       mentosmenno2
 *
 * Text Domain: place-that-face
 * Domain Path: /languages/
 */

add_action(
	'plugins_loaded',
	function() {
		// Set plugin variables
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$plugin_path = realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR;
		$plugin_data = get_plugin_data( __FILE__ );
		$plugin_url  = plugin_dir_url( __FILE__ );
		define( 'PLACE_THAT_FACE_PLUGIN_VERSION', isset( $plugin_data['Version'] ) ? $plugin_data['Version'] : '' );
		define( 'PLACE_THAT_FACE_PLUGIN_PATH', $plugin_path );
		define( 'PLACE_THAT_FACE_PLUGIN_URL', $plugin_url );
		define( 'PLACE_THAT_FACE_PLUGIN_NAMESPACE', 'Mentosmenno2\\PlaceThatFace\\' );

		// Autoload files
		$autoload_file = $plugin_path . 'vendor/autoload.php';
		if ( file_exists( $autoload_file ) ) {
			require_once $autoload_file;
		} else {
			/**
			 * Autoload using spl_autoload_register
			 * @see https://www.php.net/manual/en/language.oop5.autoload.php#120258
			 */
			$autoload_dir = PLACE_THAT_FACE_PLUGIN_PATH . 'app' . DIRECTORY_SEPARATOR;
			spl_autoload_register(
				function ( string $class ) use ( $autoload_dir ) {
					$no_plugin_ns_class = str_replace( PLACE_THAT_FACE_PLUGIN_NAMESPACE, '', $class );
					if ( $no_plugin_ns_class === $class ) {
						return false; // Class not in plugin namespace, skip autoloading
					}

					$file = str_replace( '\\', DIRECTORY_SEPARATOR, $no_plugin_ns_class ) . '.php';
					$file = $autoload_dir . $file;
					if ( ! file_exists( $file ) ) {
						throw new \Exception( 'Class ' . $class . 'not found' );
					}

					// Require the file
					require_once $file;
					return true;
				}
			);
		}

		// Register hooks
		( new Mentosmenno2\PlaceThatFace\Assets() )->register_hooks();

		// Load textdomain
		load_plugin_textdomain( 'place-that-face', false, basename( dirname( __FILE__ ) ) . '/languages/' );
	}
);
