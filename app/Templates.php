<?php

namespace Mentosmenno2\ImageCropPositioner;

class Templates {

	public const TEMPLATE_PART_DIRECTORY = 'templates';

	/** @var array */
	private $args = array();

	public function get_template( string $_file, array $_template_args = array() ): string {
		$this->args = wp_parse_args( $_template_args, array() );

		$_filepath = constant( 'IMAGE_CROP_POSITIONER_PLUGIN_PATH' ) . trailingslashit( self::TEMPLATE_PART_DIRECTORY ) . $_file . '.php';
		if ( ! file_exists( $_filepath ) ) {
			return '';
		}

		// Render the template
		ob_start();
		$template = $this;
		require $_filepath;
		$data = ob_get_clean();
		if ( ! $data ) {
			return '';
		}

		return $data;
	}

	public function echo_template( string $file, array $template_args = array() ): void {
		echo $this->get_template( $file, $template_args ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @return mixed
	 */
	public function get_arg( string $arg_name ) {
		if ( ! array_key_exists( $arg_name, $this->args ) ) {
			return null;
		}
		return $this->args[ $arg_name ];
	}

	public function echo_arg( string $arg_name ): void {
		echo strval( $this->get_arg( $arg_name ) ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Maybe render style display none attribute
	 *
	 * @param mixed $display_none
	 * @param boolean $compare
	 * @param boolean $echo
	 * @return string
	 */
	public function display_none( $display_none, bool $compare = true, bool $echo = true ) {
		return __checked_selected_helper( $display_none, $compare, $echo, 'style="display: none;"' );
	}

}
