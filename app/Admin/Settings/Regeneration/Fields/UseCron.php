<?php

namespace Mentosmenno2\ImageCropPositioner\Admin\Settings\Regeneration\Fields;

use Mentosmenno2\ImageCropPositioner\Admin\Settings\BaseToggleField;

class UseCron extends BaseToggleField {

	protected const NAME = 'image_sizes_regeneration_use_cron';

	public function get_name(): string {
		return self::PREFIX . self::NAME;
	}

	public function get_label(): string {
		return __( 'Use background process', 'image-crop-positioner' );
	}

	public function get_checkbox_label(): string {
		return __( 'Use background process (cron) for regenerating image sizes.', 'image-crop-positioner' );
	}

	public function get_description(): string {
		$description = __( 'By enabling this option, image sizes will be regenerated in the background with cronjobs. Enabling this will improve performance, however image sizes are not displayed directly. It might take some time before the new sizes are generated and shown on your website.', 'image-crop-positioner' );
		if ( defined( 'DISABLE_WP_CRON' ) && constant( 'DISABLE_WP_CRON' ) ) {
			$description .= sprintf(
				'<div class="notice notice-warning inline">%s</div>',
				__( 'The default WP cron is disabled via the DISABLE_WP_CRON constant. Make sure the cron is working correctly before enabling this option.', 'image-crop-positioner' )
			);
		}
		return $description;
	}

	public function get_default_value(): bool {
		return false;
	}
}
