# Installation

## Upload zip via WP-admin (recommended)

1. Go to the [releases tab](https://github.com/mentosmenno2/image-crop-positioner/releases) and find the latest release.
2. Download the zip named `image-crop-positioner-vx.x.x.zip` (where vx.x.x is the version number).
3. Go to your WordPress website and login as administrator.
4. Go to the plugins page.
5. Click the "Add new" button.
6. Click the "Upload plugin" button.
7. Select the zip file you've just downloaded, and click on "Install now".
8. After installation, click on the "Activate plugin" button.


## Upload zip manually

1. Go to the [releases tab](https://github.com/mentosmenno2/image-crop-positioner/releases) and find the latest release.
2. Download the zip named `image-crop-positioner-vx.x.x.zip` (where vx.x.x is the version number).
3. Extract the zip file. Inside you'll find a directory called `image-crop-positioner`. Place that directory in your sites `wp-content/plugins` directory.
4. Activate the plugin via the wp-admin panel.

## Install via Composer

It's also possible to download the plugin via Packagist.
Keep in mind that if you do, your composer should be setup in a way that packages of the type `wordpress-plugin` are installed in your `wp-content/plugins` directory.

1. Prepare your `composer.json` file for plugin installations.
	```json
		"extra": {
			"installer-paths": {
				"../../mu-plugins/{$name}": [
					"type:wordpress-muplugin",
				],
				"../../plugins/{$name}": [
					"type:wordpress-plugin"
				],
			}
		}
	```
2. Use the following command to install the plugin.
	```sh
		composer require mentosmenno2/image-crop-positioner
	```
3. Activate the plugin via the wp-admin panel.
