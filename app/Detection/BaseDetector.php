<?php
namespace Mentosmenno2\ImageCropPositioner\Detection;

use Exception;
use GdImage;
use Mentosmenno2\ImageCropPositioner\Objects\Spot;

abstract class BaseDetector {

	/** @var static|null*/
	protected static $instance = null;

	/**
	 * Constructor of detector
	 *
	 * @throws Exception When detector is not available
	 */
	protected function __construct() {

	}

	abstract public function get_slug(): string;

	/**
	 * Get instance of detector
	 *
	 * @return static
	 *
	 * @psalm-suppress UnsafeInstantiation
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new static();
		}
		return self::$instance;
	}

	/**
	 * Check if detector is available
	 *
	 * @return boolean
	 *
	 * @psalm-suppress UnsafeInstantiation
	 */
	public static function is_available(): bool {
		try {
			new static();
		} catch ( Exception $e ) {
			return false;
		}
		return true;
	}

	/**
	 * Detect spots in an image
	 *
	 * @psalm-suppress UndefinedDocblockClass
	 *
	 * @param resource|GdImage|string $file
	 * @return Spot[]
	 * @throws Exception When something went wrong detecting spots
	 */
	abstract public function detect( $file ): array;

	/**
	 * Convert the passed file to a resource
	 *
	 * @param resource|GdImage|string $file
	 * @return resource|GdImage
	 * @throws Exception When provided file cannot be converted to a resource.
	 *
	 * @psalm-suppress UndefinedDocblockClass
	 * @psalm-suppress UndefinedClass
	 */
	protected function file_to_resource( $file ) {
		if ( is_resource( $file ) || $file instanceof GdImage ) {
			return $file;
		} elseif ( is_file( $file ) ) {
			/** @var string */
			$extension     = pathinfo( $file, PATHINFO_EXTENSION );
			$extension     = $this->map_file_extension( strtolower( $extension ) );
			$function_name = 'imagecreatefrom' . $extension;
			if ( ! function_exists( $function_name ) ) {
				throw new Exception( "File extension of $file is not supported for reading data" );
			}

			/** @var resource|GdImage|false */
			$result = $function_name( $file );
			if ( $result === false ) {
				throw new Exception( "Cannot load $file" );
			}

			return $result;
		} else {
			throw new Exception( 'Provided file is not a file' );
		}
	}

	/**
	 * Map file extensions for use in php image functions
	 */
	protected function map_file_extension( string $extension ): string {
		$map = array(
			'jpg' => 'jpeg',
		);
		if ( ! isset( $map[ $extension ] ) ) {
			return $extension;
		}
		return $map[ $extension ];
	}
}
