<?php

namespace Mentosmenno2\ImageCropPositioner;

class Assets {

	public function register_hooks(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	public function is_development_mode(): bool {
		return ! file_exists( IMAGE_CROP_POSITIONER_PLUGIN_PATH . 'dist/main.js' );
	}

	public function enqueue(): void {
		if ( $this->is_development_mode() ) {
			$this->enqueue_development( 'image_crop_positioner_main', array( 'jquery' ), '/src/main.js', true );
		} else {
			$this->enqueue_production();
		}
		$this->localize();
	}

	public function enqueue_development( string $handle, array $deps, string $path, bool $in_footer ): void {
		$src = $this->get_development_src( $path );
		wp_enqueue_script( $handle, $src, $deps, IMAGE_CROP_POSITIONER_PLUGIN_VERSION, $in_footer );
	}

	public function enqueue_production(): void {
		wp_enqueue_script( 'image_crop_positioner_main', IMAGE_CROP_POSITIONER_PLUGIN_URL . 'dist/main.js', array( 'jquery' ), IMAGE_CROP_POSITIONER_PLUGIN_VERSION, true );
		wp_enqueue_style( 'image_crop_positioner_main', IMAGE_CROP_POSITIONER_PLUGIN_URL . 'dist/main.css', array(), IMAGE_CROP_POSITIONER_PLUGIN_VERSION, 'all' );
	}

	public function localize(): void {
		$data = $this->get_localize_data();
		wp_localize_script( 'image_crop_positioner_main', 'image_crop_positioner_options', $data );
	}

	public function get_localize_data(): array {
		return array();
	}

	public function get_development_src( string $path = '' ): string {
		$config   = $this->get_src_config();
		$protocol = $config['secure'] ? 'https' : 'http';
		return $protocol . '://localhost:' . $config['port'] . $path;
	}

	/**
	 * Get config settings from development/config.default.json and (optional) development/config.local.json
	 */
	public function get_src_config(): array {
		$local_config_path = IMAGE_CROP_POSITIONER_PLUGIN_PATH . 'development/config.local.json';
		$local_config      = array();
		$manifest          = json_decode( file_get_contents( IMAGE_CROP_POSITIONER_PLUGIN_PATH . 'development/config.default.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		if ( file_exists( $local_config_path ) ) {
			$local_config = json_decode( file_get_contents( $local_config_path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		}

		// Merge the two config files and the local config is always leading
		return array_merge( $manifest['config'], $local_config );
	}

	/**
	 * Get the stylesheet directory uri based on your current environment
	 */
	public function get_assets_directory_url(): string {
		if ( $this->is_development_mode() ) {
			return $this->get_development_src( '/src' );
		}
		return IMAGE_CROP_POSITIONER_PLUGIN_URL . 'dist';
	}
}
